<?php

namespace App\Services\Docker;

use App\Contracts\DockerControlContract;
use App\Exceptions\DockerControlException;
use Illuminate\Support\Str;

class DockerControlService implements DockerControlContract
{
    public function __construct(
        private readonly DockerProcessRunner $runner,
    ) {}

    public function stackMeta(): array
    {
        return array_map(function (DockerStackConfig $stack): array {
            return [
                'id' => $stack->id,
                'label' => $stack->label,
                'host_label' => $stack->hostLabel,
                'configured' => $stack->isConfigured(),
                'config_hint' => $stack->configurationHint(),
            ];
        }, $this->stacks());
    }

    public function allStacksStatus(): array
    {
        $result = [];

        foreach ($this->stacks() as $stack) {
            $entry = [
                'id' => $stack->id,
                'label' => $stack->label,
                'host_label' => $stack->hostLabel,
                'configured' => $stack->isConfigured(),
                'reachable' => false,
                'error' => $stack->configurationHint(),
                'containers' => [],
            ];

            if (! $stack->isConfigured()) {
                $result[] = $entry;

                continue;
            }

            try {
                $entry['containers'] = $this->listContainers($stack);
                $entry['reachable'] = true;
                $entry['error'] = null;
            } catch (DockerControlException $e) {
                $entry['error'] = $e->getMessage();
            }

            $result[] = $entry;
        }

        return $result;
    }

    public function performAction(string $stackId, string $containerName, string $action): void
    {
        $stack = $this->findStack($stackId);
        $this->assertContainerAllowed($stack, $containerName);

        $allowedActions = ['start', 'stop', 'restart'];

        if (! in_array($action, $allowedActions, true)) {
            throw new DockerControlException('Invalid container action.');
        }

        $this->runner->run(
            $stack,
            escapeshellarg($action).' '.escapeshellarg($containerName)
        );
    }

    /**
     * @return list<DockerStackConfig>
     */
    private function stacks(): array
    {
        return array_map(
            fn (array $stack): DockerStackConfig => DockerStackConfig::fromConfig($stack),
            config('docker.stacks', [])
        );
    }

    private function findStack(string $stackId): DockerStackConfig
    {
        foreach ($this->stacks() as $stack) {
            if ($stack->id === $stackId) {
                return $stack;
            }
        }

        throw new DockerControlException('Unknown docker stack.');
    }

    /**
     * @return list<array{name: string, status: string, state: string, image: string, actions: list<string>}>
     */
    private function listContainers(DockerStackConfig $stack): array
    {
        $filter = escapeshellarg('name='.$stack->namePrefix);
        $output = $this->runner->run(
            $stack,
            'ps -a --filter '.$filter.' --format '.escapeshellarg('{{.Names}}|{{.Status}}|{{.State}}|{{.Image}}')
        );

        if ($output === '') {
            return [];
        }

        $containers = [];

        foreach (explode("\n", $output) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line, 4);

            if (count($parts) < 4) {
                continue;
            }

            [$name, $status, $state, $image] = $parts;

            if (! $this->containerIsAllowed($stack, $name)) {
                continue;
            }

            $state = strtolower($state);

            $containers[] = [
                'name' => $name,
                'status' => $status,
                'state' => $state,
                'image' => $image,
                'actions' => $this->actionsForState($state),
            ];
        }

        usort($containers, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $containers;
    }

    private function assertContainerAllowed(DockerStackConfig $stack, string $containerName): void
    {
        if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/', $containerName)) {
            throw new DockerControlException('Invalid container name.');
        }

        if (! $this->containerIsAllowed($stack, $containerName)) {
            throw new DockerControlException('Container is not allowed for this stack.');
        }
    }

    private function containerIsAllowed(DockerStackConfig $stack, string $containerName): bool
    {
        if (! Str::startsWith($containerName, $stack->namePrefix)) {
            return false;
        }

        if ($stack->allowedContainers === []) {
            return true;
        }

        foreach ($stack->allowedContainers as $allowed) {
            if ($allowed === $containerName || Str::contains($containerName, $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function actionsForState(string $state): array
    {
        return match ($state) {
            'running' => ['restart', 'stop'],
            'exited', 'created', 'dead' => ['start'],
            'paused' => ['restart', 'stop', 'start'],
            default => ['restart'],
        };
    }
}
