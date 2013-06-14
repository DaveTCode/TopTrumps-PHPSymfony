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
    private $deckIdWithStats;
    private $client;

    protected function setUp()
    {
        $this->client = static::createClient();

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
        $this->deckIdWithStats = 
            TestCaseUtils::addDeck(static::createClient(),
                                   "Test deck 2",
                                   "Test description 2",
                                   array(array("name" => "Stat name 2", "min" => 10, "max" => 20)));
    }

    //
    // Add card tests
    //
    public function testAddCard()
    {
        $this->client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("name" => "Test card", "description" => "Test description", "image" => static::$imageBase64),
            array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals("Test card", $card->name);
        $this->assertEquals("Test description", $card->description);
    }

    public function testAddCardMissingName()
    {
        $this->client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("description" => "Test description", "image" => static::$imageBase64),
            array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);
    }

    public function testAddCardMissingDescription()
    {
        $this->client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("name" => "Test card", "image" => static::$imageBase64),
            array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);
    }

    public function testAddCardMissingImage()
    {
        $this->client->request('POST',
            '/json/deck/'.$this->deckId.'/card',
            array("name" => "Test card", "description" => "Test Description"),
            array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);
    }

    public function testAddCardWithStats()
    {
        $this->client->request('GET', '/json/deck/'.$this->deckIdWithStats);
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("id" => $deck->stats[0]->id, "value" => 12))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals("Test card with stats", $card->name);
        $this->assertEquals("Test description", $card->description);
        $this->assertEquals(12, $card->stat_values[0]->value);
    }

    public function testAddCardWithBadStatValue()
    {
        $this->client->request('GET', '/json/deck/'.$this->deckIdWithStats);
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        /*
         * Missing id
         */
        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("value" => 7))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Bad id
         */
        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("id" => "a", "value" => 7))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Missing value
         */
        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("id" => $deck->stats[0]->id))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);

        /*
         * Bad value
         */
        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("id" => $deck->stats[0]->id, "value" => "a"))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 400);
    }

    public function testAddCardWithInvalidStatValue()
    {
        $this->client->request('GET', '/json/deck/'.$this->deckIdWithStats);
        $deck = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        /*
         * Value below min
         */
        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("id" => $deck->stats[0]->id, "value" => 0))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->assertEquals(10, $card->stat_values[0]->value);

        /*
         * Value above max
         */
        $this->client->request('POST',
                         '/json/deck/'.$this->deckIdWithStats.'/card',
                         array("name" => "Test card with stats", 
                               "description" => "Test description", 
                               "image" => static::$imageBase64,
                               "stat_values" => array(array("id" => $deck->stats[0]->id, "value" => 1000))),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
        $this->assertEquals(20, $card->stat_values[0]->value);
    }

    //
    // Update card tests
    //
    public function testUpdateCardName()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $this->client->request('POST', '/json/deck/'.$this->deckId.'/card/'.$cardId,
                         array('name' => 'New name'),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals("New name", $card->name);
        $this->assertEquals("Test description", $card->description);
    }

    public function testUpdateCardDescription()
    {
        $cardId = TestCaseUtils::addCard($this->client,
            $this->deckId,
            array("name" => "Test Card",
                "description" => "Test description",
                "image" => static::$imageBase64));
        $this->client->request('POST', '/json/deck/'.$this->deckId.'/card/'.$cardId,
            array('description' => 'New description'),
            array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals("Test Card", $card->name);
        $this->assertEquals("New description", $card->description);
    }

    public function testUpdateCardImage()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $this->client->request('POST', '/json/deck/'.$this->deckId.'/card/'.$cardId,
                         array('image' => static::$imageBase64backup),
                         array());
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals("Test Card", $card->name);
        $this->assertEquals("Test description", $card->description);
    }

    public function testUpdateNonExistentCard()
    {
        $this->client->request('POST', '/json/deck/'.$this->deckId.'/card/0',
                         array('image' => static::$imageBase64backup),
                         array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testUpdateCardOnNonExistentDeck()
    {
        $this->client->request('POST', '/json/deck/0/card/0',
                         array('image' => static::$imageBase64backup),
                         array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testUpdateCardOnWrongDeck()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $this->client->request('POST', '/json/deck/'.$this->deckId2.'/card/'.$cardId,
                         array('image' => static::$imageBase64backup),
                         array());
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    //
    // Delete card tests
    //
    public function testDeleteCard()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $this->client->request('DELETE', '/json/deck/'.$this->deckId.'/card/'.$cardId);
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
    }

    public function testDeleteNonExistentCard()
    {
        $this->client->request('DELETE', '/json/deck/'.$this->deckId.'/card/0');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testDeleteCardInNonExistentDeck()
    {
        $this->client->request('DELETE', '/json/deck/0/card/0');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testDeleteCardOnWrongDeck()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test description",
                                               "image" => static::$imageBase64));
        $this->client->request('DELETE', '/json/deck/'.$this->deckId2.'/card/'.$cardId);
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    //
    // Get card tests
    //
    public function testGetCard()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test Description",
                                               "image" => static::$imageBase64));
        $this->client->request('GET', '/json/deck/'.$this->deckId.'/card/'.$cardId);
        $card = TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');

        $this->assertEquals("Test Card", $card->name);
        $this->assertEquals("Test Description", $card->description);
    }

    public function testGetNonExistentCard()
    {
        $this->client->request('GET', '/json/deck/'.$this->deckId.'/card/0');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testGetCardInNonExistentDeck()
    {
        $this->client->request('GET', '/json/deck/0/card/1');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testGetCardOnWrongDeck()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test Description",
                                               "image" => static::$imageBase64));
        $this->client->request('GET', '/json/deck/'.$this->deckId2.'/card/'.$cardId);
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
        $this->assertTrue(true);
    }

    //
    // Get card image tests
    //
    public function testGetCardImage()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test Description",
                                               "image" => static::$imageBase64));
        $this->client->request('GET', '/json/deck/'.$this->deckId.'/card/'.$cardId.'/image');
        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'image/png');
    }

    public function testGetCardImageNonExistentCard()
    {
        $this->client->request('GET', '/json/deck/'.$this->deckId.'/card/0/image');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testGetCardImageNonExistentDeck()
    {
        $this->client->request('GET', '/json/deck/0/card/0/image');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testGetCardImageWrongDeck()
    {
        $cardId = TestCaseUtils::addCard($this->client,
                                         $this->deckId,
                                         array("name" => "Test Card",
                                               "description" => "Test Description",
                                               "image" => static::$imageBase64));
        $this->client->request('GET', '/json/deck/'.$this->deckId2.'/card/'.$cardId.'/image');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}