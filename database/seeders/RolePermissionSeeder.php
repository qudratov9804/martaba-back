<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionName::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => RoleName::SuperAdmin->value, 'guard_name' => 'web']);
        $superAdmin->syncPermissions(PermissionName::values());

        $teacher = Role::firstOrCreate(['name' => RoleName::Teacher->value, 'guard_name' => 'web']);
        $teacher->syncPermissions($this->values([
            PermissionName::CategoriesView,
            PermissionName::CoursesView,
            PermissionName::CoursesCreate,
            PermissionName::CoursesUpdate,
            PermissionName::CoursesPublish,
            PermissionName::CoursesArchive,
            PermissionName::LessonsManage,
            PermissionName::QuizzesManage,
            PermissionName::AssignmentsManage,
            PermissionName::StudentsView,
            PermissionName::CouponsView,
            PermissionName::CouponsCreate,
            PermissionName::CouponsUpdate,
            PermissionName::CertificatesView,
            PermissionName::AnalyticsView,
            PermissionName::ReviewsView,
            PermissionName::ReviewsModerate,
        ]));

        $student = Role::firstOrCreate(['name' => RoleName::Student->value, 'guard_name' => 'web']);
        $student->syncPermissions($this->values([
            PermissionName::CoursesView,
            PermissionName::CategoriesView,
            PermissionName::CertificatesView,
        ]));
    }

    /**
     * @param  list<PermissionName>  $permissions
     * @return list<string>
     */
    private function values(array $permissions): array
    {
        return array_map(fn (PermissionName $permission) => $permission->value, $permissions);
    }
}
