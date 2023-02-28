<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
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
        routePrefix: '/wd/st',
        provider: ContentTypeDataProvider::class,
    )
]
class ContentTypeResource
{
    public ?int $nodeId = null;

    public ?SiteTreeResource $node = null;

    public ?string $type = null;

    public mixed $resource = null;

    /** @var BaseResource[]|null */
    public ?array $resources = null;
}
