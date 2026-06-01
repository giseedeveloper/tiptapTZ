<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\DockerControlContract;
use App\Exceptions\DockerControlException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DockerContainerActionRequest;
use App\Models\AdminActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DockerController extends Controller
{
    public function index(DockerControlContract $docker): View
    {
        return view('admin.docker.index', [
            'enabled' => (bool) config('docker.enabled'),
            'market' => config('tiptap.market'),
            'pollSeconds' => (int) config('docker.poll_seconds', 15),
            'stacks' => $docker->stackMeta(),
        ]);
    }

    public function status(DockerControlContract $docker): JsonResponse
    {
        if (! config('docker.enabled')) {
            return response()->json([
                'enabled' => false,
                'stacks' => [],
                'message' => 'Docker control is disabled. Set DOCKER_CONTROL_ENABLED=true.',
            ]);
        }

        return response()->json([
            'enabled' => true,
            'stacks' => $docker->allStacksStatus(),
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }

    public function action(DockerContainerActionRequest $request, DockerControlContract $docker): JsonResponse
    {
        if (! config('docker.enabled')) {
            return response()->json(['message' => 'Docker control is disabled.'], 503);
        }

        $validated = $request->validated();

        try {
            $docker->performAction(
                $validated['stack_id'],
                $validated['container'],
                $validated['action']
            );
        } catch (DockerControlException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        AdminActivityLog::log(
            'docker.container.'.$validated['action'],
            'docker',
            0,
            null,
            [
                'stack_id' => $validated['stack_id'],
                'container' => $validated['container'],
                'action' => $validated['action'],
            ],
        );

        return response()->json([
            'message' => ucfirst($validated['action']).' sent for '.$validated['container'].'.',
            'stacks' => $docker->allStacksStatus(),
        ]);
    }
}
