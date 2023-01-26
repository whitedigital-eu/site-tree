<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use Doctrine\DBAL\Exception;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

final readonly class SiteTreeDataProcessor extends AbstractDataProcessor
{
    public function getEntityClass(): string
    {
        return SiteTree::class;
    }

    /**
     * @throws Exception
     */
    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): SiteTree
    {
        $entity = SiteTree::create($resource, $context, $existingEntity);

        if (null !== $existingEntity) {
            $repo = $this->entityManager->getRepository(SiteTree::class);
            /* @var SiteTreeRepository $repo */
            $entity->setRoot($repo->getRootById($existingEntity->getId()));
            $entity->setParent($repo->getParentById($existingEntity->getId()));
        }

        return $entity;
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): SiteTreeResource
    {
        return SiteTreeResource::create($entity, $context);
    }
}
