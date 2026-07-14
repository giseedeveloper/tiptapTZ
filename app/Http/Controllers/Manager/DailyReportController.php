<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DailyReportController extends Controller
{
    public function __construct(
        private readonly DailyReportService $dailyReports,
    ) {}

    public function index(Request $request): View
    {
        $restaurant = $this->restaurant();
        $selectedDate = Carbon::parse($request->input('date', now()->toDateString()))->startOfDay();

        $report = DailyReport::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('report_date', $selectedDate)
            ->first();

        $previewMetrics = $report?->metrics ?? $this->dailyReports->buildMetrics($restaurant, $selectedDate);

        $history = DailyReport::query()
            ->where('restaurant_id', $restaurant->id)
            ->orderByDesc('report_date')
            ->limit(30)
            ->get();

        return view('manager.reports.daily', [
            'restaurant' => $restaurant,
            'selectedDate' => $selectedDate,
            'report' => $report,
            'metrics' => $previewMetrics,
            'history' => $history,
        ]);
    }

    public function generate(Request $request): RedirectResponse|JsonResponse
    {
        $restaurant = $this->restaurant();

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'force' => ['nullable', 'boolean'],
        ]);

        $date = Carbon::parse($validated['date'] ?? now()->toDateString())->startOfDay();
        $source = $request->expectsJson() || $request->is('api/*')
            ? DailyReport::SOURCE_API
            : DailyReport::SOURCE_MANUAL;

        $report = $this->dailyReports->generate(
            $restaurant,
            $date,
            $source,
            (bool) ($validated['force'] ?? true),
        );

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Daily report generated.',
                'report' => $this->serializeReport($report),
            ]);
        }

        return redirect()
            ->route('manager.reports.daily', ['date' => $date->toDateString()])
            ->with('success', 'Daily report generated with PDF and Excel exports.');
    }

    public function show(string $date): JsonResponse
    {
        $restaurant = $this->restaurant();
        $reportDate = Carbon::parse($date)->startOfDay();

        $report = DailyReport::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('report_date', $reportDate)
            ->first();

        $metrics = $report?->metrics ?? $this->dailyReports->buildMetrics($restaurant, $reportDate);

        return response()->json([
            'date' => $reportDate->toDateString(),
            'report' => $report ? $this->serializeReport($report) : null,
            'metrics' => $metrics,
        ]);
    }

    public function download(Request $request, string $date, string $format): BinaryFileResponse|Response
    {
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        $restaurant = $this->restaurant();
        $reportDate = Carbon::parse($date)->startOfDay();

        $report = DailyReport::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('report_date', $reportDate)
            ->first();

        if (! $report || ($format === 'pdf' && ! $report->hasPdf()) || ($format === 'excel' && ! $report->hasExcel())) {
            $report = $this->dailyReports->generate(
                $restaurant,
                $reportDate,
                $request->is('api/*') ? DailyReport::SOURCE_API : DailyReport::SOURCE_MANUAL,
                true,
            );
        }

        $absolute = $this->dailyReports->absolutePath($report, $format === 'excel' ? 'excel' : 'pdf');
        $downloadName = sprintf(
            'daily-report-%s-%s.%s',
            \Illuminate\Support\Str::slug($restaurant->name),
            $reportDate->toDateString(),
            $format === 'excel' ? 'xlsx' : 'pdf',
        );

        $contentType = $format === 'excel'
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'application/pdf';

        return response()->download($absolute, $downloadName, [
            'Content-Type' => $contentType,
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $restaurant = $this->restaurant();
        $limit = min(90, max(1, (int) $request->input('limit', 30)));

        $reports = DailyReport::query()
            ->where('restaurant_id', $restaurant->id)
            ->orderByDesc('report_date')
            ->limit($limit)
            ->get()
            ->map(fn (DailyReport $report) => $this->serializeReport($report));

        return response()->json(['data' => $reports]);
    }

    private function restaurant()
    {
        $restaurant = Auth::user()?->restaurant;
        abort_unless($restaurant, 403);

        return $restaurant;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeReport(DailyReport $report): array
    {
        return [
            'id' => $report->id,
            'report_date' => $report->report_date->toDateString(),
            'generated_at' => optional($report->generated_at)?->toIso8601String(),
            'generation_source' => $report->generation_source,
            'has_pdf' => $report->hasPdf(),
            'has_excel' => $report->hasExcel(),
            'metrics' => $report->metrics,
            'download' => [
                'pdf' => url('/api/v1/manager/daily-reports/'.$report->report_date->toDateString().'/export/pdf'),
                'excel' => url('/api/v1/manager/daily-reports/'.$report->report_date->toDateString().'/export/excel'),
                'web_pdf' => route('manager.reports.daily.download', [
                    'date' => $report->report_date->toDateString(),
                    'format' => 'pdf',
                ]),
                'web_excel' => route('manager.reports.daily.download', [
                    'date' => $report->report_date->toDateString(),
                    'format' => 'excel',
                ]),
            ],
        ];
    }
}
