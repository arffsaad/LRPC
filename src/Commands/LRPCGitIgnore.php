<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LRPCGitIgnore extends Command
{
    protected $signature = 'lrpc:gitignore';

    protected $description = 'update gitignore file with lrpc directories';

    public function handle()
    {
        $gitignorePath = base_path('.gitignore');

        if (!File::exists($gitignorePath)) {
            $this->warn('.gitignore file not found. Skipping gitignore update.');
            return;
        }

        $internalNamespace = config('lrpc.namespaces.internal');
        $externalNamespace = config('lrpc.namespaces.external');

        // Convert namespace to file path
        $internalPath = '/' . str_replace('\\', '/', $internalNamespace);
        $externalPath = '/' . str_replace('\\', '/', $externalNamespace);

        $rules = [
            $externalPath . '/',
            $internalPath . '/.metadata.json',
        ];

        $current = File::get($gitignorePath);

        foreach ($rules as $rule) {
            if (!str_contains($current, $rule)) {
                File::append($gitignorePath, PHP_EOL . $rule);
            }
        }
    }
}
