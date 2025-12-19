<?php

namespace Database\Seeders;

use App\Models\Csr;
use App\Models\User;
use Illuminate\Database\Seeder;

class CsrSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        Csr::create([
            'title' => 'Tree Planting Program',
            'content' => 'We plant trees for sustainability.',
            'create_by' => $user?->id,
        ]);

        Csr::create([
            'title' => 'Local Community Support',
            'content' => 'Supporting local craftsmen.',
            'create_by' => $user?->id,
        ]);
    }
}
