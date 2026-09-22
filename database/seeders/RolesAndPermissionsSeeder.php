<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Base data: runs in production AND local.
     * Demo data (users, tickets) goes in other seeders.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'tickets.view',         // ver propias/asignadas (policy decide el alcance)
            'tickets.view-all',     // ver todas (admin, supervisor)
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',
            'comments.view',
            'comments.create',
            'admin.access',         // entrar en /admin
        ];
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // customer: crea y ve las suyas
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web'])
            ->syncPermissions([
                'tickets.view',
                'tickets.create',
                'tickets.update',
                'comments.view',
                'comments.create',
            ]);

        // agent: ve asignadas, actualiza y cierra
        Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web'])
            ->syncPermissions([
                'tickets.view',
                'tickets.update',
                'tickets.close',
                'comments.view',
                'comments.create',
            ]);

        // admin: todo
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
