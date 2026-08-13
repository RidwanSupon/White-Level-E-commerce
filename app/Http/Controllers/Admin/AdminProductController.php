<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images']);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = \App\Models\Attribute::with('values')->get();
        $taxRates = TaxRate::where('is_active', true)->get();
        return view('admin.products.create', compact('categories', 'brands', 'attributes', 'taxRates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string'],
            'featured_image_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'product_images.*' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_tax_exempt' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['is_tax_exempt'] = $request->boolean('is_tax_exempt', false);

        return DB::transaction(function () use ($request, $validated) {
            // Handle single main image file upload if provided
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');
                $filename = 'featured_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products'), $filename);
                $validated['featured_image'] = '/uploads/products/' . $filename;
            }

            if (empty($validated['featured_image'])) {
                $validated['featured_image'] = '/images/placeholder.png';
            }

            $product = Product::create($validated);

            // Process Direct Multiple Gallery Image Uploads
            if ($request->hasFile('product_images')) {
                $images = $request->file('product_images');
                foreach ($images as $index => $imgFile) {
                    if ($imgFile->isValid()) {
                        $imgName = 'prod_' . $product->id . '_' . time() . '_' . ($index + 1) . '.' . $imgFile->getClientOriginalExtension();
                        $imgFile->move(public_path('uploads/products'), $imgName);
                        $imgPath = '/uploads/products/' . $imgName;

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imgPath,
                            'sort_order' => $index + 1,
                            'is_primary' => ($index === 0),
                        ]);

                        if ($index === 0 && ($product->featured_image === '/images/placeholder.png' || empty($product->featured_image))) {
                            $product->update(['featured_image' => $imgPath]);
                        }
                    }
                }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'product.created',
                'module' => 'products',
                'record_id' => $product->id,
                'new_values' => $product->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            app(\App\Services\StockAlertService::class)->checkStockAndNotify($product);

            Cache::forget("product_{$product->id}");
            Cache::forget("product_slug_{$product->slug}");

            return redirect()->route('admin.products.index')->with('success', 'Product and gallery images created successfully!');
        });
    }

    public function edit(int $id)
    {
        $product = Product::with(['images' => fn($q) => $q->orderBy('sort_order', 'asc'), 'variants.attributeValues'])->findOrFail($id);
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = \App\Models\Attribute::with('values')->get();
        $taxRates = TaxRate::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes', 'taxRates'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string'],
            'featured_image_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'product_images.*' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'is_tax_exempt' => ['boolean'],
        ]);

        return DB::transaction(function () use ($request, $product, $validated) {
            $oldValues = $product->toArray();
            $validated['is_active'] = $request->boolean('is_active');
            $validated['is_featured'] = $request->boolean('is_featured');
            $validated['is_tax_exempt'] = $request->boolean('is_tax_exempt');

            // Handle single main image file upload if provided
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');
                $filename = 'featured_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products'), $filename);
                $validated['featured_image'] = '/uploads/products/' . $filename;
            }

            $product->update($validated);

            // Update Serial Sort Orders for Existing Gallery Pictures
            if ($request->has('image_sort_orders')) {
                foreach ($request->input('image_sort_orders') as $imageId => $sortOrder) {
                    ProductImage::where('id', $imageId)
                        ->where('product_id', $product->id)
                        ->update(['sort_order' => (int) $sortOrder]);
                }
            }

            // Process Additional Gallery Image Uploads
            if ($request->hasFile('product_images')) {
                $images = $request->file('product_images');
                $currentMaxOrder = $product->images()->max('sort_order') ?? 0;

                foreach ($images as $index => $imgFile) {
                    if ($imgFile->isValid()) {
                        $newOrder = $currentMaxOrder + $index + 1;
                        $imgName = 'prod_' . $product->id . '_' . time() . '_' . $newOrder . '.' . $imgFile->getClientOriginalExtension();
                        $imgFile->move(public_path('uploads/products'), $imgName);
                        $imgPath = '/uploads/products/' . $imgName;

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imgPath,
                            'sort_order' => $newOrder,
                            'is_primary' => false,
                        ]);

                        if (empty($product->featured_image) || $product->featured_image === '/images/placeholder.png') {
                            $product->update(['featured_image' => $imgPath]);
                        }
                    }
                }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'product.updated',
                'module' => 'products',
                'record_id' => $product->id,
                'old_values' => $oldValues,
                'new_values' => $product->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            app(\App\Services\StockAlertService::class)->checkStockAndNotify($product->fresh());

            Cache::forget("product_{$product->id}");
            Cache::forget("product_slug_{$product->slug}");

            return redirect()->route('admin.products.index')->with('success', 'Product details and serial picture orders updated successfully!');
        });
    }

    public function setPrimaryImage(int $id)
    {
        return DB::transaction(function () use ($id) {
            $image = ProductImage::findOrFail($id);
            $productId = $image->product_id;
            $product = Product::findOrFail($productId);

            // Reset all images for this product to is_primary = false
            ProductImage::where('product_id', $productId)->update(['is_primary' => false]);

            // Set selected image as is_primary = true and sort_order = 1
            $image->update(['is_primary' => true, 'sort_order' => 1]);

            // Set as main featured_image on product table
            $product->update(['featured_image' => $image->image_path]);

            Cache::forget("product_{$productId}");
            Cache::forget("product_slug_{$product->slug}");

            return back()->with('success', 'Picture set as 1st Main Product Photo successfully!');
        });
    }

    public function destroyImage(int $id)
    {
        return DB::transaction(function () use ($id) {
            $image = ProductImage::findOrFail($id);
            $productId = $image->product_id;
            $product = Product::findOrFail($productId);

            $deletedPath = $image->image_path;
            $wasPrimary = $image->is_primary || ($product->featured_image === $deletedPath);

            // Delete the ProductImage DB record
            $image->delete();

            // If the deleted image was primary OR matched product featured_image, promote next image or reset
            if ($wasPrimary) {
                // Find next available remaining image for this product
                $nextImage = ProductImage::where('product_id', $productId)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();

                if ($nextImage) {
                    ProductImage::where('product_id', $productId)->update(['is_primary' => false]);
                    $nextImage->update(['is_primary' => true]);
                    $product->update(['featured_image' => $nextImage->image_path]);
                } else {
                    // No images remain: clear featured_image to null
                    $product->update(['featured_image' => null]);
                }
            }

            // Storage Cleanup: Check if physical file is referenced by any other record in DB
            if (!empty($deletedPath) && !str_starts_with($deletedPath, 'http')) {
                $otherReferences = ProductImage::where('image_path', $deletedPath)->count() 
                                 + Product::where('featured_image', $deletedPath)->count();
                if ($otherReferences === 0) {
                    $relativeFile = ltrim($deletedPath, '/');
                    $fullPath = public_path($relativeFile);
                    if (File::exists($fullPath)) {
                        File::delete($fullPath);
                    }
                }
            }

            Cache::forget("product_{$productId}");
            Cache::forget("product_slug_{$product->slug}");

            return back()->with('success', 'Gallery image deleted and primary image updated.');
        });
    }

    public function reorderImages(Request $request, int $id)
    {
        $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'exists:product_images,id'],
            'orders.*.sort_order' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($request, $id) {
            $product = Product::findOrFail($id);
            foreach ($request->input('orders') as $item) {
                ProductImage::where('id', $item['id'])
                    ->where('product_id', $product->id)
                    ->update(['sort_order' => $item['sort_order']]);
            }

            Cache::forget("product_{$product->id}");
            Cache::forget("product_slug_{$product->slug}");

            return response()->json(['status' => 'success', 'message' => 'Image order updated successfully.']);
        });
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product.deleted',
            'module' => 'products',
            'record_id' => $id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Cache::forget("product_{$id}");

        return back()->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(int $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        $statusStr = $product->is_active ? 'Published' : 'Unpublished (Draft)';
        return back()->with('success', "Product '{$product->name}' status changed to {$statusStr}.");
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        switch ($action) {
            case 'delete':
                Product::whereIn('id', $ids)->delete();
                $msg = 'Selected products deleted.';
                break;
            case 'publish':
                Product::whereIn('id', $ids)->update(['is_active' => true]);
                $msg = 'Selected products published.';
                break;
            case 'unpublish':
                Product::whereIn('id', $ids)->update(['is_active' => false]);
                $msg = 'Selected products unpublished.';
                break;
            default:
                return back()->with('error', 'Invalid bulk action.');
        }

        return back()->with('success', $msg);
    }

    public function exportCsv(\App\Services\CatalogCsvService $csvService)
    {
        $content = $csvService->exportCsv();
        $fileName = 'catalog_export_' . date('Y_m_d_His') . '.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function importCsv(Request $request, \App\Services\CatalogCsvService $csvService)
    {
        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt']]);

        $content = file_get_contents($request->file('csv_file')->getRealPath());
        $count = $csvService->importCsv($content);

        return back()->with('success', "Successfully imported / updated {$count} products from CSV!");
    }
}
