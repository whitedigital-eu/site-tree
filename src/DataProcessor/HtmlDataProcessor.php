<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\ApiResource\HtmlApiResource;
use WhiteDigital\SiteTree\Contracts\TreeEntity;
use WhiteDigital\SiteTree\Entity\Html;

final readonly class HtmlDataProcessor extends AbstractDataProcessor
{
    use Traits\ValidateResource;

    public function getEntityClass(): string
    {
        return Html::class;
    }

    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): Html|TreeEntity
    {
        $entity = Html::create($resource, $context, $existingEntity);

        return $this->validateEntity($entity, $resource, $context, $existingEntity);
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): HtmlApiResource
    {
        return HtmlApiResource::create($entity, $context);
    }
}
