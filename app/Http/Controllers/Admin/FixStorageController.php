<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class FixStorageController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        Artisan::call('storage:link');

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Storage link created successfully.');
    }
}
