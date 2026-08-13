@extends('layouts.admin')

@section('content')
<div class="max-w-4xl space-y-6" x-data="imageUploader()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Create New Product</h1>
            <p class="text-xs text-slate-400 mt-0.5">Add a new catalog item, upload pictures with instant screen preview & serial positioning</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-slate-400 hover:text-white">← Back to Products</a>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Basic Details -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>1. Basic Information & Status</span>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-300">Publish Status:</label>
                    <select name="is_active" class="bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-1.5 font-semibold outline-none focus:border-brand-500">
                        <option value="1" selected>✓ Published (Active)</option>
                        <option value="0">Draft (Unpublished)</option>
                    </select>
                </div>
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Product Title</label>
                    <input type="text" name="name" required placeholder="iPhone 15 Pro Max" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">SKU</label>
                    <input type="text" name="sku" required placeholder="IP15-PRO-MAX" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Category</label>
                    <select name="category_id" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Brand</label>
                    <select name="brand_id" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        <option value="">None</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Interactive Direct Multi-Image Selector & Real-Time Screen Preview -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">2. Product Pictures & Serial Management</h3>
                <span class="text-xs text-brand-400 font-semibold" x-text="previews.length + ' Pictures Selected'"></span>
            </div>

            <!-- Upload Drop Area -->
            <div class="p-6 rounded-2xl bg-slate-900/80 border-2 border-dashed border-slate-700 hover:border-brand-500 transition-colors text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-brand-500/10 text-brand-400 mx-auto flex items-center justify-center font-bold text-xl">
                    📸
                </div>
                <div>
                    <span class="font-bold text-white text-sm block">Choose Product Pictures (Upload 4 to 5 Photos)</span>
                    <span class="text-xs text-slate-400">Selected images will immediately render below so you can arrange serial positions.</span>
                </div>
                <input type="file" name="product_images[]" multiple accept="image/*" @change="handleFileSelect($event)" class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-500 file:text-white hover:file:bg-brand-600 cursor-pointer bg-slate-950 border border-slate-800 rounded-xl p-2">
            </div>

            <!-- Live Screen Preview Cards of Chosen Pictures with Serial Ordering -->
            <template x-if="previews.length > 0">
                <div class="space-y-3">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Live Selected Picture Previews & Serial Positions</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <template x-for="(item, index) in previews" :key="index">
                            <div class="relative rounded-2xl bg-slate-900 border border-slate-800 p-3 space-y-2 flex flex-col justify-between shadow-lg">
                                <div class="relative overflow-hidden rounded-xl h-32 bg-slate-950">
                                    <img :src="item.url" class="w-full h-full object-cover">
                                    <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full font-bold text-[10px] text-white shadow"
                                          :class="index === primaryIndex ? 'bg-brand-500' : 'bg-slate-950/80 border border-slate-800'">
                                        <span x-text="index === primaryIndex ? '★ 1st (Main)' : 'Position #' + (index + 1)"></span>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-800">
                                    <button type="button" @click="setPrimary(index)" class="flex-1 py-1 text-[10px] font-bold text-brand-400 bg-brand-500/10 hover:bg-brand-500/20 rounded-lg">
                                        Set 1st
                                    </button>
                                    <button type="button" @click="removeImage(index)" class="px-2 py-1 text-[10px] font-bold text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 rounded-lg">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- 3. Pricing & Inventory -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">3. Pricing & Inventory</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Price (৳)</label>
                    <input type="number" step="0.01" name="price" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Compare Price (৳)</label>
                    <input type="number" step="0.01" name="compare_price" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 10) }}" required min="0" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Low Stock Alert Quantity *</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" required min="0" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">
                    <p class="text-[10px] text-slate-500 mt-1">Notify admins when available stock reaches or falls below this quantity.</p>
                </div>
            </div>
        </div>

        <!-- Tax Rate Configuration -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>Tax Rate & Exemption Settings</span>
                <span class="text-xs text-slate-400">Configure product-specific tax rate or tax exemption</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Applicable Tax Rate</label>
                    <select name="tax_rate_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        <option value="">Default System Tax Rate (Inherited)</option>
                        @foreach($taxRates as $rate)
                            <option value="{{ $rate->id }}" {{ old('tax_rate_id') == $rate->id ? 'selected' : '' }}>
                                {{ $rate->name }} ({{ number_format($rate->rate, 2) }}%) {{ $rate->is_default ? '— System Default' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">If unselected, product inherits the system default tax rate.</p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_tax_exempt" value="1" {{ old('is_tax_exempt') ? 'checked' : '' }} class="w-5 h-5 accent-brand-500 rounded">
                        <div>
                            <span class="text-xs font-bold text-white block">Tax Exempt Product</span>
                            <span class="text-[10px] text-slate-400">When checked, 0% tax is calculated for this product.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 4. Reusable Product Attributes & Variants Selection -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>Select Applicable Product Attributes & Options</span>
                <span class="text-xs text-slate-400 font-normal">Reusable across T-Shirt, Shirt, Pant, Ladies Dress, etc.</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($attributes as $attr)
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-3">
                        <span class="text-xs font-bold text-brand-400 uppercase tracking-wider block border-b border-slate-800 pb-2 flex items-center justify-between">
                            <span>{{ $attr->name }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">({{ strtoupper($attr->type) }})</span>
                        </span>
                        <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto pr-1">
                            @foreach($attr->values as $val)
                                <label class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 cursor-pointer hover:border-brand-500/50 transition-all">
                                    <input type="checkbox" name="attribute_values[]" value="{{ $val->id }}" class="w-3.5 h-3.5 accent-brand-500 rounded">
                                    @if($val->color_code)
                                        <span class="w-3 h-3 rounded-full border border-slate-700 shrink-0" style="background-color: {{ $val->color_code }}"></span>
                                    @endif
                                    <span>{{ $val->value }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 5. Description -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">5. Product Description</h3>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Full Description</label>
                <textarea name="description" rows="4" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500"></textarea>
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20">
            Publish Product & Save Gallery Serials
        </button>
    </form>
</div>

<script>
function imageUploader() {
    return {
        previews: [],
        primaryIndex: 0,

        handleFileSelect(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            this.previews = [];
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previews.push({
                        file: files[i],
                        url: e.target.result
                    });
                };
                reader.readAsDataURL(files[i]);
            }
        },

        setPrimary(index) {
            this.primaryIndex = index;
        },

        removeImage(index) {
            this.previews.splice(index, 1);
            if (this.primaryIndex >= this.previews.length) {
                this.primaryIndex = 0;
            }
        }
    }
}
</script>
@endsection
