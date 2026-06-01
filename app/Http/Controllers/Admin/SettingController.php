<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminSettingsRequest;
use App\Models\AdminActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(UpdateAdminSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            $group = config("tiptap.admin_setting_groups.{$key}", 'general');

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'group' => $group]
            );
        }

        AdminActivityLog::log(
            'settings.updated',
            'system',
            0,
            null,
            ['keys' => array_keys($validated)],
        );

        return back()->with('success', 'Settings updated successfully.');
    }
}
