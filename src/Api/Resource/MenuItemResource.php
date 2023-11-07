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
use ApiPlatform\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Filters\ResourceDateFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceNumericFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceOrderFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceSearchFilter;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\EntityResourceMapper\UTCDateTimeImmutable;
use WhiteDigital\SiteTree\DataProcessor\MenuItemDataProcessor;
use WhiteDigital\SiteTree\DataProvider\MenuItemDataProvider;
use WhiteDigital\SiteTree\Entity\MenuItem;

#[
    ApiResource(
        shortName: 'MenuItem',
        operations: [
            new Delete(
                requirements: ['id' => '\d+', ],
            ),
            new Get(
                requirements: ['id' => '\d+', ],
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [self::WRITE, ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => [self::WRITE, ], ],
            ),
        ],
        normalizationContext: ['groups' => [self::READ, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        order: ['createdAt' => Criteria::DESC, ],
        provider: MenuItemDataProvider::class,
        processor: MenuItemDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceDateFilter::class, properties: ['createdAt', 'updatedAt']),
    ApiFilter(ResourceNumericFilter::class, properties: ['node.id']),
    ApiFilter(ResourceOrderFilter::class, properties: ['id', 'title', 'slug', 'createdAt', 'updatedAt']),
    ApiFilter(ResourceSearchFilter::class, properties: ['title', 'slug']),
    Mapping(MenuItem::class),
]
class MenuItemResource extends BaseResource
{
    public const PREFIX = 'menu_item:';

    private const READ = self::PREFIX . 'read'; // menu_item:read
    private const WRITE = self::PREFIX . 'write'; // menu_item:write

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, ])]
    public mixed $id = null;

    #[Groups([self::READ, ])]
    public ?UTCDateTimeImmutable $createdAt = null;

    #[Groups([self::READ, ])]
    public ?UTCDateTimeImmutable $updatedAt = null;

    #[Groups([self::READ, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Groups([self::READ, self::WRITE, ])]
    #[ApiProperty(openapiContext: ['example' => '/api/site_trees/1'])]
    #[Assert\NotBlank]
    public ?SiteTreeResource $node = null;

    public ?string $slug = null;
}
