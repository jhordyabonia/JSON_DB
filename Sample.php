<?php
    require_once("./JSON_DB.php");
    
    function test(){
        $db = new JSON_DB("file","id,name,opacity","id");
        $db->insert(['id'=>1,'name'=>'none0']);
        $db->insert(['id'=>2,'name'=>'none1']);
        $db->insert(['id'=>3,'name'=>'none2']);
        $db->insert(['id'=>4,'name'=>'test0']);
        $db->insert(['id'=>5,'name'=>'test1']);
        $db->insert(['id'=>6,'name'=>'test2']);
        $db->insert(['id'=>7,'color'=>'#FF0000','name'=>'red']);
        $db->update(['id'=>7],['color'=>'#0000FF','name'=>'blue']);
        $db->updateLine(6,['color'=>'#00FF00','name'=>'green','opacity'=>0.1]);

        /*$db->remove(['id'=>'2'])."\n";
        $db->remove(['name'=>'none0'])."\n";
        $db->remove(['name'=>'none'],true)."\n";
        $db->remove(['name'=>'test'],true)."\n";*/

        $db->commit();

        //print_r($db->getAll());
        print_r($db->get(['id'=>7]));
    }
    if(isset($_GET['test'])||(isset($argv[1])&&$argv[1]=="test"))
        test();