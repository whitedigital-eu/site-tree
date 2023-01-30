<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Serializer\Filter\GroupFilter;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\ApiResource\ApiResource\Traits as ARTraits;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Filters\ResourceSearchFilter;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\DataProcessor\HtmlDataProcessor;
use WhiteDigital\SiteTree\DataProvider\HtmlDataProvider;
use WhiteDigital\SiteTree\Entity\Html;

#[
    ApiResource(
        shortName: 'Html',
        operations: [
            new Get(
                requirements: ['id' => '\d+', ],
                normalizationContext: ['groups' => [self::ITEM, ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [self::PATCH, ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => [self::READ, ], ],
            ),
        ],
        routePrefix: '/wd/st',
        normalizationContext: ['groups' => [self::READ, self::ITEM, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        order: ['id' => Criteria::ASC, ],
        provider: HtmlDataProvider::class,
        processor: HtmlDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceSearchFilter::class, properties: ['node.id', ]),
]
#[Mapping(Html::class)]
class HtmlResource extends BaseResource
{
    use ARTraits\CreatedUpdated;
    use ARTraits\Groups;
    use Traits\SiteTreeNode;

    public const PREFIX = 'html:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::ITEM, self::READ, ])]
    public mixed $id = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\Type(type: Type::BUILTIN_TYPE_BOOL)]
    #[Assert\NotBlank]
    public ?bool $isActive = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $content = null;
}
