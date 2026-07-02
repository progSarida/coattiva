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

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$ID_Atto = get_var('ID_Atto');
$stampa_select = strtoupper(get_var('tipo_stampa'));

set_time_limit(100);

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];
$stemmaComune = $comune->Stemma_1;

$gestore = $comune->Gestore;
$tipo_gestore = $gestore->Tipo;
$stemmaGestore = $gestore->Stemma;

if($tipo_gestore == "Concessionario")
{
	if($stemmaGestore!="")
		$image_file = $stemmaGestore;
	else
		$image_file = "/gitco2/immagini/sarida_logo.png";
}
else
	$image_file = $stemmaComune;

$aggiunta_pigno = "";
//ATTO
if($ID_Atto!=null)
{
	$atto = new atto($ID_Atto, $c);
	switch($atto->Atto)
	{
		case "Avviso di intimazione ad adempiere": $tipo_atto = "Avv. Intimazione"; break;
		default: $tipo_atto = $atto->Atto;		break;
	}
}
else 
{
	$ID_Atto = get_var('ID_Pigno');
	$atto = new pignoramento($ID_Atto, $c);
	$aggiunta_pigno = " pignoramento";
	switch($atto->Tipo)
	{
		case "veicolo": $tipo_atto = "Pignoramento beni mobili registrati"; break;
		case "terzi": 	
			switch($atto->Tipo_Terzi)
			{
				case "lavoro": $tipo_atto = "Pignoramento presso datore di lavoro"; break;
				case "banca":  $tipo_atto = "Pignoramento presso banca"; break;
			}
				break;
			
		default: $tipo_atto = $atto->Tipo;		break;
	}
}

//PARTITA
$partita = new partita($atto->Partita_ID, $c );
$ID_partita = $partita->Comune_ID;
$anno_rif = $partita->Anno_Riferimento;
$settore = $partita->Tipo;
$rif_atto = $ID_partita."/".$anno_rif;

//UTENTE
$utente = new utente( $partita->Utente_ID , $c );
$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
$utente_id = $utente->Comune_ID;
$indirizzo_destinatario = $utente->righe_indirizzo();
$indirizzo_completo = $indirizzo_destinatario['Completo'];
$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

//PARAMETRI PAGAMENTO
$par_pagamento = new parametri_pagamento( $c, $settore );
$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
$iban = $par_pagamento->IBAN;	//IBAN
$stemma = $par_pagamento->Stemma;
$stemma_2 = $par_pagamento->Stemma_2;
$autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
$ctrl_importo_1 = $par_pagamento->Importo_1;


//ATTO
$tot_dovuto = $atto->Totale_Dovuto;

$num_rate = $atto->Rate_Previste;
$importo = $atto->Importi_Rate;
$scadenza = $atto->Scadenze_Rate;

//RIGA 1 CAUSALE BOLLETTINO
$riga1causale = $tipo_atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

$NW = new numero_letterale();
for($i=0;$i<count($importo);$i++)
{
	set_time_limit(30);
	
	$importo_let = number_format(conv_num($importo[$i]),2);
	$quinto_campo[] = $atto->quinto_campo($i+1);
	$importo_letterale[] = $NW->converti_numero_bollettino($importo_let);
	$riga2causale[] = "SCADENZA PAGAMENTO RATA ".($i+1)." ENTRO IL ".from_mysql_date($scadenza[$i]);
}

/**
 ///////////////////////////////		PDF	    //////////////////////////////////
 */

$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
$pdf->SetAutoPageBreak(false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
/**
 * 		//////////////	BOLLETTINO	//////////////
 */
if( $autorizzazione_1!=false || $td_1=="123" )
{
		
for($i=0;$i<count($importo);$i++)
{
	set_time_limit(30);
	
	$pdf->AddPage('L');
if($stampa_select == "PROVVISORIA")
	$pdf->stampa_provvisoria();
	
	$pdf->crea_bollettino();
	if($stemma == "")
		$pdf->logo_bollettino($image_file);
	else if($stemma == "ente")
		$pdf->logo_bollettino($stemmaComune);
	else if($stemma == "gestore")
		$pdf->logo_bollettino($stemmaGestore);
	$pdf->scelta_td_bollettino($td_1, $quinto_campo[$i] , $importo[$i] , "si" , $numeroContoCorrente );
	$pdf->iban_bollettino($iban);
	$pdf->intestatario_bollettino($intestatarioConto);
	$pdf->causale_bollettino($riga1causale, $riga2causale[$i]);
	$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario);
	$pdf->autorizzazione_bollettino($autorizzazione_1);
	
	$i++; 
	
	if($i<count($importo))
	{
		$pdf->crea_bollettino_inverso();
		if($stemma_2 == "")
			$pdf->logo_bollettino($image_file,'due');
		else if($stemma_2 == "ente")
			$pdf->logo_bollettino($stemmaComune,'due');
		else if($stemma_2 == "gestore")
			$pdf->logo_bollettino($stemmaGestore,'due');
		$pdf->scelta_td_bollettino($td_1, $quinto_campo[$i] , $importo[$i] , "si" , $numeroContoCorrente, 'due');
		$pdf->iban_bollettino($iban,'due');
		$pdf->intestatario_bollettino($intestatarioConto,'due');
		$pdf->causale_bollettino($riga1causale, $riga2causale[$i],'due');
		$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario,'due');
		$pdf->autorizzazione_bollettino($autorizzazione_1,'due');
	}
}	
	
$path = crea_dir( ATTI ."/". $c . "/Documenti" );
$nome_file = "Bollettini_rate_".$c."_".$atto->ID_Cronologico."_".$atto->Anno_Cronologico."_".date("Y-m-d_H-i").".pdf";

if($stampa_select == "PROVVISORIA")
{
	$pdf->Output( $nome_file , 'F');
	die;
}
else if($stampa_select == "DEFINITIVA")
	$pdf->Output( $path."/".$nome_file , 'F');
	
$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
$comune_id = single_query($query);

$salva = new documento( null , $c );

$salva->CC = $c;
$salva->Comune_ID = $comune_id + 1;
$salva->File = $nome_file;
$salva->Utente_ID = $partita->Utente_ID;
$salva->Tipo = "Inviato";
$salva->Atto = "Bollettini rate".$aggiunta_pigno;
$salva->Data_Creazione = date("Y-m-d");
$salva->Informazioni_Aggiuntive = "Bollettini rate ".$tipo_atto;
$salva->Oggetto = "";
$salva->Contenuto = "";
$salva->Data_Stampa = date("Y-m-d");

mysql_query('BEGIN');

$control_salva = $salva->Insert( true );

if( $control_salva )
{
	$id_documento = mysql_insert_id();
	
	$atto->ID_Bollettini_Rateizzazione = $id_documento;
	$control_salva = $atto->Update($ID_Atto);
	
	if( $control_salva )
	{
		mysql_query('COMMIT');
		$pdf->Output( $path."/".$nome_file );
	}
	else
	{
		echo 'ERROR '.mysql_error();
		mysql_query('ROLLBACK');
	}

}
else
{
	echo 'ERROR '.mysql_error();
	mysql_query('ROLLBACK');
}
	
	
}
	
?>