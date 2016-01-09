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

    public function setUp()
    {
        $conn = new Connection();
        Schema::createSchema();
        SetUpDb::setUp();
        $this->client = new Client([
            'base_uri' => 'http://api-emojicon.herokuapp.com',
            'timeout'  => 10.0
        ]);

        $response   =   $this->client->post('auth/login', [
            'exceptions'=> false,
            'form_params' => ['username' => 'root', 'password' => 'root']
        ]);

        $login         =   json_decode($response->getBody(), true);

        $this->token    =   $login['message'];
    }

    public function testAuthLogin()
    {
        $responseValid   =   $this->client->post('auth/login', [
            'exceptions'=> false,
            'form_params' => ['username' => 'root', 'password' => 'root']
        ]);

        $valid          =   json_decode($responseValid->getBody(), true);

        $validStatus    =   $valid['status'];
        $validStatusCode =  $responseValid->getStatusCode();;

        $this->assertSame($validStatus, 'success');
        $this->assertSame($validStatusCode, 200);
    }

    public function testAuthLoginFailed()
    {
         $responseInValid   =   $this->client->post('auth/login', [
            'exceptions'=> false,
            'form_params' => ['username' => 'root', 'password' => 'not-root']
        ]);

         $invalid        =   json_decode($responseInValid->getBody(), true);

        $invalidStatus  =   $invalid['status'];
        $invalidStatusCode = $responseInValid->getStatusCode();

        $this->assertSame($invalidStatus, 'error');
        $this->assertSame($invalidStatusCode, 401);
    }

    public function testGetAllEmojis()
    {
        $response   =   $this->client->get('emojis', ['exceptions'=> false]);
        $data       =   json_decode($response->getBody(), true);
        $scared     =   $data[0]['name'];
        $happy      =   $data[1]['category'];


        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($response->getHeader('Content-Type')[0], 'application/json');
        $this->assertSame($scared, 'Scared Face');
        $this->assertSame($happy, 'happy');


    }

    public function testGetSingleEmoji()
    {
        $response404    =   $this->client->get('emojis/1', ['exceptions'=> false]);
        $response200    =   $this->client->get('emojis/1001', ['exceptions'=> false]);
        $contentType    =   $response200->getHeader('Content-Type')[0];
        $data           =   json_decode($response200->getBody(), true);
        $keyword1       =   $data['keywords'][1004];
        $keyword2       =   $data['keywords'][1005];

        $this->assertSame($response404->getStatusCode(), 404);
        $this->assertSame($response200->getStatusCode(), 200);
        $this->assertSame($keyword1, 'scared');
        $this->assertSame($keyword2, 'face');

        $this->assertSame($contentType, 'application/json');
    }

    public function testInsertEmoji()
    {
        $data = [
            'name' => 'Angry Face',
            'char' => 'angryfacechar',
            'keywords' => [
                'angry', 'annoyed'
            ],
            'category' => 'angry',
            'created_by' => 1000
        ];

        $headers = [
                'Accept'     => 'application/json',
                'Authorization'      => "Bearer $this->token"
            ];

        $response = $this->client->post('emojis', [
            'exceptions' => false,
            'json' => $data,
            'headers' => $headers
        ]);

        $data =   json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 201);
        $this->assertSame($response->getHeader('Content-Type')[0], 'application/json');
        $this->assertSame($response->getStatusCode(), 201);
    }

    public function testUpdateEmoji()
    {
        $data = [
            'name' => 'New Angry Face',
            'char' => 'new angryfaceicon',
            'keywords' => [
                '1002'=>'new happy keyword'
            ]
        ];

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Bearer $this->token"
        ];

        $response = $this->client->put('emojis/1003', [
            'exceptions' => false,
            'json' => $data,
            'headers' => $headers
        ]);

        $result = json_decode($response->getBody(), true);

        $this->assertSame($response->getHeader('Content-Type')[0], 'application/json');
        $this->assertSame($result['status'], 'success');
    }

    public function testUpdateSideEffect()
    {
        $response    =   $this->client->get('emojis/1003', ['exceptions'=> false]);
        $data           =   json_decode($response->getBody(), true);

        $this->assertSame($data['name'], 'New Angry Face');
        $this->assertSame($data['char'], 'new angryfaceicon');
    }

    public function testDelete()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Bearer $this->token"
        ];

        $response = $this->client->delete('emojis/1003', [
            'exceptions' => false,
            'headers' => $headers
        ]);

        $result = json_decode($response->getBody(), true);

        $this->assertSame($result['status'], 'success');

        //Test Side Effect
        $response       =   $this->client->get('emojis/1003', ['exceptions'=> false]);
        $data           =   json_decode($response->getBody(), true);

        $this->assertSame($data['status'], 'error');
        $this->assertSame($data['message'], 'Emoji not found');
    }
}
