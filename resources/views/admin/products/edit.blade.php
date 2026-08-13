@extends('layouts.admin')

@section('content')
<div class="max-w-4xl space-y-6" x-data="editImageUploader()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Edit Product: {{ $product->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage catalog details, serial picture ordering & publish status</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.products.toggle_status', $product->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-2 {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30 hover:bg-amber-500/20' }}">
                    <span class="w-2 h-2 rounded-full {{ $product->is_active ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                    {{ $product->is_active ? 'Status: Published' : 'Status: Unpublished (Draft)' }}
                </button>
            </form>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-slate-400 hover:text-white">← Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-semibold">
            ✓ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Basic Information -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>1. Basic Information & Status</span>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-300">Publish State:</label>
                    <select name="is_active" class="bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-1.5 font-semibold outline-none focus:border-brand-500">
                        <option value="1" {{ $product->is_active ? 'selected' : '' }}>✓ Published (Active)</option>
                        <option value="0" {{ !$product->is_active ? 'selected' : '' }}>Draft (Unpublished)</option>
                    </select>
                </div>
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Product Title</label>
                    <input type="text" name="name" value="{{ $product->name }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ $product->sku }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Category</label>
                    <select name="category_id" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Brand</label>
                    <select name="brand_id" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        <option value="">None</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Product Gallery Pictures & Serial Positioning Section -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">2. Product Gallery Serial Positions (1st, 2nd, 3rd...)</h3>
                <span class="text-xs text-brand-400 font-semibold">{{ $product->images->count() }} Pictures Saved</span>
            </div>

            <!-- Saved Gallery Pictures with Serial Position Controls -->
            @if($product->images->isNotEmpty())
                <div class="space-y-4">
                    <label class="block text-xs font-semibold text-slate-300">Saved Gallery Pictures (Set Serial Orders)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($product->images as $index => $img)
                            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-3 space-y-3 flex flex-col justify-between shadow-md">
                                <div class="relative overflow-hidden rounded-xl h-32 bg-slate-950">
                                    <img src="{{ $img->image_path }}" class="w-full h-full object-cover">
                                    <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full font-bold text-[10px] {{ $img->is_primary || $product->featured_image == $img->image_path ? 'bg-brand-500 text-white shadow-md' : 'bg-slate-950/80 text-slate-300 border border-slate-800' }}">
                                        {{ $img->is_primary || $product->featured_image == $img->image_path ? '★ 1st (Main)' : 'Position #' . ($img->sort_order ?: ($index + 1)) }}
                                    </span>
                                </div>

                                <div class="space-y-2 pt-1 border-t border-slate-800">
                                    <div class="flex items-center justify-between gap-2">
                                        <label class="text-[11px] font-semibold text-slate-400">Serial Pos:</label>
                                        <input type="number" min="1" max="10" name="image_sort_orders[{{ $img->id }}]" value="{{ $img->sort_order ?: ($index + 1) }}" class="w-16 bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-center text-xs font-bold text-white outline-none focus:border-brand-500">
                                    </div>

                                    @if(!$img->is_primary && $product->featured_image != $img->image_path)
                                        <button type="submit" form="set-primary-form-{{ $img->id }}" class="w-full py-1.5 text-[10px] font-bold text-brand-400 bg-brand-500/10 hover:bg-brand-500/20 rounded-lg transition-all">
                                            Make 1st Main
                                        </button>
                                    @endif
                                </div>

                                <button type="submit" form="delete-img-form-{{ $img->id }}" class="w-full py-1 text-[10px] font-bold text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 rounded-lg">
                                    Delete Picture
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Direct Upload Additional Gallery Pictures with Instant Screen Preview -->
            <div class="space-y-4 pt-2 border-t border-slate-900">
                <label class="block text-xs font-semibold text-slate-300">Upload New Pictures (Instant Screen Preview)</label>
                <div class="p-5 rounded-2xl bg-slate-900/80 border-2 border-dashed border-slate-700 hover:border-brand-500 transition-colors text-center space-y-2">
                    <span class="font-bold text-white text-xs block">Choose New Image Files to Add to Gallery</span>
                    <input type="file" name="product_images[]" multiple accept="image/*" @change="handleFileSelect($event)" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-500 file:text-white hover:file:bg-brand-600 cursor-pointer bg-slate-950 border border-slate-800 rounded-xl p-2">
                </div>

                <!-- Live Screen Preview Cards for Newly Chosen Images -->
                <template x-if="newPreviews.length > 0">
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-brand-400 uppercase tracking-wider block">Newly Selected Image Previews to Upload</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <template x-for="(item, index) in newPreviews" :key="index">
                                <div class="rounded-2xl bg-slate-900 border border-brand-500/40 p-3 space-y-2 shadow-lg">
                                    <img :src="item.url" class="w-full h-28 object-cover rounded-xl border border-slate-800">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-white">
                                        <span class="text-brand-400">New Image #<span x-text="index + 1"></span></span>
                                        <button type="button" @click="removeNewImage(index)" class="text-rose-400 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. Pricing & Inventory -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">3. Pricing & Inventory</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Selling Price (৳)</label>
                    <input type="number" step="0.01" name="price" value="{{ $product->price }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Compare Price (৳)</label>
                    <input type="number" step="0.01" name="compare_price" value="{{ $product->compare_price }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required min="0" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Low Stock Alert Quantity *</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" required min="0" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">
                    <p class="text-[10px] text-slate-500 mt-1">Notify admins when available stock reaches or falls below this quantity.</p>
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
                            <option value="{{ $rate->id }}" {{ old('tax_rate_id', $product->tax_rate_id) == $rate->id ? 'selected' : '' }}>
                                {{ $rate->name }} ({{ number_format($rate->rate, 2) }}%) {{ $rate->is_default ? '— System Default' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">If unselected, product inherits the system default tax rate.</p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_tax_exempt" value="1" {{ old('is_tax_exempt', $product->is_tax_exempt) ? 'checked' : '' }} class="w-5 h-5 accent-brand-500 rounded">
                        <div>
                            <span class="text-xs font-bold text-white block">Tax Exempt Product</span>
                            <span class="text-[10px] text-slate-400">When checked, 0% tax is calculated for this product.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 4. Reusable Product Attributes & Option Values -->
        @php
            $activeValueIds = $product->variants->flatMap(fn($v) => $v->attributeValues->pluck('id'))->unique()->toArray();
        @endphp
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>Applicable Product Attributes & Option Values</span>
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
                                    <input type="checkbox" name="attribute_values[]" value="{{ $val->id }}" {{ in_array($val->id, $activeValueIds) ? 'checked' : '' }} class="w-3.5 h-3.5 accent-brand-500 rounded">
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

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">{{ $product->description }}</textarea>
        </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20">
            Save Changes & Update Gallery Serials
        </button>
    </form>

    <!-- Forms for Set Primary & Delete Image Actions -->
    @foreach($product->images as $img)
        <form id="set-primary-form-{{ $img->id }}" action="{{ route('admin.products.images.primary', $img->id) }}" method="POST" class="hidden">
            @csrf
        </form>
        <form id="delete-img-form-{{ $img->id }}" action="{{ route('admin.products.images.destroy', $img->id) }}" method="POST" class="hidden" onsubmit="return confirm('Delete picture?')">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>

<script>
function editImageUploader() {
    return {
        newPreviews: [],

        handleFileSelect(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            this.newPreviews = [];
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newPreviews.push({
                        file: files[i],
                        url: e.target.result
                    });
                };
                reader.readAsDataURL(files[i]);
            }
        },

        removeNewImage(index) {
            this.newPreviews.splice(index, 1);
        }
    }
}
</script>
@endsection
