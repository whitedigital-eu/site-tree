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
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Filters\ResourceSearchFilter;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\Attribute\Translatable;
use WhiteDigital\SiteTree\DataProcessor\HtmlDataProcessor;
use WhiteDigital\SiteTree\DataProvider\HtmlDataProvider;
use WhiteDigital\SiteTree\Entity\Html;

#[
    ApiResource(
        shortName: 'Html',
        operations: [
            new Get(
                requirements: ['id' => '\d+', ],
                normalizationContext: ['groups' => ['html:item', ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => ['html:read', ], ],
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => ['html:patch', ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => ['html:post', ], ],
            ),
        ],
        routePrefix: '/wd',
        normalizationContext: ['groups' => ['html:read', 'html:item', ], ],
        denormalizationContext: ['groups' => ['html:post', ], ],
        order: ['id' => Criteria::ASC, ],
        provider: HtmlDataProvider::class,
        processor: HtmlDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceSearchFilter::class, properties: ['node.id', ]),
]
#[Mapping(Html::class)]
class HtmlApiResource extends BaseResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['html:item', 'html:read', ])]
    public mixed $id = null;

    #[Groups(['html:read', 'html:item', 'html:patch', 'html:post', ])]
    #[Translatable]
    #[Assert\NotBlank]
    public ?array $content = null;

    #[Groups(['html:read', 'html:item', 'html:patch', 'html:post', ])]
    #[Assert\NotBlank]
    public ?SiteTreeApiResource $node = null;

    #[Groups(['html:item', 'html:read', ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups(['html:item', 'html:read', ])]
    public ?DateTimeImmutable $updatedAt = null;
}
