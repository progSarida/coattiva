<?php

class AggiornaDefault
{
    public static function AggiornaIngiunzioni($cls_db,$elab_id)
    {
        $query = "update atto
        set Tipo_Ufficiale = 'procedimento'
        Where Elaboration_Id = $elab_id and PrintTypeId = 4";
        $cls_db->ExecuteQuery($query);
    }
}

?>