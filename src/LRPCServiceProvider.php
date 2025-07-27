<?php

namespace ArffSaad\LRPC;

use ArffSaad\LRPC\Commands\LRPCSync;
use ArffSaad\LRPC\Commands\MakeRPC;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LRPCServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('lrpc')
            ->hasConfigFile()
            ->hasCommand(LRPCSync::class)
            ->hasCommand(MakeRPC::class);
    }
}
