<?php

use App\Models\Bot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->botUser = User::factory()->create([
        'email' => 'bot-branding@taptap.test',
    ]);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');

    Sanctum::actingAs($this->botUser);
});

test('branding returns default welcome image and title', function (): void {
    $response = $this->getJson('/api/bot/branding');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'TipTap')
        ->assertJsonPath('data.body', null);

    expect($response->json('data.image_url'))->toContain('icon-512.png');
});

test('branding uses bot settings when configured', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('bot/welcome/test.png', 'fake');

    Bot::query()->create([
        'name' => 'Main Bot',
        'status' => 'active',
        'settings' => [
            'welcome_title' => 'TIPTAP Africa',
            'welcome_body' => 'Order, pay, and tip from WhatsApp.',
            'welcome_image_path' => 'bot/welcome/test.png',
        ],
    ]);

    $response = $this->getJson('/api/bot/branding');

    $response->assertOk()
        ->assertJsonPath('data.title', 'TIPTAP Africa')
        ->assertJsonPath('data.body', 'Order, pay, and tip from WhatsApp.');

    expect($response->json('data.image_url'))->toContain('bot/welcome/test.png');
});
