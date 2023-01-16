<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\SiteTree\ApiResource\HtmlResource;

final readonly class HtmlDataProvider extends AbstractDataProvider
{
    /**
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     * @throws ReflectionException
     */
    protected function createResource(BaseEntity $entity, array $context): HtmlResource
    {
        return HtmlResource::create($entity, $context);
    }
}
