<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;
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

    /* @var \Symfony\Bundle\FrameworkBundle\Client $client */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testGetDeck()
    {
        $id = TestCaseUtils::addDeck($this->client, "Test Deck", "Test Description");

        $this->client->request('GET', '/json/deck/'.$id);
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
    }

    public function testGetBadDeckId()
    {
        $this->client->request('GET', '/json/deck/0');

        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testGetMultipleDecksNoneExist()
    {
        TestCaseUtils::clearDecks($this->client);

        $this->client->request('GET', '/json/deck');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->assertEquals(0, count($decks));
    }

    public function testGetMultipleDecks()
    {
        TestCaseUtils::clearDecks($this->client);
        TestCaseUtils::addDeck($this->client, "Test 1", "Test 1");
        TestCaseUtils::addDeck($this->client, "Test 2", "Test 2");
        TestCaseUtils::addDeck($this->client, "Test 3", "Test 3");

        $this->client->request('GET', '/json/deck');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

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
        TestCaseUtils::clearDecks($this->client);
        $id = TestCaseUtils::addDeck($this->client,
                                     "Test Deck",
                                     "Test Description",
                                     array(array("name" => "Test Stat 1", "min" => 1, "max" => 2),
                                           array("name" => "Test Stat 2", "min" => 2, "max" => 10),
                                           array("name" => "Test Stat 3", "min" => 3, "max" => 20)));

        $this->client->request('GET', '/json/deck/'.$id);
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

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
        $id = TestCaseUtils::addDeckWithImage($this->client, "Test Deck", "Test Description", static::$imageBase64);

        $this->client->request('GET', '/json/deck/'.$id.'/image');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'image/png');
    }

    public function testGetDeckImageWhenNoneSet()
    {
        $id = TestCaseUtils::addDeck($this->client, "Test Deck", "Test Description");

        $this->client->request('GET', '/json/deck/'.$id.'/image');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'image/png');
    }

    public function testGetOnePageOfDecks()
    {
        TestCaseUtils::clearDecks($this->client);
        foreach (range(0, 100) as $i) {
            TestCaseUtils::addDeck($this->client, "Test ".$i, "Test ".$i);
        }

        $this->client->request('GET', '/json/deck?page=0&pageSize=10');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals(10, count($decks));
    }

    public function testGetOnePageOfDecksWithTooSmallPageSize()
    {
        TestCaseUtils::clearDecks($this->client);
        foreach (range(0, 100) as $i) {
            TestCaseUtils::addDeck($this->client, "Test ".$i, "Test ".$i);
        }

        $this->client->request('GET', '/json/deck?page=0&pageSize=1');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($this->client->getContainer()->getParameter('default_deck_page_size'), count($decks));
    }

    public function testGetOnePageOfDecksWithTooLargePageSize()
    {
        TestCaseUtils::clearDecks($this->client);
        foreach (range(0, 100) as $i) {
            TestCaseUtils::addDeck($this->client, "Test ".$i, "Test ".$i);
        }

        $this->client->request('GET', '/json/deck?page=0&pageSize=2000000');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($this->client->getContainer()->getParameter('default_deck_page_size'), count($decks));
    }

    public function testGetSecondPage()
    {
        TestCaseUtils::clearDecks($this->client);
        foreach (range(0, 20) as $i) {
            TestCaseUtils::addDeck($this->client, "Test ".$i, "Test ".$i);
        }

        $this->client->request('GET', '/json/deck?page=1&pageSize=10&sortBy=name');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals(10, count($decks));
        $this->assertEquals($decks[0]->name, "Test 18");
    }

    public function testGetBadPageNumber()
    {
        TestCaseUtils::clearDecks($this->client);
        TestCaseUtils::addDeck($this->client, "Test 1", "Test 1");
        $this->client->request('GET', '/json/deck?page=a');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals(1, count($decks));
        $this->assertEquals($decks[0]->name, "Test 1");
    }

    public function testGetBadFilter()
    {
        TestCaseUtils::clearDecks($this->client);
        TestCaseUtils::addDeck($this->client, "Test 2", "Test 2");
        TestCaseUtils::addDeck($this->client, "Test 1", "Test 1");
        $this->client->request('GET', '/json/deck?orderBy=bad');
        $decks = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals(1, count($decks));
        $this->assertEquals($decks[0]->name, "Test 1");
    }
}