<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource\Traits;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;

trait SiteTreeNode
{
    #[Groups([self::READ, self::WRITE, ])]
    #[Assert\NotBlank]
    #[ApiProperty(openapiContext: ['example' => '/api/site_trees/1', ])]
    public ?SiteTreeResource $node = null;

    #[Groups([self::READ, self::WRITE, ])]
    public ?string $slug = null;
}
