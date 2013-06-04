<?php

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

class JSONCardControllerTest extends WebTestCase
{
    private static $imageBase64 = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";
    private static $imageBase64backup = "data:image/gif;base64,R0lGODlhUAAPAKIAAAsLav///88PD9WqsYmApmZmZtZfYmdakyH5BAQUAP8ALAAAAABQAA8AAAPbWLrc/jDKSVe4OOvNu/9gqARDSRBHegyGMahqO4R0bQcjIQ8E4BMCQc930JluyGRmdAAcdiigMLVrApTYWy5FKM1IQe+Mp+L4rphz+qIOBAUYeCY4p2tGrJZeH9y79mZsawFoaIRxF3JyiYxuHiMGb5KTkpFvZj4ZbYeCiXaOiKBwnxh4fnt9e3ktgZyHhrChinONs3cFAShFF2JhvCZlG5uchYNun5eedRxMAF15XEFRXgZWWdciuM8GCmdSQ84lLQfY5R14wDB5Lyon4ubwS7jx9NcV9/j5+g4JADs=";

    private $deckId;
    private $deckId2;

    protected function setUp()
    {
        /*
         * We use the same deck for all of the card tests so it is set up
         * here.
         */
        $this->deckId = TestCaseUtils::addDeck(static::createClient(),
                                               "Test deck",
                                               "Test description");
        $this->deckId2 = TestCaseUtils::addDeck(static::createClient(),
                                                "Test deck 2",
                                                "Test description 2");
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
        $card = TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $this->assertEquals("Test card", $card->name);
        $this->assertEquals("Test description", $card->description);
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
    public function testUpdateCardName()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $client->request('POST', '/json/deck/'.$this->deckId.'/card/'.$cardId,
                         array('name' => 'New name'),
                         array());
        $card = TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $this->assertEquals("New name", $card->name);
        $this->assertEquals("Test description", $card->description);
    }

    public function testUpdateCardDescription()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
            $this->deckId,
            array("name" => "Test Card",
                "description" => "Test description",
                "image" => static::$imageBase64));
        $client->request('POST', '/json/deck/'.$this->deckId.'/card/'.$cardId,
            array('description' => 'New description'),
            array());
        $card = TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $this->assertEquals("Test Card", $card->name);
        $this->assertEquals("New description", $card->description);
    }

    public function testUpdateCardImage()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $client->request('POST', '/json/deck/'.$this->deckId.'/card/'.$cardId,
                         array('image' => static::$imageBase64backup),
                         array());
        $card = TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $this->assertEquals("Test Card", $card->name);
        $this->assertEquals("Test description", $card->description);
    }

    public function testUpdateNonExistentCard()
    {
        $client = static::createClient();
        $client->request('POST', '/json/deck/'.$this->deckId.'/card/0',
                         array('image' => static::$imageBase64backup),
                         array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testUpdateCardOnNonExistentDeck()
    {
        $client = static::createClient();
        $client->request('POST', '/json/deck/0/card/0',
                         array('image' => static::$imageBase64backup),
                         array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    public function testUpdateCardOnWrongDeck()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $client->request('POST', '/json/deck/'.$this->deckId2.'/card/'.$cardId,
                         array('image' => static::$imageBase64backup),
                         array());
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

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

    public function testDeleteCardOnWrongDeck()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $client->request('DELETE', '/json/deck/'.$this->deckId2.'/card/'.$cardId);
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
        $card = TestCaseUtils::assertJsonResponse($this, $client->getResponse());

        $this->assertEquals("Test Card", $card->name);
        $this->assertEquals("Test Description", $card->description);
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

    public function testGetCardOnWrongDeck()
    {
        $client = static::createClient();
        $cardId = TestCaseUtils::addCard($client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test Description",
                                               "image" => static::$imageBase64));
        $client->request('GET', '/json/deck/'.$this->deckId2.'/card/'.$cardId);
        TestCaseUtils::assertJsonResponse($this, $client->getResponse(), 404);
    }

    //
    // Get card image tests
    //
    public function getCardImage()
    {

    }

    public function getCardImageNonExistentCard()
    {

    }

    public function getCardImageNonExistentDeck()
    {

    }

    public function getCardImageWrongDeck()
    {
        
    }
}