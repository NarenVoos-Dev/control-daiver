<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{Product, Sale, Purchase, Client, Supplier, StockAdjustment, User};
use App\Policies\{ProductPolicy, SalePolicy, PurchasePolicy, ClientPolicy, SupplierPolicy, StockAdjustmentPolicy, UserPolicy};


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected $policies = [
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
        Purchase::class => PurchasePolicy::class,
        Client::class => ClientPolicy::class,
        Supplier::class => SupplierPolicy::class,
        StockAdjustment::class => StockAdjustmentPolicy::class,
        User::class => UserPolicy::class,
    ];
}
