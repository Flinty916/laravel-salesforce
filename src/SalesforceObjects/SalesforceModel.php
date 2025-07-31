<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

use Flinty916\LaravelSalesforce\Service\SalesforceClient;
use Illuminate\Support\Collection;
use stdClass;

abstract class SalesforceModel
{

    protected static string $object = "";

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

    public static function records(): Collection
    {
        return static::query()->records();
    }

    public static function create(array|stdClass $data): string
    {
        return (self::client()->post('/services/data/v' . config('salesforce.api_version') . '/sobjects/' . static::$object, $data))->id;
    }
}
