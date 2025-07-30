<?php

namespace ArffSaad\LRPC;

use ArffSaad\LRPC\Commands\LRPCBuildMeta;
use ArffSaad\LRPC\Commands\LRPCSync;
use ArffSaad\LRPC\Commands\MakeRPC;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
}
