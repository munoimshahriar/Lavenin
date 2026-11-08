<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageProcessor
{
    public const TMP_DIR = 'uploads-tmp';

    /**
     * Re-encode an uploaded image (on the public disk) to WebP: 1200px main + 400px thumb, random names.
     * Re-encoding strips metadata and anything that is not real image data.
     *
     * @return array{0: string, 1: string} [path, thumbPath]
     */
    public static function process(string $tmpPath, string $dir): array
    {
        $disk = Storage::disk('public');
        $source = $disk->path($tmpPath);
        $name = Str::random(32);

        $main = "{$dir}/{$name}.webp";
        $thumb = "{$dir}/thumbs/{$name}.webp";

        $disk->put($main, (string) Image::read($source)->scaleDown(width: 1200, height: 1200)->toWebp(82));
        $disk->put($thumb, (string) Image::read($source)->cover(400, 400)->toWebp(78));
        $disk->delete($tmpPath);

        return [$main, $thumb];
    }
}
