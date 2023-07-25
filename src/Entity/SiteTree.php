<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

#[ORM\Entity(repositoryClass: SiteTreeRepository::class)]
#[Gedmo\Tree(type: 'nested')]
#[Mapping(SiteTreeResource::class)]
class SiteTree extends BaseEntity
{
    use Id;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[ORM\OrderBy(['left' => Criteria::ASC])]
    protected Collection $children;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    protected ?SiteTree $parent = null;

    #[ORM\Column(nullable: false)]
    protected ?string $type = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', nullable: false)]
    protected ?int $level = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', nullable: false)]
    protected ?int $left = null;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', nullable: false)]
    protected ?int $right = null;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    protected ?SiteTree $root = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $isVisible = null;

    #[ORM\Column]
    protected ?string $title = null;

    #[ORM\Column]
    protected ?string $slug = null;

    #[ORM\Column(nullable: true)]
    protected ?string $metaTitle = null;

    #[ORM\Column(nullable: true)]
    protected ?string $metaDescription = null;

    #[ORM\Column(nullable: false)]
    protected ?bool $isActive = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): static
    {
        $this->children = $children;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function setRoot(?self $root): static
    {
        $this->root = $root;

        return $this;
    }

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(?bool $isVisible): static
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): static
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLeft(): ?int
    {
        return $this->left;
    }

    public function setLeft(?int $left): static
    {
        $this->left = $left;

        return $this;
    }

    public function getRight(): ?int
    {
        return $this->right;
    }

    public function setRight(?int $right): static
    {
        $this->right = $right;

        return $this;
    }
}
