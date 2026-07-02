<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

class anni_gestiti
{
	public $ID;
	public $CC_Anno;
	public $Anno;
	public $Gestione_Coattiva;
	public $Gestione_Targhe_Estere;
	public $Gestione_Pubblicita;

	public function __construct( $c, $a, $gestione = null )
	{

		$query = "SELECT * FROM anni_gestiti WHERE CC_Anno = '".$c."' AND Anno = '".$a."'";
		if($gestione!=null)
		{
			switch($gestione)
			{
				case "COATTIVA": 		$campo_gestione = "Gestione_Coattiva";		break;
				case "TARGHEESTERE": 	$campo_gestione = "Gestione_Targhe_Estere";	break;
				case "PUBBLICITA": 		$campo_gestione = "Gestione_Pubblicita";	break;
				default: alert ("In ElencoAnni (file_function.php) manca un parametro"); $aggiunta = ""; break;
			}

			$query.= " AND ".$gestione." = 'Y'";
		}


		$result = safe_query($query);
		$val = mysql_fetch_array($result);

		$this->ID = utf8_decode($val['ID']);
		$this->CC_Anno = utf8_decode($val['CC_Anno']);
		$this->Anno = utf8_decode($val['Anno']);
		$this->Gestione_Coattiva = utf8_decode($val['Gestione_Coattiva']);
		$this->Gestione_Targhe_Estere = utf8_decode($val['Gestione_Targhe_Estere']);
		$this->Gestione_Pubblicita = utf8_decode($val['Gestione_Pubblicita']);

	}

	public function Array_Selezione_Anni($c, $gestione)
	{
		$where = "CC_Anno = '".$c."' ";

		switch ($gestione)
		{
			case "COATTIVA": 		$where.= " AND Gestione_Coattiva = 'Y' "; 		break;
			case "TARGHEESTERE": 	$where.= " AND Gestione_Targhe_Estere = 'Y' "; 	break;
			case "PUBBLICITA": 		$where.= " AND Gestione_Pubblicita = 'Y' ";		break;
			default: 				alert ("Parametro assente!"); 					break;
		}

		$array_anni = select_mysql_array("*", "anni_gestiti", $where, "Anno","DESC");



		return $array_anni;
	}

	public function Options_Anni($c, $gestione)
	{
		$array_anni = $this->Array_Selezione_Anni($c, $gestione);

		$select = "<select id='select_anno'>";
		$select.= "<option></option>";
		$select.= "<optgroup label='Anno'>";

		for($i=0;$i<count($array_anni);$i++)
			$select.= "<option value='".$array_anni[$i]['Anno']."'>".$array_anni[$i]['Anno']."</option>";

		$select.="</optgroup>";
		$select.="</select>";

		return $select;

	}

	public function Options_Anni_Veloci($c, $gestione, $pagina)
	{
		$array_anni = $this->Array_Selezione_Anni($c, $gestione);

		$select = "<select id='select_anno_veloce' onchange='conferma_anno_js(\"".$pagina."\",\"".$c."\")'>";

		for($i=0;$i<count($array_anni);$i++)
			$select.= "<option value='".$array_anni[$i]['Anno']."'>".$array_anni[$i]['Anno']."</option>";

			$select.="</select>";

			return $select;

	}

}

?>
