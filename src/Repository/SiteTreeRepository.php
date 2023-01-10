<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use WhiteDigital\SiteTree\Entity\SiteTree;

/**
 * @method SiteTree|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteTree|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteTree[]    findAll()
 * @method SiteTree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteTreeRepository extends NestedTreeRepository
{
}
