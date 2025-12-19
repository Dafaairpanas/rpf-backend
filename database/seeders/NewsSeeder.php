<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        News::create([
            'title' => 'New Product Launch',
            'content' => 'We just launched our newest collection.',
            'create_by' => $user?->id,
        ]);

        News::create([
            'title' => 'Company Milestone',
            'content' => '10 years of craftsmanship excellence.',
            'create_by' => $user?->id,
        ]);
    }
}
