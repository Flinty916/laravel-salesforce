<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterElement
{

    public string $id;
    public bool $isDeleteRestricted;
    public bool $isShareable;
    public string $modifiedDate;
    public string $photoUrl;
    public string $relativeCreatedDate;
    public string $type;
    public string $url;
    public string $visibility;
    public string $createdDate;
    public SalesforceChatterBody $body;
    public SalesforceChatterActor $actor;

    public static function fromArray(array $data): self
    {
        $chat = new self();
        $chat->id = $data['id'];
        $chat->isDeleteRestricted = $data['isDeleteRestricted'];
        $chat->isShareable = $data['isShareable'] ?? false;
        $chat->modifiedDate = $data['modifiedDate'];
        $chat->photoUrl = $data['photoUrl'];
        $chat->relativeCreatedDate = $data['relativeCreatedDate'];
        $chat->type = $data['type'];
        $chat->url = $data['url'];
        $chat->visibility = $data['visibility'];
        $chat->createdDate = $data['createdDate'];
        $chat->body = SalesforceChatterBody::fromArray((array) $data['body']);
        $chat->actor = SalesforceChatterActor::fromArray((array) $data['actor']);
        return $chat;
    }
}
