<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;
use WhiteDigital\SiteTree\Entity\Traits\Active;

#[ORM\MappedSuperclass]
#[ORM\Index(fields: ['node', 'isActive'])]
abstract class AbstractNodeEntity extends BaseEntity
{
    use Active;
    use Id;

    #[ORM\ManyToOne(targetEntity: SiteTree::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?SiteTree $node = null;

    public function getNode(): ?SiteTree
    {
        return $this->node;
    }

    public function setNode(?SiteTree $node): static
    {
        $this->node = $node;

        return $this;
    }
}
