<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Default Organization',
                'email' => 'admin@example.com',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'language' => 'en',
            ]
        );
    }
}
