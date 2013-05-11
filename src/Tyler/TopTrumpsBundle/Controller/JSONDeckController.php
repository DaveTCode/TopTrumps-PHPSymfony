<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tyler\TopTrumpsBundle\Entity\Deck;

class JSONDeckController extends Controller
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

    public function allAction()
    {
        $decks = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Deck')->findAll();
        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($decks, 'json'), 200);
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $this->get('logger')->err(implode($request->request->all()));
        $deck = new Deck();
        $deck->setName($request->request->get('name'));
        $deck->setDescription($request->request->get('description'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($deck);
        $em->flush();

        $serializer = $this->container->get('serializer');
        return new Response($serializer->serialize($deck, 'json'), 200);
    }

    public function getAction($deckId)
    {
        $deck = $this->checkDeckId($deckId);

        $serializer = $this->container->get('serializer');
        return new Response($serializer->serialize($deck, 'json'), 200);
    }

    public function removeAction($deckId)
    {
        $deck = $this->checkDeckId($deckId);

        $em = $this->getDoctrine()->getManager();
        $em->remove($deck);
        $em->flush();

        $serializer = $this->container->get('serializer');
        return new Response($serializer->serialize($deck, 'json'), 200);
    }

    public function updateAction($deckId)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $deck = $this->checkDeckId($deckId);
        $deck->setName($request->request->name);
        $deck->setDescription($request->request->description);

        $em->flush();

        $serializer = $this->container->get('serializer');
        return new Response($serializer->serialize($deck, 'json'), 200);
    }
}
