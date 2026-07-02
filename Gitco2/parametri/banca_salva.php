<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
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

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$invia = $cls_help->getVar('invia_submit');
$tipo_banca = $cls_help->getVar('tipo_banca');
$id_sede = $cls_help->getVar('id_sede');
$id_filiale = $cls_help->getVar('id_filiale');
$password = $cls_help->getVar('pass');
$denom = $cls_help->getVar('denom');
$comune = $cls_help->getVar('comune');
$array_regioni = $cls_help->getVar("regioni");
$disabilita_banca = $cls_help->getVar("disabled");
$disabilita_banca = $disabilita_banca == null ? 0 : $disabilita_banca;
$id_collegamento="";
$error = 0;
$msg = "";
$action = "";
$tipo = "";
$storico_msg = "";

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

if($tipo_banca=="filiale")
{
	$id_banca = $id_filiale;
	$id_collegamento = $id_sede;
	$tipo = " filiale di ".$comune." banca ".$denom;
}
else if($tipo_banca=="sede")
{
	$id_banca = $id_sede;
	$id_collegamento = null;
	$tipo = " banca ".$denom;

}

if($password==null) $password = "";

if($invia == "Salva")
{


	$a_paramsBanca = array(
	    'table' => 'banca',
	    'fields'=> array(
	        array(  'name' => 'CC',                  'type' => 'string', 'value' => '*****'),
			array(  'name' => 'CC_Sede',             'type' => 'string', 'value' => $cls_help->getVar('CC')),
	        array(  'name' => 'Codice_Fiscale', 		 'type' => 'string', 'value' => $cls_help->getVar('CF')),
	        array(  'name' => 'Partita_Iva',    		 'type' => 'string', 'value' => $cls_help->getVar('PI')),
	        array(  'name' => 'Comune',          		 'type' => 'string', 'value' => $cls_help->getVar('comune')),
	        array(  'name' => 'Tipo_Banca',      		 'type' => 'string', 'value' => $tipo_banca),
	        array(  'name' => 'Denominazione',       'type' => 'string', 'value' => $cls_help->getVar('denom')),
	        array(  'name' => 'Paese',           		 'type' => 'string', 'value' => 'Italia'),
	        array(  'name' => 'Provincia',           'type' => 'string', 'value' => $cls_help->getVar('prov')),
	        array(  'name' => 'Mail',      					 'type' => 'string', 'value' => $cls_help->getVar('email')),
	        array(  'name' => 'PEC',        				 'type' => 'string', 'value' => $cls_help->getVar('PEC')),
	        array(  'name' => 'Sito',      					 'type' => 'string', 'value' => $cls_help->getVar('sito')),
	        array(  'name' => 'Telefono',       		 'type' => 'string', 'value' => $cls_help->getVar('tel')),
	        array(  'name' => 'Fax',      					 'type' => 'string', 'value' => $cls_help->getVar('fax')),
	        array(  'name' => 'Toponimo',      			 'type' => 'string', 'value' => $cls_help->getVar('via')),
	        array(  'name' => 'Civico',              'type' => 'int',    'value' => $cls_help->getVar('civico')),
	        array(  'name' => 'Esponente',           'type' => 'string', 'value' => $cls_help->getVar('esponente')),
	        array(  'name' => 'Interno',  					 'type' => 'int',    'value' => $cls_help->getVar('interno')),
	        array(  'name' => 'Dettagli',            'type' => 'string', 'value' => $cls_help->getVar('dettagli')),
	        array(  'name' => 'Cap',                 'type' => 'string', 'value' => $cls_help->getVar('cap')),
	        array(  'name' => 'Orario',              'type' => 'string', 'value' => $cls_help->getVar('orario')),
	        array(  'name' => 'ID_Collegamento',     'type' => 'int',    'value' => $id_collegamento),
	        array(  'name' => 'Password',            'type' => 'string', 'value' => $password),
	        array(  'name' => 'Forma_Giuridica',     'type' => 'int',    'value' => $cls_help->getVar('forma_giuridica')),
			array(  'name' => 'disabled',            'type' => 'int', 'value' => $disabilita_banca),
	    )
	);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	$control = true;

	if($id_banca > 0)
	{
		$a_paramsBanca['updateField'] = array(  'name'=>'ID', 'type' => 'int', 'value'=> $id_banca);

		$control = $cls_db->DbSave($a_paramsBanca);
		if(!$control)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile aggiornare i dati";
		}else{
			$msg = "Dati aggiornati correttamente";
			$action = "U";
			$storico_msg = "Modificata";
		}

		if($tipo_banca=="sede"){//preparo il db per reinserire dopo tutti i collegamenti banca - regione
			$query = "DELETE FROM banca_regione WHERE banca_id = ".$id_banca.";";
			$control = $cls_db->ExecuteQuery($query);

			if($control === false){
				$cls_db->Rollback();
				$error = 1;
				$msg = "Errore! impossibile eliminare la chiave banca-regione prima del salvataggio!";
			}
		}
	}
	else
	{
		$control = $cls_db->DbSave($a_paramsBanca);
		if($control === false)
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore, impossibile inserire i dati";
		}else {
			$id_banca = $control;
			$control = true;
			$msg = "Dati inseriti correttamente";
			$action = "I";
			$storico_msg = "Inserita";
		}
	}

	if($control)
	{
		if($tipo_banca=="sede")
		{
			$QUERY = $cls_param->Get_Query_Banca($id_banca);
			$array_filiali = $cls_db->getResults($cls_db->ExecuteQuery($QUERY["filiali"]));

			for($i=0;$i<count($array_filiali);$i++)
			{

				$a_paramsBanca = array(
						'table' => 'banca',
						'fields'=> array(
								array(  'name' => 'Partita_Iva',         'type' => 'string', 'value' =>  $cls_help->getVar('PI')),
								array(  'name' => 'Codice_Fiscale',      'type' => 'string', 'value' => $cls_help->getVar('CF'))
						),
						'updateField' => array(
							array ('name'=>'ID', 'type' => 'int', 'value'=> $id_banca, 'operator' => "AND"),
							array ('name'=>'CC', 'type' => 'string', 'value'=> '*****', 'operator' => null)
						)
				);

				if(!$cls_db->DbSave($a_paramsBanca))
				{
					$cls_db->Rollback();
					$error = 1;
					$msg = "Errore: aggiornamento filiali fallito";
				}else $msg = "Aggiornamento riuscito";
			}

			$query = "INSERT INTO banca_regione (banca_id,reg_codice) VALUES ";
			$countBR = 0;
			foreach($array_regioni as $key => $value){
				
				if($countBR == 0) $query .= "(".$id_banca.",'".$value."')";
				else $query .= ", (".$id_banca.",'".$value."')";
				
				$countBR++;
			}

			$control = $cls_db->ExecuteQuery($query);

			if($control === false){
				$cls_db->Rollback();
				$error = 1;
				$msg = "Errore! impossibile inserire la chiave banca-regione!";
			}
		}
	}
	$cls_db->End_Transaction();


}
else if( $invia == "Delete" )
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	$query = "";

	if($tipo_banca=="sede")
	{

		$query = "SELECT * FROM banca WHERE ID_Collegamento = ".$id_banca;

		$numero_sedi = $cls_db->getNumberRow($cls_db->ExecuteQuery($query));
		if($numero_sedi==0)
			$query = "DELETE FROM banca WHERE ID = " . $id_banca;
		else{
			$error = 1;
			$msg = "Errore: Banca con ID = ".$id_banca." ha filiali. Eliminare prima le filiali.";

			header("Location: banca.php?c={$c}&a={$a}&id_sede={$id_banca}&error={$error}&msg={$msg}");
			DIE;

		}

		$query = "DELETE FROM banca_regione WHERE banca_id = ".$id_banca.";";
		$control = $cls_db->ExecuteQuery($query);

		if($control === false){
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore! impossibile eliminare la chiave banca-regione!";
		}
	}
	else if($tipo_banca=="filiale"){
		$query = "DELETE FROM banca WHERE ID = " . $id_banca;

		if(!$cls_db->Delete("banca","ID = " . $id_banca))
		{
			$cls_db->Rollback();
			$error = 1;
			$msg = "Errore impossibile eliminare i dati.";
		}else{
			$msg = "Dati eliminati con successo";
			$action = "D";
			$storico_msg = "Eliminata";
		}
	}

	$cls_db->End_Transaction();

}

if($error == 0)
	$storico->insRow($action, $storico_msg.$tipo);

if($tipo_banca=="filiale") header("Location: filiale.php?c={$c}&a={$a}&id_sede={$id_sede}&id_filiale={$id_banca}&error={$error}&msg={$msg}");
else header("Location: banca.php?c={$c}&a={$a}&id_sede={$id_banca}&error={$error}&msg={$msg}");
?>
