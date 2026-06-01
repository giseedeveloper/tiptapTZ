<?php

use App\Contracts\DockerControlContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    config(['docker.enabled' => true]);
});

test('super admin can view docker infrastructure page', function () {
    $this->mock(DockerControlContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('stackMeta')->once()->andReturn([
            [
                'id' => 'laravel',
                'label' => 'Laravel · Tanzania',
                'host_label' => 'tiptapafrica.co.tz',
                'configured' => true,
                'config_hint' => null,
            ],
        ]);
    });

    $this->actingAs($this->admin)
        ->get(route('admin.docker.index'))
        ->assertOk()
        ->assertSee('Docker Infrastructure')
        ->assertSee('Laravel · Tanzania');
});

test('manager cannot access docker infrastructure', function () {
    $manager = User::factory()->create();
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->get(route('admin.docker.index'))
        ->assertForbidden();
});

test('docker status json returns stacks', function () {
    $this->mock(DockerControlContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('allStacksStatus')->once()->andReturn([
            [
                'id' => 'laravel',
                'label' => 'Laravel · Tanzania',
                'host_label' => 'tiptapafrica.co.tz',
                'configured' => true,
                'reachable' => true,
                'error' => null,
                'containers' => [
                    [
                        'name' => 'tiptap_tz_app',
                        'status' => 'Up 1 hour',
                        'state' => 'running',
                        'image' => 'tiptap-app',
                        'actions' => ['restart', 'stop'],
                    ],
                ],
            ],
        ]);
    });

    $this->actingAs($this->admin)
        ->getJson(route('admin.docker.status'))
        ->assertOk()
        ->assertJsonPath('stacks.0.containers.0.name', 'tiptap_tz_app');
});

test('docker container action delegates to service', function () {
    $this->mock(DockerControlContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('performAction')
            ->once()
            ->with('laravel', 'tiptap_tz_queue', 'restart');

        $mock->shouldReceive('allStacksStatus')->once()->andReturn([]);
    });

    $this->actingAs($this->admin)
        ->postJson(route('admin.docker.action'), [
            'stack_id' => 'laravel',
            'container' => 'tiptap_tz_queue',
            'action' => 'restart',
        ])
        ->assertOk();
});

test('docker config exposes laravel and bot stacks for this app', function () {
    $stacks = config('docker.stacks');

    expect($stacks)->toHaveCount(2)
        ->and(collect($stacks)->pluck('id')->all())->toBe(['laravel', 'bot']);
});
