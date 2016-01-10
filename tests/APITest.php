<?php
namespace BB8\Emoji\Tests;
use GuzzleHttp\Client;
use BB8\Emoji\Tests\mocks\SetUpDb;

class APITest extends \PHPUnit_Framework_TestCase
{
    private static $client;
    private static $token;
    private static $mockIds;

    public static function setUpBeforeClass()
    {
        static::$mockIds = SetUpDb::setUp();
        static::$client = new Client([
            'base_uri' => 'http://api-emojicon-staging.herokuapp.com/',
            'timeout'  => 10.0
        ]);

        $response   =   static::$client->post('auth/login', [
            'exceptions'=> false,
            'form_params' => ['username' => 'test-root', 'password' => 'test-root']
        ]);
        $login         =   json_decode($response->getBody(), true);
        static::$token    =   $login['message'];
    }

    public function testCorrect()
    {
        $this->assertTrue(true, true);
    }
}
