<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\NonUniqueResultException;
use ReflectionClass;
use ReflectionException;
use WhiteDigital\EntityResourceMapper\DataProvider\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

use function strtolower;

abstract class AbstractContentTypeProvider extends AbstractDataProvider
{
    use Traits\LimitContentTypePublicAccessTrait;

    /**
     * @throws ReflectionException
     * @throws NonUniqueResultException
     */
    protected function getItem(Operation $operation, mixed $id, array $context): object
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('e')
            ->from($entityClass = $this->getEntityClass($operation, $context), 'e')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id);

        if (!$this->security->getUser()) {
            $this->limitPublicAccess($queryBuilder);
        }

        $entity = $queryBuilder->getQuery()->getOneOrNullResult();

        $this->throwErrorIfNotExists($entity, strtolower((new ReflectionClass($entityClass))->getShortName()), $id);
        $this->authorizationService->setAuthorizationOverride(fn () => $this->override(AuthorizationService::ITEM_GET, $operation->getClass()));
        $this->authorizationService->authorizeSingleObject($entity, AuthorizationService::ITEM_GET);

        return $this->createResource($entity, $context);
    }

    protected function getCollection(Operation $operation, array $context = []): array|object
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('e')->from($this->getEntityClass($operation, $context), 'e');

        if (!$this->security->getUser()) {
            $this->limitPublicAccess($queryBuilder);
        }

        $this->authorizationService->setAuthorizationOverride(fn () => $this->override(AuthorizationService::COL_GET, $operation->getClass()));
        $this->authorizationService->limitGetCollection($operation->getClass(), $queryBuilder);

        return $this->applyFilterExtensionsToCollection($queryBuilder, new QueryNameGenerator(), $operation, $context);
    }
}
