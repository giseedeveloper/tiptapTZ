<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBotEndpointRequest;
use App\Models\AdminActivityLog;
use App\Models\Bot;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BotController extends Controller
{
    public function index(): View
    {
        $bots = Bot::all();
        $botTokenConfigured = filled(config('services.bot.token'));
        $newBotToken = session('bot_token_plaintext');

        return view('admin.bots.index', compact('bots', 'botTokenConfigured', 'newBotToken'));
    }

    public function updateEndpoint(UpdateBotEndpointRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $bot = Bot::findOrFail($validated['bot_id']);
        $bot->update(['endpoint' => $validated['endpoint']]);

        return back()->with('success', 'Bot endpoint updated successfully.');
    }

    public function generateToken(Request $request): RedirectResponse
    {
        $user = User::firstOrCreate(
            ['email' => 'bot@taptap.com'],
            [
                'name' => 'WhatsApp Bot Service',
                'password' => Hash::make(Str::random(32)),
            ]
        );

        if (! $user->hasRole('bot_service')) {
            $user->assignRole('bot_service');
        }

        $user->tokens()->delete();
        $token = $user->createToken('WhatsAppBotToken')->plainTextToken;

        AdminActivityLog::log(
            'bot_token.generated',
            User::class,
            (int) $user->id,
            null,
            ['note' => 'New bot API token generated; copy to bot BOT_TOKEN env.'],
        );

        return redirect()
            ->route('admin.bots.index')
            ->with('success', 'New bot token generated. Copy it now — it will not be shown again.')
            ->with('bot_token_plaintext', $token);
    }
}
