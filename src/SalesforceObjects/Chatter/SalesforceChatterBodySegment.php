<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterBodySegment
{

    public ?string $altText;
    public ?string $htmlTag;
    public ?string $markupType;
    public string $text;
    public string $type;
    public ?string $url;

    public static function fromArray(array $data): self
    {
        $segment = new self();
        $segment->altText = $data['altText'] ?? null;
        $segment->htmlTag = $data['htmlTag'] ?? null;
        $segment->markupType = $data['markupType'] ?? null;
        $segment->text = $data['text'];
        $segment->type = $data['type'];
        $segment->url = $data['url'] ?? null;
        return $segment;
    }
}
