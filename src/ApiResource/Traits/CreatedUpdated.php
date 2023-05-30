<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\ApiResource\Traits;

use Symfony\Component\Serializer\Annotation\Groups;
use WhiteDigital\EntityResourceMapper\UTCDateTimeImmutable;

trait CreatedUpdated
{
    #[Groups([self::READ, ])]
    public ?UTCDateTimeImmutable $createdAt = null;

    #[Groups([self::READ, ])]
    public ?UTCDateTimeImmutable $updatedAt = null;
}
