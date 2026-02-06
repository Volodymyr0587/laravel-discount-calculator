<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Coupons\{
    CouponManager,
    Sale10Coupon,
    Sale20Coupon,
    Fixed50Coupon
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CouponManager::class, fn () =>
        new CouponManager([
            new Sale10Coupon(),
            new Sale20Coupon(),
            new Fixed50Coupon(),
        ])
    );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
