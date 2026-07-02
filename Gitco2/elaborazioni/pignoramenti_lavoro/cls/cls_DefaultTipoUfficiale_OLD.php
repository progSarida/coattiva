<?php
class DefaultTipoUfficiale
{


    public static function UpdateQuery($a_params,$elaboration_id)
    {
        $pec = $a_params["DefaultTipoUfficialePEC"];
        $rac = $a_params["DefaultTipoUfficialeRaccomandata"];

        $query = "Update elaborations 
                  Set DefaultPecTipoUfficiale = '$pec', 
                  DefaultRaccomandataTipoUfficiale = '$rac'
                  where Id = $elaboration_id
                  
                  ";
        return $query;
    }

    public static function ReadQuery($elaborationId)
    {
        $query = "select DefaultPecTipoUfficiale,DefaultRaccomandataTipoUfficiale from elaborations 
        where Id = $elaborationId";
        return $query;
    }
    

}
?>