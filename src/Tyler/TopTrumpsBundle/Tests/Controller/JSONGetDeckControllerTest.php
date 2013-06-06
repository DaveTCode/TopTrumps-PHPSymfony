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
class JSONGetDeckControllerTest extends WebTestCase
{
    private static $imageBase64 = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";

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
}