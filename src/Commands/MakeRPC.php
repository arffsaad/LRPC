<?php

namespace ArffSaad\LRPC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRPC extends Command
{
    protected $signature = 'make:lrpc {name?}';
    protected $description = 'Generate a new local LRPC procedure with typed DTOs';

    public function handle()
    {
        $name = Str::studly($this->argument('name') ?? $this->ask("Enter the name of the procedure (in PascalCase e.g GetUser, MyProcedure etc)"));
        $summary = $this->ask("Enter a short summary for '$name'");

        $requestFields = $this->collectFields('request');
        $responseFields = $this->collectFields('response');

        $internalNamespace = config('lrpc.namespaces.internal', 'App\\Lrpc\\Internal');
        $basePath = app_path(str_replace('\\', '/', Str::after($internalNamespace, 'App\\')));
        $dataNamespace = "$internalNamespace\\Data";
        $dataPath = "$basePath/Data";

        // Paths
        $procedureClass = "$basePath/{$name}.php";
        $requestClass = "$dataPath/{$name}Request.php";
        $responseClass = "$dataPath/{$name}Response.php";

        // Ensure directory exists
        File::ensureDirectoryExists($dataPath);

        // Generate files from stubs
        $this->generateFromStub(__DIR__.'/../../stubs/internal.stub', $procedureClass, [
            '{{ namespace }}' => $internalNamespace,
            '{{ class }}' => $name,
        ]);

        $this->generateFromStub(__DIR__.'/../../stubs/request-dto.stub', $requestClass, [
            '{{ namespace }}' => $dataNamespace,
            '{{ class }}' => "{$name}Request",
            '{{ properties }}' => $this->generateDtoConstructor($requestFields),
        ]);

        $this->generateFromStub(__DIR__.'/../../stubs/response-dto.stub', $responseClass, [
            '{{ namespace }}' => $dataNamespace,
            '{{ class }}' => "{$name}Response",
            '{{ properties }}' => $this->generateDtoConstructor($responseFields),
        ]);

        $this->info("✅ Procedure '$name' generated!");
        $this->line("Summary: $summary");
        $this->line("Request fields:");
        $this->displayFields($requestFields);
        $this->line("Response fields:");
        $this->displayFields($responseFields);
    }

    protected function collectFields(string $type): array
    {
        $fields = [];

        $this->info("Enter $type fields in format `field:type[:nullable]` (press Enter to finish)");

        while (true) {
            $input = $this->ask("$type param");

            if (empty($input)) break;

            $parts = explode(':', $input);
            $field = $parts[0] ?? null;
            $datatype = $parts[1] ?? null;
            $nullable = isset($parts[2]) && $parts[2] === 'nullable';

            if (!$field || !$datatype) {
                $this->error("Invalid format. Use: name:type[:nullable]");
                continue;
            }

            $fields[$field] = [
                'type' => $datatype,
                'nullable' => $nullable,
            ];
        }

        return $fields;
    }

    protected function generateFromStub(string $stubPath, string $outputPath, array $replacements)
    {
        $stub = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }

        File::put($outputPath, $stub);
    }

    protected function generateDtoConstructor(array $fields): string
    {
        return collect($fields)->map(function ($meta, $name) {
            $type = $meta['type'];
            if ($meta['nullable']) {
                $type = "?$type";
            }

            return "public $type \$$name";
        })->implode(",\n        ");
    }

    protected function displayFields(array $fields): void
    {
        foreach ($fields as $name => $meta) {
            $nullable = $meta['nullable'] ? 'nullable' : 'required';
            $this->line(" - $name: {$meta['type']} ($nullable)");
        }
    }
}
