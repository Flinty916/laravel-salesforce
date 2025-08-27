<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

use Illuminate\Support\Collection;

class SalesforceDescription
{
    public string $name;
    public string $label;
    public bool $custom;
    public bool $createable;
    public bool $updateable;
    public bool $deletable;
    public bool $queryable;
    public bool $searchable;
    public bool $triggerable;

    public Collection $fields;

    /** @var SalesforceChildRelationship[] */
    public array $childRelationships = [];

    public static function fromArray(array $data): self
    {
        $desc = new self();
        $desc->name = $data['name'];
        $desc->label = $data['label'];
        $desc->custom = $data['custom'];
        $desc->createable = $data['createable'];
        $desc->updateable = $data['updateable'];
        $desc->deletable = $data['deletable'];
        $desc->queryable = $data['queryable'];
        $desc->searchable = $data['searchable'];
        $desc->triggerable = $data['triggerable'];

        $desc->fields = collect(array_map(fn($f) => SalesforceField::fromArray((array)$f), $data['fields'] ?? []));
        $desc->childRelationships = array_map(fn($c) => SalesforceChildRelationship::fromArray((array)$c), $data['childRelationships'] ?? []);

        return $desc;
    }
}
