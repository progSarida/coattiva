<?php
if(empty($_GET['c']))
    $_GET['c']="D925";
if(empty($_GET['a']))  
    $_GET['a']= 2018;
if(empty($_GET['Partita_ID']))  
    $_GET['Partita_ID']= null;
if(empty($_GET['Comune_ID']))  
    $_GET['Comune_ID']= 1;
if(empty($_GET['DocumentTypeId']))  
    $_GET['DocumentTypeId']= 2;

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/headerAjax.php");
include_once CLS . "/cls_db.php";
include_once "cls_elaborationTest.php";

$cls_db = new cls_db();

echo "<pre>TEST CONTROLLI ATTI<hr></pre>";

$query_partita = " SELECT * FROM v_check_partite WHERE ";
if(!empty($_GET['Partita_ID']))
    $query_partita.="Partita_ID=".$_GET['Partita_ID'];
else
    $query_partita.="CC= '".$_GET['c']."' AND Comune_ID=".$_GET['Comune_ID'];

$results = $cls_db->ExecuteQuery($query_partita);
$partita = $cls_db->getArrayLine($results);

print_r($partita);

$query_loc_per = "SELECT * FROM lockup_periods";
$a_lockupPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query_loc_per));

$queryDocTypes = "SELECT Id, ExpireDays FROM document_type WHERE ExpireDays is not null";
$a_docExpireDays = $cls_db->getResults($cls_db->ExecuteQuery($queryDocTypes),"array","Id");

$query_par_an = "SELECT * FROM parametri_annuali WHERE CC = '" . $_GET['c'] . "' AND Anno = " . (int)date('Y');
$a_parAnnuali = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par_an));

$a_params = array(
    'Parametri_Annuali' => $a_parAnnuali,
    'Lockup_Periods' => $a_lockupPeriods,
    'Doc_Expire_Days' => $a_docExpireDays,
    'Elaboration_DocumentTypeId' => $_GET['DocumentTypeId'],
    'Data_Elaborazione' => date('Y-m-d'),            
    'Data_Calcolo_Interessi' => date('Y-m-d') 
);

$cls_elaboration = new cls_elaborationTest($a_params,true);

$a_params = array(
    "Tipo_Riscossione" => $partita['Tipo_Riscossione']
);
$cls_elaboration->setParams($a_params);

$a_getPositionStatus = $cls_elaboration->getPositionStatus($partita);




include(INC . "/footer.php");
