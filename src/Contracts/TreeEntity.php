<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Contracts;

use WhiteDigital\SiteTree\Entity\SiteTree;

interface TreeEntity
{
    public function getNode(): ?SiteTree;
}
