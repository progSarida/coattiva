<?php

class cls_notParameters{
    public function getParametersQuery($notificationType=null, $cc="*****"){
        $query = "SELECT * FROM parametri_notifica WHERE CC='".$cc."' ";
        if($notificationType!=null)
            $query.= "AND Tipo_Dato='".$notificationType."' ";
        $query.= "ORDER BY ID";

        return $query;
    }

    public function setNotParametersArray(array $a_notMode, array $a_notStock, array $a_notAnomaly){
        $a_return = null;
        $a_return['mode'] = $a_notMode;
        $a_return['stock'] = $a_notStock;
        $a_return['anomaly'] = $a_notAnomaly;
        return $a_return;
    }
}

?>