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
    use Traits\Active;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, fetch: 'EAGER')]
    #[ORM\OrderBy(['left' => Criteria::ASC])]
    private Collection $children;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'EAGER', inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private ?SiteTree $parent = null;

    #[ORM\Column(nullable: false)]
    private ?string $type = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', nullable: false)]
    private int $level;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', nullable: false)]
    private int $left;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', nullable: false)]
    private int $right;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private ?SiteTree $root = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isVisible = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $title = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $slug = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metaTitle = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metaDescription = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTranslatable = null;

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

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(?bool $isVisible): self
    {
        $this->isVisible = $isVisible;

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

    public function getSlug(): ?array
    {
        return $this->slug;
    }

    public function setSlug(?array $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMetaTitle(): ?array
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?array $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?array
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?array $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): self
    {
        $this->left = $left;

        return $this;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function setRight(int $right): self
    {
        $this->right = $right;

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
}
