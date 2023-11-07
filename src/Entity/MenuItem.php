<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\SiteTree\Api\Resource\MenuItemResource;

#[ORM\Entity]
#[ORM\Table(name: 'ct_menu_item')]
#[Mapping(MenuItemResource::class)]
class MenuItem extends AbstractNodeEntity
{
    public const TYPE = 'menu_item';

    #[ORM\Column(nullable: true)]
    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
