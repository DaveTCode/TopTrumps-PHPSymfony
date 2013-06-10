<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dat
 * Date: 10/06/13
 * Time: 17:28
 * To change this template use File | Settings | File Templates.
 */

namespace Tyler\TopTrumpsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tyler\TopTrumpsBundle\Tests\Utils\TestCaseUtils;

class JSONRemoveDeckController extends WebTestCase
{
    /* @var Client $client */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testRemoveDeck()
    {
        $id = TestCaseUtils::addDeck($this->client, "Test", "Test");
        $this->client->request('DELETE', '/json/deck/'.$id);

        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
    }

    public function testRemoveNonExistentDeck()
    {
        $this->client->request('DELETE', '/json/deck/0');

        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json', 404);
    }

    public function testRemoveDeckWithCard()
    {
        $id = TestCaseUtils::addDeck($this->client, "Test", "Test");
        TestCaseUtils::addCard($this->client, $id, array("name" => "Test", "description" => "Test", "image" => "base64"));
        $this->client->request('DELETE', '/json/deck/'.$id);

        TestCaseUtils::assertContentType($this, $this->client->getResponse(), 'application/json');
    }
}