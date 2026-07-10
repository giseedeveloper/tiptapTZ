<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\MenuEngagementSession;
use App\Notifications\CustomerMenuEngagementNotification;
use App\Services\MenuEngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MenuEngagementController extends Controller
{
    public function __construct(
        private readonly MenuEngagementService $menuEngagement,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $restaurant = $user->restaurant;

        abort_unless($restaurant, 403);

        $timeout = max(5, min(60, (int) ($restaurant->menu_engagement_timeout_minutes ?? 10)));

        $activeAlerts = MenuEngagementSession::query()
            ->with('table')
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                MenuEngagementSession::STATUS_PENDING,
                MenuEngagementSession::STATUS_NOTIFIED,
            ])
            ->latest('menu_viewed_at')
            ->get()
            ->map(function (MenuEngagementSession $session) use ($timeout, $restaurant) {
                $elapsed = $session->minutesSinceView();
                $isOverdue = $session->status === MenuEngagementSession::STATUS_NOTIFIED
                    || $elapsed >= $timeout;

                return [
                    'id' => $session->id,
                    'table' => $session->resolvedTableLabel(),
                    'status' => $session->status,
                    'menu_viewed_at' => $session->menu_viewed_at?->toIso8601String(),
                    'notified_at' => $session->notified_at?->toIso8601String(),
                    'elapsed_minutes' => $elapsed,
                    'is_overdue' => $isOverdue,
                    'message' => $isOverdue
                        ? $session->alertMessage($timeout)
                        : 'Customer viewed menu · Table '.$session->resolvedTableLabel().' · '.$elapsed.' min ago',
                ];
            });

        $history = MenuEngagementSession::query()
            ->with('table')
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                MenuEngagementSession::STATUS_CONVERTED,
                MenuEngagementSession::STATUS_NOTIFIED,
                MenuEngagementSession::STATUS_DISMISSED,
            ])
            ->latest('menu_viewed_at')
            ->paginate(20);

        $unreadNotifications = $user->unreadNotifications()
            ->where('type', CustomerMenuEngagementNotification::class)
            ->count();

        return view('manager.menu-engagement.index', [
            'restaurant' => $restaurant,
            'stats' => $this->menuEngagement->statsForRestaurant($restaurant->id),
            'activeAlerts' => $activeAlerts,
            'history' => $history,
            'timeoutMinutes' => $timeout,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function alerts(): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurant;

        abort_unless($restaurant, 403);

        $timeout = max(5, min(60, (int) ($restaurant->menu_engagement_timeout_minutes ?? 10)));

        $sessions = MenuEngagementSession::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                MenuEngagementSession::STATUS_PENDING,
                MenuEngagementSession::STATUS_NOTIFIED,
            ])
            ->latest('menu_viewed_at')
            ->limit(30)
            ->get()
            ->map(fn (MenuEngagementSession $session) => [
                'id' => $session->id,
                'table' => $session->resolvedTableLabel(),
                'status' => $session->status,
                'elapsed_minutes' => $session->minutesSinceView(),
                'is_overdue' => $session->status === MenuEngagementSession::STATUS_NOTIFIED
                    || $session->minutesSinceView() >= $timeout,
                'message' => $session->alertMessage($timeout),
                'menu_viewed_at' => $session->menu_viewed_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'stats' => $this->menuEngagement->statsForRestaurant($restaurant->id),
            'alerts' => $sessions,
            'unread_notifications' => $user->unreadNotifications()
                ->where('type', CustomerMenuEngagementNotification::class)
                ->count(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        $validated = $request->validate([
            'menu_engagement_alerts_enabled' => ['nullable', 'boolean'],
            'menu_engagement_timeout_minutes' => ['required', 'integer', 'min:5', 'max:60'],
        ]);

        $restaurant->update([
            'menu_engagement_alerts_enabled' => $request->boolean('menu_engagement_alerts_enabled'),
            'menu_engagement_timeout_minutes' => (int) $validated['menu_engagement_timeout_minutes'],
        ]);

        return back()->with('success', 'Customer engagement alert settings saved.');
    }

    public function dismiss(MenuEngagementSession $session): RedirectResponse
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant && (int) $session->restaurant_id === (int) $restaurant->id, 403);

        $this->menuEngagement->dismissSession($session);

        return back()->with('success', 'Alert dismissed.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        Auth::user()
            ->unreadNotifications()
            ->where('type', CustomerMenuEngagementNotification::class)
            ->update(['read_at' => now()]);

        return back()->with('success', 'Engagement notifications marked as read.');
    }
}
