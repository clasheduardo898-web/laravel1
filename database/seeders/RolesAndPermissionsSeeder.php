<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            'cortes.crear',
            'cortes.ver-propios',
            'cortes.ver-todos',
            'cortes.modificar',
            'cortes.eliminar',
            'cortes.verificar',
            'cortes.revertir-verificacion',
            'historial.ver',
            'informes.ver',
            'catalogos.gestionar',
            'usuarios.gestionar',
        ];

        foreach ($permisos as $permiso){
            Permission::firstOrCreate(['name' => $permiso]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $operario = Role::firstOrCreate(['name' => 'operario' ]);
        $operario->syncPermissions([
            'cortes.crear',
            'cortes.ver-propios',
        ]);

        $bodega = Role::firstOrCreate(['name' => 'bodega']);
        $bodega->syncPermissions([
            'historial.ver',
            'cortes.modificar',
            'cortes.verificar',
            'informes.ver',
        ]);
    }
}
