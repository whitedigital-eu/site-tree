<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\SiteTree\ApiResource\HtmlResource;

#[ORM\Entity]
#[Mapping(HtmlResource::class)]
class Html extends AbstractNodeEntity
{
    #[ORM\Column(nullable: true)]
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
}
