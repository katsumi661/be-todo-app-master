<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'create_task',
            'edit_task',
            'delete_task',
            'review_task'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions ke role (sesuaikan dengan kebutuhan)
        $revieweeA = Role::where('name', 'revieweeA')->first();
        $reviewerA = Role::where('name', 'reviewerA')->first();
        $revieweeB = Role::where('name', 'revieweeB')->first();
        $reviewerB = Role::where('name', 'reviewerB')->first();

        $revieweeA->permissions()->attach([
            Permission::where('name', 'create_task')->first()->id,
            Permission::where('name', 'edit_task')->first()->id,
        ]);

        $reviewerA->permissions()->attach([
            Permission::where('name', 'review_task')->first()->id,
            Permission::where('name', 'delete_task')->first()->id,
        ]);

        $revieweeB->permissions()->attach([
            Permission::where('name', 'create_task')->first()->id,
            Permission::where('name', 'edit_task')->first()->id,
        ]);

        $reviewerB->permissions()->attach([
            Permission::where('name', 'review_task')->first()->id,
            Permission::where('name', 'delete_task')->first()->id,
        ]);
    }
}
