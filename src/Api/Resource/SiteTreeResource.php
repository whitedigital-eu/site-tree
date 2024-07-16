<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Api\Resource;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\Serializer\Filter\GroupFilter;
use ArrayObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Attribute\SkipCircularReferenceCheck;
use WhiteDigital\EntityResourceMapper\Filters\ResourceBooleanFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceExistsFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceNumericFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceSearchFilter;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\Api\Traits;
use WhiteDigital\SiteTree\DataProcessor\SiteTreeDataProcessor;
use WhiteDigital\SiteTree\DataProcessor\SiteTreeMovementDataProcessor;
use WhiteDigital\SiteTree\DataProvider\SiteTreeDataProvider;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Validator\Constraints\AllowedType;

#[
    ApiResource(
        shortName: 'SiteTree',
        operations: [
            new Delete(
                requirements: ['id' => '\d+', ],
            ),
            new Get(
                requirements: ['id' => '\d+', ],
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new GetCollection(
                paginationEnabled: false,
                paginationClientEnabled: false,
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/' . SiteTreeMovementDataProcessor::UP,
                requirements: ['id' => '\d+', ],
                status: Response::HTTP_NO_CONTENT,
                openapi: new Model\Operation(
                    summary: 'Move node within level up by 1 position',
                    description: 'Move node within level up by 1 position',
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject(),
                        required: false,
                    ),
                ),
                denormalizationContext: ['groups' => [self::MOVE, ], ],
                processor: SiteTreeMovementDataProcessor::class,
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/' . SiteTreeMovementDataProcessor::DOWN,
                requirements: ['id' => '\d+', ],
                status: Response::HTTP_NO_CONTENT,
                openapi: new Model\Operation(
                    summary: 'Move node within level down by 1 position',
                    description: 'Move node within level down by 1 position',
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject(),
                        required: false,
                    ),
                ),
                denormalizationContext: ['groups' => [self::MOVE, ], ],
                processor: SiteTreeMovementDataProcessor::class,
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/' . SiteTreeMovementDataProcessor::TOP,
                requirements: ['id' => '\d+', ],
                status: Response::HTTP_NO_CONTENT,
                openapi: new Model\Operation(
                    summary: 'Move node within level up to top position',
                    description: 'Move node within level up to top position',
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject(),
                        required: false,
                    ),
                ),
                denormalizationContext: ['groups' => [self::MOVE, ], ],
                processor: SiteTreeMovementDataProcessor::class,
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/' . SiteTreeMovementDataProcessor::BOTTOM,
                requirements: ['id' => '\d+', ],
                status: Response::HTTP_NO_CONTENT,
                openapi: new Model\Operation(
                    summary: 'Move node within level down to bottom position',
                    description: 'Move node within level down to bottom position',
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject(),
                        required: false,
                    ),
                ),
                denormalizationContext: ['groups' => [self::MOVE, ], ],
                processor: SiteTreeMovementDataProcessor::class,
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/to/{position}',
                requirements: ['id' => '\d+', ],
                status: Response::HTTP_NO_CONTENT,
                openapi: new Model\Operation(
                    summary: 'Move node within level to position',
                    description: 'Move node within level to position',
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject(),
                        required: false,
                    ),
                ),
                denormalizationContext: ['groups' => [self::MOVE, ], ],
                processor: SiteTreeMovementDataProcessor::class,
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [self::PATCH, ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => [self::WRITE, ], ],
            ),
        ],
        normalizationContext: ['groups' => [self::READ, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        provider: SiteTreeDataProvider::class,
        processor: SiteTreeDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceExistsFilter::class, properties: ['parent', ]),
    ApiFilter(ResourceBooleanFilter::class, properties: ['isActive', 'isVisible']),
    ApiFilter(ResourceNumericFilter::class, properties: ['level', 'parent.id', ]),
    ApiFilter(ResourceSearchFilter::class, properties: ['slug', 'slug', 'title', 'type', ]),
]
#[Mapping(SiteTree::class)]
class SiteTreeResource extends BaseResource
{
    use Traits\CreatedUpdated;
    use Traits\Groups;

    public const PREFIX = 'site_tree:';
    public const MOVE = self::PREFIX . 'move';

    #[ApiProperty(
        required: true,
        identifier: true,
        openapiContext: ['type' => 'integer'],
    )]
    #[Groups([self::READ, ])]
    public mixed $id = null;

    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    #[ApiProperty(openapiContext: ['example' => '/api/site_trees/1', ])]
    public ?self $root = null;

    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    public ?int $level = null;

    #[Groups([self::READ, ])]
    public ?int $left = null;

    public ?int $right = null;

    #[Groups([self::READ, self::PATCH, ])]
    public bool $isActive = false;

    #[Groups([self::READ, self::PATCH, ])]
    public bool $isVisible = true;

    #[Groups([self::WRITE, self::PATCH, ])]
    #[ApiProperty(openapiContext: ['example' => '/api/site_trees/1', ])]
    public ?self $parent = null;

    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    #[Assert\NotNull]
    public ?string $slug = null;

    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    public ?string $metaTitle = null;

    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    public ?string $metaDescription = null;

    #[Groups([self::READ, self::WRITE, ])]
    #[AllowedType]
    #[Assert\NotBlank]
    public ?string $type = null;

    /** @var self[]|null */
    #[Groups([self::READ, self::WRITE, self::PATCH, ])]
    #[SkipCircularReferenceCheck]
    #[ApiProperty(openapiContext: ['example' => ['/api/site_trees/1'], ])]
    public ?array $children = null;
}
