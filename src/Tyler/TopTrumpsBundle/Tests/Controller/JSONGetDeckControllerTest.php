<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

/**
 * Class JSONDeckControllerTest
 *
 * Test the deck retrieval functions in the JSON deck controller.
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

    public function testGetDeckWithStats()
    {
        $client = static::createClient();

        TestCaseUtils::clearDecks($client);
        $id = TestCaseUtils::addDeck($client,
                                     "Test Deck",
                                     "Test Description",
                                     array(array("name" => "Test Stat 1", "min" => 1, "max" => 2),
                                           array("name" => "Test Stat 2", "min" => 2, "max" => 10),
                                           array("name" => "Test Stat 3", "min" => 3, "max" => 20)));

        $client->request('GET', '/json/deck/'.$id);
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
        $this->assertEquals(3, count($deck->stats));

        foreach ($deck->stats as $stat) {
            switch ($stat->min) {
                case 1:
                    $this->assertEquals("Test Stat 1", $stat->name);
                    $this->assertEquals(2, $stat->max);
                    break;
                case 2:
                    $this->assertEquals("Test Stat 2", $stat->name);
                    $this->assertEquals(10, $stat->max);
                    break;
                case 3:
                    $this->assertEquals("Test Stat 3", $stat->name);
                    $this->assertEquals(20, $stat->max);
                    break;
                default:
                    $this->fail("Deck returned missing stat: ".$stat->id);
            }
        }
    }

    public function testGetDeckImage()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeckWithImage($client, "Test Deck", "Test Description", static::$imageBase64);

        $client->request('GET', '/json/deck/'.$id.'/image');
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'image/png');
    }

    public function testGetDeckImageWhenNoneSet()
    {
        $client = static::createClient();
        $id = TestCaseUtils::addDeck($client, "Test Deck", "Test Description");

        $client->request('GET', '/json/deck/'.$id.'/image');
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'image/png');
    }
}