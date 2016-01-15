<?php

namespace BB8\Emoji\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
Creates Schema for database 
**/
class Schema
{
    /**
     * Generates all needed table for the application to run
     */
    public static function createSchema()
    {
        //Create users table if it does not exist already
        if (!Capsule::schema()->hasTable('users')) {
            Capsule::schema()->create('users', function ($table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('password');
                $table->integer('jit')->nullable();
                $table->timestamps();
            });
        }

        //Create emojis table if it does not exist already
        if (!Capsule::schema()->hasTable('emojis')) {
            Capsule::schema()->create('emojis', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('char');
                $table->string('category');
                $table->integer('user_id');
                $table->timestamps();
            });
        }
        
        //Create keywords table if it does not exist already
        if (!Capsule::schema()->hasTable('keywords')) {
            Capsule::schema()->create('keywords', function($table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('emoji_id');
            });
        }
    }

    /**
     * Drops all table in the application's database
     */
    public static function dropAllSchema()
    {
        Capsule::schema()->dropIfExists('keywords');
        Capsule::schema()->dropIfExists('emojis');
        Capsule::schema()->dropIfExists('users');
    }
}
