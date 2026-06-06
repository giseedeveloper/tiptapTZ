<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletStatementExporter
{
    public function __construct(
        private readonly RestaurantWalletService $wallet,
    ) {}

    public function stream(Restaurant $restaurant, ?Carbon $start = null, ?Carbon $end = null): StreamedResponse
    {
        $summary = $this->wallet->summary($restaurant);
        $breakdown = $this->wallet->paymentBreakdown($restaurant);

        $paymentsQuery = Payment::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['paid', 'completed'])
            ->with('order')
            ->latest();

        $withdrawalsQuery = Withdrawal::query()
            ->where('restaurant_id', $restaurant->id)
            ->latest();

        if ($start && $end) {
            $paymentsQuery->whereBetween('created_at', [$start, $end]);
            $withdrawalsQuery->whereBetween('created_at', [$start, $end]);
        }

        $payments = $paymentsQuery->get();
        $withdrawals = $withdrawalsQuery->get();

        $symbol = config('tiptap.currency_symbol');
        $periodLabel = ($start && $end)
            ? $start->format('Y-m-d').' to '.$end->format('Y-m-d')
            : 'All time';

        $filename = 'wallet_statement_'.$restaurant->id.'_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($restaurant, $summary, $breakdown, $payments, $withdrawals, $symbol, $periodLabel): void {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['TIPTAP Wallet Statement']);
            fputcsv($file, ['Restaurant', $restaurant->name]);
            fputcsv($file, ['Period', $periodLabel]);
            fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Metric', 'Amount ('.$symbol.')']);
            fputcsv($file, ['Total received (gross)', number_format($summary['total_earned'], 2, '.', '')]);
            fputcsv($file, ['Platform fee ('.number_format($summary['commission_rate'], 2, '.', '').'%)', number_format($summary['platform_commission'], 2, '.', '')]);
            fputcsv($file, ['Net earnings', number_format($summary['net_earned'], 2, '.', '')]);
            fputcsv($file, ['Withdrawn', number_format($summary['total_withdrawn'], 2, '.', '')]);
            fputcsv($file, ['Pending withdrawals', number_format($summary['pending_withdrawals'], 2, '.', '')]);
            fputcsv($file, ['Available balance', number_format($summary['available_balance'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['BREAKDOWN BY TYPE']);
            fputcsv($file, ['Type', 'Count', 'Total ('.$symbol.')']);
            foreach ($breakdown['by_type'] as $row) {
                fputcsv($file, [$row['type'], $row['count'], number_format($row['total'], 2, '.', '')]);
            }
            fputcsv($file, []);

            fputcsv($file, ['BREAKDOWN BY METHOD']);
            fputcsv($file, ['Method', 'Count', 'Total ('.$symbol.')']);
            foreach ($breakdown['by_method'] as $row) {
                fputcsv($file, [$row['method'], $row['count'], number_format($row['total'], 2, '.', '')]);
            }
            fputcsv($file, []);

            fputcsv($file, ['PAYMENTS']);
            fputcsv($file, ['Date', 'Order ID', 'Type', 'Method', 'Amount ('.$symbol.')', 'Reference', 'Status']);
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->order_id ?? '',
                    $payment->payment_type ?? 'order',
                    $payment->method ?? '',
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->transaction_reference ?? '',
                    $payment->status,
                ]);
            }
            fputcsv($file, []);

            fputcsv($file, ['WITHDRAWALS']);
            fputcsv($file, ['Date', 'Amount ('.$symbol.')', 'Status', 'Method', 'Details', 'Admin note']);
            foreach ($withdrawals as $withdrawal) {
                fputcsv($file, [
                    $withdrawal->created_at->format('Y-m-d H:i'),
                    number_format((float) $withdrawal->amount, 2, '.', ''),
                    $withdrawal->status,
                    $withdrawal->payment_method ?? '',
                    $withdrawal->payment_details ?? '',
                    $withdrawal->admin_note ?? '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
