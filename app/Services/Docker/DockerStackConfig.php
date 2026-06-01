<?php

namespace App\Services\Docker;

readonly class DockerStackConfig
{
    /**
     * @param  list<string>  $allowedContainers
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $hostLabel,
        public string $namePrefix,
        public array $allowedContainers,
        public string $workDir,
        public ?string $sshHost,
        public string $sshUser,
        public ?string $sshKey,
    ) {}

    /**
     * @param  array<string, mixed>  $stack
     */
    public static function fromConfig(array $stack): self
    {
        return new self(
            id: (string) $stack['id'],
            label: (string) $stack['label'],
            hostLabel: (string) $stack['host_label'],
            namePrefix: (string) $stack['name_prefix'],
            allowedContainers: array_values($stack['allowed_containers'] ?? []),
            workDir: (string) ($stack['work_dir'] ?? ''),
            sshHost: filled($stack['ssh_host'] ?? null) ? (string) $stack['ssh_host'] : null,
            sshUser: (string) ($stack['ssh_user'] ?? 'root'),
            sshKey: filled($stack['ssh_key'] ?? null) ? (string) $stack['ssh_key'] : null,
        );
    }

    public function usesSsh(): bool
    {
        return $this->sshHost !== null;
    }

    public function configurationHint(): ?string
    {
        if (! config('docker.enabled')) {
            return 'Set DOCKER_CONTROL_ENABLED=true in .env.';
        }

        if ($this->usesSsh()) {
            if ($this->sshKey === null || ! is_readable($this->sshKey)) {
                return 'Set '.($this->id === 'bot' ? 'DOCKER_BOT_SSH_KEY' : 'DOCKER_LARAVEL_SSH_KEY').' to a readable private key path.';
            }

            $keyPerms = fileperms($this->sshKey) & 0777;
            if ($keyPerms > 0600) {
                return 'SSH private key permissions too open (chmod 600 required): '.$this->sshKey;
            }

            return null;
        }

        if ($this->workDir === '') {
            return 'Set DOCKER_LARAVEL_WORK_DIR to the compose project directory, or use SSH (DOCKER_LARAVEL_SSH_HOST + key).';
        }

        if (! is_dir($this->workDir)) {
            return 'Path '.$this->workDir.' does not exist in this container. For Docker Compose deployments use DOCKER_LARAVEL_WORK_DIR=/var/www/html.';
        }

        if (! is_readable('/var/run/docker.sock')) {
            return 'Mount /var/run/docker.sock into the app container (see docker-compose.yml).';
        }

        return null;
    }

    public function isConfigured(): bool
    {
        return $this->configurationHint() === null;
    }
}
