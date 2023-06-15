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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Filters\ResourceNumericFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceOrderFilter;
use WhiteDigital\EntityResourceMapper\Filters\ResourceSearchFilter;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\DataProcessor\RedirectDataProcessor;
use WhiteDigital\SiteTree\DataProvider\RedirectDataProvider;
use WhiteDigital\SiteTree\Entity\Redirect;

#[
    ApiResource(
        shortName: 'Redirect',
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
        provider: RedirectDataProvider::class,
        processor: RedirectDataProcessor::class,
    ),
    ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),
    ApiFilter(ResourceNumericFilter::class, properties: ['node.id', 'code', ]),
    ApiFilter(ResourceOrderFilter::class, properties: ['node.id', 'id', 'code', ]),
    ApiFilter(ResourceSearchFilter::class, properties: ['content', ]),
]
#[Mapping(Redirect::class)]
class RedirectResource extends BaseResource
{
    use Traits\CreatedUpdated;
    use Traits\Groups;
    use Traits\SiteTreeNode;

    public const PREFIX = 'redirect:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, ])]
    public mixed $id = null;

    #[Groups([self::READ, self::WRITE, ])]
    #[Assert\NotNull]
    #[Assert\Choice([Response::HTTP_MOVED_PERMANENTLY, Response::HTTP_FOUND, Response::HTTP_TEMPORARY_REDIRECT, Response::HTTP_PERMANENTLY_REDIRECT, ])]
    public ?int $code = null;

    #[Groups([self::READ, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $content = null;

    #[Groups([self::READ, self::WRITE, ])]
    #[Assert\NotNull]
    public bool $isExternal = false;
}
