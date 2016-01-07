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

}
