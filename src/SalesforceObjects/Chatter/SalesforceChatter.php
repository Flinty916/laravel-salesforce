<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Flinty916\LaravelSalesforce\Service\SalesforceClient;
use Illuminate\Support\Collection;

class SalesforceChatter
{

    private SalesforceClient $client;

    public function __construct(protected string $id)
    {
        $this->client = app(SalesforceClient::class);
    }

    public function all(): Collection
    {
        $response = $this->client->get('/services/data/v' . config('salesforce.api_version') . '/chatter/feeds/record/' . $this->id . '/feed-elements');
        $response = SalesforceChatterResponse::fromArray((array) $response);
        return $response->elements;
    }

    public function post(string $message)
    {
        $data = [
            'body' => ['messageSegments' => [['type' => 'Text', 'text' => $message]]],
            'feedElementType' => 'FeedItem',
            'subjectId' => $this->id,
        ];
        return $this->client->post("/services/data/v" . config('salesforce.api_version') . "/chatter/feed-elements", $data);
    }

    public function update(string $feedElementId, string $message)
    {
        $data = array(
            'body' => ['messageSegments' => [['type' => 'Text', 'text' => $message]]]
        );
        $this->client->put("/services/data/v" . config('salesforce.api_version') . "/chatter/feed-elements/{$feedElementId}", $data);
    }
}
