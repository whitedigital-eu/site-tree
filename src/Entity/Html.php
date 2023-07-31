<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\SiteTree\ApiResource\HtmlResource;

#[ORM\Entity]
#[ORM\Table(name: 'ct_html')]
#[Mapping(HtmlResource::class)]
class Html extends AbstractNodeEntity
{
    public const TYPE = 'html';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
