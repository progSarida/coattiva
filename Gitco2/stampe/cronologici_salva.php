<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$tipo_atto = $cls_help->getVar('tipo_atto');

$dataProtocollo = $cls_help->getVar('dataProto');
$protocollo = $cls_help->getVar('proto');
$cronologico = $cls_help->getVar('crono');
$anno = $cls_help->getVar('anno');
$id = $cls_help->getVar('id');

$Tipo = $cls_help->getVar('type');

$table = "atto";
if($Tipo == "pigno")
    $table = "pignoramento_generale";

$error = 0;
$msg = "";

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

$cont = 0;
//$cls_help->alert($anno[0]."/".$cronologico[0]." ----- ".$id[0]);
for($i=0;$i<count($id);$i++)
{
	
	if($cronologico[$i]!="")
	{
        $query = "SELECT * FROM ".$table." WHERE ID = ".$id[$i]." AND CC = '".$c."'";
		$result = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),$table);// new atto($id[$i], $c);

        $result->Anno_Cronologico = $anno[$i];
        $result->ID_Cronologico = $cronologico[$i];
        $result->Data_Protocollo = $cls_date->GetDateDB($dataProtocollo[$i],"IT");
        $result->Protocollo = $protocollo[$i];
				
		$control = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $result,$table, array("ID"=>$id[$i])));// $atto->Update($id[$i]);
		
		if($control === false)
		{
		    $cls_db->Rollback();
		    $error = 1;
		    $msg = "Errore, aggiornamento fallito";
		    header("Location: gestione_stampe.php?printType=html&docType={$tipo_atto}&c={$c}&a={$a}&error={$error}&msg={$msg}");
			//echo "ERROR ".mysql_error();
			//mysql_query('ROLLBACK');
			die;
		}
		
		$cont++;
	}
}

$cls_db->End_Transaction();

//echo $table;
//die;
if($cont>0)
{
    $msg = "Dati aggiornati correttamente";
}
else
{
    $error = 2;
    $msg = "Nessun cronologico assegnato!";
}

header("Location: gestione_stampe.php?printType=html&docType={$tipo_atto}&c={$c}&a={$a}&error={$error}&msg={$msg}");
die;

?>