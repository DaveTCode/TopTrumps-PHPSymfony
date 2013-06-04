<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

class JSONCardControllerTest extends WebTestCase
{
    private static $imageBase64 = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";

    private $deckId;

    protected function setUp()
    {
        $this->deckId = TestCaseUtils::addDeck(static::createClient(), "Test deck", "Test description");
    }

    //
    // Add card tests
    //
    public function testAddCard()
    {
        $client = static::createClient();
        $client->request('POST',
                         '/json/deck/'.$this->deckId.'/card',
                         array("name" => "Test card", "description" => "Test description", "image" => static::$imageBase64),
                         array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());
        $jsonResponse = json_decode($client->getResponse()->getContent());
        $this->assertEquals("Test card", $jsonResponse->name);
        $this->assertEquals("Test description", $jsonResponse->description);
    }

    public function testAddCardMissingName()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("description" => "Test description", "image" => static::$imageBase64),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 400);
    }

    public function testAddCardMissingDescription()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("name" => "Test card", "image" => static::$imageBase64),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 400);
    }

    public function testAddCardMissingImage()
    {
        $client = static::createClient();
        $client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("name" => "Test card", "description" => "Test Description"),
            array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 400);
    }

    //
    // Update card tests
    //

    //
    // Delete card tests
    //
    public function testDeleteCard()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $client->request('DELETE', '/json/deck/'.$this->deckId.'/card/'.$cardId);
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());
    }

    public function testDeleteNonExistentCard()
    {
        $client = static::createClient();
        $client->request('DELETE', '/json/deck/'.$this->deckId.'/card/0');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testDeleteCardInNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('DELETE', '/json/deck/0/card/0');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    //
    // Get card tests
    //
    public function testGetCard()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test Description",
                                               "image" => static::$imageBase64));
        $client->request('GET', '/json/deck/'.$this->deckId.'/card/'.$cardId);
        TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $jsonResponse = json_decode($client->getResponse()->getContent());
        $this->assertEquals("Test Card", $jsonResponse->name);
        $this->assertEquals("Test Description", $jsonResponse->description);
    }

    public function testGetNonExistentCard()
    {
        $client = static::createClient();
        $client->request('GET', '/json/deck/'.$this->deckId.'/card/0');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testGetCardInNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('GET', '/json/deck/0/card/1');
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }
}