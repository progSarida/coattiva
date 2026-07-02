<?php
/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
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
}*/

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/numero_letterale.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_stp = new cls_Stampe();
$cls_utils = new cls_Utils();
$cls_params = new cls_parameters();
$cls_date = new cls_DateTimeI("IT",false);
$log = new LOG();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$ID_Atto = $cls_help->getVar('ID_Atto');
$stampa_select = strtoupper($cls_help->getVar('tipo_stampa'));
$mode = $cls_help->getVar("mode");

/*$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];*/

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($c);

if( $comune->Gestore_ID != 0 ) {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Gestore_ID']);
}
else {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Info_ID']);
}

$query = "SELECT * FROM gestore WHERE ID = '" . $comune->Ufficio_ID . "'";
$comune->Ufficio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");

$stemmaComune = $comune->Stemma_1;


$gestore = $comune->Gestore;
$tipo_gestore = $gestore->Tipo;

$indirizzo_gestore = $cls_stp->righe_indirizzo($gestore);

if($tipo_gestore == "Concessionario")
	$image_file = WEB_ROOT."/immagini/sarida_logo.png";
else
	$image_file = $stemmaComune;

$intest_gestore = $cls_stp->intestazione_gestore("Riscossione coattiva", $comune->Denominazione, $gestore);

$ufficio = $comune->Ufficio;
$intest_ufficio = $cls_stp->intestazione_ufficio($ufficio);
$aggiunta_pigno = "";
//ATTO
if($ID_Atto!=null)
{
    $query = "SELECT * FROM v_atti WHERE Atto_ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));//new atto($ID_Atto, $c);
	switch($atto->Atto)
	{
		case "Avviso di intimazione ad adempiere": $tipo_atto = "Avv. Intimazione"; break;
		default: $tipo_atto = $atto->Atto;		break;
	}
	$tot_dovuto = $atto->Totale_Dovuto;
	if($atto->Tipo_Totale_Rate==1)
        $tot_dovuto+=$atto->Diritto_Riscossione_Minimo;
	else
        $tot_dovuto+=$atto->Diritto_Riscossione_Massimo;
}
else 
{
	$ID_Atto = $cls_help->getVar('ID_Pigno');
    $query = "SELECT * FROM v_pignoramento WHERE ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));//new pignoramento($ID_Atto, $c);

    $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$ID_Atto." AND CC = '".$c."'";
    $atto->Spese_Pignoramento = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));
    $cls_stp->gestione_totali($atto);

	$totali_array = $atto->Totali_Array;
	switch($atto->Tipo_Totale_Rate)
	{
		case "1":	$tot_dovuto = $totali_array[1]; break;
		case "2":	$tot_dovuto = $totali_array[2]; break;
		case "3":	$tot_dovuto = $totali_array[3]; break;	
	}
	
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

$partita_id = $atto->Partita_ID;
$crono = $atto->ID_Cronologico;
$anno_crono = $atto->Anno_Cronologico;




$num_rate = $atto->Rate_Previste;
$importo = explode("*",$atto->Importi_Rate);
$scadenza = explode("*",$atto->Scadenze_Rate);

//PARTITA
//$partita = new partita($partita_id, $c );
$query = "SELECT * FROM tributo WHERE Partita_ID = ".$atto->Partita_ID." AND CC = '".$c."' ORDER BY Codice_Tributo ASC";
$atto->Tributo = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"tributo");
$ID_partita = $atto->Comune_ID;
$anno_rif = $atto->Anno_Riferimento;
$settore = isset($atto->Tipo)?$atto->Tipo:$atto->Tipo_Riscossione;
$rif_atto = $ID_partita."/".$anno_rif;

//UTENTE
//$utente = new utente( $partita->Utente_ID , $c );
$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$atto->Utente_ID."' AND Tipo = 'res'";
$atto->Residenza = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");
$atto->Residenza = $cls_stp->getToponimo($atto->Residenza,$c);

$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$atto->Utente_ID."' AND Tipo = 'dom'";
$atto->Domicilio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");
$atto->Domicilio = $cls_stp->getToponimo($atto->Domicilio,$c);
if($atto->Domicilio->ID==null){$atto->Domicilio = null;}

$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$atto->Utente_ID."' AND Tipo = 'rec'";
$atto->Recapito = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");
$atto->Recapito = $cls_stp->getToponimo($atto->Recapito,$c);
if($atto->Recapito->ID==null) {$atto->Recapito = null;}


$nome_utente = $atto->Cognome_Ditta." ".$atto->Nome;
$utente_id = $atto->Comune_ID;
$indirizzo_destinatario = $cls_stp->righe_indirizzo_utente($atto);
$indirizzo_completo = $indirizzo_destinatario['Completo'];
$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

//PARAMETRI PAGAMENTO
$query = "SELECT * FROM parametri_pagamento WHERE CC = '".$c."' AND Tipo_Riscossione = '".$settore."'";
$par_pagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_pagamento");//new parametri_pagamento( $c, $settore);
$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
$iban = $par_pagamento->IBAN;	//IBAN

$autorizzazione_1 = $cls_stp->testo_autorizzazione(1,$par_pagamento);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
$ctrl_importo_1 = $par_pagamento->Importo_1;

//RIGA 1 CAUSALE BOLLETTINO
/*$riga1causale = $tipo_atto." n.".$crono." del ".$anno_crono." Rif.".$rif_atto;

$NW = new numero_letterale();
for($i=0;$i<count($importo);$i++)
{
	$quinto_campo[] = $cls_stp->quinto_campo($atto,$i+1);
	
	$importo_letterale[] = $NW->converti_numero_bollettino(str_replace( "," , "." , $importo[$i] ));
	$riga2causale[] = "SCADENZA PAGAMENTO RATA ".($i+1)." ENTRO IL ".$cls_date->Get_DateNewFormat($scadenza[$i],"DB");
}*/

$comuneNascita = "";
if($atto->Paese_Nascita!="Italia" && $atto->Paese_Nascita!="ITALIA")
    $comuneNascita = "in ".$atto->Paese_Nascita;
else
    $comuneNascita = "a ".$atto->Comune_Nascita;

$identificativo_atto = $tipo_atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$cls_text = new cls_textParameters();
$a_text = $cls_db->getArrayLineNull($cls_db->SelectQuery($cls_text->getParametersQuery($c,20)),"text_parameters");

$cls_text->html_body = $a_text['Content'];
$cls_text->html_replaced_body = $cls_text->html_body;

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetAutoPageBreak(false);
$pdf->SetCellPadding(0);
$pdf->AddPage('P');


if($stampa_select == "PROVVISORIA")
    $pdf->temporaryPrinting();

$pdf->SetLineWidth(0.2);
$pdf->SetMargins(7.0, 10.0, 7.0);

$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
//////$managerCity = $cls_ente->getCityManager();
$pdf->setManagerHeader($cls_ente->a_header);

$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $c, $atto->Tipo_Riscossione)));
if(!is_array($a_responsibleParams)){

	if($mode == "ajax_pigno"){
        echo json_encode([
            "type" => "temp",
            "file_name" => "",
            "error" => 2,
            "msg" => "ATTENZIONE!!! Parametri dei responsabili assenti per ".$atto->Tipo_Riscossione."!" 
        ]);
        die;
    }
    else{
        $cls_help->alert("ATTENZIONE!!! Parametri dei responsabili assenti per ".$atto->Tipo_Riscossione."!");
        echo "<script>window.close();</script>";
    }
}

//$cls_params->setArray("responsabili",$a_responsibleParams);
//$cls_params->getSignatures($cls_ente->type);

$cls_registry = new cls_registry();
$pdf->setRecipientHeader($cls_registry->printHeader((array) $atto));

$cls_text->a_var = array(
    "{User}" => $nome_utente,
    "{CommonUserBirth}" => $comuneNascita,
    "{UserBirthDate}" => $cls_date->Get_DateNewFormat($atto->Data_Nascita,"DB"),
    "{UserAddress}" => $indirizzo_senza_provincia,
    "{Act}" => $identificativo_atto,
    "{InfoCartella}" => $atto->Tributo[0]["Info_Cartella"],
    "{AmountDue}" => number_format($tot_dovuto,2,",","")." Euro",
    "{NumberInstallments}" => $atto->Rate_Previste
);

$cls_text->replaceVariables($cls_text->a_var);

$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($cls_text->html_replaced_body, true, 0, true, 0);


//PARAMETRI TESTO
/*$para_testo = new testo_richiesta_rateizzazione(NULL);
$myId = $para_testo->CercaParametroData($c, date("Y-m-d"));
$testo = new testo_richiesta_rateizzazione($myId);

$Oggetto = strtoupper(stripslashes($testo->Oggetto));
$sottoscritto = stripslashes($testo->Sottoscritto);
$testoAtto = stripslashes($testo->Atto_Testo);
$chiedo = stripslashes($testo->Chiedo);
$chiedoTesto = stripslashes($testo->Chiedo_Testo);
$condizioni_disagiate = stripslashes($testo->Condizioni_Disagiate);
$condizione_1 = stripslashes($testo->Condizione_1);
$condizione_2 = stripslashes($testo->Condizione_2);
$condizione_3 = stripslashes($testo->Condizione_3);
$condizione_4 = stripslashes($testo->Condizione_4);
$condizione_5 = stripslashes($testo->Condizione_5);


	

SostituisciTestoTraGraffe ($sottoscritto, "{NOMEUTENTE}", $nome_utente);
SostituisciTestoTraGraffe ($sottoscritto, "{COMUNENASCITA}", $comuneNascita );
SostituisciTestoTraGraffe ($sottoscritto, "{DATANASCITA}", from_mysql_date($atto->Data_Nascita));
SostituisciTestoTraGraffe ($sottoscritto, "{INDIRIZZOUTENTE}", $indirizzo_senza_provincia);

$identificativo_atto = $tipo_atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

SostituisciTestoTraGraffe ($testoAtto, "{ATTO}", $identificativo_atto);
SostituisciTestoTraGraffe ($testoAtto, "{INFOCARTELLA}", $atto->Tributo[0]["Info_Cartella"] );

SostituisciTestoTraGraffe ($chiedoTesto, "{DOVUTO}", conv_num(number_format($tot_dovuto,2))." Euro");
SostituisciTestoTraGraffe ($chiedoTesto, "{NUMERORATE}", $atto->Rate_Previste );


/**
 ///////////////////////////////		PDF	    //////////////////////////////////


$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetAutoPageBreak(false);
$pdf->SetCellPadding(0);
$pdf->AddPage('P');

if($stampa_select == "PROVVISORIA")
	$pdf->stampa_provvisoria();

$pdf->SetLineWidth(0.2);
$pdf->SetMargins(7.0, 10.0, 7.0);


//////////////	CORPO Pagina 1	//////////////

$pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);
$pdf->destinatario_intestazione_pdf($utente_id, $c, strtoupper($gestore->Denominazione), $ID_partita, $anno_rif, $indirizzo_gestore, "" );
$pdf->oggetto_pdf($Oggetto, "", "");

//SOTTOSCRITTO
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->ln(5);

$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 0, $sottoscritto."\n" , 0, 'J', 0, 1);
$pdf->ln(5);
$pdf->MultiCell(0, 0, $testoAtto."\n" , 0, 'J', 0, 1);
$pdf->ln(5);
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(0, 0, $chiedo , 0, 'C', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 0, $chiedoTesto."\n" , 0, 'J', 0, 1);
$pdf->ln(10);
$pdf->MultiCell(98, 0, "LUOGO E DATA" , 0, 'C', 0, 0);
$pdf->MultiCell(98, 0, "IN FEDE" , 0, 'C', 0, 1);
$pdf->ln(2);
$pdf->MultiCell(98, 0, "_______________, li _________" , 0, 'C', 0, 0);
$pdf->MultiCell(98, 0, "____________________" , 0, 'C', 0, 1);
$pdf->ln(10);
$pdf->MultiCell(0, 0, $condizioni_disagiate."\n" , 0, 'J', 0, 1);
$pdf->ln(2);
$pdf->MultiCell(0, 0, "1) ".$condizione_1 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "2) ".$condizione_2 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "3) ".$condizione_3 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "4) ".$condizione_4 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "5) ".$condizione_5 , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "6) ________________________ " , 0, 'L', 0, 1);
$pdf->MultiCell(0, 0, "7) ________________________ " , 0, 'L', 0, 1);

*/
//////////////	FINE CORPO Pagina 1	//////////////

if($mode == "ajax_pigno") $path = $cls_utils->crea_dir( PIGNORAMENTI ."/". $c . "/Documenti" );
else $path = $cls_utils->crea_dir( ATTI ."/". $c . "/Documenti" );
$nome_file = "Richiesta_rateizzazione_".$c."_".$crono."_".$anno_crono."_".date("Y-m-d_H-i").".pdf";

if($stampa_select == "PROVVISORIA")
{
	if($mode == "ajax_pigno"){
        $path = $cls_utils->crea_dir( ARCHIVIO ."/temp" );
        $pdf->Output( $path."/rateizzazione_richiesta.pdf" , 'F');
        //var_dump("son qui");
        echo json_encode([
                "type" => "temp",
                "file" => ARCHIVIO_WEB."/temp/rateizzazione_richiesta.pdf",
                "error" => 0,
                "msg" => "Pdf provvisorio creato!" 
        ]);
        die;
    }
    else $pdf->Output( $nome_file , 'I');
	die;
}
else if($stampa_select == "DEFINITIVA")
	$pdf->Output( $path."/".$nome_file , 'F');

//die;
	
$query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$comune_id = isset($result["Com"])?$result["Com"]:0;//single_query($query);

$salva = new stdClass();//new documento( null , $c );

$salva->CC = $c;
$salva->Comune_ID = $comune_id + 1;
$salva->File = $nome_file;
$salva->Utente_ID = $atto->Utente_ID;
$salva->Tipo = "Ricevuto";
$salva->Atto = "Richiesta di rateizzazione".$aggiunta_pigno;
$salva->Data_Creazione = date("Y-m-d");
$salva->Informazioni_Aggiuntive = "Richiesta di rateizzazione ".$tipo_atto;
$salva->Oggetto = "";
$salva->Contenuto = "";
$salva->Data_Stampa = date("Y-m-d");

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

//$control_salva = $salva->Insert( true );

$control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva,"documento"));

if( $control_salva )
{

	if($mode == "ajax_pigno") $table = "pignoramento_generale";
    else $table = "atto";

	$id_documento = $control_salva;
    $salva = new stdClass();

    $salva->ID_Richiesta_Rateizzazione = $control_salva;
	$control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva,$table, array("ID" => $ID_Atto)));//$atto->Update($ID_Atto);
	
	if( $control_salva )
	{
		$cls_db->End_Transaction();
        $log->info("File ".$nome_file." creato come stampa definitiva, aggiornamento DB completato.\nTabella atto ID = ".$ID_Atto."\nTabella documento ID = ".$id_documento);

        if($mode == "ajax_pigno"){
            echo json_encode([
                "type" => "final",
                "file_name" => $nome_file,
                "error" => 0,
                "msg" => "Pdf definitivo creato!" 
            ]);
        }
        else $pdf->Output( $nome_file );
		die;
	}
	else
	{
		$cls_db->Rollback();
        $cls_db->End_Transaction();

		if($mode == "ajax_pigno"){
            $log->error("Aggiornamento tabella pignoramento_genarale fallita per id = ".$ID_Atto);
            echo json_encode([
                "type" => "final",
                "file_name" => "",
                "error" => 1,
                "msg" => "Aggiornamento tabella pignoramento_generale fallito!" 
            ]);
            die;
        }
        else $log->error("Aggiornamento tabella atto fallita per id = ".$ID_Atto);
	}

}
else
{
    $cls_db->Rollback();
    $cls_db->End_Transaction();
    $log->error("Inserimento tabella documento fallita");
}

if($mode == "ajax_pigno"){
    echo json_encode([
        "type" => "final",
        "file_name" => "",
        "error" => 1,
        "msg" => "Inserimento tabella documento fallito!" 
    ]);
}

?>