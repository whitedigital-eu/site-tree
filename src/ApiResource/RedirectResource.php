<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\ApiResource\ApiResource\Traits as ARTraits;
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
                denormalizationContext: ['groups' => [self::WRITE, ], ],
            ),
        ],
        routePrefix: '/wd/st',
        normalizationContext: ['groups' => [self::ITEM, self::READ, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        order: ['id' => Criteria::ASC, ],
        provider: RedirectDataProvider::class,
        processor: RedirectDataProcessor::class,
    )
]
#[Mapping(Redirect::class)]
class RedirectResource extends BaseResource
{
    use ARTraits\CreatedUpdated;
    use ARTraits\Groups;
    use Traits\SiteTreeNode;

    public const PREFIX = 'redirect:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::ITEM, self::READ, ])]
    public mixed $id = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\Type(type: Type::BUILTIN_TYPE_BOOL)]
    public ?bool $isActive = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    #[Assert\Choice([Response::HTTP_MOVED_PERMANENTLY, Response::HTTP_FOUND, Response::HTTP_TEMPORARY_REDIRECT, Response::HTTP_PERMANENTLY_REDIRECT, ])]
    public ?int $code = null;

    #[Groups([self::ITEM, self::READ, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    public ?string $content = null;
}
