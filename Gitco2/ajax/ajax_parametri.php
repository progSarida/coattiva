<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once CLS . "/cls_db.php";
	include_once CLS . "/cls_help.php";


	$cls_db = new cls_db();
	$cls_help = new cls_help();

$c = $cls_help->getVar('c');
$ComuneQuery = $cls_help->getVar('CC_Comune');
$TipoQuery = $cls_help->getVar('tipo');

$ajax = $cls_help->getVar('ajax');

switch($ajax)
{

	case "ufficio":

	$ID = $cls_help->getVar('ID');

	if($ComuneQuery=="" && $TipoQuery == "") $query = "SELECT * FROM ufficio_comune WHERE ID = {$ID} ";
	else $query = "SELECT * FROM ufficio_comune WHERE CC = '{$ComuneQuery}' AND Tipo = '{$TipoQuery}' ORDER BY Comune";

	$a_param = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	if(isset($a_param))
	{
		$CC_ente = $a_param["CC"];

		$a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '{$CC_ente}'"));

		$a_param["Com_Nome"] = addslashes($a_comune["Com_Nome"]);
		$a_param["Comune"] = addslashes($a_param["Comune"]);
		$a_param["Toponimo"] = addslashes($a_param["Toponimo"]);
	}
	else{
		$a_param["Com_Nome"] = ""; $a_param["CC"] = ""; $a_param["Comune"] = ""; $a_param["Toponimo"] = "";$a_param["Denominazione"] = "";$a_param["CC_Comune"] = "";$a_param["Provincia"] = "";
		$a_param["Cap"] = "";$a_param["Toponimo"] = "";$a_param["Civico"] = "0";$a_param["Esponente"] = "";$a_param["Interno"] = "0";$a_param["Dettagli"] = "";$a_param["Partita_Iva"] = "";
		$a_param["Telefono"] = "";$a_param["Fax"] = "";$a_param["Mail"] = "";$a_param["PEC"] = "";$a_param["Sito"] = "";$a_param["Orario"] = "";$a_param["ID"] = "";$a_param["Modalita_Invio"] = "";
	}

	$a_param["query"] = $query;
	echo json_encode($a_param);

break;

	case "uff_giudiziario":

		$CC = $cls_help->getVar('CC_ufficio');
		$tipo_ufficio = $cls_help->getVar('tipo_ufficio');

		if($cls_help->getVar("reload")==1) $itemWhere = "CC_Tribunale";
		else $itemWhere = "CC";

		$a_param = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM `v_tribunale_ivg` WHERE {$itemWhere} = '{$CC}'"));

		$a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '{$CC}'"));

		$nome_ente = $a_comune["Com_Nome"];
		$nome_ente_temp = addslashes($a_comune["Com_Nome"]);
		$a_param["Nome"] = $nome_ente_temp;

		$a_param["Comune_Tribunale"] = addslashes($a_param["Comune_Tribunale"]);
		$a_param["Toponimo_Tribunale"] = addslashes($a_param["Toponimo_Tribunale"]);
		$a_param["Comune_IVG"] = addslashes($a_param["Comune_IVG"]);
		$a_param["Toponimo_IVG"] = addslashes($a_param["Toponimo_IVG"]);

		echo json_encode($a_param);

	break;


}


?>
