<?php

namespace Flinty916\LaravelSalesforce\Commands;

use Flinty916\LaravelSalesforce\Service\SalesforceClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSalesforceObjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salesforce:generate-objects {--objects=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate classes for all Salesforce objects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = app(SalesforceClient::class);
        $outputPath = app_path('SalesforceObjects');
        $stubPath = __DIR__ . '/../resources/SObject.stub';

        if (!File::exists($stubPath)) {
            $this->error("Stub file missing at: $stubPath");
            return;
        }

        if (!File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        $only = collect(explode(',', $this->option('objects')))
            ->map(fn($s) => trim($s))
            ->filter()
            ->map(fn($s) => ucwords($s));

        $sobjects = $client->get('/sobjects')['sobjects'];

        foreach ($sobjects as $object) {
            $name = $object['name'];

            $className = preg_replace('/[^A-Za-z0-9]/', '', $name);

            if ($only->isNotEmpty() && !$only->contains($className)) {
                continue;
            }

            $describe = $client->get("/sobjects/{$name}/describe")->json();
            $fields = [];
            foreach ($describe['fields'] as $field) {
                $type = $this->sfTypeToPhpType($field['type']) ?? null;
                $fields[] = " * @property {$type} \${$field['name']}";
            }

            $stub = File::get($stubPath);
            $contents = str_replace(
                ['{{class}}', '{{object}}', '{{properties}}'],
                [$className, $name, implode("\n", $fields)],
                $stub
            );

            File::put("{$outputPath}/{$className}.php", $contents);
            $this->info("Generated: {$className}");
        }

        $this->info('Model generation complete.');
    }

    private function sfTypeToPhpType($type): string|null
    {
        switch ($type) {
            case 'id':
                $type = 'string';
                break;
            case 'string':
                $type = 'string';
                break;
            case 'phone':
                $type = 'string';
                break;
            case 'url':
                $type = 'string';
                break;
            case 'textarea':
                $type = 'string';
                break;
            case 'picklist':
                $type = 'string';
                break;
            case 'boolean':
                $type = 'bool';
                break;
            case 'int':
                $type = 'int';
                break;
            case 'double':
                $type = 'float';
                break;
            case 'currency':
                $type = 'string';
                break;
            case 'percent':
                $type = 'float';
                break;
            case 'date':
                $type = '\Carbon\Carbon|null';
                break;
            case 'datetime':
                $type = '\Carbon\Carbon|null';
                break;
            default:
                return null;
        }
        return $type;
    }
}
