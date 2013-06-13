<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

/**
 * Class JSONDeckControllerTest
 *
 * Test the JSONDeckController, mocks up a database to use for this.
 *
 * @package Tyler\TopTrumpsBundle\Tests\Controller
 */
class JSONUpdateDeckControllerTest extends WebTestCase
{
    private static $imageBase64 = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";

    /* @var Client $client */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testUpdateNonExistentDeck()
    {
        $this->client->request('POST',
            '/json/deck/0',
            array("name" => "Test Deck"),
            array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testUpdateDeckName()
    {
        $id = TestCaseUtils::addDeck($this->client, 'Test Deck pre update', 'Test description pre update');
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("name" => "Test Deck"),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals('Test Deck', $deck->name);
        $this->assertEquals('Test description pre update', $deck->description);
    }

    public function testUpdateDeckDescription()
    {
        $id = TestCaseUtils::addDeck($this->client, 'Test Deck pre update', 'Test description pre update');
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("description" => "Test Description"),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals('Test Deck pre update', $deck->name);
        $this->assertEquals('Test Description', $deck->description);
    }

    public function testUpdateDeckImage()
    {
        $id = TestCaseUtils::addDeck($this->client, 'Test Deck pre update', 'Test description pre update');
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("image" => static::$imageBase64),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals('Test Deck pre update', $deck->name);
        $this->assertEquals('Test description pre update', $deck->description);

        $this->client->request('GET', '/json/deck/'.$id.'/image');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'image/png');
    }

    public function testUpdateDeckAddStat()
    {
        $id = TestCaseUtils::addDeck($this->client, 'Test Deck pre update', 'Test description pre update');
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("name" => "Stat", "min" => 1, "max" => 10))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($id, $deck->id);
        $this->assertEquals('Test Deck pre update', $deck->name);
        $this->assertEquals('Test description pre update', $deck->description);
        $this->assertEquals(1, count($deck->stats));
        $this->assertEquals("Stat", $deck->stats[0]->name);
        $this->assertEquals(1, $deck->stats[0]->min);
        $this->assertEquals(10, $deck->stats[0]->max);
    }

    public function testUpdateDeckUpdateStat()
    {
        $id = TestCaseUtils::addDeck($this->client, 
                                     'Test Deck pre update', 
                                     'Test description pre update',
                                     array(array("name" => "Test Stat 1", "min" => 1, "max" => 10)));
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("id" => $deck->stats[0]->id, "name" => "New Stat Name", "min" => 2, "max" => 20))),
            array());
        $updatedDeck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals($id, $updatedDeck->id);
        $this->assertEquals('Test Deck pre update', $updatedDeck->name);
        $this->assertEquals('Test description pre update', $updatedDeck->description);
        $this->assertEquals(1, count($updatedDeck->stats));
        $this->assertEquals("New Stat Name", $updatedDeck->stats[0]->name);
        $this->assertEquals(2, $updatedDeck->stats[0]->min);
        $this->assertEquals(20, $updatedDeck->stats[0]->max);
    }

    public function testRemoveStat()
    {
        $id = TestCaseUtils::addDeck($this->client, 
                                     'Test Deck pre update', 
                                     'Test description pre update',
                                     array(array("name" => "Test Stat 1", "min" => 1, "max" => 10),
                                           array("name" => "Test Stat 2", "min" => 2, "max" => 20)));
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("id" => $deck->stats[0]->id, "name" => "Test Stat 1", "min" => 1, "max" => 10))),
            array());
        $updatedDeck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals(1, count($updatedDeck->stats));
    }

    public function testUpdateDeckStatMissingFields()
    {
        $id = TestCaseUtils::addDeck($this->client, 
                                     'Test Deck pre update', 
                                     'Test description pre update',
                                     array(array("name" => "Test Stat 1", "min" => 1, "max" => 10)));
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        /*
         * Missing name
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("id" => $deck->stats[0]->id, "min" => 2, "max" => 20))),
            array());
        $updatedDeck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->assertEquals(1, count($updatedDeck->stats));
        $this->assertEquals("Test Stat 1", $updatedDeck->stats[0]->name);
        $this->assertEquals(2, $updatedDeck->stats[0]->min);
        $this->assertEquals(20, $updatedDeck->stats[0]->max);

        /*
         * Missing min
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("id" => $deck->stats[0]->id, "name" => "New name", "max" => 20))),
            array());
        $updatedDeck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->assertEquals(1, count($updatedDeck->stats));
        $this->assertEquals("New name", $updatedDeck->stats[0]->name);
        $this->assertEquals(2, $updatedDeck->stats[0]->min);
        $this->assertEquals(20, $updatedDeck->stats[0]->max);

        /*
         * Missing max
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("id" => $deck->stats[0]->id, "name" => "New name", "min" => 2))),
            array());
        $updatedDeck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->assertEquals(1, count($updatedDeck->stats));
        $this->assertEquals("New name", $updatedDeck->stats[0]->name);
        $this->assertEquals(2, $updatedDeck->stats[0]->min);
        $this->assertEquals(20, $updatedDeck->stats[0]->max);
    }

    public function testUpdateDeckAddBadStat()
    {
        $id = TestCaseUtils::addDeck($this->client, 'Test Deck pre update', 'Test description pre update');

        /*
         * Missing name
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("min" => 1, "max" => 10))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Missing min
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("name" => "Stat", "max" => 10))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Nonnumeric min
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("name" => "Stat", "min" => "a", "max" => 10))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Missing max
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("name" => "Stat", "min" => 1))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Nonnumeric max
         */
        $this->client->request('POST',
            '/json/deck/'.$id,
            array("stats" => array(array("name" => "Stat", "min" => 1, "max" => "a"))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);
    }
}