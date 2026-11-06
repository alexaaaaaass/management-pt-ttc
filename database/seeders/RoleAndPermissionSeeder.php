<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan super_admin ada
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Template Role & Hak Akses
        $roleDefinitions = [
            'marketing' => [
                'approval::form'     => ['view', 'view_any', 'create', 'update', 'delete'],
                'order::confirmation' => ['view', 'view_any', 'create', 'update', 'delete'],
                'customer'           => ['view', 'view_any', 'create', 'update'],
                'sales::order'       => ['view', 'view_any', 'create', 'update'],
                'surat::jalan'       => ['view', 'view_any'],
            ],
            'ppic' => [
                'finish::good::item' => ['view', 'view_any', 'create', 'update', 'delete'],
                'master::spk'        => ['view', 'view_any', 'create', 'update', 'delete'],
                'sales::order'       => ['view', 'view_any'],
                'approval::form'     => ['view', 'view_any'],
                'order::confirmation' => ['view', 'view_any'],
                'i::m::r::printing'  => ['view', 'view_any', 'create', 'update'],
                'imr::finish'        => ['view', 'view_any', 'create', 'update'],
            ],
            'warehouse' => [
                'material::stock'    => ['view', 'view_any', 'update'],
                'material::movement' => ['view', 'view_any'],
                'penerimaan::barang' => ['view', 'view_any', 'create', 'update'],
                'internal::return'   => ['view', 'view_any', 'create', 'update'],
                'i::m::r::printing'  => ['view', 'view_any', 'update'],
                'imr::finish'        => ['view', 'view_any', 'update'],
                'surat::jalan'       => ['view', 'view_any'],
            ],
            'purchasing' => [
                'purchase::request'  => ['view', 'view_any', 'create', 'update', 'delete'],
                'purchase::order'    => ['view', 'view_any', 'create', 'update', 'delete'],
                'penerimaan::barang' => ['view', 'view_any', 'create', 'update'],
                'master::supplier'   => ['view', 'view_any', 'create', 'update'],
                'external::return'   => ['view', 'view_any', 'create', 'update'],
            ],
            'finance' => [
                'invoice'            => ['view', 'view_any', 'create', 'update', 'delete'],
                'inv::payment'       => ['view', 'view_any', 'create', 'update'],
                'trans::faktur'      => ['view', 'view_any', 'create', 'update'],
                'trans::kas'         => ['view', 'view_any', 'create', 'update'],
                'trans::kas::bank'   => ['view', 'view_any', 'create', 'update'],
                'coa::movement'      => ['view', 'view_any'],
                'master::c::o::a'    => ['view', 'view_any'],
                'po::billing'        => ['view', 'view_any', 'create', 'update'],
                'po::bill::pay'      => ['view', 'view_any', 'create', 'update'],
            ],
            'hrd' => [
                'karyawan'           => ['view', 'view_any', 'create', 'update', 'delete'],
                'gaji'               => ['view', 'view_any', 'create', 'update', 'delete'],
                'absensi'            => ['view', 'view_any', 'create', 'update'],
                'cuti'               => ['view', 'view_any', 'create', 'update'],
                'lembur'             => ['view', 'view_any', 'create', 'update'],
                'izin'               => ['view', 'view_any', 'create', 'update'],
                'bonus::karyawan'    => ['view', 'view_any', 'create', 'update'],
            ],
        ];

        foreach ($roleDefinitions as $roleName => $entities) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $grantedPermissions = [];

            foreach ($entities as $entity => $actions) {
                foreach ($actions as $action) {
                    $permName = "{$action}_{$entity}";
                    $permission = Permission::where('name', $permName)->where('guard_name', 'web')->first();
                    if ($permission) {
                        $grantedPermissions[] = $permission;
                    }
                }
            }

            if (! empty($grantedPermissions)) {
                $role->syncPermissions($grantedPermissions);
            }
        }
    }
}

