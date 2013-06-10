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
class JSONDeckControllerTest extends WebTestCase
{
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
}