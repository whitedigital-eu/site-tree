<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeApiResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

final readonly class SiteTreeDataProcessor extends AbstractDataProcessor
{
    use Traits\ValidateResource;

    public function getEntityClass(): string
    {
        return SiteTree::class;
    }

    /**
     * @throws Exception
     * @throws NonUniqueResultException
     */
    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): SiteTree
    {
        $entity = $this->validateSiteTree($resource, $context, $existingEntity);

        if (null !== $existingEntity) {
            $repo = $this->entityManager->getRepository($existingEntity::class);
            /* @var SiteTreeRepository $repo */
            $entity->setRoot($root = $repo->getRootById($existingEntity->getId()));
            $entity->setParent($repo->getParentById($existingEntity->getId()));
            $entity->setIsTranslatable($root->getIsTranslatable());
        }

        return $entity;
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

    protected function validateSiteTree(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): ?SiteTree
    {
        $exception = null;

        try {
            $this->validateResource($resource);
        } catch (UnprocessableEntityHttpException $exception) {
        }

        $entity = SiteTree::create($resource, $context, $existingEntity);

        $root = $entity->getRoot() ?? $entity->getParent()?->getRoot() ?? null;

        if (false !== $root?->getIsTranslatable() && null !== $exception) {
            throw $exception;
        }

        return $entity;
    }
}
