<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource\Traits;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Validator\Constraints\SiteTreeNotUsedInActiveItem;

trait SiteTreeNode
{
    private const EXAMPLE = ['example' => '/api/wd/st/site_trees/1', ];

    #[Groups([self::READ, self::ITEM, self::PATCH, self::WRITE, ])]
    #[Assert\NotBlank]
    #[ApiProperty(openapiContext: self::EXAMPLE)]
    #[SiteTreeNotUsedInActiveItem]
    public ?SiteTreeResource $node = null;
}
