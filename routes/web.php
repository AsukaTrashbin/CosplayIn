<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\ReturnController as CustomerReturnController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KtpController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicTenantController;
use App\Http\Controllers\Tenant\AvailabilityController;
use App\Http\Controllers\Tenant\BookingController as TenantBookingController;
use App\Http\Controllers\Tenant\CategoryController as TenantCategoryController;
use App\Http\Controllers\Tenant\CostumeController as TenantCostumeController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\OrderController as TenantOrderController;
use App\Http\Controllers\Tenant\ProfileController as TenantProfileController;
use App\Http\Controllers\Tenant\ReportController as TenantReportController;
use App\Http\Controllers\Tenant\ReturnController as TenantReturnController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik (guest)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/{costume}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/katalog/{costume}/ketersediaan', [CatalogController::class, 'availability'])
    ->name('catalog.availability');

Route::get('/tenants', [PublicTenantController::class, 'index'])->name('tenants.index');
Route::get('/tenants/{tenant}', [PublicTenantController::class, 'show'])->name('tenants.show');

Route::get('/syarat-ketentuan', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('pages.privacy');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

/*
|--------------------------------------------------------------------------
| Customer (auth + verified + role:customer)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:customer'])->group(function () {
    Route::get('/booking/{costume}', [CustomerBookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [CustomerBookingController::class, 'store'])->name('booking.store');

    Route::get('/pembayaran/{booking}', [CustomerPaymentController::class, 'show'])->name('payment.show');
    Route::post('/pembayaran/{booking}', [CustomerPaymentController::class, 'store'])->name('payment.store');
    Route::post('/pembayaran/{booking}/mock-success', [CustomerPaymentController::class, 'mockSuccess'])->name('payment.mock');

    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/pesanan-aktif', [CustomerDashboardController::class, 'activeOrders'])
        ->name('customer.orders.active');

    Route::get('/status-pembayaran', [CustomerDashboardController::class, 'payments'])
        ->name('customer.payments.index');

    Route::get('/riwayat-sewa', [CustomerDashboardController::class, 'rentalHistory'])
        ->name('customer.rentals.history');

    Route::get('/pesanan/{booking}', [CustomerOrderController::class, 'show'])->name('orders.show');

    Route::get('/pengembalian/{booking}', [CustomerReturnController::class, 'create'])->name('returns.create');
    Route::post('/pengembalian/{booking}', [CustomerReturnController::class, 'store'])->name('returns.store');

    Route::post('/ulasan/{booking}', [ReviewController::class, 'store'])->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| Tenant (auth + verified + role:tenant + status aktif)
|--------------------------------------------------------------------------
*/

Route::prefix('tenant')->middleware(['auth', 'verified', 'role:tenant', 'tenant.active'])->group(function () {
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

    Route::resource('kostum', TenantCostumeController::class)
        ->parameters(['kostum' => 'costume'])
        ->names('tenant.costumes')
        ->except(['show']);

    Route::get('/kostum/{costume}/jadwal', [AvailabilityController::class, 'index'])
        ->name('tenant.costumes.schedule');
    Route::post('/kostum/{costume}/jadwal', [AvailabilityController::class, 'store'])
        ->name('tenant.costumes.schedule.store');
    Route::delete('/kostum/{costume}/jadwal/{block}', [AvailabilityController::class, 'destroy'])
        ->name('tenant.costumes.schedule.destroy');

    Route::resource('kategori', TenantCategoryController::class)
        ->parameters(['kategori' => 'category'])
        ->names('tenant.categories')
        ->except(['show']);

    Route::get('/pengajuan', [TenantBookingController::class, 'index'])->name('tenant.bookings.index');
    Route::post('/pengajuan/{booking}/setujui', [TenantBookingController::class, 'approve'])->name('tenant.bookings.approve');
    Route::post('/pengajuan/{booking}/tolak', [TenantBookingController::class, 'reject'])->name('tenant.bookings.reject');

    Route::get('/pesanan', [TenantOrderController::class, 'index'])->name('tenant.orders.index');
    Route::get('/pesanan/{booking}', [TenantOrderController::class, 'show'])->name('tenant.orders.show');
    Route::post('/pesanan/{booking}/siap-diproses', [TenantOrderController::class, 'markReady'])->name('tenant.orders.ready');
    Route::post('/pesanan/{booking}/sedang-disewa', [TenantOrderController::class, 'markRented'])->name('tenant.orders.rented');

    Route::post('/pengembalian/{booking}/verifikasi', [TenantReturnController::class, 'verify'])->name('tenant.returns.verify');

    Route::get('/laporan', [TenantReportController::class, 'index'])->name('tenant.reports');

    Route::get('/profil', [TenantProfileController::class, 'edit'])->name('tenant.profile.edit');
    Route::put('/profil', [TenantProfileController::class, 'update'])->name('tenant.profile.update');

    Route::get('/ktp/{booking}', [KtpController::class, 'show'])->name('tenant.ktp.show');

    Route::post('/ulasan/{booking}', [ReviewController::class, 'store'])->name('tenant.reviews.store');
});

/*
|--------------------------------------------------------------------------
| Admin (auth + verified + role:admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/tenant', [AdminTenantController::class, 'index'])->name('admin.tenants.index');
    Route::post('/tenant/{tenant}/approve', [AdminTenantController::class, 'approve'])->name('admin.tenants.approve');
    Route::post('/tenant/{tenant}/nonaktif', [AdminTenantController::class, 'deactivate'])->name('admin.tenants.deactivate');
    Route::post('/tenant/{tenant}/aktifkan', [AdminTenantController::class, 'activate'])->name('admin.tenants.activate');

    Route::get('/transaksi', [AdminTransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('/transaksi/{booking}', [AdminTransactionController::class, 'show'])->name('admin.transactions.show');
    Route::post('/transaksi/{booking}/verifikasi-pembayaran', [AdminTransactionController::class, 'verifyPayment'])
        ->name('admin.transactions.verify-payment');
    Route::get('/ktp/{booking}', [KtpController::class, 'show'])->name('admin.ktp.show');

    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('admin.users.toggle');

    Route::get('/pengaturan', [AdminSettingController::class, 'edit'])->name('admin.settings.edit');
    Route::post('/pengaturan', [AdminSettingController::class, 'update'])->name('admin.settings.update');
});

/*
|--------------------------------------------------------------------------
| Webhook Payment Gateway (tanpa auth, wajib signature)
|--------------------------------------------------------------------------
*/

Route::post('/webhook/payment', [PaymentWebhookController::class, 'handle'])->name('webhook.payment');
