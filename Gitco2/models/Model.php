<?php

require_once TRAITS."/Db.php";

class Model{

    use Db;

    public static function getRow($query){
        return self::getArrayLine(self::ExecuteQuery($query));
    }

    public static function getRows($query, $key = false){
        return self::getResults(self::ExecuteQuery($query),"array",$key);
    }

}