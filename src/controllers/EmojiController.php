<?php
namespace BB8\Emoji\Controllers;

use BB8\Emoji\Controllers\BaseController;
use BB8\Emoji\Models\User;
use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiCategory;
use BB8\Emoji\Models\EmojiKeyword;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Database\Capsule\Manager as DB;

class EmojiController extends BaseController
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withAddedHeader('Content-type', 'application/json');
        $emojis = Emoji::with('keywords')->get();
        $body   =    $response->getBody();

        $result = array();
        foreach ($emojis as $emoji) {
            $result[] = $this->buildEmojiData($emoji);
        }

        $body->write(json_encode($result));
        return $response;
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        $response   =   $response->withAddedHeader('content-type', 'application/json');
        $body       =   $response->getBody();

        $emoji      =   Emoji::find($argc['id']);
        $result     =   [];

        if ($emoji != null)
        {
            $result     =   $this->buildEmojiData($emoji);
        } else {
            $response = $response->withStatus(404);
            $result     =   $this->getMessage(static::EMOJINOTFOUNDERROR, $response, 404);
        }

        $body->write(json_encode($result));
        return $response;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();
        $token      =   $request->getHeader('Authorization')[0];
        $data       =   $request->getParsedBody();
        $keywords   =   $data['keywords'];
        $body       =   $response->getBody();
        $decodedToken    =   $this->auth->decodeToken($token);
        $uid        =   $decodedToken->data->uid;
        $user       =   User::find($uid);

        $user   =   User::find($uid);
        if($user != null){
            $message = $this->authenticateRouteRequest($token, $user->jit, $response, static::EMOJICREATED, 201);

            $emoji  =   [
                    'name' => $data['name'],
                    'char' => $data['char'],
                    'category' => $data['category']
            ];

            if($message['status'] == 'success') {
                DB::transaction(function() use($user, $emoji, $keywords) {
                        $emoji = $user->emojis()->create($emoji);
                        foreach ($keywords as $keyword) {
                            $emoji->keywords()->create(['name' => $keyword]);
                        }
                });
            }
        } else {
            $message    =   $this->getMessage(static::USERDOESNOTEXISTERROR, $response, 401);
        }

        $body->write(json_encode($message));
        return $response;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();
        $emoji      =   Emoji::with('keywords')->find($argc['id']);

        $token      =   $request->getHeader('Authorization')[0];
        $decodedToken    =   $this->auth->decodeToken($token);
        $uid        =   $decodedToken->data->uid;
        $user   =   User::find($uid);


        if($user == null){
            throw new \Exception(static::USERDOESNOTEXISTERROR);
        }

        if ($emoji != null ) {
            $newData        =   $request->getParsedBody();
            $this->updateEmojiData($emoji, $newData);
            $message    =   $this->authenticateRouteRequest($token, $user->jit, $response, static::EMOJIUPDATED);
        } else {
            $message    =   $this->getMessage(static::EMOJINOTFOUNDERROR, $response, 404);
        }
        $body->write(json_encode($message));
        return $response;
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();
        $message    =   [];
        $emoji      =   Emoji::with('keywords')->find($argc['id']);
        $token      =   $request->getHeader('Authorization')[0];
        $decodedToken    =   $this->auth->decodeToken($token);
        $uid        =   $decodedToken->data->uid;
        $user   =   User::find($uid);


        if($user == null){
            throw new \Exception(static::USERDOESNOTEXISTERROR);
        }

        $message    =   $this->authenticateRouteRequest($token, $user->jit, $response, "Emoji '".$emoji->name."' deleted!");

        if( $message['status'] == 'success') {
            if(isset($emoji)) {
                Db::transaction(function() use ($emoji) {
                    $emoji->delete();
                    $emoji->keywords()->delete();
                });
            } else {
                $message    =   $this->getMessage(static::EMOJINOTFOUNDERROR, $response, 404);
            }
        }

        $body->write(json_encode($message));
        return $response;
    }

    private function buildEmojiData($emoji)
    {
        $result = array();

        $result['id']       =   $emoji->id;
        $result['name']     =   $emoji->name;
        $result['char']     =   $emoji->char;

        $keywords = array();
        foreach ($emoji->keywords as $keyword) {
            $keywords[$keyword->id]     =   $keyword->name;
        }

        $result['keywords']     =   $keywords;
        $result['category']     =   $emoji->category;
        $result['date_created']     =   $emoji->created_at->toDateString();
        $result['date_modified']    =   $emoji->updated_at->toDateString();
        $result['created_by']       =   $emoji->user->username;

        return $result;
    }

    private function updateEmojiData($emoji, $newData)
    {
        DB::transaction(function() use($emoji, $newData) {
            $emoji->name        =   isset($newData['name']) ? $newData['name'] : $emoji->name;
            $emoji->char        =   isset($newData['char']) ? $newData['char'] : $emoji->char;
            $emoji->user_id     =   isset($newData['uid'])  ? $newData['uid']  : $emoji->user_id;
            $emoji->category    =   isset($newData['category']) ? $newData['category'] : $emoji->category;

            foreach ($newData['keywords'] as $key_id => $name) {
                $keyword = EmojiKeyword::find($key_id);

                if(!isset($keyword)) {
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
