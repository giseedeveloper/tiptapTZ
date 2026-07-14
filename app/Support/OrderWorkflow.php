<?php

namespace App\Support;

/**
 * Canonical order fulfillment pipeline:
 * received → accepted → preparing → ready → served → completed
 *
 * Legacy aliases (pending/confirmed/paid) normalize to the new names.
 */
class OrderWorkflow
{
    public const RECEIVED = 'received';

    public const ACCEPTED = 'accepted';

    public const PREPARING = 'preparing';

    public const READY = 'ready';

    public const SERVED = 'served';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    /** @var list<string> */
    public const PIPELINE = [
        self::RECEIVED,
        self::ACCEPTED,
        self::PREPARING,
        self::READY,
        self::SERVED,
        self::COMPLETED,
    ];

    /** @var array<string, string> */
    public const ALIASES = [
        'pending' => self::RECEIVED,
        'confirmed' => self::ACCEPTED,
        'paid' => self::COMPLETED,
    ];

    /** @var array<string, string> */
    public const TIMESTAMP_COLUMNS = [
        self::RECEIVED => 'received_at',
        self::ACCEPTED => 'accepted_at',
        self::PREPARING => 'preparing_at',
        self::READY => 'ready_at',
        self::SERVED => 'served_at',
        self::COMPLETED => 'completed_at',
    ];

    /** @var array<string, array{label: string, color: string, next_label: string|null}> */
    public const META = [
        self::RECEIVED => ['label' => 'Received', 'color' => '#f43f5e', 'next_label' => 'Accept'],
        self::ACCEPTED => ['label' => 'Accepted', 'color' => '#8b5cf6', 'next_label' => 'Start preparing'],
        self::PREPARING => ['label' => 'Preparing', 'color' => '#f59e0b', 'next_label' => 'Mark ready'],
        self::READY => ['label' => 'Ready', 'color' => '#06b6d4', 'next_label' => 'Mark served'],
        self::SERVED => ['label' => 'Served', 'color' => '#10b981', 'next_label' => 'Complete'],
        self::COMPLETED => ['label' => 'Completed', 'color' => '#64748b', 'next_label' => null],
        self::CANCELLED => ['label' => 'Cancelled', 'color' => '#94a3b8', 'next_label' => null],
    ];

    public static function normalize(?string $status): string
    {
        $raw = strtolower(trim((string) $status));
        if ($raw === '') {
            return self::RECEIVED;
        }

        if (isset(self::ALIASES[$raw])) {
            return self::ALIASES[$raw];
        }

        if (in_array($raw, self::PIPELINE, true) || $raw === self::CANCELLED) {
            return $raw;
        }

        return $raw;
    }

    /**
     * DB values that map to a canonical stage (for WHERE IN queries).
     *
     * @return list<string>
     */
    public static function storageVariants(string $canonical): array
    {
        $canonical = self::normalize($canonical);
        $variants = [$canonical];

        foreach (self::ALIASES as $legacy => $mapped) {
            if ($mapped === $canonical) {
                $variants[] = $legacy;
            }
        }

        return array_values(array_unique($variants));
    }

    /**
     * @return list<string>
     */
    public static function liveStatuses(): array
    {
        return [
            self::RECEIVED,
            self::ACCEPTED,
            self::PREPARING,
            self::READY,
            self::SERVED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function kitchenActiveStatuses(): array
    {
        return array_values(array_unique(array_merge(
            self::storageVariants(self::RECEIVED),
            self::storageVariants(self::ACCEPTED),
            self::storageVariants(self::PREPARING),
        )));
    }

    /**
     * @return list<string>
     */
    public static function activeTableStatuses(): array
    {
        return array_values(array_unique(array_merge(
            self::storageVariants(self::RECEIVED),
            self::storageVariants(self::ACCEPTED),
            self::storageVariants(self::PREPARING),
            self::storageVariants(self::READY),
            self::storageVariants(self::SERVED),
        )));
    }

    /**
     * @return list<string>
     */
    public static function terminalStatuses(): array
    {
        return self::storageVariants(self::COMPLETED);
    }

    public static function isTerminal(?string $status): bool
    {
        return self::normalize($status) === self::COMPLETED;
    }

    public static function isBillStage(?string $status): bool
    {
        return self::normalize($status) === self::SERVED;
    }

    public static function label(?string $status): string
    {
        $key = self::normalize($status);

        return self::META[$key]['label'] ?? ucfirst((string) $status);
    }

    public static function color(?string $status): string
    {
        $key = self::normalize($status);

        return self::META[$key]['color'] ?? '#94a3b8';
    }

    public static function next(?string $status): ?string
    {
        $current = self::normalize($status);
        $index = array_search($current, self::PIPELINE, true);
        if ($index === false) {
            return null;
        }

        return self::PIPELINE[$index + 1] ?? null;
    }

    public static function timestampColumn(?string $status): ?string
    {
        $key = self::normalize($status);

        return self::TIMESTAMP_COLUMNS[$key] ?? null;
    }

    /**
     * Validation rule accepting canonical + legacy aliases + cancelled.
     */
    public static function validationRule(bool $includeCancelled = true): string
    {
        $allowed = array_merge(self::PIPELINE, array_keys(self::ALIASES));
        if ($includeCancelled) {
            $allowed[] = self::CANCELLED;
        }

        return 'in:'.implode(',', array_values(array_unique($allowed)));
    }

    /**
     * Whether moving from → to is a valid forward (or same) pipeline step.
     * Cancelled can be set from any non-terminal state.
     */
    public static function canTransition(?string $from, string $to): bool
    {
        $to = self::normalize($to);
        $from = self::normalize($from);

        if ($to === self::CANCELLED) {
            return $from !== self::COMPLETED && $from !== self::CANCELLED;
        }

        if (! in_array($to, self::PIPELINE, true)) {
            return false;
        }

        if ($from === self::CANCELLED) {
            return false;
        }

        $fromIndex = array_search($from, self::PIPELINE, true);
        $toIndex = array_search($to, self::PIPELINE, true);

        if ($fromIndex === false || $toIndex === false) {
            return $to === self::RECEIVED;
        }

        // Allow same status (noop) and forward jumps for operational flexibility.
        return $toIndex >= $fromIndex;
    }

    /**
     * Average-time thresholds (minutes) above which a stage is a bottleneck.
     *
     * @return array<string, int>
     */
    public static function bottleneckThresholds(): array
    {
        return [
            self::RECEIVED => 5,
            self::ACCEPTED => 5,
            self::PREPARING => 25,
            self::READY => 10,
            self::SERVED => 20,
        ];
    }
}
