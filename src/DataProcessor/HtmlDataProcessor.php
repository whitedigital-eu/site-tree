<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\EntityResourceMapper\DataProcessor\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\Api\Resource\HtmlResource;
use WhiteDigital\SiteTree\Entity\Html;

class HtmlDataProcessor extends AbstractDataProcessor
{
    public function getEntityClass(): string
    {
        return Html::class;
    }

    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): Html
    {
        return Html::create($resource, $context, $existingEntity);
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): HtmlResource
    {
        return HtmlResource::create($entity, $context);
    }
}
