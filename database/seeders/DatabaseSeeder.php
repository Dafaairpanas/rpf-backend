<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            MasterCategorySeeder::class,
            DimensionSeeder::class,
            ProductSeeder::class,
            CsrSeeder::class,
            NewsSeeder::class,
            // BannerSeeder::class,
            // FeaturedProductSeeder::class,
        ]);
    }
}

