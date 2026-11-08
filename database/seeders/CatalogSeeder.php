<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/** Placeholder catalog: 4 categories, 8 products with generated pastel images. Replace via admin. */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::exists()) {
            return;
        }

        $cats = collect([
            ['en' => 'Rattles & Teethers', 'bn' => 'ঝুনঝুনি ও টিদার'],
            ['en' => 'Soft Toys', 'bn' => 'নরম খেলনা'],
            ['en' => 'Learning Toys', 'bn' => 'শিক্ষামূলক খেলনা'],
            ['en' => 'Bath Toys', 'bn' => 'গোসলের খেলনা'],
        ])->map(fn ($n, $i) => Category::create(['name' => $n, 'sort' => $i]));

        $products = [
            [0, 'Rainbow Ring Rattle', 'রংধনু রিং ঝুনঝুনি', 450, 550, '0-6 months', true, ['#F9D5C2', '#FF5722']],
            [0, 'Silicone Fruit Teether', 'সিলিকন ফল টিদার', 350, null, '3-12 months', false, ['#E3EFEF', '#76A9AD']],
            [1, 'Bunny Plush Friend', 'খরগোশ পুতুল বন্ধু', 890, 1050, '0+ months', true, ['#FBE7EF', '#E58FB0']],
            [1, 'Sleepy Elephant Lovey', 'ঘুমন্ত হাতি লাভি', 750, null, '0+ months', false, ['#E8E6F5', '#8C84C8']],
            [2, 'Wooden Stacking Rings', 'কাঠের স্ট্যাকিং রিং', 990, null, '12+ months', true, ['#FFF1C9', '#F2B33D']],
            [2, 'Shape Sorter Cube', 'আকৃতি মেলানো কিউব', 1250, 1450, '18+ months', true, ['#DDEFE3', '#4F8488']],
            [3, 'Floating Duck Family', 'ভাসমান হাঁস পরিবার', 420, null, '6+ months', false, ['#FFF6C2', '#F2C200']],
            [3, 'Bath Stacking Cups', 'গোসলের স্ট্যাকিং কাপ', 590, 690, '6+ months', false, ['#D7ECF7', '#3E8DBF']],
        ];

        foreach ($products as $i => [$cat, $en, $bn, $price, $was, $age, $featured, $colors]) {
            $product = Product::create([
                'category_id' => $cats[$cat]->id,
                'name' => ['en' => $en, 'bn' => $bn],
                'description' => [
                    'en' => "<p>{$en} — soft colours, rounded edges and baby-safe, BPA-free materials. Placeholder text: replace in admin.</p>",
                    'bn' => "<p>{$bn} — নরম রং, গোলাকার কিনারা এবং শিশু-নিরাপদ BPA-মুক্ত উপকরণ। নমুনা লেখা: অ্যাডমিন থেকে বদলান।</p>",
                ],
                'price' => $price,
                'compare_at_price' => $was,
                'sku' => sprintf('LV-%03d', $i + 1),
                'stock' => $i === 3 ? 3 : 25,
                'age_range' => $age,
                'is_featured' => $featured,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $this->placeholder($colors),
                'sort' => 0,
            ]);
        }
    }

    /** Generate a pastel placeholder into the upload tmp dir; ProductImage re-encodes it to WebP + thumb. */
    private function placeholder(array $colors): string
    {
        $path = \App\Support\ImageProcessor::TMP_DIR.'/'.uniqid('seed_').'.png';
        $img = Image::create(1000, 1000)->fill($colors[0]);
        $img->drawCircle(500, 520, fn ($c) => $c->radius(260)->background($colors[1]));
        $img->drawCircle(500, 520, fn ($c) => $c->radius(120)->background('#FFFFFF'));
        Storage::disk('public')->put($path, (string) $img->toPng());

        return $path;
    }
}
