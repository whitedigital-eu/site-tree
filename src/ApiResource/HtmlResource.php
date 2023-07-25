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
use Symfony\Component\Serializer\Annotation\Groups;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Filters\ResourceNumericFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceOrderFilter;
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
        order: ['id' => Criteria::ASC, ],
        provider: HtmlDataProvider::class,
        processor: HtmlDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceNumericFilter::class, properties: ['node.id', ]),
    ApiFilter(ResourceOrderFilter::class, properties: ['node.id', 'id', ]),
    ApiFilter(ResourceSearchFilter::class, properties: ['content', ]),
]
#[Mapping(Html::class)]
class HtmlResource extends BaseResource
{
    use Traits\CreatedUpdated;
    use Traits\Groups;
    use Traits\SiteTreeNode;

    public const PREFIX = 'html:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, ])]
    public mixed $id = null;

    #[Groups([self::READ, self::WRITE, ])]
    public ?string $content = null;
}
