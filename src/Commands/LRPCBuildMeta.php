<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionNamedType;
use Illuminate\Support\Str;

class LRPCBuildMeta extends Command
{
    protected $signature = 'lrpc:build-meta {--dry-run}';
    protected $description = 'Build metadata file for all internal LRPC procedures';

    public function handle()
    {
        $internalNamespace = config('lrpc.namespaces.internal', 'App\\Lrpc\\Internal');
        $internalPath = base_path(str_replace('\\', '/', Str::after($internalNamespace, 'App\\')));

        $metadata = [];

        foreach (File::allFiles($internalPath) as $file) {
            $className = $internalNamespace . '\\' . $file->getBasename('.php');

            if (!class_exists($className)) continue;

            $ref = new ReflectionClass($className);
            if (!$ref->isSubclassOf(\ArffSaad\LRPC\Support\BaseProcedure::class)) continue;

            $instance = app($className);
            $procedureName = $ref->getShortName();

            $requestDto = $ref->getMethod('handle')->getParameters()[0]->getType()->getName();
            $responseDto = $ref->getMethod('handle')->getReturnType()->getName();

            $metadata[$procedureName] = [
                'request' => $this->extractDtoStructure($requestDto),
                'response' => $this->extractDtoStructure($responseDto),
            ];
        }

        $hash = md5(json_encode($metadata));

        $final = [
            '_hash' => $hash,
            'procedures' => $metadata,
        ];

        $outputPath = $internalPath . '/.metadata.json';

        if ($this->option('dry-run')) {
            $this->info("Metadata generated:");
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
            $type = $prop->getType();

            if (!$type instanceof ReflectionNamedType) continue;

            $props[$prop->getName()] = [
                'type' => $type->getName(),
                'nullable' => $type->allowsNull(),
            ];
        }

        return $props;
    }
}
