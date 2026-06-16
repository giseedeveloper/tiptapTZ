<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLandingPageRequest;
use App\Models\AdminActivityLog;
use App\Models\Setting;
use App\Support\LandingPageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        return view('admin.landing-page.index', [
            'content' => LandingPageContent::forAdmin(),
            'sections' => config('tiptap.landing.sections', []),
        ]);
    }

    public function update(UpdateLandingPageRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Setting::set(
                LandingPageContent::storageKey($key),
                filled($value) ? (string) $value : null,
                'landing'
            );
        }

        AdminActivityLog::log(
            'landing_page.updated',
            'system',
            0,
            null,
            ['keys' => array_keys($validated)],
        );

        return back()->with('success', 'Landing page updated successfully.');
    }
}
