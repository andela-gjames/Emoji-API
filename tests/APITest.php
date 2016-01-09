<?php
namespace BB8\Emoji\Tests;
use GuzzleHttp\Client;
use BB8\Emoji\Tests\mocks\SetUpDb;
use BB8\Emoji\Database\Schema;
use BB8\Emoji\Database\Connection;

class APITest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $token;

   public function testAdd()
   {
       $this->assertTrue(true, true);
   }
}
