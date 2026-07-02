<?php
	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
		
	include CLASSI . "/comuni.php";
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/290.php";
	include CLASSI . "/ruolo.php";*/

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";
include_once CLS."/cls_Utils.php";

$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_help = new cls_help();

$c = $cls_help->getVar('c');

$ajax = $cls_help->getVar('ajax');

switch($ajax)
{
	case "elimina_file":
		
	$atto_id = $cls_help->getVar('atto_id');
	$tipo_stampa = $cls_help->getVar('tipo_stampa');
	
	$file = $cls_help->getVar('file');
	$file_rar = $cls_help->getVar('file_rar');
	
	if($tipo_stampa=="DEFINITIVA")
	{
		$explodeCartelle = explode("/",$file);
		$nome_file = $explodeCartelle[count($explodeCartelle)-1];
		
		$cartella = "";
		for($i=0;$i<count($explodeCartelle)-1;$i++)
		{
		$cartella .= $explodeCartelle[$i]."/";
		}

        $query = "SELECT * FROM atto WHERE ID = ".$atto_id." AND CC = '".$c."'";
		$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");
		//$atto = new atto($atto_id, $c);
		
		if($atto->Numero_Flusso!=null && $atto->Numero_Flusso!="" && $atto->Numero_Flusso!=0)
		{
			echo "FLUSSO ".$atto->Numero_Flusso."/".$atto->Anno_Flusso;
			die;
		}
		
		$atto->Data_Stampa = null;
		$atto->Stato_Stampa = "Da stampare";

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

		if($cls_db->DbSave($cls_utils->GetObjectQuery($atto,"atto", array("ID"=>$atto_id)))){
		    $cls_db->End_Transaction();

            $dir = crea_dir($cartella."/ELIMINATI");
            copy($file,$dir."/Del_".$nome_file);
            unlink($file);

            echo "OK";
            die;
        }
		else{
		    $cls_db->Rollback();
            $cls_db->End_Transaction();

            echo "ERROR";
            die;
        }
		
		/*$control_update = $atto->Update($atto_id);
		
		if($control_update)
		{
			mysql_query('COMMIT');
			$dir = crea_dir($cartella."/ELIMINATI");
			copy($file,$dir."/Del_".$nome_file);
			unlink($file);
			
			echo "OK";
		}
		else 
		{
			echo "ERROR";
		}*/
	}
	else if($tipo_stampa=="FLUSSO")
	{
		$expRar = explode("/",$file_rar);
		$nome_rar = $expRar[count($expRar)-1];
		
		$explodeCartelle = explode("/",$file);
		$nome_file = $explodeCartelle[count($explodeCartelle)-1];
		
		$cartella = "";
		for($i=0;$i<count($explodeCartelle)-1;$i++)
		{
			$cartella .= $explodeCartelle[$i]."/";
		}
		
		
		$explodePunto = explode (".", $nome_file);
		$estensione = $explodePunto[1];
			
		$explode = explode ("_", $explodePunto[0]);
		$control_comune = $explode[2];
		$control_anno = $explode[3];
		$control_numero = $explode[4];
		$control_data = $explode[5];
		
		$query = "UPDATE atto SET Numero_Flusso = '' , Anno_Flusso = '' , Data_Flusso = NULL WHERE CC = '".$control_comune."' AND Numero_Flusso = '".$control_numero."' AND Anno_Flusso = '".$control_anno."' AND Data_Flusso = '".$control_data."'";

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        if($cls_db->ExecuteQuery($query)){
            $cls_db->End_Transaction();

            $dir = crea_dir($cartella."/ELIMINATI");
            copy($file,$dir."/Del_".$nome_file);
            unlink($file);
            unlink($file_rar);

            echo "OK";
            die;
        }
        else{
            $cls_db->Rollback();
            $cls_db->End_Transaction();

            echo "ERROR";
            die;
        }
		
	}

	
		break;
		
		case "nome":

		
		$ID = $cls_help->getVar('ID');

        $query = "SELECT * FROM utente WHERE ID = '".$ID."' AND CC_Comune = '".$c."'";
		$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($ID, $c);

        $query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente->Forma_Giuridica."' AND CC = '*****'";
        $utente->Forma_Giuridica_Oggetto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");
	
		if($utente->Genere!="D")
			$ritorno = $utente->Cognome."*".$utente->Nome;
		else
			$ritorno = $utente->Ditta." ".$utente->Forma_Giuridica_Oggetto->Sigla;
			
		echo $ritorno;
		die;
		break;
		
}


?>