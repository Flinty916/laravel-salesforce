<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

class SalesforceChildRelationship
{
    public string $childSObject;
    public string $field;
    public bool $deprecatedAndHidden;
    public bool $cascadeDelete;
    public bool $restrictedDelete;

    public static function fromArray(array $data): self
    {
        $rel = new self();
        $rel->childSObject = $data['childSObject'];
        $rel->field = $data['field'];
        $rel->deprecatedAndHidden = $data['deprecatedAndHidden'];
        $rel->cascadeDelete = $data['cascadeDelete'];
        $rel->restrictedDelete = $data['restrictedDelete'];

        return $rel;
    }
}
