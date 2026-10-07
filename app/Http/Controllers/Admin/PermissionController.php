<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $permissions = Permission::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Permission/Index', [
            'permissions' => $permissions,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->back()->with('message', 'Permission created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::findById($id);

        $request->validate([
            'name' => 'required|unique:permissions,name,'.$permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->back()->with('message', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::findById($id);
        $permission->delete();

        return redirect()->back()->with('message', 'Permission deleted successfully.');
    }
}
