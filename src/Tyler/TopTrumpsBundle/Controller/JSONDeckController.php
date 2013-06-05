<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tyler\TopTrumpsBundle\Entity\Deck;
use Tyler\TopTrumpsBundle\Entity\Stat;

class JSONDeckController extends AbstractDbController
{
    public function allAction()
    {
        $decks = $this->getDoctrine()->getRepository('Tyler\TopTrumpsBundle\Entity\Deck')->findAll();
        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($decks, 'json'), 200);
    }

    public function createAction()
    {
        $request = $this->getRequest();

        $this->checkRequestParam($request, array('name', 'description'));

        $em = $this->getDoctrine()->getManager();
        $deck = new Deck();
        $deck->setName($request->request->get('name'));
        $deck->setDescription($request->request->get('description'));

        /*
         * Setting the image on a deck is optional. If not set then it will be
         * null in the database and treated differently on retrieval.
         */
        if ($request->request->has('image')) {
            $deck->setImageFromURI($request->request->get('image'));
        }

        if ($request->request->has('stats')) {
            foreach ($request->request->get('stats') as $statJson) {
                if (!property_exists($statJson, 'name') ||
                    !property_exists($statJson, 'min') || !is_numeric($statJson->min) ||
                    !property_exists($statJson, 'max') || !is_numeric($statJson->max)) {
                    throw new HttpException(400, 'Invalid stat: '.json_encode($statJson));
                }

                $stat = new Stat();
                $stat->setDeck($deck);
                $stat->setName($statJson->name);
                $stat->setMin($statJson->min);
                $stat->setMax($statJson->max);

                $em->persist($stat);
                $deck->addStat($stat);
            }
        }

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
        if ($request->request->has('name')) {
            $deck->setName($request->request->get('name'));
        }
        if ($request->request->has('description')) {
            $deck->setDescription($request->request->get('description'));
        }

        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($deck, 'json'), 200);
    }
}
