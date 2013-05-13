<?php

namespace Tyler\TopTrumpsBundle\Controller;

class CardController extends AbstractDbController
{
    /**
     * Will be replaced with more sensible controllers once tested.
     */
    public function createAction($deckId)
    {
        $deck = $this->checkDeckId($deckId);

        return $this->render(
            'TylerTopTrumpsBundle:Admin:card.html.twig',
            array('deck' => $deck)
        );
    }
}
