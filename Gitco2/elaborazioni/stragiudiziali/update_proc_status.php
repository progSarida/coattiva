<?php
 try{
    
    $a_dbParams = array(
        'table' => 'procedures',
        'updateField' => array(
            array('name' => 'Id',  'type' => 'int', 'value' => $proc_id),
        ),
        'fields'=> array(
            array(  'name' => 'Procedure_Status_Id',  'type' => 'int', 'value' => $status),
            array(  'name' => 'User_Id',        'type' => 'string', 'value' => $_SESSION['aut_progr']),
            array(  'name' => 'Procedure_Date',            'type' => 'date', 'value' => date('Y-m-d')),
        
        )
    );

    $cls_db->DbSave( $a_dbParams);
    
}
catch (mysqli_sql_exception $e) {
    $cls_db->Rollback();
    //$log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
    $cls_help->alert("ERRORE!!!!!!!!");
    die;
    return;
}
?>