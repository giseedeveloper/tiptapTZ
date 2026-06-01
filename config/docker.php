<?php

$market = env('TIPTAP_MARKET', 'tz');

$stacksTz = [
    [
        'id' => 'laravel',
        'label' => 'Laravel · Tanzania',
        'host_label' => env('DOCKER_LARAVEL_HOST_LABEL', 'tiptapafrica.co.tz'),
        'name_prefix' => 'tiptap_tz_',
        'allowed_containers' => [
            'tiptap_tz_nginx',
            'tiptap_tz_app',
            'tiptap_tz_queue',
            'tiptap_tz_mysql',
            'tiptap_tz_phpmyadmin',
            'tiptap_tz_certbot',
        ],
        'work_dir' => env('DOCKER_LARAVEL_WORK_DIR', '/root/TIPTAP'),
        'ssh_host' => env('DOCKER_LARAVEL_SSH_HOST'),
        'ssh_user' => env('DOCKER_LARAVEL_SSH_USER', 'root'),
        'ssh_key' => env('DOCKER_LARAVEL_SSH_KEY'),
    ],
    [
        'id' => 'bot',
        'label' => 'WhatsApp Bot · Tanzania',
        'host_label' => env('DOCKER_BOT_HOST_LABEL', 'wa-notify.tiptapafrica.co.tz'),
        'name_prefix' => env('DOCKER_BOT_NAME_PREFIX', 'tiptop'),
        'allowed_containers' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DOCKER_BOT_ALLOWED_CONTAINERS', 'tiptopbot,bot'))
        ))),
        'work_dir' => env('DOCKER_BOT_WORK_DIR', '/opt/titap-bot/tiptopbot'),
        'ssh_host' => env('DOCKER_BOT_SSH_HOST'),
        'ssh_user' => env('DOCKER_BOT_SSH_USER', 'root'),
        'ssh_key' => env('DOCKER_BOT_SSH_KEY'),
    ],
];

$stacksZa = [
    [
        'id' => 'laravel',
        'label' => 'Laravel · South Africa',
        'host_label' => env('DOCKER_LARAVEL_HOST_LABEL', 'tiptapafrica.co.za'),
        'name_prefix' => 'tiptap_',
        'allowed_containers' => [
            'tiptap_app',
            'tiptap_queue',
            'tiptap_nginx',
            'tiptap_mysql',
            'tiptap_phpmyadmin',
            'tiptap_certbot',
        ],
        'work_dir' => env('DOCKER_LARAVEL_WORK_DIR', '/root/TIPTAP'),
        'ssh_host' => env('DOCKER_LARAVEL_SSH_HOST'),
        'ssh_user' => env('DOCKER_LARAVEL_SSH_USER', 'root'),
        'ssh_key' => env('DOCKER_LARAVEL_SSH_KEY'),
    ],
    [
        'id' => 'bot',
        'label' => 'WhatsApp Bot · South Africa',
        'host_label' => env('DOCKER_BOT_HOST_LABEL', 'wbot.tiptapafrica.co.za'),
        'name_prefix' => env('DOCKER_BOT_NAME_PREFIX', 'tiptop'),
        'allowed_containers' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DOCKER_BOT_ALLOWED_CONTAINERS', 'tiptopbot,bot'))
        ))),
        'work_dir' => env('DOCKER_BOT_WORK_DIR', '/opt/tiptap-sauth-bot/tiptopbot'),
        'ssh_host' => env('DOCKER_BOT_SSH_HOST'),
        'ssh_user' => env('DOCKER_BOT_SSH_USER', 'root'),
        'ssh_key' => env('DOCKER_BOT_SSH_KEY'),
    ],
];

return [

    'enabled' => (bool) env('DOCKER_CONTROL_ENABLED', false),

    'poll_seconds' => (int) env('DOCKER_CONTROL_POLL_SECONDS', 15),

    'timeout' => (int) env('DOCKER_CONTROL_TIMEOUT', 45),

    'docker_binary' => env('DOCKER_BINARY', 'docker'),

    'stacks' => $market === 'za' ? $stacksZa : $stacksTz,

];
