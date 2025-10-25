<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LookupTableSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '628111111111',
            'company' => 'Demo Labs',
            'provider' => 'local',
            'subscription_tier' => 'free',
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);
    }
}
