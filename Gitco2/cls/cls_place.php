<?php

class cls_place{
    function getCity_query($cityName,$code=null){

        $query = "SELECT * FROM comuni_lista ";
        $query.= "JOIN province_lista ON Pro_Codice = Com_Codice_Provincia ";
        if($code==null)
            $query.= "WHERE Com_Nome = \"".ucwords($cityName)."\"";
        else
            $query.= "WHERE Com_Codice_Catastale = \"".$code."\"";

        return $query;
    }

    function getCountry_query($countryName,$code=null){

        $query = "SELECT * FROM paesi_esteri_lista ";
        if($code==null)
            $query.= "WHERE Nome = \"".ucwords($countryName)."\"";
        else
            $query.= "WHERE CC_Paese_Estero = \"".$code."\"";

        return $query;
    }

    function getCityFromZipcode_query($zipCode){

        $query = "SELECT * FROM comuni_lista ";
        $query.= "JOIN province_lista ON Pro_Codice = Com_Codice_Provincia ";
        $query.= "WHERE Com_Cap = '".$zipCode."'";

        return $query;
    }
}

