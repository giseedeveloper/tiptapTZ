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
        return view('admin.tiptap-analysis.index', [
            'currencySymbol' => Money::symbol(),
        ]);
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

    /**
     * @param  array<string, mixed>  $validated
     * @return array{0: ?int, 1: bool}
     */
    private function resolveAnalysisScope(Request $request, array $validated): array
    {
        $overviewOnly = $request->boolean('overview');

        $restaurantId = $overviewOnly
            ? null
            : (isset($validated['restaurant_id']) ? (int) $validated['restaurant_id'] : null);

        return [$restaurantId, $overviewOnly];
    }

    public function platform(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'platform',
            'sectionTitle' => 'Platform snapshot',
            'sectionSubtitle' => 'Live revenue pulse, orders & venue health — smart anonymous overview',
        ]));
    }

    public function whatsapp(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'whatsapp',
            'sectionTitle' => 'WhatsApp bot engagement',
            'sectionSubtitle' => 'Menu taps, daily activity & option share — smart anonymous overview',
        ]));
    }

    public function qrEntry(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'qr-entry',
            'sectionTitle' => 'QR & entry points',
            'sectionSubtitle' => 'QR scans, entry share & daily trends — smart anonymous overview',
        ]));
    }

    public function journey(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'journey',
            'sectionTitle' => 'Customer journey funnel',
            'sectionSubtitle' => 'Conversion pipeline from QR scan to payment — smart anonymous funnel',
        ]));
    }

    public function feedback(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'feedback',
            'sectionTitle' => 'Feedback overview',
            'sectionSubtitle' => 'Star ratings, category breakdown & satisfaction — anonymous overview',
        ]));
    }

    public function tipsPayments(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'tips-payments',
            'sectionTitle' => 'Tips & payments',
            'sectionSubtitle' => 'Tips, payment methods & volume — anonymous overview, no names',
        ]));
    }

    public function language(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'language',
            'sectionTitle' => 'Language & peak hours',
            'sectionSubtitle' => 'Language split, peak hours & session timing — anonymous overview',
        ]));
    }

    public function venues(): View
    {
        return view('admin.tiptap-analysis.show', array_merge($this->sectionViewData(), [
            'activeSection' => 'venues',
            'sectionTitle' => 'Platform pulse',
            'sectionSubtitle' => 'Live platform totals — venues, orders, engagement & payments',
        ]));
    }

    public function snapshot(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'trend_days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId, $overviewOnly] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'snapshot' => $analysisService->platformSnapshot(
                restaurantId: $restaurantId,
                trendDays: (int) ($validated['trend_days'] ?? 30),
                overviewOnly: $overviewOnly,
            ),
            'currency_symbol' => Money::symbol(),
        ]);
    }

    public function platformPulse(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ]);

        return response()->json([
            'platform_pulse' => $analysisService->platformPulse(
                days: (int) ($validated['days'] ?? 30),
            ),
            'currency_symbol' => Money::symbol(),
        ]);
    }

    public function whatsappEngagement(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId, $overviewOnly] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'whatsapp_engagement' => $analysisService->whatsappEngagement(
                restaurantId: $restaurantId,
                days: (int) ($validated['days'] ?? 30),
            ),
        ]);
    }

    public function qrEntryPoints(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId, $overviewOnly] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'qr_entry_points' => $analysisService->qrEntryPoints(
                restaurantId: $restaurantId,
                days: (int) ($validated['days'] ?? 30),
                overviewOnly: $overviewOnly,
            ),
        ]);
    }

    public function customerJourney(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'customer_journey' => $analysisService->customerJourneyFunnel(
                restaurantId: $restaurantId,
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
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId, $overviewOnly] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'feedback_overview' => $analysisService->feedbackOverview(
                restaurantId: $restaurantId,
                days: (int) ($validated['days'] ?? 30),
                recentLimit: (int) ($validated['recent_limit'] ?? 10),
                overviewOnly: $overviewOnly,
            ),
        ]);
    }

    public function tipsAndPayments(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId, $overviewOnly] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'tips_and_payments' => $analysisService->tipsAndPayments(
                restaurantId: $restaurantId,
                days: (int) ($validated['days'] ?? 30),
                overviewOnly: $overviewOnly,
            ),
            'currency_symbol' => Money::symbol(),
        ]);
    }

    public function languageAndBehavior(Request $request, TiptapAnalysisService $analysisService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'overview' => ['sometimes', 'boolean'],
        ]);

        [$restaurantId, $overviewOnly] = $this->resolveAnalysisScope($request, $validated);

        return response()->json([
            'language_and_behavior' => $analysisService->languageAndBehavior(
                restaurantId: $restaurantId,
                days: (int) ($validated['days'] ?? 30),
                overviewOnly: $overviewOnly,
            ),
        ]);
    }
}
