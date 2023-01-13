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
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Attribute\SkipCircularReferenceCheck;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\Attribute\Translatable;
use WhiteDigital\SiteTree\DataProcessor\SiteTreeDataProcessor;
use WhiteDigital\SiteTree\DataProvider\SiteTreeDataProvider;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Validator\Constraints\AllowedType;
use WhiteDigital\SiteTree\Validator\Constraints\ValidateTree;

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
        routePrefix: '/wd',
        normalizationContext: ['groups' => ['site_tree:read', 'site_tree:item', ], ],
        denormalizationContext: ['groups' => ['site_tree:post', ], ],
        order: ['root' => Criteria::ASC, 'left' => Criteria::ASC, ],
        provider: SiteTreeDataProvider::class,
        processor: SiteTreeDataProcessor::class,
    )
]
#[Mapping(SiteTree::class)]
#[ValidateTree]
class SiteTreeApiResource extends BaseResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public mixed $id = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public ?SiteTreeApiResource $root = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?int $level = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?int $left = null;

    public ?int $right = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public bool $isActive = false;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public bool $isVisible = true;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups(['site_tree:item', 'site_tree:read', ])]
    public ?DateTimeImmutable $updatedAt = null;

    #[Groups(['site_tree:patch', 'site_tree:post', ])]
    public ?SiteTreeApiResource $parent = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[ApiProperty(openapiContext: ['example' => ['en' => 'example', 'lv' => 'piemers', ]])]
    #[Translatable]
    #[Assert\NotBlank]
    public ?array $title = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[ApiProperty(openapiContext: ['example' => ['en' => 'example', 'lv' => 'piemers', ]])]
    #[Translatable]
    #[Assert\NotBlank]
    #[Assert\All(
        new Assert\Length(min: 3),
    )]
    public ?array $slug = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[ApiProperty(openapiContext: ['example' => ['en' => 'example', 'lv' => 'piemers', ]])]
    #[Translatable]
    public ?array $metaTitle = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[ApiProperty(openapiContext: ['example' => ['en' => 'example', 'lv' => 'piemers', ]])]
    #[Translatable]
    public ?array $metaDescription = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[AllowedType]
    #[Assert\NotBlank]
    public ?string $type = null;

    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    public ?bool $isTranslatable = null;

    /** @var SiteTreeApiResource[]|null */
    #[Groups(['site_tree:item', 'site_tree:read', 'site_tree:patch', 'site_tree:post', ])]
    #[SkipCircularReferenceCheck]
    public ?array $children = null;
}
