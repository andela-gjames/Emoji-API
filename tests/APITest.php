<?php
namespace BB8\Emoji\Tests;
use GuzzleHttp\Client;
use BB8\Emoji\Tests\mocks\SetUpDb;

use BB8\Emoji\Models\User;
use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiKeyword;

class APITest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $token;
    private $mockIds;

    public function setUp()
    {
        $this->mockIds = SetUpDb::setUp();
        $this->client = new Client([
            'base_uri' => 'http://api-emojicon-staging.herokuapp.com/',
            'timeout'  => 10000.0
        ]);

        $response   =   $this->client->post('auth/login', [
            'exceptions' => false,
            'form_params' => ['username' => 'test-root', 'password' => 'test-root']
        ]);

        $login          =   json_decode($response->getBody(), true);
        $this->token    =   $login['token'];
    }

    public function testAuthLogin()
    {
         $response   =   $this->client->post(
             'auth/login',
             [
             'exceptions'=> false,
             'form_params' => ['username' => 'test-root', 'password' => 'test-root']
             ]
         );
        $result         =   json_decode($response->getBody(), true);

        $this->assertNotNull($result['token']);
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testAuthLoginInvalidUserOrPasswordFailure()
    {
        $response   =   $this->client->post('auth/login', [
            'exceptions'=> false,
            'form_params' => ['username' => 'root', 'password' => 'not-root']
        ]);
        $result        =   json_decode($response->getBody(), true);

        $this->assertSame($result['status'], 'error');
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testGetAllEmojis()
    {
        $response   =   $this->client->get('emojis', ['exceptions'=> false]);

        $data       =   json_decode($response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($response->getHeader('Content-Type')[0], 'application/json');
        $this->assertSame($data[0]['name'], 'Happy Face');
        $this->assertSame($data[0]['category'], 'Happy');
    }

    public function testGetSingleEmoji()
    {
        $emojiId    =   $this->mockIds['emojiId'];
        $keywordID   =   $this->mockIds['keywordsId'][0];

        $response       =   $this->client->get("emojis/$emojiId", ['exceptions'=> false]);
        $contentType    =   $response->getHeader('Content-Type')[0];
        $data           =   json_decode($response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data['keywords'][$keywordID], 'happy');
        $this->assertSame($response->getHeader('Content-Type')[0], 'application/json');
    }



    public static function tearDownAfterClass()
    {
        SetUpDb::tearDown();
    }
}
