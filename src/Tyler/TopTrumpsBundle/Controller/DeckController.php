<?php

namespace Tyler\TopTrumpsBundle\Controller;

class DeckController extends AbstractDbController
{
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
}