<?php
namespace BB8\Emoji\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Connection
{
    public function __construct()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
        $dotenv->load();

        $this->capsule = new Capsule();
        $this->capsule->addConnection(array(
            'driver'    => getenv('driver'),
            'host'      => getenv('host'),
            'database'  => __DIR__.'/'.getenv('database'),
            'charset'   => getenv('charset'),
            'username'  => getenv('username'),
            'password'  => getenv('password'),
            'collation' => getenv('collation')
        ));

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher());
        $this->capsule->getConnection('default');
    }
}