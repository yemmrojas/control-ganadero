<?php

namespace App\Providers;

use App\Domain\Contracts\BinanceClientInterface;
use App\Domain\Contracts\QueryRepositoryInterface;
use App\Domain\Contracts\SmaCalculatorInterface;
use App\Domain\Services\SmaCalculator;
use App\Infrastructure\ExternalServices\BinanceClient;
use App\Infrastructure\Persistence\EloquentQueryRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Vincula las interfaces de servicios con sus implementaciones concretas
     * para permitir inyección de dependencias y facilitar el testing con mocks.
     * 
     * Las interfaces viven en Domain (contratos).
     * Las implementaciones viven en Domain (SmaCalculator) o Infrastructure (BinanceClient, Repositories).
     */
    public function register(): void
    {
        // Domain Service
        $this->app->bind(SmaCalculatorInterface::class, SmaCalculator::class);
        
        // Infrastructure Services
        $this->app->bind(BinanceClientInterface::class, BinanceClient::class);
        $this->app->bind(QueryRepositoryInterface::class, EloquentQueryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
