<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Attribute\SkipCircularReferenceCheck;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\DataProcessor\SiteTreeDataProcessor;
use WhiteDigital\SiteTree\DataProvider\SiteTreeDataProvider;
use WhiteDigital\SiteTree\Entity\SiteTree;

#[
    ApiResource(
        shortName: 'SiteTree',
        operations: [
            new Delete(
                requirements: ['id' => '\d+', ],
            ),
            new Get(
                requirements: ['id' => '\d+', ],
                normalizationContext: ['groups' => ['site_tree:item', ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => ['site_tree:read', ], ],
            ),
            new GetCollection(
                uriTemplate: '/site_trees/roots',
                normalizationContext: ['groups' => ['site_tree:read', ], ],
                name: 'roots',
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => ['site_tree:patch', ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => ['site_tree:post', ], ],
            ),
        ],
        normalizationContext: ['groups' => ['site_tree:read', 'site_tree:item', ], ],
        denormalizationContext: ['groups' => ['site_tree:post', ], ],
        order: ['id' => Criteria::ASC, ],
        provider: SiteTreeDataProvider::class,
        processor: SiteTreeDataProcessor::class,
    )
]
#[Mapping(SiteTree::class)]
class SiteTreeApiResource extends BaseResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public mixed $id = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?int $lvl = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?int $lft = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?int $rgt = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public bool $isActive = false;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public bool $isVisible = true;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public bool $isTranslatable = true;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public ?array $title = null;

    /** @var SiteTreeApiResource[]|null */
    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[SkipCircularReferenceCheck]
    public ?array $children = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?DateTimeImmutable $updatedAt = null;

    public ?SiteTreeApiResource $parent = null;

    public ?SiteTreeApiResource $root = null;
}
