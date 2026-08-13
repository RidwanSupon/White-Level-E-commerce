@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Role-Based Access Control (RBAC)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage administrative roles and assign granular module permissions</p>
        </div>
    </div>

    <!-- Create Custom Role Modal / Card -->
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4 max-w-2xl">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Create Staff Role</h3>
        <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Role Title</label>
                <input type="text" name="name" required placeholder="e.g. Content Editor" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                <input type="text" name="description" placeholder="Brief responsibility summary..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
            </div>
            <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Create Role</button>
        </form>
    </div>

    <!-- Existing Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($roles as $role)
            <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h4 class="font-bold text-white text-base">{{ $role->name }}</h4>
                        <span class="text-[11px] text-slate-400">{{ $role->description }}</span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-900 text-brand-400 border border-slate-800">
                        {{ $role->users_count }} Staff Members
                    </span>
                </div>

                <form action="{{ route('admin.roles.permissions', $role->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <span class="text-xs font-bold text-slate-300 block uppercase">Permissions:</span>
                    <div class="grid grid-cols-2 gap-2 text-xs max-h-40 overflow-y-auto pr-1">
                        @foreach($permissions as $module => $modulePerms)
                            @foreach($modulePerms as $perm)
                                <label class="flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }} class="rounded text-brand-500">
                                    <span class="text-[11px]">{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold border border-slate-800">Save Permissions</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
