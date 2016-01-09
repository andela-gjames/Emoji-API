<?php
namespace BB8\Emoji\Tests;
use GuzzleHttp\Client;
use BB8\Emoji\Tests\mocks\SetUpDb;

class APITest extends \PHPUnit_Framework_TestCase
{
    private static $client;
    private static $token;
    private static $mockIds;

    public function testTrue()
    {
        $this->assertTrue(true, true);
    }
}
