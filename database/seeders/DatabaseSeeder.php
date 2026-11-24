<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@ttc.com'],
            [
                'name' => 'Administrator TTC',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        if (! $admin->hasRole($superAdminRole)) {
            $admin->assignRole($superAdminRole);
        }

        $this->call([
            KaryawanSeeder::class,
            IndigamaCoaSeeder::class,
            MasterSupplierSeeder::class,
            BismillahMacroSalesSeeder::class,
            PracetakMaterialSeeder::class,
        ]);
    }
}