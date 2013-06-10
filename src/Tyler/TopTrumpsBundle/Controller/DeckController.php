<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
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
     * @return Response
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
     * @return Response - rendered html page.
     */
    public function viewDecksDisplayAction()
    {
        $query = \RequestUtilityFunctions::createDeckQueryFromRequest(
            $this->getDoctrine()->getManager(),
            $this->getRequest(),
            $this->container);
        $decks = $query->getResult();

        return $this->render(
            'TylerTopTrumpsBundle:Admin:all-deck-view.html.twig',
            array('decks' => $decks)
        );
    }
}