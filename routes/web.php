<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PageController;

// صفحة الرئيسية
Route::get('/', [HomeController::class, 'index'])->name('home');

// صفحة المنتجات
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

// صفحة البائعين
Route::get('/vendors', [App\Http\Controllers\Frontend\VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{id}', [App\Http\Controllers\Frontend\VendorController::class, 'show'])->name('vendors.show');

// صفحة التصنيفات
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');

// صفحة العروض
Route::get('/offers', function () {
    return view('offers.index');
})->name('offers.index');

Route::get('/offers/{id}', function ($id) {
    return view('offers.show', ['id' => $id]);
})->name('offers.show');

// صفحة البحث
Route::get('/search', [SearchController::class, 'index'])->name('search');

// صفحة المفضلة
Route::get('/wishlist', function () {
    return view('wishlist.index');
})->name('wishlist.index');

// صفحة سلة التسوق
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// صفحة الدفع
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::post('/checkout/shipping-cost', [App\Http\Controllers\CheckoutController::class, 'getShippingCost'])->name('checkout.shipping-cost');
    Route::post('/checkout/apply-coupon', [App\Http\Controllers\CheckoutController::class, 'applyCoupon'])->name('checkout.apply-coupon');
    Route::delete('/checkout/remove-coupon', [App\Http\Controllers\CheckoutController::class, 'removeCoupon'])->name('checkout.remove-coupon');
});

// Payment Webhooks (لا تحتاج middleware)
Route::post('/webhooks/stripe', [App\Http\Controllers\PaymentWebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [App\Http\Controllers\PaymentWebhookController::class, 'paypal'])->name('webhooks.paypal');
Route::post('/webhooks/fawry', [App\Http\Controllers\PaymentWebhookController::class, 'fawry'])->name('webhooks.fawry');

// Shipping Routes
Route::get('/tracking', [App\Http\Controllers\ShippingController::class, 'trackShipment'])->name('tracking');
Route::get('/shipping', [App\Http\Controllers\ShippingController::class, 'getProviders'])->name('shipping.providers');
Route::post('/shipping/options', [App\Http\Controllers\ShippingController::class, 'getShippingOptions'])->name('shipping.options');
Route::post('/shipping/order-options/{order}', [App\Http\Controllers\ShippingController::class, 'getShippingOptionsForOrder'])->name('shipping.order-options');

// Customer shipping routes
Route::middleware(['auth'])->group(function () {
    // Customer tracking page
    Route::get('/tracking/customer', [App\Http\Controllers\ShippingController::class, 'trackShipment'])->name('customer.tracking');
});

// Vendor shipping routes
Route::middleware(['auth', 'vendor'])->group(function () {
    Route::post('/orders/{order}/shipments', [App\Http\Controllers\ShippingController::class, 'createShipment'])->name('vendor.orders.shipments');
    Route::get('/shipments/{shipment}', [App\Http\Controllers\ShippingController::class, 'getShipment'])->name('vendor.shipments.show');
    Route::post('/shipments/{shipment}/status', [App\Http\Controllers\ShippingController::class, 'updateStatus'])->name('vendor.shipments.update-status');
    Route::get('/shipments/{shipment}/label', [App\Http\Controllers\ShippingController::class, 'printLabel'])->name('vendor.shipments.label');
});

// Admin shipping routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/shipping', [App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('admin.shipping');
    Route::get('/admin/api/shipments', [App\Http\Controllers\Admin\ShippingController::class, 'getShipments'])->name('admin.api.shipments');
    Route::get('/admin/api/shipments/{shipment}', [App\Http\Controllers\Admin\ShippingController::class, 'getShipment'])->name('admin.api.shipments.show');
    Route::get('/admin/api/shipping-providers', [App\Http\Controllers\Admin\ShippingController::class, 'getProviders'])->name('admin.api.shipping-providers');
    Route::get('/admin/api/shipping-providers/{provider}', [App\Http\Controllers\Admin\ShippingController::class, 'getProvider'])->name('admin.api.shipping-providers.show');
    Route::get('/admin/api/shipping-zones', [App\Http\Controllers\Admin\ShippingController::class, 'getZones'])->name('admin.api.shipping-zones');
    Route::get('/admin/api/shipping-zones/{zone}', [App\Http\Controllers\Admin\ShippingController::class, 'getZone'])->name('admin.api.shipping-zones.show');
    Route::get('/admin/api/shipping-rates', [App\Http\Controllers\Admin\ShippingController::class, 'getRates'])->name('admin.api.shipping-rates');
    Route::get('/admin/api/shipping-rates/{rate}', [App\Http\Controllers\Admin\ShippingController::class, 'getRate'])->name('admin.api.shipping-rates.show');

    // CRUD operations for shipping providers
    Route::post('/admin/shipping-providers', [App\Http\Controllers\Admin\ShippingController::class, 'createProvider'])->name('admin.shipping-providers.create');
    Route::put('/admin/shipping-providers/{provider}', [App\Http\Controllers\Admin\ShippingController::class, 'updateProvider'])->name('admin.shipping-providers.update');
    Route::delete('/admin/shipping-providers/{provider}', [App\Http\Controllers\Admin\ShippingController::class, 'deleteProvider'])->name('admin.shipping-providers.delete');

    // CRUD operations for shipping zones
    Route::post('/admin/shipping-zones', [App\Http\Controllers\Admin\ShippingController::class, 'createZone'])->name('admin.shipping-zones.create');
    Route::put('/admin/shipping-zones/{zone}', [App\Http\Controllers\Admin\ShippingController::class, 'updateZone'])->name('admin.shipping-zones.update');
    Route::delete('/admin/shipping-zones/{zone}', [App\Http\Controllers\Admin\ShippingController::class, 'deleteZone'])->name('admin.shipping-zones.delete');

    // CRUD operations for shipping rates
    Route::post('/admin/shipping-rates', [App\Http\Controllers\Admin\ShippingController::class, 'createRate'])->name('admin.shipping-rates.create');
    Route::put('/admin/shipping-rates/{rate}', [App\Http\Controllers\Admin\ShippingController::class, 'updateRate'])->name('admin.shipping-rates.update');
    Route::delete('/admin/shipping-rates/{rate}', [App\Http\Controllers\Admin\ShippingController::class, 'deleteRate'])->name('admin.shipping-rates.delete');
});

// صفحة تسجيل الدخول
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// صفحة إنشاء الحساب
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// صفحة من نحن
Route::get('/about', [PageController::class, 'about'])->name('pages.about');

// صفحة اتصل بنا
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('pages.contact.submit');

// صفحة سياسة الخصوصية
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');

// صفحة الشروط والأحكام
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');

// صفحة المساعدة
Route::get('/help', [PageController::class, 'help'])->name('pages.help');

// صفحة الأسئلة الشائعة
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');

// صفحة المدونة
Route::get('/blog', function () {
    return view('blog.index');
})->name('blog.index');

Route::get('/blog/{id}', function ($id) {
    return view('blog.show', ['id' => $id]);
})->name('blog.show');

// صفحة العميل الشخصي
Route::middleware(['auth'])->group(function () {
    Route::get('/account', function () {
        return view('account.index');
    })->name('account.index');
    
    Route::get('/account/orders', function () {
        return view('account.orders');
    })->name('account.orders');
    
    Route::get('/account/profile', function () {
        return view('account.profile');
    })->name('account.profile');
    
    Route::get('/account/address', function () {
        return view('account.address');
    })->name('account.address');
    
    Route::get('/account/wishlist', function () {
        return view('account.wishlist');
    })->name('account.wishlist');
    
    Route::get('/account/settings', function () {
        return view('account.settings');
    })->name('account.settings');
});

// لوحة تحكم المسؤول
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // إدارة المنتجات
    Route::get('/products', function () {
        return view('admin.products.index');
    })->name('admin.products.index');
    
    Route::get('/products/create', function () {
        return view('admin.products.create');
    })->name('admin.products.create');
    
    Route::get('/products/{id}/edit', function ($id) {
        return view('admin.products.edit', ['id' => $id]);
    })->name('admin.products.edit');
    
    // إدارة البائعين
    Route::get('/vendors', function () {
        return view('admin.vendors.index');
    })->name('admin.vendors.index');
    
    Route::get('/vendors/create', function () {
        return view('admin.vendors.create');
    })->name('admin.vendors.create');
    
    Route::get('/vendors/{id}/edit', function ($id) {
        return view('admin.vendors.edit', ['id' => $id]);
    })->name('admin.vendors.edit');
    
    // إدارة الطلبات
    Route::get('/orders', function () {
        return view('admin.orders.index');
    })->name('admin.orders.index');
    
    Route::get('/orders/{id}', function ($id) {
        return view('admin.orders.show', ['id' => $id]);
    })->name('admin.orders.show');
    
    // إدارة العملاء
    Route::get('/customers', function () {
        return view('admin.customers.index');
    })->name('admin.customers.index');
    
    Route::get('/customers/create', function () {
        return view('admin.customers.create');
    })->name('admin.customers.create');
    
    Route::get('/customers/{id}/edit', function ($id) {
        return view('admin.customers.edit', ['id' => $id]);
    })->name('admin.customers.edit');
    
    // إدارة المحاسبة
    Route::get('/accounting', function () {
        return view('admin.accounting.index');
    })->name('admin.accounting.index');
    
    Route::get('/accounting/transactions', function () {
        return view('admin.accounting.transactions.index');
    })->name('admin.accounting.transactions.index');
    
    Route::get('/accounting/transactions/create', function () {
        return view('admin.accounting.transactions.create');
    })->name('admin.accounting.transactions.create');
    
    Route::get('/accounting/transactions/{id}', function ($id) {
        return view('admin.accounting.transactions.show', ['id' => $id]);
    })->name('admin.accounting.transactions.show');
    
    Route::get('/accounting/transactions/{id}/edit', function ($id) {
        return view('admin.accounting.transactions.edit', ['id' => $id]);
    })->name('admin.accounting.transactions.edit');
    
    Route::get('/accounting/payouts', function () {
        return view('admin.accounting.payouts.index');
    })->name('admin.accounting.payouts.index');
    
    Route::get('/accounting/payouts/create', function () {
        return view('admin.accounting.payouts.create');
    })->name('admin.accounting.payouts.create');
    
    Route::get('/accounting/payouts/{id}', function ($id) {
        return view('admin.accounting.payouts.show', ['id' => $id]);
    })->name('admin.accounting.payouts.show');
    
    Route::get('/accounting/payouts/{id}/edit', function ($id) {
        return view('admin.accounting.payouts.edit', ['id' => $id]);
    })->name('admin.accounting.payouts.edit');
    
    // إدارة المخزون
    Route::get('/inventory', function () {
        return view('admin.inventory.index');
    })->name('admin.inventory.index');
    
    Route::get('/inventory/stock/adjust', function () {
        return view('admin.inventory.stock.adjust');
    })->name('admin.inventory.stock.adjust');
    
    Route::get('/inventory/transactions', function () {
        return view('admin.inventory.transactions.index');
    })->name('admin.inventory.transactions.index');
    
    Route::get('/inventory/transactions/{id}', function ($id) {
        return view('admin.inventory.transactions.show', ['id' => $id]);
    })->name('admin.inventory.transactions.show');
    
    // إدارة التوصيات
    Route::get('/recommendations', function () {
        return view('admin.recommendations.index');
    })->name('admin.recommendations.index');
    
    Route::get('/recommendations/products', function () {
        return view('admin.recommendations.products.index');
    })->name('admin.recommendations.products.index');
    
    Route::get('/recommendations/products/{id}', function ($id) {
        return view('admin.recommendations.products.show', ['id' => $id]);
    })->name('admin.recommendations.products.show');
    
    Route::get('/recommendations/products/create', function () {
        return view('admin.recommendations.products.create');
    })->name('admin.recommendations.products.create');
    
    Route::get('/recommendations/products/{id}/edit', function ($id) {
        return view('admin.recommendations.products.edit', ['id' => $id]);
    })->name('admin.recommendations.products.edit');
    
    Route::get('/recommendations/customers', function () {
        return view('admin.recommendations.customers.index');
    })->name('admin.recommendations.customers.index');
    
    Route::get('/recommendations/customers/create', function () {
        return view('admin.recommendations.customers.create');
    })->name('admin.recommendations.customers.create');
    
    Route::get('/recommendations/settings', function () {
        return view('admin.recommendations.settings');
    })->name('admin.recommendations.settings');
    
    // إدارة الإعدادات
    Route::get('/settings', function () {
        return view('admin.settings.index');
    })->name('admin.settings.index');
    
    Route::get('/settings/site', function () {
        return view('admin.settings.site');
    })->name('admin.settings.site');
    
    Route::get('/settings/payment', function () {
        return view('admin.settings.payment');
    })->name('admin.settings.payment');
    
    Route::get('/settings/shipping', function () {
        return view('admin.settings.shipping');
    })->name('admin.settings.shipping');
    
    Route::get('/settings/email', function () {
        return view('admin.settings.email');
    })->name('admin.settings.email');
});

// صفحة الخطأ 404
Route::fallback(function () {
    return view('errors.404');
});
