<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;
use WhiteDigital\SiteTree\ApiResource\SiteTreeApiResource;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

#[ORM\Entity(repositoryClass: SiteTreeRepository::class)]
#[Gedmo\Tree(type: 'nested')]
#[Mapping(SiteTreeApiResource::class)]
class SiteTree extends BaseEntity
{
    use Id;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, fetch: 'EAGER')]
    #[ORM\OrderBy(['lft' => Criteria::ASC])]
    private Collection $children;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?SiteTree $parent = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(nullable: false)]
    private int $lvl;

    #[Gedmo\TreeLeft]
    #[ORM\Column(nullable: false)]
    private int $lft;

    #[Gedmo\TreeRight]
    #[ORM\Column(nullable: false)]
    private int $rgt;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?SiteTree $root = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isVisible = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTranslatable = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $title = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function setRoot(?self $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(?bool $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getIsTranslatable(): ?bool
    {
        return $this->isTranslatable;
    }

    public function setIsTranslatable(?bool $isTranslatable): self
    {
        $this->isTranslatable = $isTranslatable;

        return $this;
    }

    public function getTitle(): ?array
    {
        return $this->title;
    }

    public function setTitle(?array $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function setLvl(int $lvl): self
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function setLft(int $lft): self
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): self
    {
        $this->rgt = $rgt;

        return $this;
    }
}
