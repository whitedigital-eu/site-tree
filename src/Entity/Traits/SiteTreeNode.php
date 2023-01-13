<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\SiteTree\Entity\SiteTree;

trait SiteTreeNode
{
    #[ORM\ManyToOne(targetEntity: SiteTree::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?SiteTree $node = null;

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
