<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\TiptapAnalysisService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TiptapAnalysisController extends Controller
{
    public function index(): View
    {
        return view('admin.tiptap-analysis.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function sectionViewData(): array
    {
        return [
            'restaurants' => Restaurant::query()->orderBy('name')->get(['id', 'name', 'is_active']),
            'currencySymbol' => Money::symbol(),
            'currencyCode' => config('tiptap.currency_code'),
        ];
    }

    public function platform(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'platform',
            'sectionTitle' => 'Platform snapshot',
            'sectionSubtitle' => 'Orders, revenue trend & top venues',
        ]));
    }

    public function whatsapp(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'whatsapp',
            'sectionTitle' => 'WhatsApp bot engagement',
            'sectionSubtitle' => 'Which menu options customers use most',
        ]));
    }

    public function qrEntry(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'qr-entry',
            'sectionTitle' => 'QR & entry points',
            'sectionSubtitle' => 'Waiter QR vs table QR vs restaurant tag',
        ]));
    }

    public function journey(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'journey',
            'sectionTitle' => 'Customer journey funnel',
            'sectionSubtitle' => 'Track each step from QR scan to paid',
        ]));
    }

    public function feedback(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'feedback',
            'sectionTitle' => 'Feedback overview',
            'sectionSubtitle' => 'Ratings, alerts & recent comments',
        ]));
    }

    public function tipsPayments(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'tips-payments',
            'sectionTitle' => 'Tips & payments',
            'sectionSubtitle' => 'Tips collected, payment methods & bill vs quick pay',
        ]));
    }

    public function language(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'language',
            'sectionTitle' => 'Language & peak hours',
            'sectionSubtitle' => 'Customer language preference & busiest bot hours',
        ]));
    }

    public function venues(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'venues',
            'sectionTitle' => 'Venue comparison',
            'sectionSubtitle' => 'All key metrics per restaurant',
        ]));
    }

    public function snapshot(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'trend_days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'snapshot' => $analysisService->platformSnapshot(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                trendDays: (int) ($validated['trend_days'] ?? 30),
            ),
            'currency_symbol' => Money::symbol(),
        ]);
    }

    public function whatsappEngagement(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'whatsapp_engagement' => $analysisService->whatsappEngagement(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                days: (int) ($validated['days'] ?? 30),
            ),
        ]);
    }

    public function qrEntryPoints(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'qr_entry_points' => $analysisService->qrEntryPoints(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                days: (int) ($validated['days'] ?? 30),
            ),
        ]);
    }

    public function customerJourney(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'customer_journey' => $analysisService->customerJourneyFunnel(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                days: (int) ($validated['days'] ?? 30),
            ),
        ]);
    }

    public function feedbackOverview(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'recent_limit' => ['nullable', 'integer', 'min:5', 'max:25'],
        ]);

        return response()->json([
            'feedback_overview' => $analysisService->feedbackOverview(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                days: (int) ($validated['days'] ?? 30),
                recentLimit: (int) ($validated['recent_limit'] ?? 10),
            ),
        ]);
    }

    public function tipsAndPayments(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'tips_and_payments' => $analysisService->tipsAndPayments(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                days: (int) ($validated['days'] ?? 30),
            ),
            'currency_symbol' => Money::symbol(),
        ]);
    }

    public function languageAndBehavior(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'language_and_behavior' => $analysisService->languageAndBehavior(
                restaurantId: isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null,
                days: (int) ($validated['days'] ?? 30),
            ),
        ]);
    }
}
