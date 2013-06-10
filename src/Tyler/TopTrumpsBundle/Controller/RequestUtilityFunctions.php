<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestUtilityFunctions
{
    /**
     * @param ObjectManager $em
     * @param Request $request
     * @param $container
     * @return Query - A query object from which we can retrieve a set of
     * decks.
     */
    public static function createDeckQueryFromRequest(ObjectManager $em,
                                                      Request $request,
                                                      ContainerInterface $container)
    {
        $pageSize = $request->get('pageSize');
        $page = $request->get('page', 0);
        $filterUnsafe = $request->query->get('filter', '%');
        $orderByDirSafe = $request->get('orderByDirection', 'ASC');

        if ($orderByDirSafe !== 'DESC' && $orderByDirSafe !== 'ASC') {
            $orderByDirSafe = 'ASC';
        }

        /*
         * Bind the page size between the minimum and maximum sensible values.
         *
         * These are set to keep memory usage on the server down.
         */
        if (!is_numeric($pageSize) ||
            $pageSize > $container->getParameter('max_deck_page_size') ||
            $pageSize < $container->getParameter('min_deck_page_size')) {
            $pageSize = $container->getParameter('default_deck_page_size');
        }

        if (!is_numeric($page)) {
            $page = 0;
        }

        /*
         * The order by field cannot be set dynamically in the query builder
         * so we set it using a switch instead. Note this couples the class
         * to the notion of a deck more closely.
         */
        $orderByUnsafe = $request->get('orderBy', 'name');
        switch ($orderByUnsafe) {
            case 'Name':
            case 'name':
                $orderBySafe = 'd.name';
                break;
            default:
                $orderBySafe = 'd.name';
                break;
        }

        /* @var $qb QueryBuilder */
        $qb = $em->createQueryBuilder();
        $query = $qb
            ->select('d')
            ->from('TylerTopTrumpsBundle:Deck', 'd')
            ->where($qb->expr()->orX($qb->expr()->like('d.name', ':filter'),
                    $qb->expr()->like('d.description', ':filter')))
            ->orderBy($orderBySafe, $orderByDirSafe)
            ->setParameter('filter', '%'.$filterUnsafe.'%')
            ->setFirstResult($pageSize * $page)
            ->setMaxResults($pageSize)
            ->getQuery();

        return $query;
    }
}