<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function assignRole(Request $request, User $user)
    {

        $roleName = $request->input('role');
        $user->assignRole($roleName);
        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function revokeRole(Request $request, User $user)
    {
        $roleName = $request->input('role');
        $user->revokeRole($roleName);
        return response()->json(['message' => 'Role revoked successfully']);
    }

    public function assignPermissionToRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($request->input('roleId'));
        $permissionName = $request->input('permission');
        $role->assignPermission($permissionName);
        return response()->json(['message' => 'Permission assigned to role successfully']);
    }

    public function revokePermissionFromRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($request->input('roleId'));
        $permissionName = $request->input('permission');
        $role->revokePermission($permissionName);
        return response()->json(['message' => 'Permission revoked from role successfully']);
    }

}
