<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterResponse
{

    public ?string $currentPageToken;
    public ?string $currentPageUrl;
    public Collection $elements;
    public ?bool $isModifiedToken;
    public ?string $isModifiedUrl;
    public ?string $nextPageToken;
    public ?string $nextPageUrl;
    public ?string $updatesToken;
    public ?string $updatesUrl;

    public static function fromArray(array $data): self
    {
        $response = new self();
        $response->currentPageToken = $data['currentPageToken'];
        $response->currentPageUrl = $data['currentPageUrl'];
        $response->elements = new Collection();
        foreach ($data['elements'] as $ele)
            $response->elements->push(SalesforceChatterElement::fromArray((array) $ele));
        $response->isModifiedToken = $data['isModifiedToken'];
        $response->nextPageToken = $data['nextPageToken'];
        $response->nextPageUrl = $data['nextPageUrl'];
        $response->updatesToken = $data['updatesToken'];
        $response->updatesUrl = $data['updatesUrl'];
        return $response;
    }
}
