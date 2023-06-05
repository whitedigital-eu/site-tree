<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use WhiteDigital\SiteTree\DataProvider\TypeDataProvider;

#[
    ApiResource(
        shortName: 'SiteTree',
        operations: [
            new GetCollection(
                uriTemplate: '/types',
            ),
        ],
        paginationClientEnabled: false,
        paginationEnabled: false,
        provider: TypeDataProvider::class,
    )
]
class TypeResource
{
    public ?string $type = null;

    public bool $isSingle = false;
}
