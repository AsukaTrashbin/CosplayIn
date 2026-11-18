<?php

namespace App\Providers;

use App\Services\Payment\MockPaymentGateway;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            // TODO: Pilih implementasi Midtrans/Xendit setelah gateway final ditentukan.
            return match (config('cosplayin.payment.gateway')) {
                'mock' => new MockPaymentGateway(),
                default => new MockPaymentGateway(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination tanpa kelas CSS (styling ditunda).
        \Illuminate\Pagination\Paginator::defaultView('pagination.default');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination.default');
    }
}
