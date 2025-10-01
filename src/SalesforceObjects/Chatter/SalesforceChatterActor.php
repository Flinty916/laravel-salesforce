<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterActor
{

    public ?string $additionalLabel;
    public string $communityNickname;
    public ?string $companyName;
    public string $displayName;
    public ?string $firstName;
    public string $id;
    public bool $isActive;
    public bool $isInThisCommunity;
    public ?string $lastName;
    public SalesforceChatterActorMotif $motif;
    public ?string $mySubscription;
    public SalesforceChatterActorPhoto $photo;
    public ?string $reputation;
    public ?string $title;
    public ?string $type;
    public ?string $url;
    public ?string $userType;

    public static function fromArray(array $data): self
    {
        $actor = new self();
        $actor->additionalLabel = $data['additionalLabel'];
        $actor->communityNickname = $data['communityNickname'];
        $actor->companyName = $data['companyName'];
        $actor->displayName = $data['displayName'];
        $actor->firstName = $data['firstName'];
        $actor->id = $data['id'];
        $actor->isActive = $data['isActive'];
        $actor->isInThisCommunity = $data['isInThisCommunity'];
        $actor->lastName = $data['lastName'];
        $actor->motif = SalesforceChatterActorMotif::fromArray((array) $data['motif']);
        $actor->photo = SalesforceChatterActorPhoto::fromArray((array) $data['photo']);
        $actor->mySubscription = $data['mySubscription'];
        $actor->reputation = $data['reputation'];
        $actor->title = $data['title'];
        $actor->type = $data['type'];
        $actor->url = $data['url'];
        $actor->userType = $data['userType'];
        return $actor;
    }
}
