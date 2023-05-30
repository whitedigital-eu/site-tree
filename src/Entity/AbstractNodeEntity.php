<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;

#[ORM\MappedSuperclass]
abstract class AbstractNodeEntity extends BaseEntity
{
    use Id;

    #[ORM\ManyToOne(targetEntity: SiteTree::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?SiteTree $node = null;

    #[ORM\Column(nullable: true)]
    protected ?string $slug = null;

    public function getNode(): ?SiteTree
    {
        return $this->node;
    }

    public function setNode(?SiteTree $node): static
    {
        $this->node = $node;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
