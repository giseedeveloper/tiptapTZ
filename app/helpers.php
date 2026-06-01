<?php

if (! function_exists('public_asset')) {
    /**
     * URL for files in /public (logo, favicon, flags). Uses relative paths when
     * APP_URL is still localhost so production deploys work before .env is fixed.
     */
    function public_asset(string $path): string
    {
        $path = ltrim($path, '/');
        $assetUrl = config('app.asset_url');

        if (is_string($assetUrl) && $assetUrl !== '') {
            return rtrim($assetUrl, '/').'/'.$path;
        }

        $appUrl = rtrim((string) config('app.url', ''), '/');

        if ($appUrl !== ''
            && ! str_contains($appUrl, 'localhost')
            && ! str_contains($appUrl, '127.0.0.1')) {
            return $appUrl.'/'.$path;
        }

        return '/'.$path;
    }
}
