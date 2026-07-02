<?php

require_once MODELS."/Model.php";

class PartitaTributi extends Model{

    static function getById($id){
        if(empty($id))
            return null;

        $query = "SELECT * FROM partita_tributi WHERE ID=".$id;
        return self::getRow($query);
    }

    static function getPignoramentiByPartita($partitaId){
        if(empty($partitaId))
            return array();

        $query = "SELECT P.*, DT.Description AS DocumentType FROM pignoramento_generale P ".
        "JOIN document_type DT ON DT.Id=P.DocumentTypeId ".
        "WHERE P.Partita_ID = ".$partitaId;
        return self::getRows($query);

    }

}