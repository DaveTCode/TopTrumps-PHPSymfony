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
    private static $imageBase64 = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";

    //
    // Test get deck functions
    //

    public function testGetDeck()
    {
        $client = static::createClient();

        $id = TestCaseUtils::addDeck($client, "Test Deck", "Test Description");

        $client->request('GET', '/json/deck/'.$id);
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
    }

    public function testGetBadDeckId()
    {
        $client = static::createClient();

        $client->request('GET', '/json/deck/0');

        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 404);
    }

    public function testGetMultipleDecksNoneExist()
    {
        $client = static::createClient();
        TestCaseUtils::clearDecks($client);

        $client->request('GET', '/json/deck');
        $decks = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');
        $this->assertEquals(0, count($decks));
    }

    public function testGetMultipleDecks()
    {
        $client = static::createClient();
        TestCaseUtils::clearDecks($client);
        TestCaseUtils::addDeck($client, "Test 1", "Test 1");
        TestCaseUtils::addDeck($client, "Test 2", "Test 2");
        TestCaseUtils::addDeck($client, "Test 3", "Test 3");

        $client->request('GET', '/json/deck');
        $decks = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals(3, count($decks));
        foreach ($decks as $deck) {
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
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 404);
    }

    public function testUpdateDeckName()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, 'Test Deck pre update', 'Test description pre update');
        $client->request('POST',
            '/json/deck/'.$id,
            array("name" => "Test Deck"),
            array());
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals('Test Deck', $deck->name);
        $this->assertEquals('Test description pre update', $deck->description);
    }

    public function testUpdateDeckDescription()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, 'Test Deck pre update', 'Test description pre update');
        $client->request('POST',
            '/json/deck/'.$id,
            array("description" => "Test Description"),
            array());
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals('Test Deck pre update', $deck->name);
        $this->assertEquals('Test Description', $deck->description);
    }

    //
    // Test remove deck options.
    //

    public function testRemoveDeck()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, "Test", "Test");
        $client->request('DELETE', '/json/deck/'.$id);

        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');
    }

    public function testRemoveNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('DELETE', '/json/deck/0');

        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 404);
    }

    public function testRemoveDeckWithCard()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, "Test", "Test");
        TestCaseUtils::addCard($client, $id, array("name" => "Test", "description" => "Test", "image" => "base64"));
        $client->request('DELETE', '/json/deck/'.$id);

        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');
    }
}