<?php

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

    $this->restaurant = Restaurant::create([
        'name' => 'PDF Bistro',
        'location' => 'Dar',
        'is_active' => true,
    ]);
    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->botUser = User::factory()->create(['email' => 'bot-menu-pdf@test']);
    Role::firstOrCreate(['name' => 'bot_service', 'guard_name' => 'web']);
    $this->botUser->assignRole('bot_service');
});

it('allows manager to upload menu pdf', function () {
    $file = UploadedFile::fake()->create('menu.pdf', 500, 'application/pdf');

    $this->actingAs($this->manager)
        ->post(route('manager.menu-pdf.store'), ['menu_pdf' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->restaurant->refresh();

    expect($this->restaurant->menu_pdf)->not->toBeNull();
    Storage::disk('public')->assertExists($this->restaurant->menu_pdf);
});

it('returns menu pdf url from bot api', function () {
    Sanctum::actingAs($this->botUser);

    $path = 'menu_pdfs/test-menu.pdf';
    Storage::disk('public')->put($path, '%PDF-1.4 fake');
    $this->restaurant->update(['menu_pdf' => $path]);

    $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/menu-pdf")
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.menu_pdf_url', $this->restaurant->menuPdfUrl())
        ->assertJsonPath('data.filename', 'test-menu.pdf');
});

it('rejects non pdf upload', function () {
    $file = UploadedFile::fake()->image('menu.jpg');

    $this->actingAs($this->manager)
        ->post(route('manager.menu-pdf.store'), ['menu_pdf' => $file])
        ->assertSessionHasErrors('menu_pdf');
});
