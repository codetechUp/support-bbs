<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les rôles 'admin' et 'support'
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $supportRole = Role::firstOrCreate(['name' => 'support']);

        // Créer un utilisateur Administrateur par défaut
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('password'),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Créer un utilisateur Support par défaut pour les tests
        $support = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Agent Support Demo',
                'password' => Hash::make('password'),
            ]
        );
        if (!$support->hasRole('support')) {
            $support->assignRole($supportRole);
        }
    }
}
