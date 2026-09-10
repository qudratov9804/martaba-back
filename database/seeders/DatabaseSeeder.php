<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Organization;
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
        $this->call([
            RolePermissionSeeder::class,
            OrganizationSeeder::class,
        ]);

        $organization = Organization::where('slug', 'default')->firstOrFail();

        $admin = User::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole(RoleName::SuperAdmin->value);

        $teacher = User::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $student = User::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Student',
            'email' => 'student@example.com',
        ]);
        $student->assignRole(RoleName::Student->value);
    }
}
