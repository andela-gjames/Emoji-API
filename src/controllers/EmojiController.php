<?php

namespace BB8\Emoji\Controllers;

use BB8\Emoji\Auth;
use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiKeyword;
use BB8\Emoji\Models\User;
use BB8\Emoji\Exceptions\EmojiException;
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
        //Get all Emojis and their associated keywords
        $emojis = Emoji::with('keywords')->get();

        //Return Data in array
        $result = [];
        foreach ($emojis as $emoji) {
            $result[] = $this->buildEmojiData($emoji);
        }

        //Write to response output
        $response->getBody()->write(json_encode($result));

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
        //Get the single Emoji with the id from route
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
        $response->getBody()->write(json_encode($result));

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
        //Get Emoji data from request
        $data = $request->getParsedBody();
        
        //Extract keywords from data
        $keywords = $data['keywords'];
        
        //Decode token to get object of data
        $decodedToken = Auth::decodeToken($request->getHeader('Authorization')[0], $request);
        
        //Check if user exist with the user id
        $user = User::find($decodedToken->data->uid);
      
        //Generate emoji data to return
        $emoji = [
            'name'     => $data['name'],
            'char'     => $data['char'],
            'category' => $data['category'],
        ];

        DB::transaction(function () use ($user, $emoji, $keywords) {
            //Insert Emoji data into emojis table
            $emoji = $user->emojis()->create($emoji);

            $keywordsObj = [];
            
            //create emoji keyword objects
            foreach ($keywords as $keyword) {
                $obj = new EmojiKeyword();
                $obj->name = $keyword;
                $keywordsObj[] = $obj;
            }
            
            //Save all keywords objects
            $emoji->keywords()->saveMany($keywordsObj);
        });    

        $response->getBody()->write(json_encode(['message' => 'Emoji created']));
        return $response;
    }

     /**
     * PUTs route for updating an Emoji
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @param  array                  $argc     contains route query params
     * @return ResponseInterface      HTTP response of client request
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        //Get emoji to be updated
        $emoji = Emoji::with('keywords')->find($argc['id']);
        
        $message = '';
        //Check that Emoji is not null then update if true
        if ($emoji != null) {
            $emoji->update($request->getParsedBody());
            $message = ['message' => static::EMOJIUPDATED];
        } else {
            throw new EmojiException(static::EMOJINOTFOUNDERROR, 404);
        }
        //Write to response body and return $response
        $response->getBody()->write(json_encode($message));
        return $response;
    }
    
    /**
     * PUTs route for updating an Emoji Keyword
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @param  array                  $argc     contains route query params
     * @return ResponseInterface      HTTP response of client request
     */
    public function updateKey(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        //Get emoji
        $emoji = Emoji::with('keywords')->find($argc['id']);
        
        //Get emoji keyword to be updated
        $keyword = $emoji->keywords()->find($argc['kId']);
        
        //Ensure keyword is not null
        if($keyword != null) {
            $keyword->update($request->getParsedBody());
            $message = ['message' => 'Keyword updated'];
        } else {
            throw new EmojiException('Keyword not found', 404);
        }
        
        //Write to response body and return $response
        $response->getBody()->write(json_encode($message));
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
        $message = [];
        $emoji = Emoji::with('keywords')->find($argc['id']);

        if (isset($emoji)) {
            Db::transaction(function () use ($emoji) {
                $emoji->delete();
                $emoji->keywords()->delete();
            });
            $message = ['message'=>'Emoji has been deleted'];
        } else {
            $response = $response->withStatus(404);
            $message = ['message' => 'Emoji does not exist'];
        }
        $response->getBody()->write(json_encode($message));
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
}
