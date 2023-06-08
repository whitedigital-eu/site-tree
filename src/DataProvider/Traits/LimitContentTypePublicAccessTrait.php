<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider\Traits;

use Doctrine\ORM\QueryBuilder;

trait LimitContentTypePublicAccessTrait
{
    protected function limitPublicAccess(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->leftJoin('e.node', 'n')
            ->andWhere('n.isActive = true');
    }
}
