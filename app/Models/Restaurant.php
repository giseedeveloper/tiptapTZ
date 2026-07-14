<?php

namespace App\Models;

use App\Services\SystemPaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Restaurant extends Model
{
    public const STATUS_PENDING = "pending";

    public const STATUS_APPROVED = "approved";

    public const STATUS_REJECTED = "rejected";

    public const STATUS_ACTIVE = "active";

    protected $fillable = [
        "name",
        "location",
        "phone",
        "support_phone",
        "logo",
        "menu_image",
        "menu_pdf",
        "menu_engagement_alerts_enabled",
        "menu_engagement_timeout_minutes",
        "branch_group_id",
        "branch_name",
        "branch_sort_order",
        "is_active",
        "tag_prefix",
        "selcom_vendor_id",
        "selcom_api_key",
        "selcom_api_secret",
        "selcom_is_live",
        "payout_method",
        "payout_details",
        "kitchen_token",
        "kitchen_token_generated_at",
        "approval_status",
        "approved_at",
        "approved_by",
        "rejection_reason",
        "rejected_at",
        "subscription_package_id",
        "plan_selected_at",
        "trial_ends_at",
        "tip_settings",
        "busy_mode",
        "busy_eta_multiplier",
    ];

    /**
     * Default tip configuration merged over stored tip_settings.
     *
     * @var array<string, mixed>
     */
    public const TIP_SETTINGS_DEFAULTS = [
        "categories" => [
            "waiter" => true,
            "barista" => true,
            "kitchen" => true,
        ],
        "suggestion_mode" => "percent", // percent | fixed
        "percentages" => [5, 10, 15],
        "fixed_amounts" => [500, 1000, 2000, 5000],
        "value_visible" => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
            "menu_engagement_alerts_enabled" => "boolean",
            "menu_engagement_timeout_minutes" => "integer",
            "selcom_is_live" => "boolean",
            "approved_at" => "datetime",
            "rejected_at" => "datetime",
            "plan_selected_at" => "datetime",
            "trial_ends_at" => "datetime",
            "kitchen_token_generated_at" => "datetime",
            "tip_settings" => "array",
            "busy_mode" => "boolean",
            "busy_eta_multiplier" => "float",
        ];
    }

    /**
     * Tip configuration with defaults applied.
     *
     * @return array<string, mixed>
     */
    public function tipSettings(): array
    {
        $stored = is_array($this->tip_settings) ? $this->tip_settings : [];

        $categories = array_merge(
            self::TIP_SETTINGS_DEFAULTS["categories"],
            is_array($stored["categories"] ?? null) ? $stored["categories"] : [],
        );

        $mode = in_array($stored["suggestion_mode"] ?? null, ["percent", "fixed"], true)
            ? $stored["suggestion_mode"]
            : self::TIP_SETTINGS_DEFAULTS["suggestion_mode"];

        $percentages = $this->sanitizeNumberList(
            $stored["percentages"] ?? null,
            self::TIP_SETTINGS_DEFAULTS["percentages"],
            1,
            100,
        );

        $fixed = $this->sanitizeNumberList(
            $stored["fixed_amounts"] ?? null,
            self::TIP_SETTINGS_DEFAULTS["fixed_amounts"],
            1,
            10_000_000,
        );

        return [
            "categories" => [
                "waiter" => (bool) $categories["waiter"],
                "barista" => (bool) $categories["barista"],
                "kitchen" => (bool) $categories["kitchen"],
            ],
            "suggestion_mode" => $mode,
            "percentages" => $percentages,
            "fixed_amounts" => $fixed,
            "value_visible" => array_key_exists("value_visible", $stored)
                ? (bool) $stored["value_visible"]
                : self::TIP_SETTINGS_DEFAULTS["value_visible"],
        ];
    }

    public function tipCategoryEnabled(string $category): bool
    {
        return (bool) ($this->tipSettings()["categories"][$category] ?? false);
    }

    public function tipsValueVisible(): bool
    {
        return (bool) $this->tipSettings()["value_visible"];
    }

    public function isBusy(): bool
    {
        return (bool) $this->busy_mode;
    }

    public function busyEtaMultiplier(): float
    {
        $multiplier = (float) ($this->busy_eta_multiplier ?? 1.5);

        return $multiplier >= 1.0 ? $multiplier : 1.0;
    }

    /**
     * @param  mixed  $value
     * @param  list<int>  $default
     * @return list<int>
     */
    private function sanitizeNumberList($value, array $default, int $min, int $max): array
    {
        if (! is_array($value)) {
            return $default;
        }

        $clean = [];
        foreach ($value as $item) {
            if (! is_numeric($item)) {
                continue;
            }
            $n = (int) round((float) $item);
            if ($n >= $min && $n <= $max) {
                $clean[] = $n;
            }
        }

        $clean = array_values(array_unique($clean));

        return $clean !== [] ? $clean : $default;
    }

    /**
     * Boot method - auto-generate tag_prefix on create
     */
    protected static function booted()
    {
        static::creating(function ($restaurant) {
            if (empty($restaurant->tag_prefix)) {
                $restaurant->tag_prefix = $restaurant->generateUniqueTagPrefix();
            }

            // Restaurants created directly in code (admin panel, seeders, tests) are
            // considered live. Public self-registration explicitly sets PENDING.
            if (blank($restaurant->approval_status)) {
                $restaurant->approval_status = self::STATUS_ACTIVE;
            }
        });
    }

    public function branchGroup(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranchGroup::class, "branch_group_id");
    }

    public function displayName(): string
    {
        return filled($this->branch_name) ? (string) $this->branch_name : (string) $this->name;
    }

    /**
     * Generate unique tag prefix from restaurant name
     */
    public function generateUniqueTagPrefix()
    {
        // Take first 3 letters of restaurant name, uppercase
        $name = preg_replace("/[^A-Za-z]/", "", $this->name);
        $basePrefix = strtoupper(substr($name, 0, 3));

        // If less than 3 chars, pad with X
        $basePrefix = str_pad($basePrefix, 3, "X");

        // Check if exists, if so add number
        $prefix = $basePrefix;
        $counter = 1;

        while (
            Restaurant::where("tag_prefix", $prefix)
                ->where("id", "!=", $this->id ?? 0)
                ->exists()
        ) {
            $prefix = substr($basePrefix, 0, 2) . $counter;
            $counter++;
            if ($counter > 9) {
                // If all single digits used, use random 3 chars
                $prefix = $basePrefix . chr(rand(65, 90));
            }
        }

        return $prefix;
    }

    /**
     * Get next available table tag number
     */
    public function getNextTableTagNumber()
    {
        $lastTable = Table::withoutGlobalScopes()
            ->where("restaurant_id", $this->id)
            ->whereNotNull("table_tag")
            ->orderByRaw("CAST(SUBSTRING(table_tag, -2) AS UNSIGNED) DESC")
            ->first();

        if (
            $lastTable &&
            preg_match('/(\d+)$/', $lastTable->table_tag, $matches)
        ) {
            return (int) $matches[1] + 1;
        }

        return 1;
    }

    /**
     * Get next available waiter code number
     */
    public function getNextWaiterCodeNumber()
    {
        $lastWaiter = User::where("restaurant_id", $this->id)
            ->whereNotNull("waiter_code")
            ->orderByRaw("CAST(SUBSTRING(waiter_code, -2) AS UNSIGNED) DESC")
            ->first();

        if (
            $lastWaiter &&
            preg_match('/(\d+)$/', $lastWaiter->waiter_code, $matches)
        ) {
            return (int) $matches[1] + 1;
        }

        return 1;
    }

    /**
     * Generate table tag for this restaurant
     */
    public function generateTableTag($number = null)
    {
        $number = $number ?? $this->getNextTableTagNumber();

        return $this->tag_prefix .
            "-T" .
            str_pad($number, 2, "0", STR_PAD_LEFT);
    }

    /**
     * Generate waiter code for this restaurant
     */
    public function generateWaiterCode($number = null)
    {
        $number = $number ?? $this->getNextWaiterCodeNumber();

        return $this->tag_prefix .
            "-W" .
            str_pad($number, 2, "0", STR_PAD_LEFT);
    }

    /**
     * Menu image URL for fetch (storage: storage/app/public/menu or menu_images).
     */
    public function menuImageUrl(): ?string
    {
        if (!$this->menu_image) {
            return null;
        }

        return route("storage.serve", ["path" => $this->menu_image]);
    }

    /**
     * WhatsApp menu PDF URL (storage/app/public/menu_pdfs).
     */
    public function menuPdfUrl(): ?string
    {
        if (!$this->menu_pdf) {
            return null;
        }

        return route("storage.serve", ["path" => $this->menu_pdf]);
    }

    public function menuPdfFilename(): string
    {
        if (!$this->menu_pdf) {
            return "menu.pdf";
        }

        $basename = basename($this->menu_pdf);

        return str_ends_with(strtolower($basename), ".pdf")
            ? $basename
            : $basename . ".pdf";
    }

    /**
     * Phone number shown to customers for support (WhatsApp bot). Prefers support_phone, else phone.
     */
    public function getCustomerSupportPhone(): ?string
    {
        return $this->support_phone ?? $this->phone;
    }

    /**
     * System-wide payment gateway credentials (admin → Payment Integration).
     *
     * @return array{vendor_id: ?string, api_key: ?string, api_secret: ?string, is_live: bool}
     */
    public function getSelcomCredentials(): array
    {
        return app(SystemPaymentGateway::class)->credentials();
    }

    /**
     * Whether the platform payment gateway is configured (all restaurants share this).
     */
    public function hasSelcomConfigured(): bool
    {
        return app(SystemPaymentGateway::class)->isConfigured();
    }

    public function hasPayoutProfile(): bool
    {
        return filled($this->payout_method) && filled($this->payout_details);
    }

    /**
     * Whether this restaurant may collect mobile-money payments: the platform
     * gateway must be configured AND the plan must include the capability.
     */
    public function canAcceptMobilePayments(): bool
    {
        return $this->hasSelcomConfigured() &&
            $this->planAllows(SubscriptionPackage::CAP_PAYMENTS);
    }

    public function subscriptionPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    /**
     * Whether the restaurant's plan grants a capability. Restaurants without an
     * assigned plan (admin-created / internal) get full access.
     */
    public function planAllows(string $capability): bool
    {
        $package = $this->subscriptionPackage;

        if (!$package) {
            return true;
        }

        return $package->hasCapability($capability);
    }

    /**
     * Numeric plan limit for a resource key ('tables' | 'waiters'). null = unlimited.
     */
    public function planLimit(string $key): ?int
    {
        $package = $this->subscriptionPackage;

        if (!$package) {
            return null;
        }

        return match ($key) {
            "tables" => $package->table_limit,
            "waiters" => $package->waiter_limit,
            default => null,
        };
    }

    /**
     * Whether adding one more of $key stays within the plan limit.
     */
    public function withinLimit(string $key, int $currentCount): bool
    {
        $limit = $this->planLimit($key);

        if ($limit === null) {
            return true;
        }

        return $currentCount < $limit;
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "approved_by");
    }

    public function isPending(): bool
    {
        return $this->approval_status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    public function isLiveActive(): bool
    {
        return $this->approval_status === self::STATUS_ACTIVE;
    }

    public function needsPlanSelection(): bool
    {
        return $this->isApproved() && $this->subscription_package_id === null;
    }

    public function isFullyOnboarded(): bool
    {
        return $this->isLiveActive() && $this->subscription_package_id !== null;
    }

    public function markApproved(?int $adminId = null): void
    {
        $this->forceFill([
            "approval_status" => self::STATUS_APPROVED,
            "approved_at" => now(),
            "approved_by" => $adminId,
            "rejection_reason" => null,
            "rejected_at" => null,
            "is_active" => true,
        ])->save();
    }

    public function markRejected(string $reason, ?int $adminId = null): void
    {
        $this->forceFill([
            "approval_status" => self::STATUS_REJECTED,
            "rejection_reason" => $reason,
            "rejected_at" => now(),
            "approved_by" => $adminId,
            "is_active" => false,
        ])->save();
    }

    /**
     * @param  Builder<Restaurant>  $query
     * @return Builder<Restaurant>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where("approval_status", self::STATUS_PENDING);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function waiters()
    {
        return $this->hasMany(User::class)->role("waiter");
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function tips()
    {
        return $this->hasMany(Tip::class);
    }

    public function getWhatsappQrUrlAttribute()
    {
        $botNumber = \App\Models\Setting::get(
            "whatsapp_bot_number",
            config("tiptap.default_whatsapp_bot_number"),
        );
        // Strip non-numeric characters
        $cleanNumber = preg_replace("/[^0-9]/", "", $botNumber);

        $message = "START_" . $this->id;

        return "https://wa.me/" . $cleanNumber . "?text=" . urlencode($message);
    }
}
