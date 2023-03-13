<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource\Traits;

use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedUpdated
{
    #[Groups([self::READ, self::ITEM, ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups([self::READ, self::ITEM, ])]
    public ?DateTimeImmutable $updatedAt = null;
}
