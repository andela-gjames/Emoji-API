<?php

namespace BB8\Emoji\Tests\mocks;
use BB8\Emoji\Database\Connection;
use BB8\Emoji\Models\User;
use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiKeyword;
use BB8\Emoji\Database\Schema;

class SetUpDb
{
    private static $conn;
    public static function setUp()
    {
        static::$conn = new Connection();
        Schema::createSchema();
        $user = User::firstOrCreate(['username'=>'test-root', 'password' => hash('SHA256', 'test-root')]);
        $emojiData = [
            'name' => 'Happy Face',
            'char' => ':)',
            'category' => 'Happy'
        ];

        $keywords = ['happy', 'face', 'emotion'];
        $emoji = $user->emojis()->firstOrCreate($emojiData);

        $keyId = array();
        foreach ($keywords as $keyword) {
            $key = $emoji->keywords()->firstOrCreate(['name' => $keyword]);
            $keyId[] = $key->id;
        }
        return ['userId' => $user->id, 'emojiId' => $emoji->id, 'keywordsId'=> $keyId];
    }

    public static function tearDown()
    {
        User::truncate();
        Emoji::truncate();
        EmojiKeyword::truncate();

        Schema::dropAllSchema();
    }
}
