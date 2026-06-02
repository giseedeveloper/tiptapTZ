<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBrandedQrService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;

class QrCodeController extends Controller
{
    public function __construct(
        private readonly WhatsAppBrandedQrService $qr,
    ) {}

    public function whatsapp(Request $request): Response
    {
        $request->validate([
            'data' => ['required', 'string', 'max:2048'],
            'size' => ['nullable', 'integer', 'min:100', 'max:2000'],
        ]);

        $size = (int) $request->integer('size', 400);

        try {
            $png = $this->qr->generate($request->string('data')->toString(), $size);
        } catch (InvalidArgumentException $e) {
            abort(400, $e->getMessage());
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }
}
