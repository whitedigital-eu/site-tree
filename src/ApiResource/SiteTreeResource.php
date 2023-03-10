<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

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
use WhiteDigital\ApiResource\ApiResource\Traits as ARTraits;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Attribute\SkipCircularReferenceCheck;
use WhiteDigital\EntityResourceMapper\Filters\ResourceDateFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceNumericFilter;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\DataProcessor\SiteTreeDataProcessor;
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
                normalizationContext: ['groups' => [self::ITEM, ], ],
            ),
            new GetCollection(
                paginationEnabled: false,
                paginationClientEnabled: false,
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/up',
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
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/down',
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
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/top',
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
            ),
            new Patch(
                uriTemplate: '/site_trees/{id}/bottom',
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
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [self::PATCH, ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => [self::WRITE, ], ],
            ),
        ],
        routePrefix: '/wd/st',
        normalizationContext: ['groups' => [self::ITEM, self::READ, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        provider: SiteTreeDataProvider::class,
        processor: SiteTreeDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceDateFilter::class, properties: ['createdAt', 'updatedAt', ]),
    ApiFilter(ResourceNumericFilter::class, properties: ['level']),
]
#[Mapping(SiteTree::class)]
class SiteTreeResource extends BaseResource
{
    use ARTraits\CreatedUpdated;
    use ARTraits\Groups;

    public const PREFIX = 'site_tree:';
    public const MOVE = self::PREFIX . 'move';

    #[ApiProperty(identifier: true)]
    #[Groups([self::ITEM, self::READ, ])]
    public mixed $id = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public ?self $root = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?int $level = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?int $left = null;

    public ?int $right = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public bool $isActive = false;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public bool $isVisible = true;

    #[Groups([self::PATCH, self::WRITE, ])]
    public ?self $parent = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $slug = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public ?string $metaTitle = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public ?string $metaDescription = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[AllowedType]
    #[Assert\NotBlank]
    public ?string $type = null;

    /** @var self[]|null */
    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[SkipCircularReferenceCheck]
    public ?array $children = null;
}
