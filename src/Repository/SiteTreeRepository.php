<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Repository;

use Doctrine\DBAL\Exception;
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
     */
    public function getRootById(int $id): ?SiteTree
    {
        $rootId = $this->_em->getConnection()->prepare(sprintf('SELECT root_id FROM %s WHERE id = %d', $this->getClassMetadata()->getTableName(), $id))->executeQuery()->fetchOne();

        if (null !== $rootId) {
            return $this->find($rootId);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function getParentById(int $id): ?SiteTree
    {
        $parentId = $this->_em->getConnection()->prepare(sprintf('SELECT parent_id FROM %s WHERE id = %d', $this->getClassMetadata()->getTableName(), $id))->executeQuery()->fetchOne();

        if (null !== $parentId) {
            return $this->find($parentId);
        }

        return null;
    }

    public function findAllActiveByMaxLevel(int $level): array
    {
        $qb = $this->createQueryBuilder('st');

        return $qb
            ->select('st')
            ->where('st.level <= :level')
//            ->andWhere('st.isActive = true')
            ->setParameter('level', $level)
            ->getQuery()
            ->getResult();
    }
}
