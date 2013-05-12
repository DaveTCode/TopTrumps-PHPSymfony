<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CardController extends Controller
{
    private function checkDeckId($deckId)
    {
        $deck = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Deck')->find($deckId);

        if (!$deck)
        {
            throw $this->createNotFoundException('No deck found for id '.$deckId);
        }

        return $deck;
    }

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
