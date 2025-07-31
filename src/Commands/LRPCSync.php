<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonRPC\Client;

class LRPCSync extends Command
{
    protected $signature = 'lrpc:sync {--service=}';

    protected $description = 'Sync LRPC metadata and generate external procedures';

    public function handle()
    {
        $services = config('lrpc.services');
        $externalNamespace = config('lrpc.namespaces.external');

        if (! $services || empty($services)) {
            $this->warn('No services defined in config.');

            return;
        }

        $externalBasePath = $this->namespaceToPath($externalNamespace);

        foreach ($services as $name => $config) {
            if ($this->option('service') && $this->option('service') !== $name) {
                continue;
            }

            $this->info("Syncing: $name");

            try {
                $client = new Client($config['url'].'/lrpc');
                $newMeta = $client->execute('metadata');

                // Create base path
                $servicePath = base_path($externalBasePath.'/'.$name);
                if (! File::exists($servicePath)) {
                    File::makeDirectory($servicePath, 0755, true);
                }

                // Load old metadata for comparison
                $metaPath = $servicePath.'/.metadata.json';
                $oldMeta = File::exists($metaPath) ? json_decode(File::get($metaPath), true) : ['procedures' => []];

                $stubDir = __DIR__.'/../../stubs';

                foreach ($newMeta['procedures'] as $procedureName => $procedure) {
                    $oldHash = $oldMeta['procedures'][$procedureName]['_hash'] ?? null;

                    if ($oldHash === $procedure['_hash']) {
                        $this->line("✓ Skipped: $procedureName (unchanged)");

                        continue;
                    }

                    $this->line("↺ Updating: $procedureName");

                    $procClass = Str::studly($procedureName);
                    $procFile = $servicePath."/{$procClass}.php";

                    // Create DTOs
                    foreach (['request', 'response'] as $type) {
                        $dtoName = $procClass.ucfirst($type);
                        $dtoFile = $servicePath."/{$dtoName}.php";

                        $dtoFields = collect($procedure[$type])
                            ->map(fn ($field) => '    public '.($field['nullable'] ? '?' : '')."{$field['type']} \${$field['name']};")
                            ->implode(PHP_EOL);

                        $this->generateFromStub(
                            $stubDir.'/dto.stub',
                            $dtoFile,
                            [
                                '{{ namespace }}' => $externalNamespace.'\\'.$name,
                                '{{ class }}' => $dtoName,
                                '{{ fields }}' => $dtoFields,
                            ]
                        );
                    }

                    // Create Procedure class
                    $this->generateFromStub(
                        $stubDir.'/external.stub',
                        $procFile,
                        [
                            '{{ namespace }}' => $externalNamespace.'\\'.$name,
                            '{{ class }}' => $procClass,
                            '{{ requestDto }}' => $procClass.'Request',
                            '{{ responseDto }}' => $procClass.'Response',
                        ]
                    );
                }

                // Save updated metadata
                File::put($metaPath, json_encode($newMeta, JSON_PRETTY_PRINT));
                $this->info("✓ Finished syncing: $name");
            } catch (\Throwable $e) {
                $this->error("✗ Failed to sync {$name}: ".$e->getMessage());
            }
        }
    }

    protected function namespaceToPath(string $namespace): string
    {
        // Assume PSR-4 autoloading with base namespace "App"
        $relative = Str::after($namespace, 'App\\');

        return 'app/'.str_replace('\\', '/', $relative);
    }

    protected function generateFromStub(string $stubPath, string $outputPath, array $replacements)
    {
        $stub = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }

        File::put($outputPath, $stub);
    }
}
