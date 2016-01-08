<?php
namespace BB8\Emoji\Tests;
use GuzzleHttp\Client;
use BB8\Emoji\Tests\mocks\SetUpDb;
use BB8\Emoji\Database\Schema;
use BB8\Emoji\Database\Connection;

class APITest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setUp()
    {
        $conn = new Connection();
        Schema::createSchema();
        SetUpDb::setUp();
        $this->client = new Client([
            'base_uri' => 'http://emoji.dev.com',
            'timeout'  => 10.0
        ]);
    }

    public function testAuthLogin()
    {

    }

    public function testGetEmojis()
    {
        $response404 = $this->client->get('emoji/1', ['exceptions'=> false]);
        $response200 = $this->client->get('emoji/1001', ['exceptions'=> false]);

        $this->assertSame($response404->getStatusCode(), 404);
        $this->assertSame($response200->getStatusCode(), 200);
        $this->assertSame($response200->getHeader('Content-Type')[0], 'application/json');
    }
}

//
////var_dump($response->getContentType());
//var_dump(gettype($response->json()));
//var_dump($response->getStatusCode());
//var_dump(json_decode($response->getBody(), true));
////
////$request = new Request('GET', 'http://emoji.dev.com/emojis');
////var_dump($request);
