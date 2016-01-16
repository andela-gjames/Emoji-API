<?php

namespace BB8\Emoji\Tests;

use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Tests\mocks\SetUpDb;
use GuzzleHttp\Client;

class APITest extends \PHPUnit_Framework_TestCase
{
    private static $client;
    private static $token;
    private static $mockIds;

    public static function setUpBeforeClass()
    {
        static::$mockIds = SetUpDb::setUp();
        static::$client = new Client([
            'base_uri' => getenv('base_uri'),
            'timeout'  => 10000.0,
        ]);

        $response = static::$client->post('auth/login', [
            'exceptions'  => false,
            'form_params' => ['username' => 'test-root', 'password' => 'test-root'],
        ]);
        $login = json_decode($response->getBody(), true);
        static::$token = $login['token'];
    }

    public function testTrue()
    {
        $this->assertTrue(true, true);
    }

    public static function tearDownAfterClass()
    {
        SetUpDb::tearDown();
    }
}
