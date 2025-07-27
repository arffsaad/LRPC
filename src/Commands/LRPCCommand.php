<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;

class LRPCCommand extends Command
{
    public $signature = 'lrpc';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
