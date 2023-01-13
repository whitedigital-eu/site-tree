<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
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
                normalizationContext: ['groups' => ['redirect:item', ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => ['redirect:read', ], ],
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => ['redirect:patch', ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => ['redirect:post', ], ],
            ),
        ],
        routePrefix: '/wd',
        normalizationContext: ['groups' => ['redirect:read', 'redirect:item', ], ],
        denormalizationContext: ['groups' => ['redirect:post', ], ],
        order: ['id' => Criteria::ASC, ],
        provider: RedirectDataProvider::class,
        processor: RedirectDataProcessor::class,
    )
]
#[Mapping(Redirect::class)]
class RedirectApiResource extends BaseResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['redirect:item', 'redirect:read', ])]
    public mixed $id = null;

    #[Groups(['redirect:item', 'redirect:read', 'redirect:patch', 'redirect:post', ])]
    #[Assert\NotBlank]
    public ?int $code = null;

    #[Groups(['redirect:item', 'redirect:read', 'redirect:patch', 'redirect:post', ])]
    #[Assert\NotBlank]
    public ?string $content = null;

    #[Groups(['redirect:read', 'redirect:item', 'redirect:patch', 'redirect:post', ])]
    #[Assert\NotBlank]
    public ?SiteTreeApiResource $node = null;

    #[Groups(['redirect:item', 'redirect:read', ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups(['redirect:item', 'redirect:read', ])]
    public ?DateTimeImmutable $updatedAt = null;
}
