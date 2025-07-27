<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;

class MakeRPC extends Command
{
    public $signature = 'make:lrpc';

    public $description = 'Create a new RPC class.';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
