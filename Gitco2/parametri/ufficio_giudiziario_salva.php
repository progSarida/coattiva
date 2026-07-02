<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_storico.php";


$storico = new storico('storicoParametri','8');
$cls_db = new cls_db();
$cls_help = new cls_help();


if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$ID_uff = $cls_help->getVar('ID_uff');
$invia = $cls_help->getVar('invia_submit');
$tipo = $cls_help->getVar('tipo_uff');

$error = 0;
$msg = "";
$action = "";
$msg_storico = "";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

switch($tipo)
{
	case "tribunale":
			$ufficio_giud = "'Tribunale'";
		break;
	case "giudice":
			$ufficio_giud = "'Giudice di pace'";
		break;
	case "appello":
			$ufficio_giud = "'Corte d'appello'";
		break;
	case "cort_giust_trib":
			$ufficio_giud = "'Corte di giustizia tributaria di I grado'";
		break;
	case "comm_trib_reg":
			$ufficio_giud = "'Commissione tributaria regionale'";
			break;
	case "cassazione":
			$ufficio_giud = "'Corte di cassazione'";
		break;
	default:
			$ufficio_giud = "'Tribunale'";
		break;
}

if( $invia == "Delete" )
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$table = "`ufficio_giudiziario`";
	$where = "`CC` = '" . $c . "' AND `Tipo` = '".$tipo."'";

	if(!$cls_db->Delete($table,$where)){
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, Impossibile cancellare i dati.";
	}
	else{
		$msg = "Dati eliminati correttamente";
		$action = 'D';
		$msg_storico = "Eliminati dati ";
	}

	$cls_db->End_Transaction();

}
else if($invia == "Insert" || $invia == "Update")
{

	$a_paramsUfficioGiudiz = array(
							'table'=>'ufficio_giudiziario',
							'fields'=> array (
															array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('c')),//
															array(  'name' => 'CC_Ufficio',     'type' => 'string', 'value' => $cls_help->getVar('CC_uff')),//
															array(  'name' => 'Tipo',           'type' => 'string', 'value' => $cls_help->getVar('tipo_uff')),//
															array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune')),//
															array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('prov')),//
															array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),//
															array(  'name' => 'Sezione',        'type' => 'string', 'value' => $cls_help->getVar('sezione')),//
															array(  'name' => 'Toponimo',       'type' => 'string', 'value' => $cls_help->getVar('via')),//
															array(  'name' => 'Civico',         'type' => 'int',    'value' => $cls_help->getVar('civico')),//
															array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente')),//
															array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),//
															array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),//
															array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),//
															array(  'name' => 'Fax',            'type' => 'string', 'value' => $cls_help->getVar('fax')),
															array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),//
															array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),//
															array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),
															array(  'name' => 'Denominazione',  'type' => 'string', 'value' => 'Comune di '.$cls_help->getVar('comune')),
															array(  'name' => 'Responsabile_1',           'type' => 'string', 'value' => $cls_help->getVar('desc_resp_1')),
															array(  'name' => 'Nome_Responsabile_1',      'type' => 'string', 'value' => $cls_help->getVar('resp_1')),
															array(  'name' => 'Telefono_Responsabile_1',  'type' => 'string', 'value' => $cls_help->getVar('tel_resp_1')),
															array(  'name' => 'Fax_Responsabile_1',       'type' => 'string', 'value' => $cls_help->getVar('fax_resp_1')),
															array(  'name' => 'Mail_Responsabile_1',      'type' => 'string', 'value' => $cls_help->getVar('mail_resp_1')),
															array(  'name' => 'Responsabile_2',           'type' => 'string', 'value' => $cls_help->getVar('desc_resp_2')),
															array(  'name' => 'Nome_Responsabile_2',      'type' => 'string', 'value' => $cls_help->getVar('resp_2')),
															array(  'name' => 'Telefono_Responsabile_2',  'type' => 'string', 'value' => $cls_help->getVar('tel_resp_2')),
															array(  'name' => 'Fax_Responsabile_2',       'type' => 'string', 'value' => $cls_help->getVar('fax_resp_2')),
															array(  'name' => 'Mail_Responsabile_2',      'type' => 'string', 'value' => $cls_help->getVar('mail_resp_2')),
															array(  'name' => 'Responsabile_3',           'type' => 'string', 'value' => $cls_help->getVar('desc_resp_3')),
															array(  'name' => 'Nome_Responsabile_3',      'type' => 'string', 'value' => $cls_help->getVar('resp_3')),
															array(  'name' => 'Telefono_Responsabile_3',  'type' => 'string', 'value' => $cls_help->getVar('tel_resp_3')),
															array(  'name' => 'Fax_Responsabile_3',       'type' => 'string', 'value' => $cls_help->getVar('fax_resp_3')),
															array(  'name' => 'Mail_Responsabile_3',      'type' => 'string', 'value' => $cls_help->getVar('mail_resp_3'))
														)
					);

	if($invia == "Insert")
	{
		$cls_db->Start_Transaction();
		$cls_db->Begin_Transaction();
		$control_salva = $cls_db->DbSave($a_paramsUfficioGiudiz);

		if(!$control_salva){
			$cls_db->Rollback();
			$error = 1;
			$msg = "Inserimento fallito, Errore";
		}
		else{
			$msg= "Inserimento avvenuto con successo";
			$action = 'I';
			$msg_storico = "Inseriti dati ";
		}
	}
	else
	{
			$a_paramsUfficioGiudiz['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $ID_uff);

			$cls_db->Start_Transaction();
			$cls_db->Begin_Transaction();
			$control_salva = $cls_db->DbSave($a_paramsUfficioGiudiz);

			if(!$control_salva){
				$cls_db->Rollback();
				$error = 1;
				$msg = "Aggiornamento fallito, Errore";
			}
			else {
				$msg = "Aggiornamento riuscito";
				$action = 'U';
				$msg_storico = "Modificati dati ";
			}
		}

		$cls_db->End_Transaction();

}

if($error == 0)
	$storico->insRow($action, $msg_storico." ufficio giudiziario per ente ".$nome_ente."[".$c."]");

header("Location: ufficio_giudiziario.php?tipo_ufficio={$tipo}&a={$a}&c={$c}&error={$error}&msg={$msg}");

?>
