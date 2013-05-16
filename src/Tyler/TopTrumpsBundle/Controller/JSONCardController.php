<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tyler\TopTrumpsBundle\Entity\Card;

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

        $this->get('logger')->err($request);

        $em->persist($card);
        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($card, 'json'), 200);
    }
}
