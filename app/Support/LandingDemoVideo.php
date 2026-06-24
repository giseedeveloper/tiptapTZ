<?php

namespace App\Support;

class LandingDemoVideo
{
    /**
     * @return array{provider: string, embed_url: string, poster: string|null}|null
     */
    public static function embed(?string $url): ?array
    {
        if (! filled($url)) {
            return null;
        }

        $url = trim($url);

        if ($youtubeId = self::youtubeId($url)) {
            return [
                'provider' => 'youtube',
                'embed_url' => 'https://www.youtube-nocookie.com/embed/'.$youtubeId.'?rel=0&modestbranding=1',
                'poster' => 'https://i.ytimg.com/vi/'.$youtubeId.'/hqdefault.jpg',
            ];
        }

        if ($vimeoId = self::vimeoId($url)) {
            return [
                'provider' => 'vimeo',
                'embed_url' => 'https://player.vimeo.com/video/'.$vimeoId.'?dnt=1',
                'poster' => null,
            ];
        }

        if (self::isDirectVideo($url)) {
            return [
                'provider' => 'file',
                'embed_url' => $url,
                'poster' => null,
            ];
        }

        return null;
    }

    protected static function youtubeId(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected static function vimeoId(string $url): ?string
    {
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected static function isDirectVideo(string $url): bool
    {
        return (bool) preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url);
    }
}
