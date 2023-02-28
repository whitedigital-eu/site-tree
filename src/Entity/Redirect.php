<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\SiteTree\ApiResource\RedirectResource;

#[ORM\Entity]
#[Mapping(RedirectResource::class)]
class Redirect extends AbstractNodeEntity
{
    #[ORM\Column(nullable: false)]
    protected ?int $code = null;

    #[ORM\Column(nullable: false)]
    protected ?string $content = null;

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
