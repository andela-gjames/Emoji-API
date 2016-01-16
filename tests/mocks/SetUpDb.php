<?php

namespace BB8\Emoji\Tests\mocks;

use BB8\Emoji\Database\Connection;
use BB8\Emoji\Database\Schema;
use BB8\Emoji\Models\Emoji;
use BB8\Emoji\Models\EmojiKeyword;
use BB8\Emoji\Models\User;

class SetUpDb
{
    private static $conn;

    /**
     * Create database tables required for testing
     * @return array array of test data created
     */
    public static function setUp()
    {
        //Load environment variables
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
        if (!getenv('APP_ENV')) {
            $dotenv->load();
        }

        //Create connection and execute schema
        static::$conn = new Connection();
        Schema::createSchema();
        
        //Add test data to user table if not exist or no errors returned
        $user = User::firstOrCreate(['username' => 'test-root', 'password' => hash('SHA256', 'test-root')]);
        $emojiData = [
            'name'     => 'Happy Face',
            'char'     => ':)',
            'category' => 'Happy',
        ];

        //Build keywords array and create users emojis
        $keywords = ['happy', 'face', 'emotion'];
        $emoji = $user->emojis()->firstOrCreate($emojiData);

        //Add keywords to keywords table
        $keyId = [];
        foreach ($keywords as $keyword) {
            $key = $emoji->keywords()->firstOrCreate(['name' => $keyword]);
            $keyId[] = $key->id;
        }

        return ['userId' => $user->id, 'emojiId' => $emoji->id, 'keywordsId' => $keyId];
    }

    /**
     * undo all setup made for testing
     */
    public static function tearDown()
    {
        User::truncate();
        Emoji::truncate();
        EmojiKeyword::truncate();

        Schema::dropAllSchema();
    }
}
