<?php

namespace ArffSaad\LRPC\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ArffSaad\LRPC\LRPC
 */
class LRPC extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ArffSaad\LRPC\LRPC::class;
    }
}
