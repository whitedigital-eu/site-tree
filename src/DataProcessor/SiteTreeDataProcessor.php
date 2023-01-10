<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeApiResource;
use WhiteDigital\SiteTree\Entity\SiteTree;

final readonly class SiteTreeDataProcessor extends AbstractDataProcessor
{
    public function getEntityClass(): string
    {
        return SiteTree::class;
    }

    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): SiteTree
    {
        return SiteTree::create($resource, $context, $existingEntity);
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): SiteTreeApiResource
    {
        return SiteTreeApiResource::create($entity, $context);
    }
}
