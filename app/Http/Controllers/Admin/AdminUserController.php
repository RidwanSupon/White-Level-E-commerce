<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $adminUsers = User::where('is_admin', true)->orWhereHas('roles')->with('roles')->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('adminUsers', 'roles'));
    }

    public function assignRole(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $roleId = $request->input('role_id');

        $user->roles()->sync([$roleId]);
        $user->update(['is_admin' => true]);

        return back()->with('success', "Role updated for user {$user->name}!");
    }
}
