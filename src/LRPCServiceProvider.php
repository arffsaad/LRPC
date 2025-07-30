<?php

namespace ArffSaad\LRPC;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use ArffSaad\LRPC\Commands\LRPCSync;
use ArffSaad\LRPC\Commands\MakeRPC;
use ArffSaad\LRPC\Commands\LRPCBuildMeta;

class LRPCServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lrpc')
            ->hasConfigFile('lrpc')
            ->hasCommands([
                LRPCSync::class,
                MakeRPC::class,
                LRPCBuildMeta::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // You can bind singletons or services here in the future if needed.
        // e.g., $this->app->singleton(LRPCManager::class);
    }

    public function bootingPackage()
    {
        // Publish config file on install
        $this->publishes([
            __DIR__ . '/../config/lrpc.php' => config_path('lrpc.php'),
        ], 'lrpc-config');
    }
}
