<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLandingLeadRequest;
use App\Models\LandingLead;
use Illuminate\Http\RedirectResponse;

class LandingLeadController extends Controller
{
    public function store(StoreLandingLeadRequest $request): RedirectResponse
    {
        LandingLead::query()->updateOrCreate(
            [
                'email' => $request->string('email')->lower()->toString(),
                'market' => (string) config('tiptap.market', 'tz'),
            ],
            [
                'source' => 'efficiency_guide',
                'ip_address' => $request->ip(),
            ]
        );

        return redirect()
            ->to(url('/').'#lead-magnet')
            ->with('lead_magnet_success', true);
    }
}
