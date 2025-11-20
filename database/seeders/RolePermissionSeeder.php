<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Hapus dulu (opsional) agar idempotent
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions untuk resource Attendance
        $permissions = [
            'attendance.create',
            'attendance.view',            // view own / records you are allowed to see
            'attendance.view_subordinates', // melihat daftar absensi bawahan
            'attendance.update',
            'attendance.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Roles
        $roles = [
            'admin',
            'team_member',
            'group_leader',
            'section_leader',
            'supervisor',
            'assistant_manager',
            'team_manager',
            'departement_manager',
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // Assign permissions:
        // Admin => semua permission
        $admin = Role::where('name', 'admin')->first();
        $admin->syncPermissions($permissions);

        // Non-admin : hanya boleh create & view own
        $basicPerms = ['attendance.create', 'attendance.view'];
        $nonAdmins = [
            'team_member',
            'group_leader',
            'section_leader',
            'supervisor',
            'assistant_manager',
            'team_manager',
            'departement_manager',
        ];
        foreach ($nonAdmins as $r) {
            Role::where('name', $r)->first()->syncPermissions($basicPerms);
        }

        // Beri beberapa role kemampuan melihat bawahan (sesuaikan)
        // Misal managers & leaders boleh melihat bawahan
        $seeSubordinates = [
            'group_leader',
            'section_leader',
            'supervisor',
            'team_manager',
            'departement_manager',
        ];
        foreach ($seeSubordinates as $r) {
            Role::where('name', $r)->first()->givePermissionTo('attendance.view_subordinates');
        }

        // Opsional: assign roles to existing users berdasarkan field `position`
        // Perhatikan: sesuaikan nama field jika berbeda (misal 'position' di users table)
        foreach (User::cursor() as $user) {
            if ($user->position) {
                $pos = strtolower($user->position); // sesuaikan normalisasi
                if (Role::where('name', $pos)->exists()) {
                    $user->syncRoles([$pos]);
                }
            }
        }
    }
}
