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

//    public function testAuthLogin()
//    {
//         $response   =   $this->client->post(
//             'auth/login',
//             [
//             'exceptions'=> false,
//             'form_params' => ['username' => 'test-root', 'password' => 'test-root']
//             ]
//         );
//        $result         =   json_decode($response->getBody(), true);
//
//        $this->assertNotNull($result['token']);
//        $this->assertSame($response->getStatusCode(), 200);
//    }

    public function testCases()
    {
        $this->assertTrue(true, true);
    }

    public function tearDown()
    {
        SetUpDb::tearDown();
    }
}
