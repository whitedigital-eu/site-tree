<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Contracts;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use WhiteDigital\SiteTree\Entity\AbstractNodeEntity;
use WhiteDigital\SiteTree\Entity\SiteTree;

#[AutoconfigureTag]
interface ContentTypeFinderInterface
{
    public function findContentType(string $path): SiteTree|AbstractNodeEntity;

    public static function getDefaultPriority(): int;
}
