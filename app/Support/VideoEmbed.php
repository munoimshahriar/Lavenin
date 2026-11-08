<?php

namespace App\Support;

class VideoEmbed
{
    /** Turn a YouTube or Facebook video link into a safe embed URL; anything else returns null. */
    public static function from(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';
        if (preg_match('~(^|\.)(facebook\.com|fb\.watch)$~', $host)) {
            return 'https://www.facebook.com/plugins/video.php?href='.rawurlencode($url).'&show_text=false';
        }

        return null;
    }
}
