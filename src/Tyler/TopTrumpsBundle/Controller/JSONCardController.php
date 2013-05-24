<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tyler\TopTrumpsBundle\Entity\Card;
use Tyler\TopTrumpsBundle\Entity\StatValue;

class JSONCardController extends AbstractDbController
{
    public function getAction($deckId, $cardId)
    {
        $card = $this->checkCardId($deckId, $cardId);

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    public function updateAction($deckId, $cardId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $card = $this->checkCardId($deckId, $cardId);
        $card->setName($request->request->get('name'));
        $card->setDescription($request->request->get('description'));

        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    public function removeAction($deckId, $cardId)
    {
        $em = $this->getDoctrine()->getManager();
        $card = $this->checkCardId($deckId, $cardId);

        $em->remove($card);
        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }

    public function createAction($deckId)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $deck = $this->checkDeckId($deckId);

        $card = new Card();
        $card->setName($request->request->get('name'));
        $card->setDescription($request->request->get('description'));
        $card->setDeck($deck);

        foreach ($request->request->get('stat_values') as $statValueJson) {
            $stat = $this->checkStatId($deckId, $statValueJson->get("id"));
            $statValue = new StatValue();
            $statValue->setCard($card);
            $statValue->setStat($stat);

            /*
             * Note that the value is capped when it is entered into the stat value
             * although that will be enforced by the database at some point.
             *
             * TODO : Enforce in database.
             */
            $value = $statValueJson->get("value");
            $statValue->setValue(min(max($stat->getMin(), $value), $stat->getMax()));

            $card->addStatValue($statValue);
        }

        $em->persist($card);
        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }
}
