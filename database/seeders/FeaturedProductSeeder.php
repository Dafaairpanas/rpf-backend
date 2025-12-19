<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class FeaturedProductSeeder extends Seeder
{
    public function run(): void
    {
        // Mark first 5 products as featured
        $products = Product::take(5)->get();

        foreach ($products as $product) {
            $product->update(['is_featured' => true]);
        }

        $count = $products->count();
        $this->command->info("Marked {$count} products as featured!");
    }
}
