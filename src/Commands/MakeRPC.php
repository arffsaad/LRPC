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
        $name = Str::studly($this->argument('name') ?? $this->ask('Enter the name of the procedure (in PascalCase e.g GetUser, MyProcedure etc)'));
        $summary = $this->ask("Enter a short summary for '$name'");

        $requestFields = $this->askForFields('Request');
        $responseFields = $this->askForFields('Response');

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

        $this->generateFromStub(__DIR__.'/../../stubs/dto.stub', $requestClass, [
            '{{ namespace }}' => $dataNamespace,
            '{{ class }}' => "{$name}Request",
            '{{ properties }}' => $this->generateDtoConstructor($requestFields),
        ]);

        $this->generateFromStub(__DIR__.'/../../stubs/dto.stub', $responseClass, [
            '{{ namespace }}' => $dataNamespace,
            '{{ class }}' => "{$name}Response",
            '{{ properties }}' => $this->generateDtoConstructor($responseFields),
        ]);

        $this->info('✅ Procedure generated!');
        $this->info("✏️ $name - $summary");
        $this->line('Request fields');
        $this->table(
            ['Name', 'Type', 'Nullable'],
            collect($requestFields)->map(fn ($field) => [
                $field['name'],
                $field['type'],
                $field['nullable'] ? 'Yes' : 'No',
            ])
        );

        $this->line('Response fields');
        $this->table(
            ['Name', 'Type', 'Nullable'],
            collect($responseFields)->map(fn ($field) => [
                $field['name'],
                $field['type'],
                $field['nullable'] ? 'Yes' : 'No',
            ])
        );

        $this->line("📦\nGenerated files:");
        $this->line("🔗 Procedure: $procedureClass");
        $this->line("🔗 Request DTO: $requestClass");
        $this->line("🔗 Response DTO: $responseClass");
    }

    protected array $availableTypes = [
        'string',
        'int',
        'float',
        'bool',
        'array',
        'object',
    ];

    protected function askForFields(string $section): array
    {
        $this->info("Define $section Parameters");
        $this->line('------------------');

        $fields = [];

        while (true) {
            $name = $this->ask('Parameter Name (Leave blank to finish)');
            if (empty($name)) {
                break;
            }
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
                $this->error("Invalid parameter name '$name'. Must be a valid PHP variable name.");

                continue;
            }

            $types = array_values($this->availableTypes);

            $type = $this->choice(
                "Select type for '$name'",
                $types,
            );

            $nullable = $this->confirm("Is '$name' nullable?", false);

            $fields[] = [
                'name' => $name,
                'type' => $type,
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
        return collect($fields)->map(function ($meta) {
            $type = $meta['type'];
            $name = $meta['name'];

            if ($meta['nullable']) {
                $type = "?$type";
            }

            return "public $type \$$name";
        })->implode(",\n        ");
    }
}
