<?php
namespace BB8\Emoji\Tests;
use GuzzleHttp\Client;
use BB8\Emoji\Tests\mocks\SetUpDb;

use BB8\Emoji\Models\User;
use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiKeyword;

class APITest extends \PHPUnit_Framework_TestCase
{
    private static $client;
    private static $token;
    private static $mockIds;

    public static function setUpBeforeClass()
    {
        static::$mockIds = SetUpDb::setUp();
        static::$client = new Client([
            'base_uri' => 'http://api-emojicon-staging.herokuapp.com',
            'timeout'  => 10000.0
        ]);

        $response   =   static::$client->post('auth/login', [
            'exceptions' => false,
            'form_params' => ['username' => 'test-root', 'password' => 'test-root']
        ]);
        $login         =   json_decode($response->getBody(), true);
        static::$token    =   $login['message'];
    }

    public function testAuthLoginFailed()
    {
        $response   =   static::$client->post('auth/login', [
            'exceptions'=> false,
            'form_params' => ['username' => 'root', 'password' => 'not-root']
        ]);
        $result        =   json_decode($response->getBody(), true);

        $this->assertSame($result['status'], 'error');
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testAuthLogin()
    {
         $response   =   static::$client->post(
             'auth/login',
             [
             'exceptions'=> false,
             'form_params' => ['username' => 'test-root', 'password' => 'test-root']
             ]
         );
        $result         =   json_decode($response->getBody(), true);

        $this->assertSame($result['status'], 'success');
        $this->assertSame($response->getStatusCode(), 200);
    }

    public static function tearDownAfterClass()
    {
        SetUpDb::tearDown();
    }


    public function testCorrect()
    {
        $this->assertTrue(true, true);
    }
}
