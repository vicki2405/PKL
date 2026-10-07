<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const PROTECTED_ROLES = ['admin', 'super-admin'];

    private const SYSTEM_ROLES = ['admin', 'super-admin', 'siswa', 'pembimbing'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::whereNotIn('name', ['pembimbing', 'siswa'])
            ->with('permissions')
            ->get();
        $permissions = Permission::all();

        return Inertia::render('Admin/Role/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        $roleName = strtolower(trim($request->name));
        if (in_array($roleName, self::SYSTEM_ROLES, true)) {
            return redirect()->back()->with('error', 'Nama role "'.$request->name.'" adalah role sistem dan tidak dapat dibuat ulang.');
        }

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->back()->with('message', 'Role created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findById($id);

        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'array',
        ]);

        $newName = strtolower(trim($request->name));
        if (in_array($role->name, self::PROTECTED_ROLES, true) && $newName !== strtolower($role->name)) {
            return redirect()->back()->with('error', 'Role "'.$role->name.'" adalah role inti dan namanya tidak dapat diubah.');
        }
        if (in_array($newName, self::SYSTEM_ROLES, true)) {
            return redirect()->back()->with('error', 'Nama role "'.$request->name.'" adalah role sistem dan tidak dapat digunakan.');
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->back()->with('message', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findById($id);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()->back()->with('error', 'Cannot delete core roles.');
        }

        $assignedUsers = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', 'App\\Models\\User')
            ->count();

        if ($assignedUsers > 0) {
            return redirect()->back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh '.$assignedUsers.' user. Lepaskan role dari user tersebut terlebih dahulu.');
        }

        $role->delete();

        return redirect()->back()->with('message', 'Role deleted successfully.');
    }
}
