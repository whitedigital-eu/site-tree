<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;
use WhiteDigital\SiteTree\ApiResource\HtmlApiResource;
use WhiteDigital\SiteTree\Contracts\TreeEntity;

#[ORM\Entity]
#[Mapping(HtmlApiResource::class)]
class Html extends BaseEntity implements TreeEntity
{
    use Id;
    use Traits\SiteTreeNode;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $content = null;

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): self
    {
        $this->content = $content;

        return $this;
    }
}
