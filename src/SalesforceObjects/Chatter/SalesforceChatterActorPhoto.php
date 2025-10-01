<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterActorPhoto
{

    public string $fullEmailPhotoUrl;
    public string $largePhotoUrl;
    public string $mediumPhotoUrl;
    public ?string $photoVersionId;
    public string $smallPhotoUrl;
    public string $standardEmailPhotoUrl;
    public string $url;


    public static function fromArray(array $data): self
    {
        $photo = new self();
        $photo->fullEmailPhotoUrl = $data['fullEmailPhotoUrl'];
        $photo->largePhotoUrl = $data['largePhotoUrl'];
        $photo->mediumPhotoUrl = $data['mediumPhotoUrl'];
        $photo->photoVersionId = $data['photoVersionId'];
        $photo->smallPhotoUrl = $data['smallPhotoUrl'];
        $photo->standardEmailPhotoUrl = $data['standardEmailPhotoUrl'];
        $photo->url = $data['url'];
        return $photo;
    }
}
