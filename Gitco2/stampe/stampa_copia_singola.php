<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/flussi.php";
include CLASSI . "/pdf_con_bollettino.php";
include CLASSI . "/numero_letterale.php";
include_once FPDI . "/fpdi.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$partita_id = get_var('partita');
$pignoramento_id = get_var('pignoramento');
$tipo_copia = get_var('tipo_copia');
$num_not = get_var('num_not');
$num_terzo = get_var('num_terzo');


$partita = new partita($partita_id, $c);
$pignoramento = new pignoramento($pignoramento_id, $c);

switch($pignoramento->Tipo)
{
	case "terzi":
			
		switch($pignoramento->Tipo_Terzi)
		{
			case "lavoro":
				$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Datore_di_Lavoro/STAMPE DEFINITIVE" );
				$tipo_pigno_nome_file = "presso_lavoro";
				break;
					
			case "banca":
				$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Presso_Terzi/Banca/STAMPE DEFINITIVE" );
				$tipo_pigno_nome_file = "presso_banca";
				break;
		}
			
		break;
	
	case "veicolo":
		$stampa_dir = crea_dir( ATTI ."/". $c . "/Pignoramenti/Veicolo/STAMPE DEFINITIVE" );
		$tipo_pigno_nome_file = "veicolo";
		break;
}

$pdf_stampato = $pignoramento->pignoramento_stampato( $pignoramento->Tipo , "DEFINITIVA", $pignoramento->Tipo_Terzi );
$file_base = "Pignoramento_".$tipo_pigno_nome_file."_".$c."_".$pignoramento->Anno_Cronologico."_".$pignoramento->ID_Cronologico."_".$pignoramento->Data_Stampa;

$arrayConcat = array();
if($tipo_copia=="originale")
{
	$file_temp = $file_base."_originale.pdf";
	for($i=0;$i<count($pdf_stampato['stampa_originale']);$i++)
		$arrayConcat[$i] = $pdf_stampato['stampa_originale'][$i];
}
else 
{
	$file_relata = $stampa_dir."/".$file_base."_".$tipo_copia."_".$num_not.".pdf";
	
	if($tipo_copia=="rel_debitore")
	{
		$file_temp = $file_base."_copia_debitore.pdf";
		$file_copia = $stampa_dir."/".$file_base."_copia_debitore.pdf";
	}
	else if($tipo_copia=="rel_istituto")
	{
		$file_temp = $file_base."_copia_istituto.pdf";
		$file_copia = $stampa_dir."/".$file_base."_copia_istituto.pdf";
	}
	else if($tipo_copia=="rel_terzo")
	{
		$file_temp = $file_base."_copia_terzo_".$num_terzo.".pdf";
		$file_copia = $stampa_dir."/".$file_base."_copia_terzo_".$num_terzo.".pdf";
		$file_relata = $stampa_dir."/".$file_base."_".$tipo_copia."_".$num_terzo."_".$num_not.".pdf";
	}
		
	if(is_file($file_copia)===false)
		$file_copia = $stampa_dir."/".$file_base."_copia.pdf";	
	
	$arrayConcat[] = $file_copia;
	$arrayConcat[] = $file_relata;
}

$mergepdf = new Concat_Pdf();
$mergepdf->setFiles($arrayConcat);
$mergepdf->Concat();
$mergepdf->Output($file_temp, "I");
			
?>