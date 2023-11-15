<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\EntityResourceMapper\DataProcessor\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\Api\Resource\MenuItemResource;
use WhiteDigital\SiteTree\Entity\MenuItem;

class MenuItemDataProcessor extends AbstractDataProcessor
{
    public function getEntityClass(): string
    {
        return MenuItem::class;
    }

    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): MenuItem
    {
        return MenuItem::create($resource, $context, $existingEntity);
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): MenuItemResource
    {
        return MenuItemResource::create($entity, $context);
    }
}
