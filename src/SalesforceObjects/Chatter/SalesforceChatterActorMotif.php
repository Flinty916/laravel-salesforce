<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects\Chatter;

use Illuminate\Support\Collection;

class SalesforceChatterActorMotif
{

    public string $color;
    public string $largeIconUrl;
    public string $mediumIconUrl;
    public string $smallIconUrl;
    public ?string $svgIconUrl;


    public static function fromArray(array $data): self
    {
        $motif = new self();
        $motif->color = $data['color'];
        $motif->largeIconUrl = $data['largeIconUrl'];
        $motif->mediumIconUrl = $data['mediumIconUrl'];
        $motif->smallIconUrl = $data['smallIconUrl'];
        $motif->svgIconUrl = $data['svgIconUrl'];
        return $motif;
    }
}
