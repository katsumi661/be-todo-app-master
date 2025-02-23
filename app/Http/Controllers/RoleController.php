<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function updatePermissions(Request $request, $role)
    {
        // Cari role berdasarkan ID
        $role = Role::findOrFail($role);

        // Validasi request
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id', // Pastikan ID valid
        ]);

        // Sinkronisasi permission dengan role
        $role->permissions()->sync($request->permissions);

        return response()->json([
            'success'    => true,
            'message'    => 'Permissions updated successfully',
            'role'       => $role->load('permissions'),
        ]);
    }
}
