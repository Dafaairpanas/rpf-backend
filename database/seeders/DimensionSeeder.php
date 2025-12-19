<?php

namespace Database\Seeders;

use App\Models\Dimension;
use Illuminate\Database\Seeder;

class DimensionSeeder extends Seeder
{
    public function run(): void
    {
        Dimension::create(['width' => 40, 'height' => 80, 'depth' => 40]);
        Dimension::create(['width' => 60, 'height' => 75, 'depth' => 60]);
        Dimension::create(['width' => 120, 'height' => 75, 'depth' => 80]);
    }
}
