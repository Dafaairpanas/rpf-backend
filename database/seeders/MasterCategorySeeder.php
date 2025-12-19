<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use Illuminate\Database\Seeder;

class MasterCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Indoor',
            'Outdoor',
        ];

        foreach ($categories as $name) {
            MasterCategory::firstOrCreate(['name' => $name]);
        }
    }
}
