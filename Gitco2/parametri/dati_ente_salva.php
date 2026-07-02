<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_storico.php";

$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_utils = new cls_Utils();

if(!isset($_SESSION['username']))
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$Select_Tax = $cls_help->getVar('Select_Tax');
$info_id = $cls_help->getVar('info_id');

$a_paramsGestore = array(
    'table' => 'gestore',
    'fields'=> array(
        array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('CC')),
        array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune')),
        array(  'name' => 'Codice_Fiscale', 'type' => 'string', 'value' => $cls_help->getVar('CF')),
        array(  'name' => 'Partita_Iva',    'type' => 'string', 'value' => $cls_help->getVar('PI')),
        array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),
        array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),
        array(  'name' => 'Fax',            'type' => 'string', 'value' => $cls_help->getVar('fax')),
        array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),
        array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),
        array(  'name' => 'Toponimo',       'type' => 'string', 'value' => $cls_help->getVar('via')),
        array(  'name' => 'Civico',         'type' => 'int',    'value' => $cls_help->getVar('civico')),
        array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente')),
        array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),
        array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),
        array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('Pro_Sigla')),
        array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),
        array(  'name' => 'Tipo',           'type' => 'string', 'value' => 'Comune'),
        array(  'name' => 'Denominazione',  'type' => 'string', 'value' => 'Comune di '.$cls_help->getVar('comune')),
        array(  'name' => 'Paese',          'type' => 'string', 'value' => 'Italia'),
        array(  'name' => 'Orario',         'type' => 'string', 'value' => '')
    )
);

/*$a_paramsGestoreObj["CC"] = $cls_help->getVar('CC');
$a_paramsGestoreObj["Comune"] = $cls_help->getVar('comune');
$a_paramsGestoreObj["Codice_Fiscale"] = $cls_help->getVar('CF');
$a_paramsGestoreObj["Partita_Iva"] = $cls_help->getVar('PI');
$a_paramsGestoreObj["Mail"] = $cls_help->getVar('email');
$a_paramsGestoreObj["Telefono"] = $cls_help->getVar('tel');
$a_paramsGestoreObj["Fax"] = $cls_help->getVar('fax');
$a_paramsGestoreObj["PEC"] = $cls_help->getVar('PEC');
$a_paramsGestoreObj["Sito"] = $cls_help->getVar('sito');
$a_paramsGestoreObj["Toponimo"] = $cls_help->getVar('via');
$a_paramsGestoreObj["Civico"] = $cls_help->getVar('civico');
$a_paramsGestoreObj["Esponente"] = $cls_help->getVar('esponente');
$a_paramsGestoreObj["Interno"] = $cls_help->getVar('interno');
$a_paramsGestoreObj["Dettagli"] = $cls_help->getVar('dettagli');
$a_paramsGestoreObj["Provincia"] = $cls_help->getVar('Pro_Sigla');
$a_paramsGestoreObj["Tipo"] = "Comune";
$a_paramsGestoreObj["Denominazione"] = 'Comune di '.$cls_help->getVar('comune');
$a_paramsGestoreObj["Paese"] = "Italia";
$a_paramsGestoreObj["Orario"] = "";

$a_paramsGestoreObjWhere["ID"] = (object) ["value" => $info_id, "operator" => "AND"];
$a_paramsGestoreObjWhere["CC"] = $cls_help->getVar('CC');

$test = $cls_utils->GetObjectQuery($a_paramsGestoreObj,"gestore",$a_paramsGestoreObjWhere);*/

$msg = "";
$err = 0;

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($info_id>0){
    $a_paramsGestore['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $info_id);
    if(!$cls_db->DbSave($a_paramsGestore)){
        $cls_db->Rollback();
        $msg = "Salvataggio fallito! Errore nell'aggiornamento del gestore";
        $err = 1;
    }
}
else{
    $insertId = $cls_db->DbSave($a_paramsGestore);
    if(!$insertId){
        $cls_db->Rollback();
        $msg = "Salvataggio fallito! Errore nell'inserimento del gestore";
        $err = 1;
    }
    else
        $info_id = $insertId;
}

if($msg==""){
    $a_paramsEnte = array(
        'table'=>'enti_gestiti',
        'fields'=> array (
            array(  'name'=>'Info_ID',         'type'=>'int',       'value'=>$info_id),
            array(  'name'=>'Codice_290',      'type'=>'int',       'value'=>$cls_help->getVar('codice_290')),
            array(  'name'=>'Select_Tax',              'type'=>'int', 'value'=>$cls_help->getVar('Select_Tax')),
            array(  'name'=>'Mesi_Inattivita_Sgravio', 'type'=>'int', 'value'=>max(1, (int)$cls_help->getVar('mesi_inattivita_sgravio')))
        ),
        'updateField'=>array(   'name'=>'CC',  'type'=>'string',    'value'=>$cls_help->getVar('CC'))
    );

    if(!$cls_db->DbSave($a_paramsEnte)){
        $cls_db->Rollback();
        $msg = "Salvataggio fallito! Errore nell'aggiornamento dell'ente";
        $err = 1;
    }
    else{
        $cls_db->End_Transaction();
        $msg = "Salvataggio avvenuto con successo";
    }
}

if($err == 0)
    $storico->insRow('U', "Modifica dati ente ".$cls_help->getVar('comune')."[".$c."]");

header("Location: dati_ente.php?c=".$c."&a=".$a."&msg=".$msg."&error=".$err);

?>
