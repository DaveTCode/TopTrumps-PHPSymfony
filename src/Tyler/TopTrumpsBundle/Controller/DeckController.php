<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Doctrine\ORM\QueryBuilder;

class DeckController extends AbstractDbController
{
    /*
     * The default number of elements to display on a page of decks.
     */
    private static $MIN_PAGE_SIZE = 10;
    private static $MAX_PAGE_SIZE = 100;
    private static $DEFAULT_PAGE_SIZE = 30;

    /**
     * Action function for displaying a page in which the user can add a new
     * card.
     *
     * @param int $deckId - The deck in which to create a new card.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newCardDisplayAction($deckId)
    {
        $deck = $this->checkDeckId($deckId);

        return $this->render(
            'TylerTopTrumpsBundle:Admin:deck-edit.html.twig',
            array('deck' => $deck, 'card' => null)
        );
    }

    /**
     * Action function for displaying a page where the user can edit a card.
     *
     * @param int $deckId - The deck in which the card is to be edited.
     * @param int $cardId - The card to display for editing.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editCardDisplayAction($deckId, $cardId)
    {
        $deck = $this->checkDeckId($deckId);
        $card = $this->checkCardId($deckId, $cardId);

        return $this->render(
            'TylerTopTrumpsBundle:Admin:deck-edit.html.twig',
            array('deck' => $deck, 'card' => $card)
        );
    }

    /**
     * Controller used to render a view of all decks. Takes a set of optional
     * query parameters to filter and sort the resulting decks.
     *
     * @return \Symfony\Component\HttpFoundation\Response - rendered html page.
     */
    public function viewDecksDisplayAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pageSize = $this->getRequest()->get('pageSize', static::$DEFAULT_PAGE_SIZE);
        $page = $this->getRequest()->get('page', 0);
        $filterUnsafe = $this->getRequest()->query->get('filter', '%');
        $orderByDirSafe = $this->getRequest()->get('orderByDirection', 'ASC');

        if ($orderByDirSafe !== 'DESC' && $orderByDirSafe !== 'ASC') {
            $orderByDirSafe = 'ASC';
        }

        /*
         * Bind the page size between the minimum and maximum sensible values.
         *
         * These are set to keep memory usage on the server down.
         */
        if (!is_numeric($pageSize) || $pageSize > static::$MAX_PAGE_SIZE || $pageSize < static::$MIN_PAGE_SIZE) {
            $this->get('logger')->warning('Page size not in the valid range: '.$pageSize);
            $pageSize = static::$DEFAULT_PAGE_SIZE;
        }

        if (!is_numeric($page)) {
            $this->get('logger')->warning('Page not a number: '.$page);
            $page = 0;
        }

        /*
         * The order by field cannot be set dynamically in the query builder
         * so we set it using a switch instead. Note this couples the class
         * to the notion of a deck more closely.
         */
        $orderByUnsafe = $this->getRequest()->get('orderBy', 'name');
        switch ($orderByUnsafe) {
            case 'Name':
            case 'name':
                $orderBySafe = 'd.name';
                break;
            default:
                $this->get('logger')->warning('Order by not recognised: '.$orderByUnsafe);
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

        $decks = $query->getResult();

        return $this->render(
            'TylerTopTrumpsBundle:Admin:all-deck-view.html.twig',
            array('decks' => $decks)
        );
    }
}