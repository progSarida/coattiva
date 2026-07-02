<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_paramUtils.php";
include_once CLS . "/cls_storico.php";													// inclusione classe


$storico = new storico('storicoParametri','8');	
$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$error = 0;
$msg = "";
$action = "";
$storico_msg = "";
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$tipo_riscossione = "*****";
$anno_par = $cls_help->getVar('anno_par');

$par_id = $cls_help->getVar('par_id');

$invia = $cls_help->getVar('invia_submit');

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$data_spese_ing = new cls_DateTime($cls_help->getVar('data_spese_ing'),"IT");
$data_spese_pigno = new cls_DateTime($cls_help->getVar('data_spese_pigno'),"IT");//to_mysql_date(get_var('data_spese_pigno'));
$data_spese_cautelari = new cls_DateTime($cls_help->getVar('data_spese_cautelari'),"IT");//to_mysql_date(get_var('data_spese_pigno'));
$data_ric_spese = new cls_DateTime($cls_help->getVar('data_ric_spese'),"IT");//to_mysql_date(get_var('data_ric_spese'));
$data_spese_post = new cls_DateTime($cls_help->getVar('data_spese_post'),"IT");//to_mysql_date(get_var('data_spese_post'));
$data_spese_racc = new cls_DateTime($cls_help->getVar('data_spese_racc'),"IT");//to_mysql_date(get_var('data_spese_racc'));
$data_spese_post_ag = new cls_DateTime($cls_help->getVar('data_spese_post_ag'),"IT");//to_mysql_date(get_var('data_spese_post_ag'));
$data_can = new cls_DateTime($cls_help->getVar('data_can'),"IT");//to_mysql_date(get_var('data_can'));
$data_cad = new cls_DateTime($cls_help->getVar('data_cad'),"IT");//to_mysql_date(get_var('data_cad'));
$data_a_mani = new cls_DateTime($cls_help->getVar('data_a_mani'),"IT");//to_mysql_date(get_var('data_a_mani'));
$data_a_mani_pigno = new cls_DateTime($cls_help->getVar('data_a_mani_pigno'),"IT");//to_mysql_date(get_var('data_a_mani_pigno'));
$data_a_mani_cautelari = new cls_DateTime($cls_help->getVar('data_a_mani_cautelari'),"IT");//to_mysql_date(get_var('data_a_mani_pigno'));
$data_iva = new cls_DateTime($cls_help->getVar('data_iva'),"IT");//to_mysql_date(get_var('data_iva'));

$magg_preavv = $cls_help->getVar('magg_preavv');
if($magg_preavv!="no")	$magg_preavv = "si";

$magg_ing = $cls_help->getVar('magg_ing');
if($magg_ing!="no")	$magg_ing = "si";

$flag_sgravi = $cls_help->getVar('flag_sgravi');
if($flag_sgravi!=1)	$flag_sgravi = 0;


if($invia == "Salva")
{
	$a_paramsAnno = array(
	    'table' => 'parametri_annuali',
	    'fields'=> array(
	        array(  'name' => 'CC',            							  			'type' => 'string', 'value' => $cls_help->getVar('c')),
	        array(  'name' => 'Anno',         						    			'type' => 'int',    'value' => $cls_help->getVar('anno_par')),
	        array(  'name' => 'Tipo_Riscossione',      			  			'type' => 'string', 'value' => '*****'),//
	        array(  'name' => 'Spese_Notifica',               			'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('spese_ing'))),
	        array(  'name' => 'Spese_Notifica_Data',            		'type' => 'date',   'value' => $data_spese_ing->GetDateDB()),
	        array(  'name' => 'Spese_Notifica_New',             		'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_spese_ing'))),
			  array(  'name' => 'Spese_Notifica_Pignoramento',    		'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('spese_pigno'))),
		      array(  'name' => 'Spese_Notifica_Pignoramento_Data',   'type' => 'date',   'value' => $data_spese_pigno->GetDateDB()),
		      array(  'name' => 'Spese_Notifica_Pignoramento_New',    'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_spese_pigno'))),
			  array(  'name' => 'Spese_Notifica_Cautelari',    		'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('spese_cautelari'))),
		      array(  'name' => 'Spese_Notifica_Cautelari_Data',   'type' => 'date',   'value' => $data_spese_cautelari->GetDateDB()),
		      array(  'name' => 'Spese_Notifica_Cautelari_New',    'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_spese_cautelari'))),
					array(  'name' => 'Spese_Ricerca',    									'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('ric_spese'))),
		      array(  'name' => 'Spese_Ricerca_Data',   							'type' => 'date',   'value' => $data_ric_spese->GetDateDB()),
		      array(  'name' => 'Spese_Ricerca_New',   								'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_ric_spese'))),
					array(  'name' => 'Spese_Postali',    									'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('spese_post'))),
		      array(  'name' => 'Spese_Postali_Data',   							'type' => 'date',   'value' => $data_spese_post->GetDateDB()),
		      array(  'name' => 'Spese_Postali_New',   								'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_spese_post'))),
					array(  'name' => 'Spese_Raccomandata',    							'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('spese_racc'))),
		      array(  'name' => 'Spese_Raccomandata_Data',   					'type' => 'date',   'value' => $data_spese_racc->GetDateDB()),
		      array(  'name' => 'Spese_Raccomandata_New',   					'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_spese_racc'))),
					array(  'name' => 'Spese_Postali_AG',    								'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('spese_post_ag'))),
		      array(  'name' => 'Spese_Postali_AG_Data',   						'type' => 'date',   'value' => $data_spese_post_ag->GetDateDB()),
		      array(  'name' => 'Spese_Postali_AG_New',   						'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_spese_post_ag'))),
					array(  'name' => 'CAN',    														'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('can'))),
		      array(  'name' => 'CAN_Data',   												'type' => 'date',   'value' => $data_can->GetDateDB()),
		      array(  'name' => 'CAN_New',   													'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_can'))),
					array(  'name' => 'CAD',    														'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('cad'))),
		      array(  'name' => 'CAD_Data',   												'type' => 'date',   'value' => $data_cad->GetDateDB()),
		      array(  'name' => 'CAD_New',   													'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_cad'))),
					array(  'name' => 'A_Mani',    													'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('a_mani'))),
		      array(  'name' => 'A_Mani_Data',   											'type' => 'date',   'value' => $data_a_mani->GetDateDB()),
		      array(  'name' => 'A_Mani_New',   											'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_a_mani'))),
			  array(  'name' => 'A_Mani_Pignoramento',    						'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('a_mani_pigno'))),
		      array(  'name' => 'A_Mani_Pignoramento_Data',   				'type' => 'date',   'value' => $data_a_mani_pigno->GetDateDB()),
		      array(  'name' => 'A_Mani_Pignoramento_New',   					'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_a_mani_pigno'))),
			  array(  'name' => 'A_Mani_Cautelari',    						'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('a_mani_cautelari'))),
		      array(  'name' => 'A_Mani_Cautelari_Data',   				'type' => 'date',   'value' => $data_a_mani_cautelari->GetDateDB()),
		      array(  'name' => 'A_Mani_Cautelari_New',   					'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('nuovo_a_mani_cautelari'))),
					array(  'name' => 'IVA',    														'type' => 'int',    'value' => $cls_help->getVar('iva')),
		      array(  'name' => 'IVA_Data',   												'type' => 'date',   'value' => $data_iva->GetDateDB()),
		      array(  'name' => 'IVA_New',   													'type' => 'int',    'value' => $cls_help->getVar('nuovo_iva')),
		      array(  'name' => 'Maggiorazione_Preavviso',   					'type' => 'string', 'value' => $magg_preavv),
		      array(  'name' => 'Maggiorazione_Ingiunzione',   				'type' => 'string', 'value' => $magg_ing),
			  array(  'name' => 'Flag_Sgravi_Elenco_Pagamenti',   				'type' => 'int', 'value' => $flag_sgravi),
			  array(  'name' => 'Spese_Pec',   							'type' => 'float', 'value' => $cls_param->conv_num($cls_help->getVar('spese_pec'))),
			  array(  'name' => 'Spese_Pec_Banca',   							'type' => 'float', 'value' => $cls_param->conv_num($cls_help->getVar('spese_pec_banca'))),
		      array(  'name' => 'Giorni_Diritto',   									'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('giorni_diritto'))),
		      array(  'name' => 'Diritto_Riscossione_Minimo',   			'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('diritto_minimo'))),
		      array(  'name' => 'Diritto_Riscossione_Massimo',   			'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('diritto_massimo'))),
		      array(  'name' => 'Importo_Minimo',   									'type' => 'int',    'value' => $cls_param->conv_num($cls_help->getVar('importo_min')))
	    )
	);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if($par_id == 0)
	{
		if(!$cls_db->DbSave($a_paramsAnno))
		{
			$cls_db->Rollback();
			$msg = "Errore, inserimento fallito";
			$error = 1;
		}else{
			$msg = "Inserimento riuscito con successo";
			$action = "I";
			$storico_msg = "Inseriti ";
		}
	}
	else
	{
		$a_paramsAnno['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=>$par_id);
		if(!$cls_db->DbSave($a_paramsAnno))
		{
			$cls_db->Rollback();
			$msg = "Errore, aggiornamento fallito";
			$error = 1;
		}else{
			$msg = "Aggiornamento riuscito con successo";
			$action = "U";
			$storico_msg = "Modificati ";
		}
	}

	$cls_db->End_Transaction();
}

if($error == 0)
	$storico->insRow($action, $storico_msg."parametri annuali ente ".$nome_ente." [".$c ."] per l'anno ".$cls_help->getVar('anno_par'));

header("Location: par_annuali.php?anno_scelta={$anno_par}&c={$c}&a={$a}&tipo_riscossione={$tipo_riscossione}&error={$error}&msg={$msg}");
?>
