<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use WhiteDigital\SiteTree\DataProvider\ContentTypeDataProvider;

#[
    ApiResource(
        shortName: 'ContentType',
        operations: [
            new Get(
                uriTemplate: '/content_types/{id}',
                requirements: ['id' => '.+', ],
            ),
        ],
        routePrefix: '/wd',
        provider: ContentTypeDataProvider::class,
    )
]
class ContentTypeApiResource
{
    public ?int $nodeId = null;

    public ?SiteTreeApiResource $node = null;

    public ?string $type = null;
}
