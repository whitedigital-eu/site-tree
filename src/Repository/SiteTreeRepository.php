<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function sprintf;

/**
 * @method SiteTree|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteTree|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteTree[]    findAll()
 * @method SiteTree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteTreeRepository extends NestedTreeRepository
{
    /**
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function getRootById(int $id): ?SiteTree
    {
        $rootId = $this->_em->getConnection()->prepare(sprintf('SELECT root_id FROM %s WHERE id = %d', $this->getClassMetadata()->getTableName(), $id))->executeQuery()->fetchOne();

        if (null !== $rootId) {
            return $this->findSiteTreeById($rootId, null);
        }

        return null;
    }

    /**
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function getParentById(int $id, ?bool $status = null): ?SiteTree
    {
        $parentId = $this->_em->getConnection()->prepare(sprintf('SELECT parent_id FROM %s WHERE id = %d', $this->getClassMetadata()->getTableName(), $id))->executeQuery()->fetchOne();

        if (null !== $parentId) {
            return $this->findSiteTreeById($parentId, $status);
        }

        return null;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findSiteTreeById(int $id, ?bool $status = true): ?SiteTree
    {
        $qb = $this->createQueryBuilder('st');

        if (null !== $status) {
            $qb
                ->andWhere('st.isActive = :status')
                ->setParameter('status', $status);
        }

        return $qb
            ->select('st')
            ->andWhere('st.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    public function findAllActiveByLevel(int $level): array
    {
        $qb = $this->createQueryBuilder('st');

        return $qb
            ->select('st')
            ->andWhere('st.level = :level')
            ->andWhere('st.isActive = true')
            ->setParameter('level', $level)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
