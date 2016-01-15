<?php

namespace BB8\Emoji\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
/**
    Connection class creates a connection to the database
**/
class Connection
{
    /**
     * Creates connection to the database
     */
    public function __construct()
    {
        //Intialize and Check for .env file
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');

        //Check if app environment is testing, staging or production
        if (!getenv('APP_ENV')) {
            $dotenv->load();
        }

        //Create a connection capsule
        $this->capsule = new Capsule();

        //Add a new connection
        $this->capsule->addConnection([
                'driver'    => getenv('driver'),
                'host'      => getenv('host'),
                'database'  => getenv('database'),
                'charset'   => getenv('charset'),
                'username'  => getenv('username'),
                'password'  => getenv('password'),
                'collation' => getenv('collation'),
        ]);

        //Boostrap with eloquent
        $this->capsule->bootEloquent();
        //Set the connection as globale
        $this->capsule->setAsGlobal();
        //set event dispater
        $this->capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher());
        //Get the default connection
        $this->capsule->getConnection('default');
    }
}
