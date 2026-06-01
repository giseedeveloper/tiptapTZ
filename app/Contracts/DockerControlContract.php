<?php

namespace App\Contracts;

interface DockerControlContract
{
    /**
     * @return list<array{id: string, label: string, host_label: string, configured: bool, config_hint: ?string}>
     */
    public function stackMeta(): array;

    /**
     * @return list<array{
     *     id: string,
     *     label: string,
     *     host_label: string,
     *     configured: bool,
     *     reachable: bool,
     *     error: ?string,
     *     containers: list<array{name: string, status: string, state: string, image: string, actions: list<string>}>
     * }>
     */
    public function allStacksStatus(): array;

    /**
     * @param  'start'|'stop'|'restart'  $action
     */
    public function performAction(string $stackId, string $containerName, string $action): void;
}
