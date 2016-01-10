<?php
namespace BB8\Emoji\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Schema
{
    public static function createSchema()
    {
        if (!Capsule::schema()->hasTable('users')) {
            Capsule::schema()->create('users', function($table){
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('password');
                $table->integer('jit')->nullable();
                $table->timestamps();
            });
        }

        if (!Capsule::schema()->hasTable('emojis')) {
            Capsule::schema()->create('emojis', function($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('char');
                $table->string('category');
                $table->integer('user_id');
//                $table->foreign('user_id')->references('id')->on('users');
                $table->timestamps();
            });
        }

        if (!Capsule::schema()->hasTable('keywords')) {
            Capsule::schema()->create('keywords', function($table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('emoji_id');
//                $table->foreign('emoji_id')->references('id')->on('emoji')->onDelete('cascade');
            });
        }
    }

    public static function dropAllSchema()
    {
        Capsule::schema()->dropIfExists('keywords');
        Capsule::schema()->dropIfExists('emojis');
        Capsule::schema()->dropIfExists('users');
    }
}
