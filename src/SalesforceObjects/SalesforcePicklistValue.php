<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

class SalesforcePicklistValue
{
    public bool $active;
    public bool $defaultValue;
    public string $label;
    public ?string $validFor;
    public string $value;


    public static function fromArray(array $data): self
    {
        $field = new self();
        $field->label = $data['label'];
        $field->value = $data['value'];
        $field->validFor = $data['validFor'];
        $field->active = $data['active'] ?? false;
        $field->defaultValue = $data['defaultValue'] ?? false;
        return $field;
    }
}
