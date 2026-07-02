<?php
include_once CLS ."/traits.php";

class GestionePivot
{
    protected $cls_db;
    use tSelectSQL;

    function  __construct($cls_db){
            $this->cls_db = $cls_db;
    }

    public function Insert($partita_id,$procedure_id,$position_status_id,$flag_elaboration)
    {
        $a_dbParams_procedure = array(
            'table' => 'partita_procedure_pvt',
            'fields'=> array(
                array(  'name' => 'Partita_Id',      'type' => 'int', 'value' => $partita_id),
                array(  'name' => 'Procedure_Id',      'type' => 'int', 'value' => $procedure_id),
                array(  'name' => 'Position_Status_Id',      'type' => 'int', 'value' => $position_status_id),
                array(  'name' => 'Flag_Elaboration',      'type' => 'int', 'value' => $flag_elaboration)
                
            )
        );
        
        $id=$this->cls_db->DbInsert($a_dbParams_procedure);
        return  $id;
    }

    public function UpdateStatus($status_id,$id)
    {
        $q = "update partita_procedure_pvt set Position_Status_Id=$status_id where Id=$id";
        $this->UpdateSQL($q);
    }

}

?>