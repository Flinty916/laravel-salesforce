<?php

namespace Flinty916\LaravelSalesforce\Commands;

use Flinty916\LaravelSalesforce\Service\SalesforceClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSalesforceObjects extends Command
{
    protected $signature = 'salesforce:generate-objects {--objects=}';
    protected $description = 'Generate classes for all Salesforce objects';

    public function handle()
    {
        $client = app(SalesforceClient::class);
        $outputPath = app_path('SalesforceObjects');
        $stubPath = __DIR__ . '/../resources/SObject.stub';

        if (!File::exists($stubPath)) {
            $this->error("Stub file missing at: $stubPath");
            return self::FAILURE;
        }

        if (!File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        $only = collect(explode(',', (string) $this->option('objects')))
            ->map(fn($s) => trim($s))
            ->filter() // remove empties
            ->values();

        $sobjects = ((array) $client->get('/services/data/v' . config('salesforce.api_version') . '/sobjects'))['sobjects'] ?? [];

        foreach ($sobjects as $object) {
            $name = $object->name; // e.g. Eye_Exam__c

            if ($only->isNotEmpty() && !$only->contains($name)) {
                continue;
            }

            // Class name: remove __c and snake underscores
            $nameWithoutSuffix = preg_replace('/__c$/', '', $name);
            $className = str_replace('_', '', ucwords($nameWithoutSuffix, '_'));

            $describe = (array) $client->get("/services/data/v" . config('salesforce.api_version') . "/sobjects/{$name}/describe");
            $fieldsForDoc = [];
            $casts = [];
            $constants = [];

            foreach ($describe['fields'] ?? [] as $field) {
                $sfType = $field->type ?? null;
                $phpDocType = $this->sfTypeToPhpType($sfType) ?? 'mixed';
                $fieldsForDoc[] = " * @property {$phpDocType} \${$field->name}";

                if ($cast = $this->sfTypeToCast($sfType)) {
                    $casts[$field->name] = $cast;
                }
            }

            // Pretty-print casts with short array syntax
            $castsExport = $this->exportArrayShort($casts);
            $constantsCode = implode("\n\t", $constants);

            $stub = File::get($stubPath);
            $contents = str_replace(
                ['{{class}}', '{{object}}', '{{properties}}', '{{casts}}', '{{constants}}'],
                [$className, $name, implode("\n", $fieldsForDoc), $castsExport, $constantsCode],
                $stub
            );

            File::put("{$outputPath}/{$className}.php", $contents);
            $this->info("Generated: {$className}");
        }

        $this->info('Model generation complete.');
        return self::SUCCESS;
    }

    private function sfTypeToPhpType(?string $type): ?string
    {
        return match ($type) {
            'id', 'string', 'phone', 'url', 'textarea', 'picklist', 'currency' => 'string',
            'boolean' => 'bool',
            'int'     => 'int',
            'double'  => 'float',
            'percent' => 'float',
            'date'    => '\Carbon\Carbon|null',
            'datetime' => '\Carbon\Carbon|null',
            default   => null,
        };
    }

    private function sfTypeToCast(?string $type): ?string
    {
        return match ($type) {
            'id', 'string', 'phone', 'url', 'textarea', 'picklist', 'currency' => 'string',
            'boolean' => 'bool',
            'int'     => 'int',
            'double'  => 'float',
            'percent' => 'float',
            'date'    => 'date',
            'datetime' => 'datetime',
            default   => null, // leave as-is
        };
    }

    /**
     * Export an array as a short-notation PHP array without class names,
     * stable key ordering for cleaner diffs.
     */
    private function exportArrayShort(array $arr): string
    {
        ksort($arr);
        $parts = [];
        foreach ($arr as $k => $v) {
            // keys are SF field names; always quote
            $k = var_export($k, true);
            $v = var_export($v, true);
            $parts[] = "    {$k} => {$v},";
        }
        if (empty($parts)) {
            return "[]";
        }
        return "[\n" . implode("\n", $parts) . "\n]";
    }
}
