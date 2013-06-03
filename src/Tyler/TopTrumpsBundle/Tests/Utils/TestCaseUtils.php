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
     * @param $name
     * @param $description
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
}
