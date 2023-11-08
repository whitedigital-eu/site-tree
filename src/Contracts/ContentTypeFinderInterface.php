<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Contracts;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;

#[AutoconfigureTag]
interface ContentTypeFinderInterface
{
    public function findContentType(string $path): BaseEntity;

    public static function getDefaultPriority(): int;

    public function getSitemapEntries(): array;
}
