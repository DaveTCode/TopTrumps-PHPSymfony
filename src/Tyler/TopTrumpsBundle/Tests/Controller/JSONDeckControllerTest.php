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
    //
    // Add deck tests
    //
    public function testAddDeck()
    {
        $client = static::createClient();
        $client->request('POST',
                         '/json/deck',
                         array("name" => "Test Deck", "description" => "Test Description"),
                         array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $deck = json_decode($client->getResponse()->getContent());
        $this->assertEquals(1, $deck->id);
        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
    }

    public function testAddDeckMissingName()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("description" => "Test Description"),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 400);
    }

    public function testAddDeckMissingDescription()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck"),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 400);
    }

    //
    // Test get deck functions
    //

    public function testGetDeck()
    {
        $client = static::createClient();

        $id = TestCaseUtils::addDeck($client, "Test Deck", "Test Description");

        $client->request('GET', '/json/deck/'.$id);
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $deck = json_decode($client->getResponse()->getContent());
        $this->assertEquals($id, $deck->id);
        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
    }

    public function testGetBadDeckId()
    {
        $client = static::createClient();

        $client->request('GET', '/json/deck/0');

        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testGetMultipleDecksNoneExist()
    {
        $client = static::createClient();
        TestCaseUtils::clearDecks($client);

        $client->request('GET', '/json/deck');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $responseJson = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0, count($responseJson));
    }

    public function testGetMultipleDecks()
    {
        $client = static::createClient();
        TestCaseUtils::clearDecks($client);
        TestCaseUtils::addDeck($client, "Test 1", "Test 1");
        TestCaseUtils::addDeck($client, "Test 2", "Test 2");
        TestCaseUtils::addDeck($client, "Test 3", "Test 3");

        $client->request('GET', '/json/deck');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $responseJson = json_decode($client->getResponse()->getContent());
        $this->assertEquals(3, count($responseJson));
        foreach ($responseJson as $deck) {
            switch ($deck->id) {
                case 1:
                    $this->assertEquals('Test 1', $deck->name);
                    $this->assertEquals('Test 1', $deck->description);
                    break;
                case 2:
                    $this->assertEquals('Test 2', $deck->name);
                    $this->assertEquals('Test 2', $deck->description);
                    break;
                case 3:
                    $this->assertEquals('Test 3', $deck->name);
                    $this->assertEquals('Test 3', $deck->description);
                    break;
                default:
                    $this->fail("Deck returned that was not added: ".$deck->id);
                    break;
            }
        }
    }

    //
    // Test update deck options
    //
    public function testUpdateNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck/0',
            array("name" => "Test Deck"),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testUpdateDeckName()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, 'Test Deck pre update', 'Test description pre update');
        $client->request('POST',
            '/json/deck/'.$id,
            array("name" => "Test Deck"),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $jsonResponse = json_decode($client->getResponse()->getContent());
        $this->assertEquals($id, $jsonResponse->id);
        $this->assertEquals('Test Deck', $jsonResponse->name);
        $this->assertEquals('Test description pre update', $jsonResponse->description);
    }

    public function testUpdateDeckDescription()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, 'Test Deck pre update', 'Test description pre update');
        $client->request('POST',
            '/json/deck/'.$id,
            array("description" => "Test Description"),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $jsonResponse = json_decode($client->getResponse()->getContent());
        $this->assertEquals($id, $jsonResponse->id);
        $this->assertEquals('Test Deck pre update', $jsonResponse->name);
        $this->assertEquals('Test Description', $jsonResponse->description);
    }

    //
    // Test remove deck options.
    //

    public function testRemoveDeck()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, "Test", "Test");
        $client->request('DELETE', '/json/deck/'.$id);

        TestCaseUtils::assertJsonResponse($this, $client->getResponse());
    }

    public function testRemoveNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('DELETE', '/json/deck/0');

        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }
}