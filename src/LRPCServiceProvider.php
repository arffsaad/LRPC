<?php

namespace ArffSaad\LRPC;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ArffSaad\LRPC\Commands\LRPCCommand;

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
            ->hasViews()
            ->hasMigration('create_lrpc_table')
            ->hasCommand(LRPCCommand::class);
    }
}
