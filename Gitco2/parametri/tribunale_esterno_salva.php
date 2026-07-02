<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_paramUtils.php";
include_once CLS . "/cls_storico.php";


$storico = new storico('storicoParametri','8');
$cls_help = new cls_help();
$cls_db = new cls_db();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');

$CC_uff = $cls_help->getVar('CC_uff');
$ID_uff = $cls_help->getVar('ID_uff');
$tipo = $cls_help->getVar('tipo_uff');

$CC_uff2 = $cls_help->getVar('CC_uff_collegato');
$ID_uff2 = $cls_help->getVar('ID_uff_collegato');
$tipo2 = $cls_help->getVar('tipo_uff_collegato');

$comune = $cls_help->getVar('comune');
$CC_ID = $cls_help->getVar('CC');

$error = 0;
$msg = "";
$action = "";
$storico_msg = "";

$sezione = $cls_help->getVar('sezione');
if($sezione == null) $sezione = "";
$sezione2 = $cls_help->getVar('sezione2');
if($sezione2 == null) $sezione2 = "";



if( $invia == "Delete" )
{
	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->Delete("ufficio_giudiziario","ID = {$ID_uff}"))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile eliminare i dati.";
	}

	if(!$cls_db->Delete("ufficio_giudiziario","ID = {$ID_uff2}"))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore impossibile eliminare i dati.";
	}

	if($error == 0){
		$msg = "Dati eliminati.";
		$action = "D";
		$storico_msg = "Eliminati ";
	}

	$cls_db->End_Transaction();

}

if($invia == "Insert" || $invia == "Update")
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	$a_paramsTribE = array(
			'table' => 'ufficio_giudiziario',
			'fields'=> array(
					array(  'name' => 'CC',           'type' => 'string', 'value' => $cls_help->getVar('CC')),
					array(  'name' => 'CC_Ufficio',   'type' => 'string', 'value' => $CC_uff),
					array(  'name' => 'Tipo',         'type' => 'string', 'value' => $tipo),
					array(  'name' => 'Comune',     	'type' => 'string', 'value' => $cls_help->getVar('ufficio_sede')),
					array(  'name' => 'Provincia',    'type' => 'string', 'value' => $cls_help->getVar('prov')),
					array(  'name' => 'Cap',    		  'type' => 'string', 'value' => $cls_help->getVar('cap')),
					array(  'name' => 'Sezione',      'type' => 'string', 'value' => $sezione),
					array(  'name' => 'Toponimo',     'type' => 'string', 'value' => $cls_help->getVar('via')),
					array(  'name' => 'Civico',       'type' => 'int',    'value' => $cls_help->getVar('civico')),
					array(  'name' => 'Esponente',    'type' => 'string', 'value' =>  $cls_help->getVar('esponente')),
					array(  'name' => 'Interno',      'type' => 'int',    'value' => $cls_help->getVar('interno')),
					array(  'name' => 'Dettagli',     'type' => 'string', 'value' => $cls_help->getVar('dettagli')),
					array(  'name' => 'Telefono',     'type' => 'string', 'value' => $cls_help->getVar('tel')),
					array(  'name' => 'Fax',       		'type' => 'string', 'value' => $cls_help->getVar('fax')),
					array(  'name' => 'Mail',         'type' => 'string', 'value' => $cls_help->getVar('email')),
					array(  'name' => 'Sito',         'type' => 'string', 'value' => $cls_help->getVar('sito')),
					array(  'name' => 'PEC',          'type' => 'string', 'value' => $cls_help->getVar('PEC')),
					array(  'name' => 'Denominazione',   'type' => 'string', 'value' => ""),
					array(  'name' => 'Forma_Giuridica', 'type' => 'int',    'value' => null)
			)
	);

	if($ID_uff == "")
	{

		if(!$cls_db->DbSave($a_paramsTribE))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile inserire i dati";
		}
	}
	else
	{

		$a_paramsTribE['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $ID_uff);

		if(!$cls_db->DbSave($a_paramsTribE))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile aggiornare i dati";
		}
	}

	if ($error === 0)

	{
		echo "<h1>Seconda query</h1>";

			$a_paramsTribE = array(
					'table' => 'ufficio_giudiziario',
					'fields'=> array(
							array(  'name' => 'CC',           	 'type' => 'string', 'value' => $CC_uff),
							array(  'name' => 'CC_Ufficio',   	 'type' => 'string', 'value' => $CC_uff2),
							array(  'name' => 'Tipo',         	 'type' => 'string', 'value' => $tipo2),
							array(  'name' => 'Comune',     		 'type' => 'string', 'value' => $cls_help->getVar('ufficio_sede2')),
							array(  'name' => 'Provincia',    	 'type' => 'string', 'value' => $cls_help->getVar('prov2')),
							array(  'name' => 'Cap',    		     'type' => 'string', 'value' => $cls_help->getVar('cap2')),
							array(  'name' => 'Sezione',    		 'type' => 'string', 'value' => $sezione2),
							array(  'name' => 'Toponimo',     	 'type' => 'string', 'value' => $cls_help->getVar('via2')),
							array(  'name' => 'Civico',      		 'type' => 'int',    'value' => $cls_help->getVar('civico2')),
							array(  'name' => 'Esponente',   		 'type' => 'string', 'value' =>  $cls_help->getVar('esponente2')),
							array(  'name' => 'Interno',     		 'type' => 'int',    'value' => $cls_help->getVar('interno2')),
							array(  'name' => 'Dettagli',    		 'type' => 'string', 'value' => $cls_help->getVar('dettagli2')),
							array(  'name' => 'Telefono',    		 'type' => 'string', 'value' => $cls_help->getVar('tel2')),
							array(  'name' => 'Fax',       			 'type' => 'string', 'value' => $cls_help->getVar('fax2')),
							array(  'name' => 'Mail',        		 'type' => 'string', 'value' => $cls_help->getVar('email2')),
							array(  'name' => 'Sito',        		 'type' => 'string', 'value' => $cls_help->getVar('sito2')),
							array(  'name' => 'PEC',          	 'type' => 'string', 'value' => $cls_help->getVar('PEC2')),
							array(  'name' => 'Denominazione',   'type' => 'string', 'value' => $cls_help->getVar('denominazione2')),
							array(  'name' => 'Forma_Giuridica', 'type' => 'int',    'value' => $cls_help->getVar('forma_giuridica2'))
					)
			);

			if($ID_uff2 == "")
			{
				if(!$cls_db->DbSave($a_paramsTribE))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore impossibile inserire i dati";
				}
			}
			else
			{
				$a_paramsTribE['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $ID_uff2);

				if(!$cls_db->DbSave($a_paramsTribE))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore impossibile aggiornare i dati";
				}

			}

	}


	if($error == 0){
		$msg = "Dati aggiornati correttamente";
		if($invia == "Insert"){
			$action = "I";
			$storico_msg = "Inseriti ";
		}
		else if($invia == "Update"){
			$action = "U";
			$storico_msg = "Modificati ";
		}
	}

	$cls_db->End_Transaction();

}

$storico->insRow('', $storico_msg."dati Tribunale/Ist. vendite giudiziarie per ente ".$comune);

header("Location: tribunale_esterno.php?&c={$c}&a={$a}&error={$error}&msg={$msg}&ComuneID={$comune}&CC={$CC_ID}");

?>
