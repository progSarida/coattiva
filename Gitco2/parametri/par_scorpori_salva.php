<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

//require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_split_payment.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoParametri','8');
$cls_help = new cls_help();
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$cls_db = new cls_db();
$cls_split = new cls_split_payment();

$error = 0;
$msg = "";
$action = "";
$storico_msg = "";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$split = $cls_help->getVar('split');
$split_type = $cls_help->getVar('split_type');
$split_category1 = $cls_help->getVar('split_category1');
$split_category2 = $cls_help->getVar('split_category2');
$split_category3 = $cls_help->getVar('split_category3');
$split_category4 = $cls_help->getVar('split_category4');
$split_category5 = $cls_help->getVar('split_category5');
$split_category6 = $cls_help->getVar('split_category6');
$a_parameters['cc'] = $c;

for($i=1;$i<=count($split);$i++) {
    $a_categories = array(1=>$split_category1[$i],2=>$split_category2[$i],3=>$split_category3[$i],4=>$split_category4[$i],5=>$split_category5[$i],6=>$split_category6[$i]);
    $a_parameters['split'.$i] = $split[$i];
    $a_parameters['split'.$i.'_type'] = $split_type[$i];
    $a_parameters['split'.$i.'_categories'] = serialize($a_categories);
}

$a_paramsId = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersIdQuery($c) ) );

if($a_paramsId['id']>0){
    $a_return['message'] = "Parametri aggiornati correttamente";
    if(!$cls_db->UpdateFromKey("split_payment_parameters",$a_parameters,"id=".$a_paramsId['id']))
    {
      $error = 1;
      $msg = "Errore, aggiornamento non riuscito";
    }else{
      $msg = "Aggiornamento dati riuscito correttamente";
      $action = "U";
      $storico_msg = "Modificati";
    }
}
else{
    $a_return['message'] = "Nuovi parametri aggiunti correttamente";
    $returnInsert = $cls_db->InsertFromKey("split_payment_parameters",$a_parameters);

    if(!$returnInsert["result"]){
      $error = 1;
      $msg = "Errore, inserimento non riuscito";
    }else{
      $msg = "Inserimento dati riuscito correttamente";
      $action = "I";
      $storico_msg = "Inseriti";
    }
}

$a_return['response'] = true;

if($error == 0)
$storico->insRow($action, $storico_msg." parametri di scorporo ente ".$nome_ente."[".$c."]");

echo json_encode($a_return);

header("Location: par_scorpori.php?c={$c}&a={$a}&msg={$msg}&error={$error}");
?>
