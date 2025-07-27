<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;

class LRPCSync extends Command
{
    public $signature = 'lrpc:sync';

    public $description = 'Use this command (preferably with supervisor) to actively sync RPCs locally.';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
