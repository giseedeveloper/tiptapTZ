<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serve profile, menu and menu_images from storage (works when storage:link is missing on host).
 */
class ServeStorageController extends Controller
{
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        $path = str_replace(['../', '..'], '', $path);
        $allowed = ['profile/', 'menu/', 'menu_images/', 'menu_pdfs/', 'menu_items/', 'categories/'];
        $ok = false;
        foreach ($allowed as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $ok = true;
                break;
            }
        }
        if (! $ok || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
