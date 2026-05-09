<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BillImageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BillImageController extends Controller
{
    public function __invoke(Request $request, BillImageService $billImageService, int $orderId, ?string $signature = null): Response
    {
        $order = Order::withoutGlobalScopes()
            ->with(['restaurant', 'items'])
            ->findOrFail($orderId);

        $provided = $signature ?? $request->query('signature');
        if ($provided === null || $provided === '') {
            abort(404);
        }

        if (! hash_equals($order->billImageSignature(), (string) $provided)) {
            abort(403);
        }

        return response($billImageService->renderPng($order), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
