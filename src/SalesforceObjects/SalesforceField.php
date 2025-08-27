<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

use Illuminate\Support\Collection;

class SalesforceField
{
    public string $name;
    public string $label;
    public string $type;
    public bool $nillable;
    public bool $createable;
    public bool $updateable;
    public bool $unique;
    public bool $autoNumber;
    public int $length;
    public Collection $picklistValues;

    public static function fromArray(array $data): self
    {
        $field = new self();
        $field->name = $data['name'];
        $field->label = $data['label'];
        $field->type = $data['type'];
        $field->nillable = $data['nillable'];
        $field->createable = $data['createable'];
        $field->updateable = $data['updateable'];
        $field->unique = $data['unique'] ?? false;
        $field->autoNumber = $data['autoNumber'] ?? false;
        $fields = [];
        foreach ($data['picklistValues'] as $rawField) {
            $fields[] = SalesforcePicklistValue::fromArray((array) $rawField);
        }
        $field->picklistValues = collect($fields);
        return $field;
    }
}
