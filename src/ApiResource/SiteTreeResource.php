<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\ApiResource\ApiResource\Traits as ARTraits;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Attribute\SkipCircularReferenceCheck;
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
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new GetCollection(
                uriTemplate: '/site_trees/roots',
                normalizationContext: ['groups' => [self::READ, ], ],
                name: 'roots',
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
        order: ['root' => Criteria::ASC, 'left' => Criteria::ASC, ],
        provider: SiteTreeDataProvider::class,
        processor: SiteTreeDataProcessor::class,
    )
]
#[Mapping(SiteTree::class)]
class SiteTreeResource extends BaseResource
{
    use ARTraits\CreatedUpdated;
    use ARTraits\Groups;

    public const PREFIX = 'site_tree:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::ITEM, self::READ, ])]
    public mixed $id = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public ?SiteTreeResource $root = null;

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
    public ?SiteTreeResource $parent = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public ?string $slug = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public ?string $metaTitle = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    public ?string $metaDescription = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[AllowedType]
    #[Assert\NotBlank]
    public ?string $type = null;

    /** @var SiteTreeResource[]|null */
    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[SkipCircularReferenceCheck]
    public ?array $children = null;
}
