<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Admin Microservice API",
 *     description="API for managing user roles and permissions in the admin microservice",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     description="Admin API Server",
 *     url="http://localhost:8000/api"
 * )
 * @OA\Tag(
 *     name="Admin",
 *     description="Operations about admin tasks"
 * )
 * @OA\Schema(
 *     schema="RolePermissions",
 *     type="object",
 *     title="Role and Permissions",
 *     description="Role and permissions manipulation model",
 *     properties={
 *         @OA\Property(
 *             property="role",
 *             type="string",
 *             description="Name of the role"
 *         ),
 *         @OA\Property(
 *             property="permission",
 *             type="string",
 *             description="Name of the permission"
 *         )
 *     }
 * )
 */


class AdminController extends Controller
{
/**
     * @OA\Post(
     *     path="/admin/users/{user}/assign-role",
     *     tags={"Admin"},
     *     summary="Assign a role to a user",
     *     operationId="assignRole",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID to assign role to",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="role", type="string", description="Role to assign")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role assigned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User or role not found"
     *     )
     * )
     */
    public function assignRole(Request $request, User $user)
    {

        $roleName = $request->input('role');
        $user->assignRole($roleName);
        return response()->json(['message' => 'Role assigned successfully']);
    }
/**
     * @OA\Post(
     *     path="/admin/users/{user}/revoke-role",
     *     tags={"Admin"},
     *     summary="Revoke a role from a user",
     *     operationId="revokeRole",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID to revoke role from",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="role", type="string", description="Role to revoke")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role revoked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role revoked successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User or role not found"
     *     )
     * )
     */
    public function revokeRole(Request $request, User $user)
    {
        $roleName = $request->input('role');
        $user->revokeRole($roleName);
        return response()->json(['message' => 'Role revoked successfully']);
    }
/**
     * @OA\Post(
     *     path="/admin/roles/{roleId}/assign-permission",
     *     tags={"Admin"},
     *     summary="Assign permission to a role",
     *     operationId="assignPermissionToRole",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="Role ID to assign permission to",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="permission", type="string", description="Permission to assign")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission assigned to role successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permission assigned to role successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role or permission not found"
     *     )
     * )
     */
    public function assignPermissionToRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($request->input('roleId'));
        $permissionName = $request->input('permission');
        $role->assignPermission($permissionName);
        return response()->json(['message' => 'Permission assigned to role successfully']);
    }
/**
     * @OA\Post(
     *     path="/admin/roles/{roleId}/revoke-permission",
     *     tags={"Admin"},
     *     summary="Revoke permission from a role",
     *     operationId="revokePermissionFromRole",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="Role ID to revoke permission from",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="permission", type="string", description="Permission to revoke")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission revoked from role successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permission revoked from role successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role or permission not found"
     *     )
     * )
     */
    public function revokePermissionFromRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($request->input('roleId'));
        $permissionName = $request->input('permission');
        $role->revokePermission($permissionName);
        return response()->json(['message' => 'Permission revoked from role successfully']);
    }

}
