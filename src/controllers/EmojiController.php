<?php

namespace BB8\Emoji\Controllers;

use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiKeyword;
use BB8\Emoji\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmojiController extends BaseController
{
    /**
     * Intializes dependency container
     * @param Container $container Slim dependency container
     */
    public function __construct($container)
    {
        //Call parent constructor
        parent::__construct($container);
    }

     /**
     * Index route for getting all Emojis
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Set return Content-type to JSON
        $response = $response->withAddedHeader('Content-type', 'application/json');
        //Get all Emojis and their associated keywords
        $emojis = Emoji::with('keywords')->get();
        //Get response body for writing to client
        $body = $response->getBody();

        //Return Data in array
        $result = [];
        foreach ($emojis as $emoji) {
            $result[] = $this->buildEmojiData($emoji);
        }

        //Write to response output
        $body->write(json_encode($result));

        //Return response
        return $response;
    }

     /**
     * GETs a single Emoji
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        //Set response type to JSON
        $response = $response->withAddedHeader('content-type', 'application/json');
        //Get Response body for writing message
        $body = $response->getBody();

        //Get a single Emoji with id from route
        $emoji = Emoji::find($argc['id']);
        
        //Check if the Emoji with the id exist
        if ($emoji != null) {
            //Build Emoji data to return
            $result = $this->buildEmojiData($emoji);
        } else {
            //Repond with 404 if Emoji not found
            $response = $response->withStatus(404);
            $result = $this->getMessage(static::EMOJINOTFOUNDERROR, $response, 404);
        }

        //Write message to response interface
        $body->write(json_encode($result));

        //Return response
        return $response;
    }

     /**
     * Index route for getting all Emojis
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Set response type to JSON
        $response = $response->withAddedHeader('Content-type', 'application/json');
        //Get body of response to write to
        $body = $response->getBody();
        //Get token for accessing this route
        $token = $request->getHeader('Authorization')[0];
        //Get Emoji data from request
        $data = $request->getParsedBody();
        //Extract keywords from data
        $keywords = $data['keywords'];
        //Get body of response to write to
        $body = $response->getBody();
        //Decode token to get object of data
        $decodedToken = $this->auth->decodeToken($token);
        //Extract the user id from decoded token
        $uid = $decodedToken->data->uid;
        //Check if user exist with the user id
        $user = User::find($uid);
        if ($user != null) {
            //Authenticae the route request to ensure user has priviledge to access it
            $message = $this->authenticateRouteRequest($token, $user->jit, $response, static::EMOJICREATED, 201);
            //Generate emoji data to return
            $emoji = [
                'name'     => $data['name'],
                'char'     => $data['char'],
                'category' => $data['category'],
            ];

            //Check if user is authenticated to access route and insert emoji data if present
            if ($message['status'] == 'success') {
                DB::transaction(function () use ($user, $emoji, $keywords) {
                    //Insert Emoji data into emojis table
                    $emoji = $user->emojis()->create($emoji);
                    
                    //Add eachbkeyword to keywords table
                    foreach ($keywords as $keyword) {
                        $emoji->keywords()->create(['name' => $keyword]);
                    }
                });
            }
        } else {
            //Return message if user does not exist
            $message = $this->getMessage(static::USERDOESNOTEXISTERROR, $response, 401);
        }

        $body->write(json_encode($message));
        return $response;
    }

     /**
     * PUTs route for updating an Emoji
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @param  integer                $argc     ID of Emoji to update
     * @return ResponseInterface      HTTP response of client request
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        //Set return type to JSON and get body of response
        $response = $response->withAddedHeader('Content-type', 'application/json');
        $body = $response->getBody();
        
        //Get emoji to be updated
        $emoji = Emoji::with('keywords')->find($argc['id']);

        //Get the token from the header of request and decode the token
        $token = $request->getHeader('Authorization')[0];
        $decodedToken = $this->auth->decodeToken($token);
        
        //Extract user id from token and get the user
        $uid = $decodedToken->data->uid;
        $user = User::find($uid);

        //Check if user exist
        if ($user == null) {
            throw new \Exception(static::USERDOESNOTEXISTERROR);
        }

        //Check that Emoji is not null then update if true
        if ($emoji != null) {
            $newData = $request->getParsedBody();
            $this->updateEmojiData($emoji, $newData);
            $message = $this->authenticateRouteRequest($token, $user->jit, $response, static::EMOJIUPDATED);
        } else {
            //Return 404 not found if emoji does not exist
            $message = $this->getMessage(static::EMOJINOTFOUNDERROR, $response, 404);
        }
        //Write to response body and return $response
        $body->write(json_encode($message));
        return $response;
    }

    /**
     * DELETE route for deleting an Emoji
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @param  integer                $argc     ID of Emoji to delete
     * @return ResponseInterface      HTTP response of client request
     */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        //Intialize parameters
        $response = $response->withAddedHeader('Content-type', 'application/json');
        $body = $response->getBody();
        $message = [];
        $emoji = Emoji::with('keywords')->find($argc['id']);
        //Get token from header
        $token = $request->getHeader('Authorization')[0];
        //Decode token
        $decodedToken = $this->auth->decodeToken($token);
        //Get user id from decoded token
        $uid = $decodedToken->data->uid;
        //Find user with $uid
        $user = User::find($uid);

        //Check if user does not exist
        if ($user == null) {
            throw new \Exception(static::USERDOESNOTEXISTERROR);
        }

        //Authenticate that token is valid
        $message = $this->authenticateRouteRequest($token, $user->jit, $response, "Emoji '".$emoji->name."' deleted!");

        //If token is valid delete Emoji from record
        if ($message['status'] == 'success') {
            if (isset($emoji)) {
                Db::transaction(function () use ($emoji) {
                    $emoji->delete();
                    $emoji->keywords()->delete();
                });
            } else {
                $message = $this->getMessage(static::EMOJINOTFOUNDERROR, $response, 404);
            }
        }
        $body->write(json_encode($message));
        return $response;
    }

    /**
     * Build an Eloquent Collection object into an array
     * @param  Collection $emoji Eloquent object to build
     * @return array      built emoji data to return
     */
    private function buildEmojiData($emoji)
    {
        //Intialize result to empty
        $result = [];

        //Set data to return
        $result['id'] = $emoji->id;
        $result['name'] = $emoji->name;
        $result['char'] = $emoji->char;
        
        //Initialize keywords
        $keywords = [];
        //Popluate keywords
        foreach ($emoji->keywords as $keyword) {
            $keywords[$keyword->id] = $keyword->name;
        }

        //Add generated keywords to result
        $result['keywords'] = $keywords;
        $result['category'] = $emoji->category;
        $result['date_created'] = $emoji->created_at->toDateString();
        $result['date_modified'] = $emoji->updated_at->toDateString();
        $result['created_by'] = $emoji->user->username;

        return $result;
    }

    /**
     * Abstracted method to update emoji
     * @param Emoji $emoji   Old emoji data
     * @param array $newData New Emoji Data
     */
    private function updateEmojiData($emoji, $newData)
    {
        //Start transaction
        DB::transaction(function () use ($emoji, $newData) {
            //Check each if data in emoji needs overriding
            $emoji->name = isset($newData['name']) ? $newData['name'] : $emoji->name;
            $emoji->char = isset($newData['char']) ? $newData['char'] : $emoji->char;
            $emoji->user_id = isset($newData['uid'])  ? $newData['uid']  : $emoji->user_id;
            $emoji->category = isset($newData['category']) ? $newData['category'] : $emoji->category;

            //Update keywords
            foreach ($newData['keywords'] as $key_id => $name) {
                $keyword = EmojiKeyword::find($key_id);

                if (!isset($keyword)) {
                    $keyword = new EmojiKeyword();
                    $keyword->emoji_id = $emoji->id;
                }
                $keyword->name = $name;
                $keyword->save();
            }
            $emoji->save();

        });
    }
}
