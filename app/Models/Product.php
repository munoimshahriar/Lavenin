<?php

namespace App\Models;

use App\Support\Slug;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\SearchesTranslatedName;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations, SearchesTranslatedName;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'category_id', 'slug', 'name', 'description', 'price', 'compare_at_price', 'sku',
        'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'video_url', 'age_range',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = Slug::unique(static::class, $product->getTranslation('name', 'en', false) ?: $product->getTranslation('name', 'bn', false));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function firstImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price && $this->compare_at_price > $this->price;
    }

    public function thumbUrl(): ?string
    {
        $img = $this->relationLoaded('firstImage') ? $this->firstImage : $this->images->first();

        return $img ? Storage::disk('public')->url($img->thumb_path ?: $img->path) : null;
    }

    public function videoEmbedUrl(): ?string
    {
        return VideoEmbed::from($this->video_url);
    }
}
