<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class AbstractDbController
 * @package Tyler\TopTrumpsBundle\Controller
 */
abstract class AbstractDbController extends Controller
{
    /**
     * Used to abstract away retrieving a card from the database or throwing a
     * not found exception (404).
     *
     * Also ensures that the card is part of a particular deck.
     *
     * @param int $deckId - The card is required to be part of this deck
     * @param int $cardId - The card id to find
     * @return \Tyler\TopTrumpsBundle\Entity\Card
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function checkCardId($deckId, $cardId)
    {
        $card = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Card')->find($cardId);

        if (!$card) {
            throw $this->createNotFoundException('No card found for id ' . $cardId);
        }
        if ($card->getDeck()->getId() != $deckId) {
            throw $this->createNotFoundException('Card ' . $cardId . ' is not part of deck ' . $deckId);
        }

        return $card;
    }

    /**
     * Used to abstract away retrieving a deck from the database or throwing a
     * not found exception (404).
     *
     * @param $deckId - The deck id to find
     * @return \Tyler\TopTrumpsBundle\Entity\Deck
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function checkDeckId($deckId)
    {
        $deck = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Deck')->find($deckId);

        if (!$deck) {
            throw $this->createNotFoundException('No deck found for id ' . $deckId);
        }

        return $deck;
    }
}