<?php

namespace App\Models;

use App\Support\ImageProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'thumb_path', 'sort'];

    protected static function booted(): void
    {
        // Uploads land in products/tmp; re-encode to WebP (1200px + 400px thumb) with random names.
        static::saving(function (ProductImage $image) {
            if ($image->isDirty('path') && str_starts_with((string) $image->path, ImageProcessor::TMP_DIR.'/')) {
                $old = $image->getOriginal('path') ? [$image->getOriginal('path'), $image->getOriginal('thumb_path')] : [];
                [$image->path, $image->thumb_path] = ImageProcessor::process($image->path, 'products');
                Storage::disk('public')->delete(array_filter($old));
            }
        });

        static::deleted(function (ProductImage $image) {
            Storage::disk('public')->delete(array_filter([$image->path, $image->thumb_path]));
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function thumbUrl(): string
    {
        return Storage::disk('public')->url($this->thumb_path ?: $this->path);
    }
}
