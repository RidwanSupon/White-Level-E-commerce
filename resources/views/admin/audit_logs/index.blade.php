@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white font-display">System Audit Logs</h1>
        <p class="text-xs text-slate-400 mt-0.5">Immutable record of administrative actions, settings changes, and security events</p>
    </div>

    <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3.5 px-4">User</th>
                    <th class="py-3.5 px-4">Action</th>
                    <th class="py-3.5 px-4">Module</th>
                    <th class="py-3.5 px-4">Record ID</th>
                    <th class="py-3.5 px-4">IP Address</th>
                    <th class="py-3.5 px-4 text-right">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($logs as $log)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3.5 px-4 font-bold text-white">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="py-3.5 px-4 font-mono text-brand-400">{{ $log->action }}</td>
                        <td class="py-3.5 px-4 text-slate-400 uppercase font-bold text-[10px]">{{ $log->module }}</td>
                        <td class="py-3.5 px-4 text-slate-300 font-mono">#{{ $log->record_id ?? '—' }}</td>
                        <td class="py-3.5 px-4 text-slate-500 font-mono">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-400">{{ $log->created_at->format('M d, H:i:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
