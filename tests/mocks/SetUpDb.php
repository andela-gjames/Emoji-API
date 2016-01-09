<?php

namespace BB8\Emoji\Tests\mocks;

class SetUpDb
{
    public static function setUp()
    {
        $DBH = new \PDO('sqlite:'.__DIR__.'/../../src/database/database.sqlite');
        $DBH->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $DBH->exec("INSERT OR IGNORE INTO 'users' VALUES(1000, 'root','4813494d137e1631bba301d5acab6e7bb7aa74ce1185d456565ef51d737677b2', null,'2016-01-05 23:04:47','2016-01-07 21:33:20');");

        $DBH->exec("INSERT OR IGNORE INTO 'emojis' VALUES(1001,'Scared Face','scaredfaceicon','scared',1000,'2016-01-05 23:15:58','2016-01-08 00:03:06');");
        $DBH->exec("INSERT OR IGNORE INTO 'emojis' VALUES(1002,'Happy Face','happyfaceicon','happy',1000,'2016-01-05 23:15:58','2016-01-08 00:03:06');");
        $DBH->exec("INSERT OR IGNORE INTO 'emojis' VALUES(1003,'Sad Face','sadfaceicon','sad',1000,'2016-01-05 23:15:58','2016-01-08 00:03:06');");


        $DBH->exec("INSERT OR IGNORE INTO 'keywords' VALUES(1004,'scared', 1001);");
        $DBH->exec("INSERT OR IGNORE INTO 'keywords' VALUES(1005,'face', 1001);");
        $DBH->exec("INSERT OR IGNORE INTO 'keywords' VALUES(1006,'happy', 1002);");
        $DBH->exec("INSERT OR IGNORE INTO 'keywords' VALUES(1007,'happy face', 1002);");
        $DBH->exec("INSERT OR IGNORE INTO 'keywords' VALUES(1008,'sad', 1003);");
        $DBH->exec("INSERT OR IGNORE INTO 'keywords' VALUES(1009,'sad face', 1003);");
    }
}
