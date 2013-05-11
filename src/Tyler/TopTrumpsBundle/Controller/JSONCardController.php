<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tyler\TopTrumpsBundle\Entity\Card;

class JSONCardController extends Controller 
{
    private function checkCardId($deckId, $cardId)
    {
        $card = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Card')->find($cardId);

        if (!$card)
        {
            throw $this->createNotFoundException('No card found for id '.$cardId);
        }
        if ($card->getDeck()->getId() != $deckId)
        {
            throw $this->createNotFoundException('Card '.$cardId.' is not part of deck '.$deckId);
        }

        return $card;
    }

    private function checkDeckId($deckId)
    {
        $deck = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Deck')->find($deckId);

        if (!$deck)
        {
            throw $this->createNotFoundException('No deck found for id '.$deckId);
        }

        return $deck;
    }

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

        $em->persist($card);
        $em->flush();

        $serializer = $this->container->get('serializer');
        return new Response($serializer->serialize($card, 'json'), 200);
    }
}
