<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Active
{
    #[ORM\Column(nullable: false)]
    protected ?bool $isActive = null;

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
