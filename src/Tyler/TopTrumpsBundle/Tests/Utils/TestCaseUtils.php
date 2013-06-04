<?php

namespace Tyler\TopTrumpsBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TestCaseUtils
{
    /**
     * Verify that the response passed in has JSON format content and matches
     * whatever status code requested.
     *
     * @param WebTestCase $testCase - Used to assert.
     * @param Response $response - The response from the client.
     * @param int $statusCode - Defaults to 200. Set otherwise.
     */
    public static function assertJsonResponse(WebTestCase $testCase, Response $response, $statusCode = 200)
    {
        $testCase->assertEquals(
            $statusCode, $response->getStatusCode(),
            $response->getContent()
        );
        $testCase->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }

    /**
     * Utility function to add a deck and return the id. Used to ensure
     * that we have a specific deck in the database for other tests.
     *
     * @param Client $client
     * @param string $name
     * @param string $description
     * @return int
     */
    public static function addDeck(Client $client, $name, $description)
    {
        $client->request('POST',
            '/json/deck',
            array("name" => $name, "description" => $description),
            array());

        return json_decode($client->getResponse()->getContent())->id;
    }

    /**
     * Delete a single deck from the database. No guarantees that the deck
     * exists and this is not checked by the function.
     *
     * @param Client $client
     * @param int $deckId
     */
    public static function deleteDeck(Client $client, $deckId)
    {
        $client->request('DELETE', '/json/deck/'.$deckId);
    }

    /**
     * Remove all decks from the database. Utility function to clear the deck
     * table so that we are in a known state.
     *
     * @param Client $client
     */
    public static function clearDecks(Client $client)
    {
        $client->request('GET', '/json/deck');
        $jsonResponse = json_decode($client->getResponse()->getContent());
        foreach (array_map(function($deck) { return $deck->id; }, $jsonResponse) as $id) {
            static::deleteDeck($client, $id);
        }
    }

    /**
     * Utility function to create a card on a given deck and return the card
     * id.
     *
     * @param Client $client
     * @param $deckId - The deck to create the card in.
     * @param array $fields - All fields required to create a card plus any
     * optional ones.
     *
     * @return int - The resulting card id.
     */
    public static function addCard(Client $client, $deckId, array $fields)
    {
        $client->request('POST',
                         '/json/deck/'.$deckId.'/card',
                         $fields,
                         array());

        return json_decode($client->getResponse()->getContent())->id;
    }
}
