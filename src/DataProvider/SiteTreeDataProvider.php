<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\Php82\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;

final readonly class SiteTreeDataProvider extends AbstractDataProvider
{
    /**
     * @throws ReflectionException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->getCollection($operation, $context);
        }

        return $this->getItem($operation, $uriVariables['id'], $context);
    }

    /**
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     * @throws ReflectionException
     */
    protected function createResource(BaseEntity $entity, array $context): SiteTreeResource
    {
        return SiteTreeResource::create($entity, $context);
    }

    protected function getCollection(Operation $operation, array $context = []): array|object
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $queryBuilder = $this->entityManager->getRepository($resourceClass = $this->getEntityClass($operation))->getChildrenQueryBuilder();
        $queryBuilder->orderBy('node.root, node.left');

        $this->authorizationService->limitGetCollection($operation->getClass(), $queryBuilder);

        return $this->applyFilterExtensionsToCollection($queryBuilder, new QueryNameGenerator(), $operation, $context);
    }
}
