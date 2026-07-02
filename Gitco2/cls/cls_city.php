<?php


class cls_city
{
    public function getCityProvince_query($cityCode){
        if($cityCode==null)
            return false;

        $query = "SELECT C.*, P.* FROM comuni_lista 
        as C join province_lista as P on C.Com_Codice_Provincia = P.Pro_Codice 
        WHERE C.Com_Codice_Catastale = '".$cityCode."'";

        return $query;
    }

}