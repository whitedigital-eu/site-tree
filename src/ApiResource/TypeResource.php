<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use WhiteDigital\SiteTree\DataProvider\TypeDataProvider;

#[
    ApiResource(
        shortName: 'SiteTree',
        operations: [
            new GetCollection(
                uriTemplate: '/available_types',
                openapi: new Model\Operation(
                    summary: 'Returns list of available content types',
                    description: 'Returns list of available content types',
                ),
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
