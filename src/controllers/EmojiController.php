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

}
