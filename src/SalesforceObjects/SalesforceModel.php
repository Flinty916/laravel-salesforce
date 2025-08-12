<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

use Flinty916\LaravelSalesforce\Service\SalesforceClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use stdClass;

abstract class SalesforceModel
{

    protected static string $object = "";
    public ?string $Id = null;

    protected static function client(): SalesforceClient
    {
        return app(SalesforceClient::class);
    }

    public static function query(): SalesforceQueryBuilder
    {
        return new SalesforceQueryBuilder(static::$object);
    }

    public static function fields(array $fields): SalesforceQueryBuilder
    {
        return static::query()->fields($fields);
    }

    public static function where(...$args): SalesforceQueryBuilder
    {
        return static::query()->where(...$args);
    }

    public static function orWhere(...$args): SalesforceQueryBuilder
    {
        return static::query()->orWhere(...$args);
    }

    public static function orderBy(string $field, string $direction = 'asc'): SalesforceQueryBuilder
    {
        return static::query()->orderBy($field, $direction);
    }

    public static function limit(int $limit): SalesforceQueryBuilder
    {
        return static::query()->limit($limit);
    }

    public static function get(): SalesforceQueryBuilder
    {
        return static::query()->get();
    }

    public static function find(string $id): ?static
    {
        $record = static::query()
            ->fields(['FIELDS(ALL)'])
            ->where('Id', '=', $id)
            ->limit(1)
            ->first();

        if (!$record) {
            return null;
        }

        $model = new static();
        foreach ($record as $key => $value) {
            $model->{$key} = $value;
        }

        return $model;
    }

    public static function records(): Collection
    {
        return static::query()->records();
    }

    public static function create(array|stdClass $data): ?static
    {
        $response = (self::client()->post('/services/data/v' . config('salesforce.api_version') . '/sobjects/' . static::$object, $data))->id;
        $model = new static();
        $model->Id = $response;
        return $model;
    }

    public function update(array|stdClass $data): void
    {
        $this->client()->put('/services/data/v' . config('salesforce.api_version') . '/sobjects/' . static::$object . '/' . $this->Id, $data);
    }

    public function delete(): void
    {
        $response = $this->client()->delete('/services/data/v' . config('salesforce.api_version') . '/sobjects/' . static::$object . '/' . $this->Id);
        Log::debug("DELETE: " . json_encode($response, JSON_PRETTY_PRINT));
    }
}
