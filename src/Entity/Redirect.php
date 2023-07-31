<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\SiteTree\ApiResource\RedirectResource;

#[ORM\Entity]
#[ORM\Table(name: 'ct_redirect')]
#[Mapping(RedirectResource::class)]
class Redirect extends AbstractNodeEntity
{
    public const TYPE = 'redirect';

    #[ORM\Column(nullable: false)]
    protected ?int $code = null;

    #[ORM\Column(nullable: false)]
    protected ?string $content = null;

    #[ORM\Column(options: ['default' => false])]
    protected bool $isExternal = false;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getIsExternal(): bool
    {
        return $this->isExternal;
    }

    public function setIsExternal(bool $isExternal): static
    {
        $this->isExternal = $isExternal;

        return $this;
    }
}
