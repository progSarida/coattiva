<?php
include_once CLS . "/cls_db.php";

class cls_testiUtils
{

  public $cls_db;

  public function __construct()
  {
    $this->cls_db = new cls_db();
  }

  public function CercaParametroData ($CCcomune, $dataConfronto, $blocco_stampa="no")
  {

	$query = "SELECT ID FROM parametri_testo_ingiunzione
				WHERE CC = '" . $CCcomune . "' AND
				Data_Creazione_Parametri <= '". $dataConfronto . "'
				ORDER BY Data_Creazione_Parametri DESC";
	//$result = safe_query($query);
	//$rigaParametro = mysql_fetch_assoc($result);
	$rigaParametro = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_testo_ingiunzione");

	if ($rigaParametro['ID'] != null)
	{
		// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
		$id = $rigaParametro['ID'];
		return $id;
	}
	else
	{
		//alert("Il testo non e' stato personalizzato per questo comune!");

		if($blocco_stampa!="no")
		{
			return null;
		}
		// se non sono MAI stati inseriti parametri per questo comune,
		// prendo il PIU' recente da un qualsiasi comune!
		$query = "SELECT ID FROM parametri_testo_ingiunzione
				WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
				ORDER BY Data_Creazione_Parametri DESC";
		//$result = safe_query($query);
		//$rigaParametro = mysql_fetch_assoc($result);
	    $rigaParametro = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_testo_ingiunzione");

		$id = $rigaParametro['ID'];
		return $id;
	}
  }

	public function CercaParametroDataSollecito ($CCcomune, $dataConfronto)
	{
		$query = "SELECT ID FROM parametri_testo_sollecito_ingiunzione
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
		//$result = safe_query($query);
		//$rigaParametro = mysql_fetch_assoc($result);
		$rigaParametro = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_testo_sollecito_ingiunzione");

		if ($rigaParametro['ID'] != null)
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM parametri_testo_sollecito_ingiunzione
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			//$result = safe_query($query);
			//$rigaParametro = mysql_fetch_assoc($result);
			$rigaParametro = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_testo_sollecito_ingiunzione");

			$id = $rigaParametro['ID'];
			return $id;
		}
	}

	public function CercaParametroDataPignoramento ($CCcomune, $dataConfronto, $blocco_stampa = "no")
	{
		$query = "SELECT ID FROM testo_pignoramento_presso_lavoro
					WHERE CC = '" . $CCcomune . "' AND
					Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";

		$ID = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_presso_lavoro")["ID"];

		if ($ID != null)
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			return $ID;
		}
		else
		{
			alert("Il testo non e' stato personalizzato per questo comune!");

			if($blocco_stampa!="no")
			{
				return null;
			}
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM testo_pignoramento_presso_lavoro
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			$ID = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"testo_pignoramento_presso_lavoro")["ID"];

			return $ID;
		}
	}

	public function CercaParametroDataIntimazione ($CCcomune, $dataConfronto,$blocco_stampa="no")
	{
		$query = "SELECT ID FROM parametri_atto_intimazione_ingiunzione 
					WHERE CC = '" . $CCcomune . "' AND 
					Data_Creazione_Parametri <= '". $dataConfronto . "'  
					ORDER BY Data_Creazione_Parametri DESC";
		//$result = safe_query($query);
		$rigaParametro = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_atto_intimazione_ingiunzione");// mysql_fetch_assoc($result);

		if ($rigaParametro['ID'] != null)
		{
			// anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
			$id = $rigaParametro['ID'];
			return $id;
		}
		else
		{
			echo "<script>alert('Il testo non e stato personalizzato per questo comune!');</script>";

			if($blocco_stampa!="no")
			{
				return null;
			}
			// se non sono MAI stati inseriti parametri per questo comune,
			// prendo il PIU' recente da un qualsiasi comune!
			$query = "SELECT ID FROM parametri_atto_intimazione_ingiunzione
					WHERE Data_Creazione_Parametri <= '". $dataConfronto . "'
					ORDER BY Data_Creazione_Parametri DESC";
			//$result = safe_query($query);
			$rigaParametro = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_atto_intimazione_ingiunzione");//mysql_fetch_assoc($result);

			$id = $rigaParametro['ID'];
			return $id;
		}
	}


}

 ?>
