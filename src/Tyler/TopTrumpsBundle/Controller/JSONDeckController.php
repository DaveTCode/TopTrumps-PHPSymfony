<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tyler\TopTrumpsBundle\Entity\Deck;
use Tyler\TopTrumpsBundle\Entity\Stat;

class JSONDeckController extends AbstractDbController
{
    /**
     * Retrieve all decks without filtering the response at all.
     *
     * @return Response
     */
    public function allAction()
    {
        $query = RequestUtilityFunctions::createDeckQueryFromRequest(
            $this->getDoctrine()->getManager(),
            $this->getRequest(),
            $this->container);

        $decks = $query->getResult();
        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($decks, 'json'), 200);
    }

    /**
     * Create a new deck. Both the name and description must be set. Otherwise,
     * the image and the stats are optional extras.
     *
     * Returns the deck as a json object.
     *
     * @return Response - The json encoding of the deck including its new id.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
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
                if (!array_key_exists('name', $statJson) ||
                    !array_key_exists('min', $statJson) || !is_numeric($statJson['min']) ||
                    !array_key_exists('max', $statJson) || !is_numeric($statJson['max'])) {
                    throw new HttpException(400, 'Invalid stat: '.json_encode($statJson));
                }

                if ($statJson['max'] < $statJson['min']) {
                    throw new HttpException(400, 'Invalid stat - min is greater than max');
                }

                $stat = new Stat();
                $stat->setDeck($deck);
                $stat->setName($statJson['name']);
                $stat->setMin($statJson['min']);
                $stat->setMax($statJson['max']);

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
        if ($request->request->has('image')) {
            $deck->setImageFromURI($request->request->get('image'));
        }

        if ($request->request->has('stats')) {
            /*
             * Update any existing stats regardless of whether the values have
             * been changed.
             */
            foreach ($deck->getStats() as $stat) {
                $found = false;

                /* @var Stat $stat */
                foreach ($request->request->get('stats') as $statJson) {
                    if (array_key_exists('id', $statJson)) {
                        if ($statJson['id'] === $stat->getId()) {
                            $found = true;

                            if (array_key_exists('name', $statJson)) {
                                $stat->setName($statJson['name']);
                            }

                            if (array_key_exists('min', $statJson) && is_numeric($statJson['min'])) {
                                $stat->setMin($statJson['min']);
                            }

                            if (array_key_exists('max', $statJson) && is_numeric($statJson['max'])) {
                                if ($statJson['max'] < $statJson['min']) {
                                    throw new HttpException(400, 'Invalid stat - min is greater than max');
                                }

                                $stat->setMax($statJson['max']);
                            }
                        }
                    }
                }

                /*
                 * Any stat which exists on the deck but wasn't in the input 
                 * http request is removed from the deck.
                 */
                if (!$found) {
                    $deck->removeStat($stat);
                }
            }

            /*
             * Any new stats will not have an id key on the json object. These
             * should be added to the stat array for the deck.
             */
            foreach ($request->request->get('stats') as $statJson) {
                if (!array_key_exists('id', $statJson)) {
                    if (!array_key_exists('name', $statJson) ||
                        !array_key_exists('min', $statJson) || !is_numeric($statJson['min']) ||
                        !array_key_exists('max', $statJson) || !is_numeric($statJson['max'])) {
                        throw new HttpException(400, 'Invalid stat: '.json_encode($statJson));
                    }

                    if ($statJson['max'] < $statJson['min']) {
                        throw new HttpException(400, 'Invalid stat - min is greater than max');
                    }

                    $stat = new Stat();
                    $stat->setDeck($deck);
                    $stat->setName($statJson['name']);
                    $stat->setMin($statJson['min']);
                    $stat->setMax($statJson['max']);

                    $em->persist($stat);
                    $deck->addStat($stat);

                    /*
                     * Any existing cards must be updated to include the new
                     * stat. To do this we fix them all to the minimum stat 
                     * value.
                     */
                    foreach ($deck->getCards() as $card) {
                        $statValue = new StatValue();
                        $statValue->setStat($stat);
                        $statValue->setCard($card);
                        $statValue->setValue($stat->getMin());
                    }
                }
            }
        }

        $em->flush();

        $serializer = $this->container->get('serializer');

        return new Response($serializer->serialize($deck, 'json'), 200);
    }

    /**
     * The image for a deck is retrieved separately from the deck itself. It
     * is returned as an image object as if the user was requesting a static
     * png file from the server.
     *
     * @param int $deckId - The deck for which we are retrieving an image.
     * @return Response - Contains the image with appropriate http headers set
     * to indicate the image type.
     */
    public function getDeckImageAction($deckId)
    {
        $deck = $this->checkDeckId($deckId);

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        if (is_null($deck->getImage()) || "" === $deck->getImage()) {
            $fp = fopen(__DIR__."\\..\\Resources\\public\\images\\no-deck-image.png", "rb");
            $response->setContent(stream_get_contents($fp));
            fclose($fp);
        } else {
            $response->setContent(stream_get_contents($deck->getImage()));
        }

        return $response;
    }
}
