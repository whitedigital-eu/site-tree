<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\SiteTree\ApiResource\RedirectApiResource;

final readonly class RedirectDataProvider extends AbstractDataProvider
{
    /**
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     * @throws ReflectionException
     */
    protected function createResource(BaseEntity $entity, array $context): RedirectApiResource
    {
        return RedirectApiResource::create($entity, $context);
    }
}
