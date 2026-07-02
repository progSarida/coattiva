<?php

require_once MODELS."/Model.php";

class TariffaCoazione extends Model{

    static function getById($id){
        $query = "SELECT * FROM tariffe_coazione WHERE ID=".$id;
        return self::getRow($query);
    }

}