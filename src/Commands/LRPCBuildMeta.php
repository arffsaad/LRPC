<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionNamedType;

class LRPCBuildMeta extends Command
{
    protected $signature = 'lrpc:build-meta {--dry-run}';

    protected $description = 'Build metadata file for all internal LRPC procedures';

    public function handle()
    {
        $internalNamespace = config('lrpc.namespaces.internal', 'App\\Lrpc\\Internal');
        $internalPath = base_path(str_replace('\\', '/', $internalNamespace));

        $procedures = [];

        $this->info("Scanning for procedures in: {$internalPath}");

        foreach (File::files($internalPath) as $file) {
            $className = $internalNamespace.'\\'.$file->getBasename('.php');
            if (! class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);
            if (! $ref->isSubclassOf(\ArffSaad\LRPC\Support\BaseProcedure::class)) {
                continue;
            }

            $procedureName = $ref->getShortName();
            $this->info("Processing procedure: {$procedureName}");

            $requestDto = $ref->getMethod('requestType')->invoke($ref->newInstance());
            $responseDto = $ref->getMethod('responseType')->invoke($ref->newInstance());

            $request = $this->extractDtoStructure($requestDto);
            $response = $this->extractDtoStructure($responseDto);

            $body = [
                'request' => $request,
                'response' => $response,
                'description' => $ref->getMethod('desc')->invoke($ref->newInstance()),
            ];

            $procedures[$procedureName] = array_merge($body, [
                '_hash' => md5(json_encode($body)),
            ]);
        }

        $final = [
            '_generated_at' => now()->toIso8601String(),
            'procedures' => $procedures,
        ];

        $outputPath = $internalPath.'/.metadata.json';

        if ($this->option('dry-run')) {
            $this->info('Metadata generated:');
            $this->info(json_encode($final, JSON_PRETTY_PRINT));
        } else {
            File::put($outputPath, json_encode($final, JSON_PRETTY_PRINT));
            $this->info("Metadata written to {$outputPath}");
        }
    }

    protected function extractDtoStructure(string $dtoClass): array
    {
        $ref = new ReflectionClass($dtoClass);
        $props = [];

        foreach ($ref->getProperties() as $prop) {
            if ($prop->getDeclaringClass()->getName() !== $dtoClass) {
                continue;
            }

            $type = $prop->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            $props[$prop->getName()] = [
                'type' => $type->getName(),
                'nullable' => $type->allowsNull(),
            ];
        }

        return $props;
    }
}
