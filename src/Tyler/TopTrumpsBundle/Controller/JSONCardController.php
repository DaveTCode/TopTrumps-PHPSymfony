<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tyler\TopTrumpsBundle\Entity\Card;
use Tyler\TopTrumpsBundle\Entity\StatValue;

class JSONCardController extends AbstractDbController
{
    /**
     * Action to retrieve the card information for a given card/deck combo.
     *
     * @param $deckId - The card must be part of this deck or a 404 response
     * is issued.
     * @param $cardId - The card to retrieve.
     * @return Response - The full json blob describing this card.
     */
    public function getAction($deckId, $cardId)
    {
        $card = $this->checkCardId($deckId, $cardId);

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    /**
     * Given a card and a json blob this action updates the cards values
     * including stat values and the image.
     *
     * @param int $deckId - The card is checked as being part of this deck.
     * @param int $cardId - The card to update.
     * @return Response - The full card with the new information.
     */
    public function updateAction($deckId, $cardId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $card = $this->checkCardId($deckId, $cardId);
        $card->setName($request->request->get('name'));
        $card->setDescription($request->request->get('description'));

        /*
         * Iterate over the existing stat values and update the values. Note
         * that all stat values must exist on card creation so we do not have
         * to either remove or add a stat value to the database.
         */
        foreach ($card->getStatValues() as $statValue) {
            foreach ($request->request->get('stat_values') as $statValueJson) {
                if ($statValueJson["id"] === $statValue->getId()) {
                    /*
                     * Set the current value even if it's the same.
                     */
                    $statValue->setValue($statValueJson["value"]);
                }
            }
        }

        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    /**
     * Action to remove a card from a deck. This will cascade down and remove
     * all dependent data as well.
     *
     * @param $deckId - The card must be part of this deck or a 404 will be
     * returned.
     * @param $cardId - The card to remove.
     * @return Response - The card that was removed.
     */
    public function removeAction($deckId, $cardId)
    {
        $em = $this->getDoctrine()->getManager();
        $card = $this->checkCardId($deckId, $cardId);

        $em->remove($card);
        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    /**
     * Action function used to create a card from a json blob.
     *
     * @param int $deckId - Forces the card to be part of this deck.
     * @return Response - The card object is returned including the newly
     * created card id.
     */
    public function createAction($deckId)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $deck = $this->checkDeckId($deckId);

        $card = new Card();
        $card->setName($request->request->get('name'));
        $card->setDescription($request->request->get('description'));
        $card->setDeck($deck);

        foreach ($request->request->get('stat_values') as $statValueArray) {
            $stat = $this->checkStatId($deckId, $statValueArray["id"]);
            $statValue = new StatValue();
            $statValue->setCard($card);
            $statValue->setStat($stat);

            /*
             * Note that the value is capped when it is entered into the stat value
             * although that will be enforced by the database at some point.
             *
             * TODO : Enforce in database.
             */
            $value = $statValueArray["value"];
            $statValue->setValue(min(max($stat->getMin(), $value), $stat->getMax()));
            $em->persist($statValue);

            $card->addStatValue($statValue);
        }

        $em->persist($card);
        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    /**
     * The image for the card can be retrieved separately from the main card
     * data by using this action function.
     *
     * @param int $deckId - The card must be part of this deck.
     * @param int $cardId - The card whose image we want.
     */
    public function getCardImageAction($deckId, $cardId)
    {
        $card = $this->checkCardId($deckId, $cardId);

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        if (is_null($card->getImage()) || "" === $card->getImage()) {
            $fp = fopen(__DIR__."\\..\\Resources\\public\\images\\no-card-image.png", "rb");
            $response->setContent(stream_get_contents($fp));
            fclose($fp);
        } else {
            $response->setContent($card->getImage());
        }

        $response->send();
    }
}
