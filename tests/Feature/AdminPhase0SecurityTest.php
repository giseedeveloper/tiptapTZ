<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

function validAdminSettingsPayload(): array
{
    return [
        'system_name' => 'TIPTAP TZ',
        'support_email' => 'support@tiptap.co.tz',
        'commission_rate' => '5',
        'min_withdrawal' => '50000',
        'demo_push' => '0',
        'whatsapp_bot_number' => '255794321510',
        'webhook_secret' => 'whsec_test',
    ];
}

test('debug selcom route is removed', function () {
    $this->get('/test-selcom')->assertNotFound();
});

test('public fix-storage route is removed', function () {
    $this->get('/fix-storage')->assertNotFound();
});

test('fix-storage requires super admin', function () {
    $this->post(route('admin.fix-storage'))->assertRedirect(route('login'));

    $this->actingAs($this->admin)
        ->post(route('admin.fix-storage'))
        ->assertRedirect(route('admin.dashboard'));
});

test('settings update rejects unknown keys', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.settings.update'), [
            ...validAdminSettingsPayload(),
            'is_super_hacker' => 'yes',
        ])
        ->assertSessionHasNoErrors();

    expect(Setting::where('key', 'is_super_hacker')->exists())->toBeFalse();
    expect(Setting::where('key', 'system_name')->value('value'))->toBe('TIPTAP TZ');
});

test('bot index does not expose configured token from config', function () {
    Config::set('services.bot.token', '2|plaintext-token-must-not-appear-in-html');

    $this->actingAs($this->admin)
        ->get(route('admin.bots.index'))
        ->assertOk()
        ->assertDontSee('plaintext-token-must-not-appear-in-html', false)
        ->assertSee('Bot API token configured');
});

test('generate bot token flashes once and does not write laravel env file', function () {
    $envPath = base_path('.env');
    $before = file_exists($envPath) ? file_get_contents($envPath) : '';

    $this->actingAs($this->admin)
        ->post(route('admin.bots.generate-token'))
        ->assertRedirect(route('admin.bots.index'))
        ->assertSessionHas('bot_token_plaintext');

    $token = session('bot_token_plaintext');
    expect($token)->not->toBeEmpty();

    $after = file_exists($envPath) ? file_get_contents($envPath) : '';
    expect($after)->toBe($before);

    expect(User::where('email', 'bot@taptap.com')->exists())->toBeTrue();
});
