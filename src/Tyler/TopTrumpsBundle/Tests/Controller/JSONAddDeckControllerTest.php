<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

/**
 * Class JSONDeckControllerTest
 *
 * Test the create deck part of the deck controller.
 *
 * @package Tyler\TopTrumpsBundle\Tests\Controller
 */
class JSONAddDeckControllerTest extends WebTestCase
{
    private static $imageBase64 = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";

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
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

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
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 400);
    }

    public function testAddDeckMissingDescription()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck"),
            array());
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 400);
    }

    public function testAddDeckWithImage()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck", "description" => "Test Description", "image" => static::$imageBase64),
            array());
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
    }

    public function testAddDeckWithStat()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck",
                  "description" => "Test Description",
                  "image" => static::$imageBase64,
                  "stats" => array(array("name" => "Test Stat 1", "min" => 1, "max" => 10))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
        $this->assertEquals(1, count($deck->stats));
        $this->assertEquals("Test Stat 1", $deck->stats[0]->name);
        $this->assertEquals(1, $deck->stats[0]->min);
        $this->assertEquals(10, $deck->stats[0]->max);
    }

    public function testAddDeckWithStatMissingName()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck",
                "description" => "Test Description",
                "image" => static::$imageBase64,
                "stats" => array(array("min" => 1, "max" => 10))),
            array());
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 400);
    }

    public function testAddDeckWithStatMissingMin()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck",
                  "description" => "Test Description",
                  "image" => static::$imageBase64,
                  "stats" => array(array("name" => "Test stat 1", "max" => 10))),
            array());
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 400);
    }

    public function testAddDeckWithStatMissingMax()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck",
                  "description" => "Test Description",
                  "image" => static::$imageBase64,
                  "stats" => array(array("name" => "Test stat 1", "min" => 10))),
            array());
        TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json', 400);
    }

    public function testAddDeckWithStats()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck',
            array("name" => "Test Deck",
                "description" => "Test Description",
                "image" => static::$imageBase64,
                "stats" => array(array("name" => "Test Stat 1", "min" => 1, "max" => 10),
                                 array("name" => "Test Stat 2", "min" => 2, "max" => 100),
                                 array("name" => "Test Stat 3", "min" => 3, "max" => 70))),
            array());
        $deck = TestCaseUtils::assertContentType($this, $client->getResponse(), 'application/json');

        $this->assertEquals("Test Deck", $deck->name);
        $this->assertEquals("Test Description", $deck->description);
        $this->assertEquals(3, count($deck->stats));

        foreach ($deck->stats as $stat) {
            switch ($stat->min) {
                case 1:
                    $this->assertEquals("Test Stat 1", $stat->name);
                    $this->assertEquals(1, $stat->min);
                    $this->assertEquals(10, $stat->max);
                    break;
                case 2:
                    $this->assertEquals("Test Stat 2", $stat->name);
                    $this->assertEquals(2, $stat->min);
                    $this->assertEquals(100, $stat->max);
                    break;
                case 3:
                    $this->assertEquals("Test Stat 3", $stat->name);
                    $this->assertEquals(3, $stat->min);
                    $this->assertEquals(70, $stat->max);
                    break;
            }
        }
    }
}