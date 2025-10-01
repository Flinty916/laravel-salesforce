<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterBody
{

    public bool $isRichText;
    public string $text;
    public Collection $messageSegments;

    public static function fromArray(array $data): self
    {
        $body = new self();
        $body->isRichText = $data['isRichText'];
        $body->text = $data['text'];
        $body->messageSegments = new Collection();
        foreach ($data['messageSegments'] as $segment)
            $body->messageSegments->push(SalesforceChatterBodySegment::fromArray((array) $segment));
        return $body;
    }
}
