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
    public function __construct()
    {
        parent::__construct();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withAddedHeader('Content-type', 'application/json');
        $emojis = Emoji::with('keywords')->get();

        $result = array();

        foreach ($emojis as $emoji) {
            $result[] = $this->buildEmojiData($emoji);
        }

        $body   =    $response->getBody();
        $body->write(json_encode($result));
        return $response;
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();

        $emoji      =   Emoji::find($argc['id']);
        $result     =   [];

        if ($emoji != null)
        {
            $result     =   $this->buildEmojiData($emoji);
        }

        $body->write(json_encode($result));
        return $response;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();
        $data       =   $request->getParsedBody();
        $keywords   =   $data['keywords'];
        $uid        =   $data['uid'];
        $body       =   $response->getBody();
        $message    =   '';
        $user       =   User::find($uid);

        DB::transaction(function() use($uid, $data, $keywords, $body) {
            $user   =   User::find($uid);

            if($user == null){
                throw new \Exception('User does not exist');
            }

            $emoji  =   [
                'name' => $data['name'],
                'char' => $data['char'],
                'category' => $data['category']
            ];

            $emoji = $user->emojis()->create($emoji);
            foreach ($keywords as $keyword) {
                $emoji->keywords()->create(['name' => $keyword]);
            }

            $message    =   [
                'status' => 'Success',
                'message' => 'Emoji Created Sucessfully'
            ];
            $body->write(json_encode($message));
        });

        return $response;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();
        $emoji      =   Emoji::with('keywords')->find($argc['id']);
        if ($emoji != null ) {
            $newData        =   $request->getParsedBody();

            DB::transaction(function() use($emoji, $newData) {

                $emoji->name    =   isset($newData['name']) ? $newData['name'] : $emoji->name;
                $emoji->char    =   isset($newData['char']) ? $newData['char'] : $emoji->char;
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
            $message        =   [
                'status' => 'Success',
                'message' => 'Emoji Updated'
            ];

        } else {
            $message    =   [
                'status' => 'Error',
                'message' => 'Emoji not found'
            ];
        }
        $body->write(json_encode($message));
        return $response;
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, $argc)
    {
        $response   =   $response->withAddedHeader('Content-type', 'application/json');
        $body       =   $response->getBody();
        $message    =   [];
        $emoji     =   Emoji::with('keywords')->find($argc['id']);

        if(isset($emoji)) {
            Db::transaction(function() use ($emoji) {
                $emoji->delete();
                $emoji->keywords()->delete();
            });

            $message = [
                    "status" => "success",
                    "message" => "Emoji '".$emoji->name."' deleted!"
            ];
        } else {
            $message = [
                "status" => "Error",
                "message" => "Emoji Not found"
            ];
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




}
