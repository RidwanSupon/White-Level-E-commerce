@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white font-display">Administrative Staff Accounts</h1>
        <p class="text-xs text-slate-400 mt-0.5">Manage admin staff, assigned roles, and login history</p>
    </div>

    <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3.5 px-4">Staff Name</th>
                    <th class="py-3.5 px-4">Email</th>
                    <th class="py-3.5 px-4">Assigned Role</th>
                    <th class="py-3.5 px-4">Last Login</th>
                    <th class="py-3.5 px-4 text-right">Assign Role</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($adminUsers as $user)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3.5 px-4 font-bold text-white">{{ $user->name }}</td>
                        <td class="py-3.5 px-4 text-slate-400">{{ $user->email }}</td>
                        <td class="py-3.5 px-4">
                            @foreach($user->roles as $role)
                                <span class="px-2.5 py-1 bg-brand-500/10 text-brand-400 rounded-full font-bold text-[10px] uppercase mr-1">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                            @if($user->roles->isEmpty())
                                <span class="text-slate-500">Super Admin</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('admin.users.assign_role', $user->id) }}" method="POST" class="inline-flex gap-2">
                                @csrf
                                <select name="role_id" class="bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-xs text-white outline-none">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1 bg-brand-500 text-white font-bold rounded-lg text-xs">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
