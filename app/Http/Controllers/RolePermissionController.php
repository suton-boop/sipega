<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Tampilkan Matriks Hak Akses (RBAC)
     */
    public function index()
    {
        $roles = Role::where('name', '!=', 'SuperAdmin')->get(); // Sembunyikan super jika ada
        $permissions = Permission::all();
        
        return view('admin.rbac.index', compact('roles', 'permissions'));
    }

    /**
     * Sinkronisasi Hak Akses via Checkbox
     */
    public function sync(Request $request)
    {
        // dd($request->all()); // Debugging
        
        $roles = Role::all();
        foreach ($roles as $role) {
            // Ambil array permission untuk role ini dari request
            $rolePermissions = $request->input('permissions.' . $role->id, []);
            
            // Sync dengan spatie (akan menghapus yang tidak ada di array dan menambah yang baru)
            $role->syncPermissions($rolePermissions);
        }

        return back()->with('success', 'Matriks Hak Akses (RBAC) SIPEGA berhasil diperbarui secara profesional.');
    }
}
