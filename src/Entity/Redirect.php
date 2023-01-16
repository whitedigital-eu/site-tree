<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;
use WhiteDigital\SiteTree\ApiResource\RedirectApiResource;
use WhiteDigital\SiteTree\Contracts\TreeEntity;

#[ORM\Entity]
#[Mapping(RedirectApiResource::class)]
class Redirect extends BaseEntity implements TreeEntity
{
    use Id;
    use Traits\Active;
    use Traits\SiteTreeNode;

    #[ORM\Column(nullable: false)]
    private ?int $code = null;

    #[ORM\Column(nullable: false)]
    private ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }
}
