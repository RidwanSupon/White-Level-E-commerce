<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\ShopController;

use Illuminate\Support\Facades\Route;

// Production Health Check Endpoint for Render
Route::get('/health', fn () => response('OK', 200))->name('health');

// Enable White-Label branding middleware across web routes
Route::middleware(['web', \App\Http\Middleware\SetWhiteLabelBranding::class])->group(function () {
    
    // CUSTOMER PORTAL & CMS ROUTES
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
    Route::get('/page/{slug}', [\App\Http\Controllers\Customer\CmsPageController::class, 'show'])->name('page.show');

    // SEO XML Sitemap & Robots.txt & PWA Manifest
    Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');
    Route::get('/manifest.json', fn () => response()->json(json_decode(file_get_contents(public_path('manifest.json')), true)));

    // Marketing Newsletter
    Route::post('/newsletter/subscribe', [\App\Http\Controllers\Customer\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

    // Cart & Buy Now Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/data', [CartController::class, 'data'])->name('cart.data');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
    Route::post('/buy-now', [CartController::class, 'buyNow'])->name('buy_now');
    Route::patch('/cart/item/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/item/{id}', [CartController::class, 'destroy'])->name('cart.remove');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

    // Checkout & Order Processing
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.calculate_shipping');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/process', [CheckoutController::class, 'process']);
    Route::get('/payment/callback/{gateway}', [\App\Http\Controllers\Customer\PaymentController::class, 'callback'])->name('payment.callback');

    // Customer Account, Wishlist & Orders
    Route::middleware('auth')->group(function () {
        Route::get('/account/dashboard', [AccountController::class, 'dashboard'])->name('customer.dashboard');
        Route::get('/account/order/{id}', [AccountController::class, 'showOrder'])->name('customer.orders.show');
        
        // Wishlist Subsystem
        Route::get('/account/wishlist', [\App\Http\Controllers\Customer\WishlistController::class, 'index'])->name('customer.wishlist.index');
        Route::post('/wishlist/toggle', [\App\Http\Controllers\Customer\WishlistController::class, 'toggle'])->name('customer.wishlist.toggle');
        Route::post('/wishlist/{id}/move-to-cart', [\App\Http\Controllers\Customer\WishlistController::class, 'moveToCart'])->name('customer.wishlist.move_to_cart');
        Route::delete('/wishlist/{id}', [\App\Http\Controllers\Customer\WishlistController::class, 'destroy'])->name('customer.wishlist.destroy');

        // Product Reviews
        Route::post('/product/{id}/review', [\App\Http\Controllers\Customer\ReviewController::class, 'store'])->name('product.review');

        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });

    // Customer Authentication
    Route::middleware('guest')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login']);
        Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register']);
    });

    // ADMIN PORTAL ROUTES
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Protected Admin Routes (RBAC & Admin Access Middleware)
        Route::middleware([\App\Http\Middleware\AdminAccess::class])->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Product Management & CSV Import/Export
            Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
            Route::get('/products/export', [AdminProductController::class, 'exportCsv'])->name('products.export');
            Route::post('/products/import', [AdminProductController::class, 'importCsv'])->name('products.import');
            Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
            Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
            Route::get('/products/{id}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('products.update');
            Route::patch('/products/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle_status');
            Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
            Route::delete('/products/images/{id}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
            Route::post('/products/images/{id}/primary', [AdminProductController::class, 'setPrimaryImage'])->name('products.images.primary');
            Route::post('/products/{id}/images/reorder', [AdminProductController::class, 'reorderImages'])->name('products.images.reorder');
            Route::post('/products/bulk', [AdminProductController::class, 'bulkAction'])->name('products.bulk');

            // Product Attributes & Swatches
            Route::get('/attributes', [\App\Http\Controllers\Admin\AdminAttributeController::class, 'index'])->name('attributes.index');
            Route::post('/attributes', [\App\Http\Controllers\Admin\AdminAttributeController::class, 'store'])->name('attributes.store');
            Route::post('/attributes/{id}/values', [\App\Http\Controllers\Admin\AdminAttributeController::class, 'storeValue'])->name('attributes.values.store');
            Route::delete('/attributes/values/{id}', [\App\Http\Controllers\Admin\AdminAttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
            Route::delete('/attributes/{id}', [\App\Http\Controllers\Admin\AdminAttributeController::class, 'destroy'])->name('attributes.destroy');

            // Categories & Brands Management
            Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('/brands', [AdminBrandController::class, 'index'])->name('brands.index');
            Route::post('/brands', [AdminBrandController::class, 'store'])->name('brands.store');
            Route::delete('/brands/{id}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

            // Order Management & Invoices
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
            Route::get('/orders/{id}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

            // Manual bKash & Nagad Payment Verification
            Route::get('/payments', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{id}', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'show'])->name('payments.show');
            Route::post('/payments/{id}/verify', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'verify'])->name('payments.verify');
            Route::post('/payments/{id}/reject', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'reject'])->name('payments.reject');

            // Shipping Zones & Location Charges
            Route::get('/shipping-zones', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'index'])->name('shipping_zones.index');
            Route::post('/shipping-zones', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'store'])->name('shipping_zones.store');
            Route::put('/shipping-zones/{id}', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'update'])->name('shipping_zones.update');
            Route::patch('/shipping-zones/{id}/toggle-status', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'toggleStatus'])->name('shipping_zones.toggle_status');
            Route::delete('/shipping-zones/{id}', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'destroy'])->name('shipping_zones.destroy');

            // Tax & Finance Management
            Route::get('/taxes', [\App\Http\Controllers\Admin\AdminTaxController::class, 'index'])->name('taxes.index');
            Route::post('/taxes/settings', [\App\Http\Controllers\Admin\AdminTaxController::class, 'storeSettings'])->name('taxes.settings');
            Route::get('/taxes/create', [\App\Http\Controllers\Admin\AdminTaxController::class, 'create'])->name('taxes.create');
            Route::post('/taxes', [\App\Http\Controllers\Admin\AdminTaxController::class, 'store'])->name('taxes.store');
            Route::get('/taxes/{id}/edit', [\App\Http\Controllers\Admin\AdminTaxController::class, 'edit'])->name('taxes.edit');
            Route::put('/taxes/{id}', [\App\Http\Controllers\Admin\AdminTaxController::class, 'update'])->name('taxes.update');
            Route::patch('/taxes/{id}/toggle-status', [\App\Http\Controllers\Admin\AdminTaxController::class, 'toggleStatus'])->name('taxes.toggle_status');
            Route::delete('/taxes/{id}', [\App\Http\Controllers\Admin\AdminTaxController::class, 'destroy'])->name('taxes.destroy');

            // Inventory Control & Movement Auditing
            Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/adjust', [AdminInventoryController::class, 'adjust'])->name('inventory.adjust');

            // Coupons & Promotions
            Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
            Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
            Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');

            // Roles & Permissions (RBAC)
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::post('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

            // Admin Users Management
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('/users/{id}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign_role');

            // Audit Logs
            Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit_logs.index');

            // Banners & Hero Promotions
            Route::get('/banners', [\App\Http\Controllers\Admin\AdminBannerController::class, 'index'])->name('banners.index');
            Route::post('/banners', [\App\Http\Controllers\Admin\AdminBannerController::class, 'store'])->name('banners.store');
            Route::delete('/banners/{id}', [\App\Http\Controllers\Admin\AdminBannerController::class, 'destroy'])->name('banners.destroy');

            // Shipping Zones & Rates Single Source of Truth
            Route::get('/shipping', function () {
                return redirect()->route('admin.shipping_zones.index');
            })->name('shipping.index');
            Route::post('/shipping/zones', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'storeZone'])->name('shipping.zones.store');
            Route::post('/shipping/methods', [\App\Http\Controllers\Admin\AdminShippingZoneController::class, 'storeMethod'])->name('shipping.methods.store');

            // Payment Gateway Credentials & Mode Configurator
            Route::get('/payment-methods', [\App\Http\Controllers\Admin\AdminPaymentConfigController::class, 'index'])->name('payment_methods.index');
            Route::post('/payment-methods', [\App\Http\Controllers\Admin\AdminPaymentConfigController::class, 'update'])->name('payment_methods.update');

            // CMS Custom Pages Builder
            Route::get('/pages', [\App\Http\Controllers\Admin\AdminPageController::class, 'index'])->name('pages.index');
            Route::get('/pages/create', [\App\Http\Controllers\Admin\AdminPageController::class, 'create'])->name('pages.create');
            Route::post('/pages', [\App\Http\Controllers\Admin\AdminPageController::class, 'store'])->name('pages.store');
            Route::get('/pages/{id}/edit', [\App\Http\Controllers\Admin\AdminPageController::class, 'edit'])->name('pages.edit');
            Route::put('/pages/{id}', [\App\Http\Controllers\Admin\AdminPageController::class, 'update'])->name('pages.update');
            Route::delete('/pages/{id}', [\App\Http\Controllers\Admin\AdminPageController::class, 'destroy'])->name('pages.destroy');

            // Reports & Analytics Dashboard
            Route::get('/reports', [\App\Http\Controllers\Admin\AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [\App\Http\Controllers\Admin\AdminReportController::class, 'export'])->name('reports.export');

            // Admin Notifications Inbox
            Route::get('/notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/{id}/read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAsRead'])->name('notifications.read');

            // White-Label Settings Management
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
});
