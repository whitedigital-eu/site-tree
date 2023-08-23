<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Api\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\OpenApi\Model;
use WhiteDigital\SiteTree\DataProvider\ContentTypeDataProvider;

#[
    ApiResource(
        shortName: 'SiteTree',
        operations: [
            new Get(
                uriTemplate: '/content_types/{slug}',
                uriVariables: [
                    'slug' => new Link(fromProperty: 'slug', fromClass: self::class, identifiers: ['slug']),
                ],
                requirements: ['slug' => '.+', ],
                openapi: new Model\Operation(
                    summary: 'Check if given slug (id) is a valid site tree slug',
                    description: 'Check if given slug (id) is a valid site tree slug',
                ),
            ),
        ],
        paginationClientEnabled: false,
        paginationEnabled: false,
        provider: ContentTypeDataProvider::class,
    )
]
class ContentTypeResource
{
    public ?int $nodeId = null;

    #[ApiProperty(openapiContext: ['example' => '/api/site_trees/1', ])]
    public ?SiteTreeResource $node = null;

    public ?string $type = null;

    public mixed $resource = null;

    public ?string $slug = null;
}
