@extends('layouts.admin')

@section('content')
<div class="space-y-8" x-data="{ showCreateModal: false, editingZone: null }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Shipping Zones & Advance Charges</h1>
            <p class="text-slate-400 text-xs mt-1">Manage location-based delivery charges and Outside Dhaka advance payment rules</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-500/20 transition-all flex items-center gap-2">
            + Create Shipping Zone
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Zones Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($zones as $zone)
            <div class="bg-slate-950 rounded-3xl p-6 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full {{ $zone->zone_type === 'dhaka' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <div>
                            <h3 class="font-extrabold text-white text-base">{{ $zone->name }}</h3>
                            <span class="text-[11px] font-mono text-slate-400 uppercase">Type: {{ $zone->zone_type === 'dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}</span>
                        </div>
                    </div>
                    <form action="{{ route('admin.shipping_zones.toggle_status', $zone->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3 py-1 rounded-full text-[11px] font-bold {{ $zone->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400' }}">
                            {{ $zone->is_active ? 'Active' : 'Disabled' }}
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-semibold">Delivery Charge:</span>
                        <span class="font-display font-extrabold text-white text-lg">{{ format_price($zone->delivery_charge) }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block font-semibold">Advance Required?</span>
                        @if($zone->advance_payment_required)
                            <span class="px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-extrabold rounded-lg text-[11px] inline-block mt-1">
                                ⚡ YES (Advance Required)
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-800 text-slate-300 font-bold rounded-lg text-[11px] inline-block mt-1">
                                NO (Normal Flow)
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <span class="text-xs font-semibold text-slate-400 block mb-1">Target Districts / Areas:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($zone->districts_json ?? [] as $district)
                            <span class="px-2.5 py-0.5 bg-slate-900 border border-slate-800 text-slate-300 font-mono text-[11px] rounded-lg">{{ $district }}</span>
                        @empty
                            <span class="text-slate-500 text-xs italic">All other Bangladesh districts</span>
                        @endforelse
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-900 pt-3">
                    <button type="button" @click="editingZone = {{ json_encode($zone) }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-200 rounded-xl text-xs font-bold transition-colors">
                        Edit Zone
                    </button>
                    <form action="{{ route('admin.shipping_zones.destroy', $zone->id) }}" method="POST" onsubmit="return confirm('Delete this shipping zone?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 text-rose-400 hover:text-rose-300 text-xs font-bold">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 1. Create Zone Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full space-y-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Create New Shipping Zone</h3>
            
            <form action="{{ route('admin.shipping_zones.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Zone Name *</label>
                    <input type="text" name="name" placeholder="e.g. Inside Dhaka" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Zone Type *</label>
                        <select name="zone_type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white outline-none focus:border-brand-500">
                            <option value="dhaka">Inside Dhaka</option>
                            <option value="outside_dhaka">Outside Dhaka</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Delivery Charge (৳) *</label>
                        <input type="number" step="0.01" name="delivery_charge" placeholder="150.00" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-brand-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Target Districts (Comma separated)</label>
                    <input type="text" name="districts" placeholder="Dhaka, Gazipur, Narayanganj" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-brand-500">
                    <p class="text-[10px] text-slate-500 mt-1">Leave empty to apply to all unassigned districts</p>
                </div>

                <div class="space-y-3 bg-slate-950 p-4 rounded-2xl border border-slate-800">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="advance_payment_required" value="1" class="accent-amber-500">
                        <span class="text-xs font-bold text-amber-400">Require Advance Payment of Delivery Charge</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="accent-brand-500">
                        <span class="text-xs font-semibold text-slate-300">Active Zone</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-500/20">Save Zone</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Zone Modal -->
    <div x-show="editingZone" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full space-y-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Edit Shipping Zone</h3>
            
            <form x-bind:action="'/admin/shipping-zones/' + (editingZone ? editingZone.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Zone Name *</label>
                    <input type="text" name="name" x-model="editingZone.name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Zone Type *</label>
                        <select name="zone_type" x-model="editingZone.zone_type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white outline-none focus:border-brand-500">
                            <option value="dhaka">Inside Dhaka</option>
                            <option value="outside_dhaka">Outside Dhaka</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Delivery Charge (৳) *</label>
                        <input type="number" step="0.01" name="delivery_charge" x-model="editingZone.delivery_charge" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-brand-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Target Districts (Comma separated)</label>
                    <input type="text" name="districts" :value="editingZone ? (editingZone.districts_json || []).join(', ') : ''" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-brand-500">
                </div>

                <div class="space-y-3 bg-slate-950 p-4 rounded-2xl border border-slate-800">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="advance_payment_required" value="1" :checked="editingZone && editingZone.advance_payment_required" class="accent-amber-500">
                        <span class="text-xs font-bold text-amber-400">Require Advance Payment of Delivery Charge</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="editingZone && editingZone.is_active" class="accent-brand-500">
                        <span class="text-xs font-semibold text-slate-300">Active Zone</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="editingZone = null" class="px-5 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-500/20">Update Zone</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
