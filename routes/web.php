<?php

use App\Http\Controllers\Admin\BoardingHouseVerificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AiBoardingHouseSearchController;
use App\Http\Controllers\AiReviewController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthenticationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BoardingHouseSearchController;
use App\Http\Controllers\BoardingHouseShowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\AiFinancialAnalysisController;
use App\Http\Controllers\Owner\BoardingHouseController;
use App\Http\Controllers\Owner\ComplaintController as OwnerComplaintController;
use App\Http\Controllers\Owner\FinancialReportController;
use App\Http\Controllers\Owner\IncomingBookingController;
use App\Http\Controllers\Owner\InvoiceController as OwnerInvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\SubscriptionPaymentReturnController;
use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\ComplaintController as TenantComplaintController;
use App\Http\Controllers\Tenant\InvoiceController as TenantInvoiceController;
use App\Http\Controllers\Tenant\MidtransPaymentReturnController;
use App\Http\Controllers\Tenant\PaymentController as TenantPaymentController;
use App\Http\Controllers\Tenant\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/cari', BoardingHouseSearchController::class)->name('boarding-houses.search');
Route::get('/kos/{boardingHouse}', BoardingHouseShowController::class)->name('boarding-houses.show');
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/auth/google', [GoogleAuthenticationController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthenticationController::class, 'callback'])->name('auth.google.callback');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/ai/cari-kos', AiBoardingHouseSearchController::class)->name('ai.boarding-houses.search');
    Route::post('/kos/{boardingHouse}/ai-review', [AiReviewController::class, 'show'])->name('api.ai-review');

    Route::get('/langganan', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/langganan/{planCode}/midtrans', [SubscriptionPaymentController::class, 'store'])->name('subscriptions.payments.store');
    Route::get('/langganan/midtrans/selesai', SubscriptionPaymentReturnController::class)->name('subscriptions.payments.finish');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/dashboard/penyewa', [DashboardController::class, 'tenant'])
        ->middleware('role:tenant')
        ->name('tenant.dashboard');

    Route::get('/dashboard/pemilik', [DashboardController::class, 'owner'])
        ->middleware(['role:owner', 'subscribed'])
        ->name('owner.dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::middleware(['role:owner'])->group(function (): void {
        Route::get('/booking/masuk', [IncomingBookingController::class, 'index'])->name('owner.bookings.index');
        Route::patch('/booking/{booking}/accept', [IncomingBookingController::class, 'accept'])->name('owner.bookings.accept');
        Route::patch('/booking/{booking}/reject', [IncomingBookingController::class, 'reject'])->name('owner.bookings.reject');

        Route::get('/listing', [BoardingHouseController::class, 'index'])->name('owner.listings.index');
        Route::get('/listing/tambah', [BoardingHouseController::class, 'create'])->name('owner.listings.create');
        Route::post('/listing', [BoardingHouseController::class, 'store'])->name('owner.listings.store');
        Route::get('/listing/{boardingHouse}', [BoardingHouseController::class, 'show'])->name('owner.listings.show');
        Route::get('/listing/{boardingHouse}/edit', [BoardingHouseController::class, 'edit'])->name('owner.listings.edit');
        Route::put('/listing/{boardingHouse}', [BoardingHouseController::class, 'update'])->name('owner.listings.update');
        Route::patch('/listing/{boardingHouse}/submit', [BoardingHouseController::class, 'submit'])->name('owner.listings.submit');

        Route::get('/tagihan', [OwnerInvoiceController::class, 'index'])->name('owner.invoices.index');
        Route::get('/tagihan/buat', [OwnerInvoiceController::class, 'create'])->name('owner.invoices.create');
        Route::post('/tagihan', [OwnerInvoiceController::class, 'store'])->name('owner.invoices.store');
        Route::patch('/tagihan/{invoice}/mark-paid', [OwnerInvoiceController::class, 'markPaid'])->name('owner.invoices.mark-paid');

        Route::middleware(['subscribed'])->group(function (): void {
            Route::get('/laporan', FinancialReportController::class)->name('owner.reports.financial');
            Route::post('/laporan/analisis-ai', AiFinancialAnalysisController::class)->name('owner.reports.financial.ai');
        });

        Route::get('/keluhan/masuk', [OwnerComplaintController::class, 'index'])->name('owner.complaints.index');
        Route::get('/keluhan/masuk/{complaint}', [OwnerComplaintController::class, 'show'])->name('owner.complaints.show');
        Route::post('/keluhan/masuk/{complaint}/reply', [OwnerComplaintController::class, 'reply'])->name('owner.complaints.reply');
        Route::patch('/keluhan/masuk/{complaint}/status', [OwnerComplaintController::class, 'updateStatus'])->name('owner.complaints.status');
    });

    Route::middleware('role:tenant')->group(function (): void {
        Route::get('/booking', [BookingController::class, 'index'])->name('tenant.bookings.index');
        Route::get('/booking/{boardingHouse}', [BookingController::class, 'create'])->name('tenant.bookings.create');
        Route::post('/booking/{boardingHouse}', [BookingController::class, 'store'])->name('tenant.bookings.store');
        Route::get('/tagihan-saya', [TenantInvoiceController::class, 'index'])->name('tenant.invoices.index');
        Route::get('/pembayaran/{invoice}', [TenantInvoiceController::class, 'show'])->name('tenant.invoices.show');
        Route::post('/pembayaran/{invoice}/midtrans', [TenantPaymentController::class, 'store'])->name('tenant.payments.midtrans.store');
        Route::get('/pembayaran/midtrans/selesai', MidtransPaymentReturnController::class)->name('tenant.payments.midtrans.finish');

        Route::get('/keluhan', [TenantComplaintController::class, 'index'])->name('tenant.complaints.index');
        Route::get('/keluhan/buat', [TenantComplaintController::class, 'create'])->name('tenant.complaints.create');
        Route::post('/keluhan', [TenantComplaintController::class, 'store'])->name('tenant.complaints.store');
        Route::get('/keluhan/{complaint}', [TenantComplaintController::class, 'show'])->name('tenant.complaints.show');
        Route::post('/keluhan/{complaint}/reply', [TenantComplaintController::class, 'reply'])->name('tenant.complaints.reply');

        Route::get('/ulasan/buat', [ReviewController::class, 'create'])->name('tenant.reviews.create');
        Route::post('/ulasan', [ReviewController::class, 'store'])->name('tenant.reviews.store');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/listing', [BoardingHouseVerificationController::class, 'index'])->name('listings.index');
        Route::patch('/listing/{boardingHouse}/verify', [BoardingHouseVerificationController::class, 'verify'])->name('listings.verify');
        Route::patch('/listing/{boardingHouse}/reject', [BoardingHouseVerificationController::class, 'reject'])->name('listings.reject');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
