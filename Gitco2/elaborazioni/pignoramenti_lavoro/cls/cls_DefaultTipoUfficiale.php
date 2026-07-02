<?php
class DefaultTipoUfficiale
{


    public static function UpdateQuery($a_params,$elaboration_id)
    {
        $pec = isset($a_params["DefaultTipoUfficialePEC"])?$a_params["DefaultTipoUfficialePEC"]:null;
        $pec1 = isset($a_params["DefaultTipoStampaPEC"])?$a_params["DefaultTipoStampaPEC"]:null;
        $rac = isset($a_params["DefaultTipoUfficialeRaccomandata"])?$a_params["DefaultTipoUfficialeRaccomandata"]:null;
        $rac1 = isset($a_params["DefaultTipoStampaRaccomandata"])?$a_params["DefaultTipoStampaRaccomandata"]:null;

        if($pec != null && $pec1 != null && $rac != null && $rac1 != null ){
            $query = "Update elaborations Set 
                DefaultPecTipoUfficiale = '$pec',
                DefaultPecTipoStampa = '$pec1', 
                DefaultRaccomandataTipoUfficiale = '$rac',
                DefaultRaccomandataTipoStampa = '$rac1'
                where Id = $elaboration_id";
        }
        else{
            $query = "Update elaborations Set 
                DefaultPecTipoUfficiale = NULL,
                DefaultPecTipoStampa = NULL, 
                DefaultRaccomandataTipoUfficiale = NULL,
                DefaultRaccomandataTipoStampa = NULL
                where Id = $elaboration_id";
        }
        return $query;
    }

    public static function ReadQuery($elaborationId)
    {
        $query = "select DefaultPecTipoUfficiale,DefaultRaccomandataTipoUfficiale,DefaultPecTipoStampa,DefaultRaccomandataTipoStampa from elaborations 
        where Id = $elaborationId";
        return $query;
    }
    

}
?>