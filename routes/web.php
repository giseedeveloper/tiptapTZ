<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\TiptapAnalysisController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BillImageController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboard;
use App\Http\Controllers\RestaurantRegistrationController;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::view('/', 'welcome')->name('home');

// Serve profile & menu_images from storage (works when storage:link missing on host)
Route::get('/serve-storage/{path}', \App\Http\Controllers\ServeStorageController::class)->where('path', '.*')->name('storage.serve');
// Path signature avoids some WAFs that block ?signature=... on shared hosting.
Route::get('/bill-image/{orderId}/{signature}', BillImageController::class)
    ->where(['orderId' => '[0-9]+', 'signature' => '[a-f0-9]{64}'])
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
    ])
    ->name('bill.image');
Route::get('/bill-image/{orderId}', BillImageController::class)
    ->where('orderId', '[0-9]+')
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
    ])
    ->name('bill.image.legacy');

Route::get('/qr/whatsapp', [\App\Http\Controllers\QrCodeController::class, 'whatsapp'])
    ->middleware('throttle:120,1')
    ->name('qr.whatsapp');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/register-restaurant', [RestaurantRegistrationController::class, 'create'])->name('restaurant.register');
Route::post('/register-restaurant', [RestaurantRegistrationController::class, 'store'])->name('restaurant.register.store');

Route::get('/register-waiter', [\App\Http\Controllers\WaiterRegistrationController::class, 'create'])->name('waiter.register');
Route::post('/register-waiter', [\App\Http\Controllers\WaiterRegistrationController::class, 'store'])->name('waiter.register.store');

Route::middleware('auth')->group(function () {
    Route::post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('manager')) {
        return redirect()->route('manager.dashboard');
    } elseif ($user->hasRole('waiter')) {
        return redirect()->route('waiter.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes removed as ProfileController is not implemented yet
/*
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/

// Admin Portal
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboard::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/analytics', [AdminDashboard::class, 'getAnalytics'])->name('dashboard.analytics');

    Route::get('/tiptap-analysis', [TiptapAnalysisController::class, 'index'])->name('tiptap-analysis.index');
    Route::get('/tiptap-analysis/platform', [TiptapAnalysisController::class, 'platform'])->name('tiptap-analysis.platform');
    Route::get('/tiptap-analysis/whatsapp', [TiptapAnalysisController::class, 'whatsapp'])->name('tiptap-analysis.whatsapp');
    Route::get('/tiptap-analysis/qr-entry', [TiptapAnalysisController::class, 'qrEntry'])->name('tiptap-analysis.qr-entry');
    Route::get('/tiptap-analysis/journey', [TiptapAnalysisController::class, 'journey'])->name('tiptap-analysis.journey');
    Route::get('/tiptap-analysis/feedback', [TiptapAnalysisController::class, 'feedback'])->name('tiptap-analysis.feedback');
    Route::get('/tiptap-analysis/tips-payments', [TiptapAnalysisController::class, 'tipsPayments'])->name('tiptap-analysis.tips-payments');
    Route::get('/tiptap-analysis/language', [TiptapAnalysisController::class, 'language'])->name('tiptap-analysis.language');
    Route::get('/tiptap-analysis/venues', [TiptapAnalysisController::class, 'venues'])->name('tiptap-analysis.venues');

    Route::get('/tiptap-analysis/platform-pulse', [TiptapAnalysisController::class, 'platformPulse'])->name('tiptap-analysis.platform-pulse');
    Route::get('/tiptap-analysis/snapshot', [TiptapAnalysisController::class, 'snapshot'])->name('tiptap-analysis.snapshot');
    Route::get('/tiptap-analysis/whatsapp-engagement', [TiptapAnalysisController::class, 'whatsappEngagement'])->name('tiptap-analysis.whatsapp-engagement');
    Route::get('/tiptap-analysis/qr-entry-points', [TiptapAnalysisController::class, 'qrEntryPoints'])->name('tiptap-analysis.qr-entry-points');
    Route::get('/tiptap-analysis/customer-journey', [TiptapAnalysisController::class, 'customerJourney'])->name('tiptap-analysis.customer-journey');
    Route::get('/tiptap-analysis/feedback-overview', [TiptapAnalysisController::class, 'feedbackOverview'])->name('tiptap-analysis.feedback-overview');
    Route::get('/tiptap-analysis/tips-and-payments', [TiptapAnalysisController::class, 'tipsAndPayments'])->name('tiptap-analysis.tips-and-payments');
    Route::get('/tiptap-analysis/language-and-behavior', [TiptapAnalysisController::class, 'languageAndBehavior'])->name('tiptap-analysis.language-and-behavior');

    Route::get('search', [\App\Http\Controllers\Admin\SearchController::class, 'index'])
        ->middleware('throttle:admin-search')
        ->name('search.index');
    Route::get('live-orders', [\App\Http\Controllers\Admin\LiveOrderController::class, 'index'])->name('live-orders.index');
    Route::get('live-orders/feed', [\App\Http\Controllers\Admin\LiveOrderController::class, 'feed'])->name('live-orders.feed');
    Route::get('customer-requests', [\App\Http\Controllers\Admin\CustomerRequestController::class, 'index'])->name('customer-requests.index');
    Route::post('customer-requests/{id}/complete', [\App\Http\Controllers\Admin\CustomerRequestController::class, 'complete'])->name('customer-requests.complete');
    Route::get('tips', [\App\Http\Controllers\Admin\TipController::class, 'index'])->name('tips.index');
    Route::get('payroll', [\App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
    Route::get('menus/{restaurant}', [\App\Http\Controllers\Admin\MenuController::class, 'show'])->name('menus.show');
    Route::get('activity-log', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-log.index');

    // Restaurants
    Route::resource('restaurants', \App\Http\Controllers\Admin\RestaurantController::class);
    Route::post('restaurants/{restaurant}/toggle-status', [\App\Http\Controllers\Admin\RestaurantController::class, 'toggleStatus'])->name('restaurants.toggle-status');

    // Restaurant join requests (approval queue)
    Route::get('restaurant-requests', [\App\Http\Controllers\Admin\RestaurantRequestController::class, 'index'])->name('restaurant-requests.index');
    Route::get('restaurant-requests/{restaurant}', [\App\Http\Controllers\Admin\RestaurantRequestController::class, 'show'])->name('restaurant-requests.show');
    Route::post('restaurant-requests/{restaurant}/approve', [\App\Http\Controllers\Admin\RestaurantRequestController::class, 'approve'])->name('restaurant-requests.approve');
    Route::post('restaurant-requests/{restaurant}/reject', [\App\Http\Controllers\Admin\RestaurantRequestController::class, 'reject'])->name('restaurant-requests.reject');
    Route::post('restaurant-requests/bulk-approve', [\App\Http\Controllers\Admin\RestaurantRequestController::class, 'bulkApprove'])->name('restaurant-requests.bulk-approve');

    // Subscription plans (pricing packages)
    Route::resource('plans', \App\Http\Controllers\Admin\SubscriptionPackageController::class)
        ->parameters(['plans' => 'plan'])
        ->except(['show']);

    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'start'])->name('impersonate.start');

    // Users
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['create', 'store']);

    // Waiters (all waiters + unique codes + search like manager)
    Route::get('waiters', [\App\Http\Controllers\Admin\WaiterController::class, 'index'])->name('waiters.index');
    Route::get('waiters/search', [\App\Http\Controllers\Admin\WaiterController::class, 'search'])->name('waiters.search');

    // Orders
    Route::get('orders/export', [\App\Http\Controllers\Admin\OrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)
        ->except(['create', 'store', 'edit']);

    // Payments
    Route::get('payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/export', [\App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');

    Route::get('payment-integration', [\App\Http\Controllers\Admin\PaymentIntegrationController::class, 'index'])->name('payment-integration.index');
    Route::post('payment-integration', [\App\Http\Controllers\Admin\PaymentIntegrationController::class, 'update'])->name('payment-integration.update');
    Route::post('payment-integration/test', [\App\Http\Controllers\Admin\PaymentIntegrationController::class, 'test'])->name('payment-integration.test');

    // Withdrawals
    Route::get('withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Bots
    Route::get('bots', [\App\Http\Controllers\Admin\BotController::class, 'index'])->name('bots.index');
    Route::post('bots/update-endpoint', [\App\Http\Controllers\Admin\BotController::class, 'updateEndpoint'])->name('bots.update-endpoint');
    Route::post('bots/update-branding', [\App\Http\Controllers\Admin\BotController::class, 'updateBranding'])->name('bots.update-branding');
    Route::post('bots/generate-token', [\App\Http\Controllers\Admin\BotController::class, 'generateToken'])
        ->middleware('throttle:bot-token')
        ->name('bots.generate-token');

    Route::post('system/fix-storage', \App\Http\Controllers\Admin\FixStorageController::class)
        ->name('fix-storage');

    Route::get('infrastructure/docker', [\App\Http\Controllers\Admin\DockerController::class, 'index'])->name('docker.index');
    Route::get('infrastructure/docker/status', [\App\Http\Controllers\Admin\DockerController::class, 'status'])->name('docker.status');
    Route::post('infrastructure/docker/action', [\App\Http\Controllers\Admin\DockerController::class, 'action'])
        ->middleware('throttle:docker-control')
        ->name('docker.action');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('notifications.send');

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings/update', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    Route::get('landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'index'])->name('landing-page.index');
    Route::post('landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'update'])->name('landing-page.update');
});

// Manager onboarding (waiting + plan selection) — NOT gated by approval, else redirect loop
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.onboarding.')->group(function () {
    Route::get('/waiting', [\App\Http\Controllers\Manager\OnboardingController::class, 'waiting'])->name('waiting');
    Route::get('/waiting/status', [\App\Http\Controllers\Manager\OnboardingController::class, 'status'])->name('status');
    Route::get('/select-plan', [\App\Http\Controllers\Manager\OnboardingController::class, 'plan'])->name('plan');
    Route::post('/select-plan', [\App\Http\Controllers\Manager\OnboardingController::class, 'selectPlan'])->name('plan.store');
});

// Manager Portal
Route::middleware(['auth', 'role:manager', 'restaurant.approved'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboard::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [ManagerDashboard::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/analytics', [ManagerDashboard::class, 'getAnalytics'])->name('dashboard.analytics');
    Route::get('/live-orders', [\App\Http\Controllers\Manager\LiveOrderController::class, 'index'])->name('orders.live');
    Route::get('/orders/history', [\App\Http\Controllers\Manager\OrderHistoryController::class, 'index'])->name('orders.history');
    Route::get('/orders/history/export', [\App\Http\Controllers\Manager\OrderHistoryController::class, 'export'])->name('orders.history.export');
    Route::get('/orders/{order}', [\App\Http\Controllers\Manager\OrderHistoryController::class, 'show'])->name('orders.show');
    Route::post('/orders', [\App\Http\Controllers\Manager\LiveOrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/whatsapp-bill', [\App\Http\Controllers\Manager\LiveOrderController::class, 'sendWhatsAppBill'])->name('orders.whatsapp-bill');
    Route::put('/orders/{order}', [\App\Http\Controllers\Manager\LiveOrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [\App\Http\Controllers\Manager\LiveOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/menu', [\App\Http\Controllers\Manager\MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu', [\App\Http\Controllers\Manager\MenuController::class, 'store'])->name('menu.store');
    Route::put('/menu/{menuItem}', [\App\Http\Controllers\Manager\MenuController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{menuItem}', [\App\Http\Controllers\Manager\MenuController::class, 'destroy'])->name('menu.destroy');

    // Categories
    Route::post('/categories', [\App\Http\Controllers\Manager\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Manager\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Manager\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/waiters', [\App\Http\Controllers\Manager\WaiterController::class, 'index'])->name('waiters.index');
    Route::get('/waiters/history', [\App\Http\Controllers\Manager\WaiterController::class, 'history'])->name('waiters.history');
    Route::get('/waiters/search', [\App\Http\Controllers\Manager\WaiterController::class, 'search'])->name('waiters.search');
    Route::post('/waiters/{waiter}/link', [\App\Http\Controllers\Manager\WaiterController::class, 'link'])->name('waiters.link');
    Route::post('/waiters/{waiter}/unlink', [\App\Http\Controllers\Manager\WaiterController::class, 'unlink'])->name('waiters.unlink');
    Route::post('/waiters/{waiter}/generate-order-portal-password', [\App\Http\Controllers\Manager\WaiterController::class, 'generateOrderPortalPassword'])->name('waiters.generate-order-portal-password');
    Route::get('/payroll', [\App\Http\Controllers\Manager\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll', [\App\Http\Controllers\Manager\PayrollController::class, 'store'])->name('payroll.store');
    Route::get('/payroll/history', [\App\Http\Controllers\Manager\PayrollController::class, 'history'])->name('payroll.history');
    Route::get('/payroll/export', [\App\Http\Controllers\Manager\PayrollController::class, 'export'])->name('payroll.export');
    Route::get('/payments', [\App\Http\Controllers\Manager\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [\App\Http\Controllers\Manager\PaymentController::class, 'export'])->name('payments.export');
    Route::post('/payments/selcom/initiate', [\App\Http\Controllers\Manager\PaymentController::class, 'initiateSelcom'])->name('payments.selcom.initiate');
    Route::get('/payments/selcom/status/{order}', [\App\Http\Controllers\Manager\PaymentController::class, 'checkSelcomStatus'])->name('payments.selcom.status');
    Route::get('/wallet', [\App\Http\Controllers\Manager\WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/export', [\App\Http\Controllers\Manager\WalletController::class, 'export'])->name('wallet.export');
    Route::put('/wallet/payout-profile', [\App\Http\Controllers\Manager\WalletController::class, 'updatePayoutProfile'])->name('wallet.payout-profile.update');
    Route::post('/wallet/notifications/read', [\App\Http\Controllers\Manager\WalletController::class, 'markNotificationsRead'])->name('wallet.notifications.read');
    Route::post('/wallet/withdraw', [\App\Http\Controllers\Manager\WalletController::class, 'store'])->name('wallet.store');
    Route::get('/feedback', [\App\Http\Controllers\Manager\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/food-ratings', [\App\Http\Controllers\Manager\FoodRatingController::class, 'index'])->name('food-ratings.index');
    Route::get('/tips', [\App\Http\Controllers\Manager\TipController::class, 'index'])->name('tips.index');
    Route::get('/api', [\App\Http\Controllers\Manager\ApiController::class, 'index'])->name('api.index');
    Route::post('/api/support-phone', [\App\Http\Controllers\Manager\ApiController::class, 'updateSupportPhone'])->name('api.support-phone.update');

    // Menu Image Upload
    Route::get('/menu-pdf', [\App\Http\Controllers\Manager\MenuPdfController::class, 'index'])->name('menu-pdf.index');
    Route::post('/menu-pdf', [\App\Http\Controllers\Manager\MenuPdfController::class, 'store'])->name('menu-pdf.store');
    Route::delete('/menu-pdf', [\App\Http\Controllers\Manager\MenuPdfController::class, 'destroy'])->name('menu-pdf.destroy');
    Route::redirect('/menu-image', '/manager/menu-pdf')->name('menu-image.index');

    Route::resource('tables', \App\Http\Controllers\Manager\TableController::class);
    Route::get('/help', [\App\Http\Controllers\Manager\HelpController::class, 'index'])->name('help.index');

    Route::get('/reports/performance', [\App\Http\Controllers\Manager\ReportController::class, 'performance'])->name('reports.performance');
    Route::get('/reports/export-performance', [\App\Http\Controllers\Manager\ReportController::class, 'exportPerformance'])->name('reports.export-performance');
});

// Waiter Portal (dashboard allowed when not linked; other routes require linked restaurant)
Route::middleware(['auth', 'role:waiter'])->prefix('waiter')->name('waiter.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Waiter\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [\App\Http\Controllers\Waiter\HistoryController::class, 'index'])->name('history');
    Route::post('/profile', [\App\Http\Controllers\Waiter\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/salary-slip', [\App\Http\Controllers\Waiter\SalarySlipController::class, 'index'])->name('salary-slip.index');
    Route::get('/salary-slip/{period}', [\App\Http\Controllers\Waiter\SalarySlipController::class, 'show'])->name('salary-slip.show')->where('period', '[0-9]{4}-[0-9]{2}');
    Route::get('/salary-slip/{period}/download', [\App\Http\Controllers\Waiter\SalarySlipController::class, 'download'])->name('salary-slip.download')->where('period', '[0-9]{4}-[0-9]{2}');
    Route::get('/help', [\App\Http\Controllers\Waiter\HelpController::class, 'index'])->name('help.index');
    Route::middleware('waiter.linked')->group(function () {
        Route::get('/dashboard/stats', [\App\Http\Controllers\Waiter\DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/menu', [\App\Http\Controllers\Waiter\MenuController::class, 'index'])->name('menu');
        Route::get('/orders', [\App\Http\Controllers\Waiter\DashboardController::class, 'orders'])->name('orders');
        Route::get('/tips', [\App\Http\Controllers\Waiter\DashboardController::class, 'tips'])->name('tips');
        Route::get('/ratings', [\App\Http\Controllers\Waiter\DashboardController::class, 'ratings'])->name('ratings');
        Route::post('/requests/{request}/complete', [\App\Http\Controllers\Waiter\DashboardController::class, 'completeRequest'])->name('requests.complete');
        Route::post('/orders/{order}/claim', [\App\Http\Controllers\Waiter\DashboardController::class, 'claimOrder'])->name('orders.claim');
        Route::get('/handover', [\App\Http\Controllers\Waiter\DashboardController::class, 'handover'])->name('handover');
        Route::post('/handover', [\App\Http\Controllers\Waiter\DashboardController::class, 'handoverSubmit'])->name('handover.submit');
        Route::post('/status', [\App\Http\Controllers\Waiter\DashboardController::class, 'updateStatus'])->name('status.update');
    });
});

// Kitchen Display System (KDS) - Secret URL Access
Route::prefix('kitchen')->name('kitchen.')->group(function () {
    // Public KDS display (accessed via secret token)
    Route::get('/display/{token}', [\App\Http\Controllers\KitchenController::class, 'display'])->name('display');

    // API endpoints for real-time updates (no auth, uses token)
    Route::get('/api/{token}/orders', [\App\Http\Controllers\KitchenController::class, 'getOrders'])->name('api.orders');
    Route::get('/api/{token}/history', [\App\Http\Controllers\KitchenController::class, 'getOrderHistory'])->name('api.history');
    Route::post('/api/{token}/order/status', [\App\Http\Controllers\KitchenController::class, 'updateStatus'])->name('api.order.status');
    Route::post('/api/{token}/item/status', [\App\Http\Controllers\KitchenController::class, 'updateItemStatus'])->name('api.item.status');
});

// Manager KDS Token Management
Route::middleware(['auth', 'role:manager', 'restaurant.approved'])->prefix('manager')->name('manager.')->group(function () {
    Route::post('/kitchen/generate-token', [\App\Http\Controllers\KitchenController::class, 'generateToken'])->name('kitchen.generate');
    Route::post('/kitchen/revoke-token', [\App\Http\Controllers\KitchenController::class, 'revokeToken'])->name('kitchen.revoke');
});

// TIPTAP ORDER Portal (waiter login with manager-generated password)
Route::prefix('order-portal')->name('order-portal.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\OrderPortal\LoginController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\OrderPortal\LoginController::class, 'store']);
    Route::post('/logout', [\App\Http\Controllers\OrderPortal\LoginController::class, 'destroy'])->name('logout');

    Route::middleware('order.portal')->group(function () {
        Route::get('/orders', [\App\Http\Controllers\OrderPortal\LiveOrdersController::class, 'index'])->name('orders');
        Route::post('/orders', [\App\Http\Controllers\OrderPortal\LiveOrdersController::class, 'store'])->name('orders.store');
        Route::put('/orders/{order}', [\App\Http\Controllers\OrderPortal\LiveOrdersController::class, 'update'])->name('orders.update');
        Route::delete('/orders/{order}', [\App\Http\Controllers\OrderPortal\LiveOrdersController::class, 'destroy'])->name('orders.destroy');
        Route::post('/payments/selcom/initiate', [\App\Http\Controllers\OrderPortal\LiveOrdersController::class, 'paymentInitiate'])->name('payments.selcom.initiate');
        Route::get('/payments/selcom/status/{order}', [\App\Http\Controllers\OrderPortal\LiveOrdersController::class, 'paymentStatus'])->name('payments.selcom.status');
    });
});
