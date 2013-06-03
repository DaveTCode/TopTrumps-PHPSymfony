<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

/**
 * Class JSONDeckControllerTest
 *
 * Test the JSONDeckController, mocks up a database to use for this.
 *
 * @package Tyler\TopTrumpsBundle\Tests\Controller
 */
class JSONDeckControllerTest extends WebTestCase
{
    public function testGetBadDeckId()
    {
        $client = static::createClient();

        $client->request('GET', '/json/deck/0');

        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testAddDeck()
    {
        $client = static::createClient();
        $client->request('POST',
                         '/json/deck',
                         array("name" => "Test Deck", "description" => "Test Description"),
                         array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());
    }

    public function testGetDeck()
    {
        $client = static::createClient();

        $client->request('GET', '/json/deck/1');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $deck = json_decode($client->getResponse()->getContent());
        $this->assertEquals(1, $deck->id);
        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
    }

    public function testRemoveDeck()
    {
        $client = static::createClient();
        $client->request('DELETE', '/json/deck/1');

        TestCaseUtils::assertJsonResponse($this, $client->getResponse());
    }

    public function testRemoveNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('DELETE', '/json/deck/0');

        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }
}