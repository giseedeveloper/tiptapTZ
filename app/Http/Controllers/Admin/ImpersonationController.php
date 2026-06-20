<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Support\AdminPortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(User $user): RedirectResponse
    {
        $admin = Auth::user();
        abort_unless($admin && AdminPortalAccess::can($admin, 'admin.panel.impersonate'), 403);
        abort_unless($user->hasRole('manager'), 403, 'Only managers can be impersonated.');
        abort_if($user->hasRole('super_admin'), 403);
        abort_if(session()->has('impersonator_id'), 403, 'Stop the current impersonation session first.');

        session(['impersonator_id' => $admin->id]);

        Auth::login($user);

        AdminActivityLog::log(
            'user.impersonate.start',
            User::class,
            (int) $user->id,
            null,
            ['manager_email' => $user->email, 'restaurant_id' => $user->restaurant_id],
        );

        return redirect()
            ->route('manager.dashboard')
            ->with('info', "You are viewing the manager portal as {$user->name}. Use “Exit impersonation” to return.");
    }

    public function stop(): RedirectResponse
    {
        $impersonatorId = session('impersonator_id');
        abort_unless($impersonatorId, 403);

        $admin = User::query()->findOrFail($impersonatorId);
        abort_unless(AdminPortalAccess::isPortalUser($admin), 403);

        $impersonated = Auth::user();

        session()->forget('impersonator_id');

        Auth::login($admin);

        if ($impersonated) {
            AdminActivityLog::log(
                'user.impersonate.stop',
                User::class,
                (int) $impersonated->id,
                ['manager_email' => $impersonated->email],
                null,
            );
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Returned to admin portal.');
    }
}
