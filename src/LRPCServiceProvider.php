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

    public function register(): void
    {
        parent::register();

        // Make config available even if not published
        $this->mergeConfigFrom(
            __DIR__.'/../config/lrpc.php',
            'lrpc'
        );
    }
}
