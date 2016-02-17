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

    /**
     * Creates all database fields required to run test.
     */
    public static function setUpBeforeClass()
    {
        //Set up database and get the ids of test data
        static::$mockIds = SetUpDb::setUp();

        //Create a new guzzleHttp client
        static::$client = new Client([
            'base_uri' => getenv('base_uri'),
            'timeout'  => 10000.0,
        ]);

        //Login user to get token for other operations during test
        $response = static::$client->post('auth/login', [
            'exceptions'  => false,
            'form_params' => ['username' => 'test-root', 'password' => 'test-root'],
        ]);
        $login = json_decode($response->getBody(), true);
        static::$token = $login['token'];
    }

    /**
     * Test  user login and authentication.
     */
    public function testAuthLogin()
    {
        $response = static::$client->post(
             'auth/login',
             [
             'exceptions'  => false,
             'form_params' => ['username' => 'test-root', 'password' => 'test-root'],
             ]
         );
        $result = json_decode($response->getBody(), true);

        $this->assertNotNull($result['token']);
        $this->assertSame($response->getStatusCode(), 200);
    }

    /**
     * Test invalid credentials during user login.
     */
    public function testAuthLoginFailed()
    {
        $response = static::$client->post('auth/login', [
            'exceptions'  => false,
            'form_params' => ['username' => 'root', 'password' => 'not-root'],
        ]);
        $result = json_decode($response->getBody(), true);

        $this->assertSame($result['message'], 'username or password incorrect');
        $this->assertSame($response->getStatusCode(), 401);
    }

    /**
     * Test return of all emojis from database.
     */
    public function testGetAllEmojis()
    {
        $response = static::$client->get('emojis', ['exceptions' => false]);

        $data = json_decode($response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame(str_replace('; charset=UTF-8', '', $response->getHeader('Content-Type')[0]), 'application/json');
        $this->assertSame($data[0]['name'], 'Happy Face');
        $this->assertSame($data[0]['category'], 'Happy');
    }

    /**
     * Test return of single emoji from database.
     */
    public function testGetSingleEmoji()
    {
        $emojiId = static::$mockIds['emojiId'];
        $keywordID = static::$mockIds['keywordsId'][0];

        $response = static::$client->get("emojis/$emojiId", ['exceptions' => false]);
        $contentType = $response->getHeader('Content-Type')[0];
        $data = json_decode($response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data['keywords'][$keywordID], 'happy');
        $this->assertSame(str_replace('; charset=UTF-8', '', $response->getHeader('Content-Type')[0]), 'application/json');
    }

    /**
     * Test Emoji not found.
     */
    public function testGetSingleEmojiFailed()
    {
        $response = static::$client->get('emojis/100', ['exceptions' => false]);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 404);
        $this->assertSame($data['message'], 'Emoji not found');
    }

    /**
     * Test inserting of new emoji.
     */
    public function testInsertEmoji()
    {
        $response = $this->setUpInsertData(static::$mockIds['userId']);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame(str_replace('; charset=UTF-8', '', $response->getHeader('Content-Type')[0]), 'application/json');
        $this->assertSame($data['message'], 'Emoji created');
    }

    /**
     * Test update of an emoji.
     */
    public function testUpdateEmoji()
    {
        $emojiId = static::$mockIds['emojiId'];

        $data = [
            'name'     => 'New Angry Face',
            'char'     => 'new angryfaceicon',
            'keywords' => [
                '1002' => 'new angry keyword',
            ],
        ];
        $headers = [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer '.static::$token,
        ];
        $response = static::$client->put("emojis/$emojiId", [
            'exceptions' => false,
            'json'       => $data,
            'headers'    => $headers,
        ]);
        $result = json_decode($response->getBody(), true);
        $this->assertSame(str_replace('; charset=UTF-8', '', $response->getHeader('Content-Type')[0]), 'application/json');
        $this->assertSame($result['message'], 'Emoji updated');
    }

    /**
     * @depends testUpdateEmoji
     * 
     * Test side effect of updating an emoji
     */
    public function testUpdateSideEffect()
    {
        $emojiId = static::$mockIds['emojiId'];
        $response = static::$client->get("emojis/$emojiId", ['exceptions' => false]);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($data['name'], 'New Angry Face');
        $this->assertSame($data['char'], 'new angryfaceicon');
    }

    /**
     * Test delete of an emoji and its side effect.
     */
    public function testDelete()
    {
        $emojiId = static::$mockIds['emojiId'];

        $headers = [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer '.static::$token,
        ];

        $response = static::$client->delete("emojis/$emojiId", [
            'exceptions' => false,
            'headers'    => $headers,
        ]);

        $result = json_decode($response->getBody(), true);
        $this->assertSame($result['message'], 'Emoji has been deleted');

        //Test Side Effect
        $response = static::$client->get("emojis/$emojiId", ['exceptions' => false]);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($data['message'], 'Emoji not found');
    }

    /**
     * Test logout of a user from the emoji API.
     */
    public function testAuthLogout()
    {
        $headers = [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer '.static::$token,
        ];
        $response = static::$client->get('auth/logout', [
            'exceptions' => false,
            'headers'    => $headers,
        ]);
        $result = json_decode($response->getBody(), true);

        $this->assertSame($result['message'], 'user has been logged out');
    }

    /**
     * Sets up data for inserting test data for update test case.
     *
     * @return ResponseInterface object the server response
     */
    public function setUpInsertData()
    {
        $data = [
            'name'     => 'Angry Face',
            'char'     => 'angryfacechar',
            'keywords' => [
                'angry', 'annoyed',
            ],
            'category' => 'angry',
        ];
        $headers = [
            'Accept'             => 'application/json',
            'Authorization'      => 'Bearer '.static::$token,
        ];
        $response = static::$client->post('emojis', [
            'exceptions' => false,
            'json'       => $data,
            'headers'    => $headers,
        ]);

        return $response;
    }

    /**
     * Destroys all data table used for test.
     */
    public static function tearDownAfterClass()
    {
        SetUpDb::tearDown();
    }
}
