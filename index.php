<?php

require __DIR__.'/src/route.php';
//require 'vendor/autoload.php';
//
//
////require 'vendor/autoload.php';
//use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Capsule\Manager as Capsule;
//use Slim\App;
//
//$configuration = [
//    'settings' => [
//        'displayErrorDetails' => true,
//    ],
//];
//$app 	= new App($configuration);
//
//class User extends Model
//{
//    protected $fillable = ['password', 'username'];
//}
//
//$capsule = new Capsule();
//$capsule->addConnection(array(
//    'driver'    => 'sqlite',
//    'database'  => __DIR__.'/src/Database/database2.sqlite'
//));
//
//
//$capsule->bootEloquent();
//$capsule->setAsGlobal();
//$capsule->getConnection('default');
//
//$app->get('/', function($req, $resp, $args) {
//    var_dump(User::create(['username' => 'Georgwwqweqwee James', 'password'=>'1234', 'jit'=>'12345']));
//
//});
//
//$app->run();
