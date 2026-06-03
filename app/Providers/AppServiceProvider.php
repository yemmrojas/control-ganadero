<?php

namespace App\Providers;

use App\Services\Binance\BinanceClient;
use App\Services\Binance\BinanceClientInterface;
use App\Services\Math\SmaCalculator;
use App\Services\Math\SmaCalculatorInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Vincula las interfaces de servicios con sus implementaciones concretas
     * para permitir inyección de dependencias y facilitar el testing con mocks.
     */
    public function register(): void
    {
        $this->app->bind(BinanceClientInterface::class, BinanceClient::class);
        $this->app->bind(SmaCalculatorInterface::class, SmaCalculator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
