<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentIntegrationRequest;
use App\Models\AdminActivityLog;
use App\Services\SelcomService;
use App\Services\SystemPaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentIntegrationController extends Controller
{
    public function __construct(
        private readonly SystemPaymentGateway $gateway,
        private readonly SelcomService $selcom,
    ) {}

    public function index(): View
    {
        return view('admin.payment-integration.index', [
            'gatewayName' => config('tiptap.payment_gateway'),
            'configured' => $this->gateway->isConfigured(),
            'values' => $this->gateway->displayValues(),
        ]);
    }

    public function update(UpdatePaymentIntegrationRequest $request): RedirectResponse
    {
        $this->gateway->persist($request->validated());

        AdminActivityLog::log(
            'payment_integration.updated',
            'system',
            0,
            null,
            ['gateway' => config('tiptap.payment_gateway')],
        );

        return back()->with('success', config('tiptap.payment_gateway').' credentials updated for all restaurants.');
    }

    public function test(): JsonResponse
    {
        if (! $this->gateway->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Save payment credentials first.',
            ], 422);
        }

        $credentials = $this->gateway->credentials();
        $testOrderId = 'TEST-'.time();

        try {
            $result = $this->selcom->checkOrderStatus($credentials, $testOrderId);

            if (isset($result['resultcode']) && in_array($result['resultcode'], ['404', '000'], true)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful. Mode: '.($credentials['is_live'] ? 'LIVE' : 'TEST'),
                ]);
            }

            if (isset($result['resultcode']) && in_array($result['resultcode'], ['401', '403'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Check API Key and Secret.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Connection successful. Mode: '.($credentials['is_live'] ? 'LIVE' : 'TEST'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ], 422);
        }
    }
}
