<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Repository;

use Freemium\Repository\SubscriptionRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use DateTime;

class SubscriptionRepository extends EntityRepository implements
    SubscriptionRepositoryInterface
{
    public function findBillable()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('s')
            ->from('Model\Subscription', 's')
            ->where('s.paid_through <= :today')
            ->andWhere('s.rate > :zero')
            ->setParameters([
                'today' => (new DateTime('today'))->format('Y-m-d'),
                'zero'  => 0
            ])->getQuery();

        return $query;
    }


    public function findExpired()
    {

    }
}
