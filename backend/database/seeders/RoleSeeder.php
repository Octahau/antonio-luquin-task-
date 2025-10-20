<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'editor']);
        $viewerRole = Role::create(['name' => 'viewer']);

        //Usuarios de prueba
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $editor = User::create([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'password' => bcrypt('password'),
        ]);
        $editor->assignRole('editor');

        $viewer = User::create([
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
        ]);
        $viewer->assignRole('viewer');
    }
}
