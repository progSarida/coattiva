<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);

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

include_once CLS."/cls_printer_params.php";
include_once CLS."/cls_db.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$PrinterId = get_var("PrinterId");
$PrintTypeId = get_var("PrintTypeId");
$cls_db = new cls_db();
$cls_params = new cls_printer_params();
$a_printerParams = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_params->getPrinterChargeQuery($PrinterId,$PrintTypeId)));

$a = get_var('a');
$c = get_var('c');
$stampa_select = strtoupper(get_var('stampa_select'));

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;

$comune_completo = "";
if(substr($comune->CC,0,1)!="U")
    $comune_completo.= "Comune di ";

$comune_completo.= ucwords($nome_com);

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

//STEMMA GESTORE
if($stemmaGestore!=""){
    $percorso_stemma_gestore = $_SERVER['DOCUMENT_ROOT'].$stemmaGestore;
}
else{
    $percorso_stemma_gestore = $_SERVER['DOCUMENT_ROOT']."/gitco2/immagini/sarida_logo.png";
    $stemmaGestore = "/gitco2/immagini/sarida_logo.png";
}

$stemma_image_gestore = explode("/",$percorso_stemma_gestore);
$stemma_image_gestore = $stemma_image_gestore[count($stemma_image_gestore)-1];

//STEMMA COMUNE
$percorso_stemma_comune = $_SERVER['DOCUMENT_ROOT'].$stemmaComune;

$stemma_image_comune = explode("/",$percorso_stemma_comune);
$stemma_image_comune = $stemma_image_comune[count($stemma_image_comune)-1];

$percorso_image_file = $_SERVER['DOCUMENT_ROOT'].$image_file;
$stemma_image_file = explode("/",$percorso_image_file);
$stemma_image_file = $stemma_image_file[count($stemma_image_file)-1];

$intest_gestore = $gestore->intestazione_gestore("Riscossione coattiva", $comune_completo);

$ufficio = $comune->Ufficio;
$intest_ufficio = $ufficio->intestazione_ufficio();

$data_file = date('Y-m-d');
$ora_file = date('H-i-s');
$vedi_file = "";

$comm_trib_prov = new ufficio_giudiziario($c, "comm_trib_prov");
if($comm_trib_prov->ID <= 0 || $comm_trib_prov->CC_Ufficio==null)
{
    alert("COMMISSIONE TRIBUTARIA PROVINCIALE assente!!! Inserire autorita' nei parametri per procedere con la stampa.");
    die;
}

$ctp = strtoupper("COMMISSIONE TRIBUTARIA PROVINCIALE DI ".$comm_trib_prov->Comune);
$ctp_indirizzo = $comm_trib_prov->righe_indirizzo();
$ctp_PEC = $comm_trib_prov->PEC;
$ctp_Mail = $comm_trib_prov->Mail;
$ctp_recapiti = "";

if($comm_trib_prov->Telefono!="")
    $ctp_recapiti.= "Tel: ".$comm_trib_prov->Telefono;
if($comm_trib_prov->Fax!="")
    $ctp_recapiti.= " - Fax: ".$comm_trib_prov->Fax;
if($comm_trib_prov->Mail!="")
    $ctp_recapiti.= " - Mail: ".$comm_trib_prov->Mail;
if($comm_trib_prov->PEC!="")
    $ctp_recapiti.= " - PEC: ".$comm_trib_prov->PEC;

if($ctp_recapiti!="")
    $ctp_recapiti = "( ".$ctp_recapiti." )";

$tribunale = new ufficio_giudiziario($c,"tribunale");
if($tribunale->Comune == "")
{
    alert("TRIBUNALE assente!!! Inserire autorita' nei parametri per procedere con la stampa.");
    die;
}
$trib = strtoupper("TRIBUNALE DI ".$tribunale->Comune);
$trib_indirizzo = $tribunale->righe_indirizzo();
$trib_PEC = $tribunale->PEC;
$trib_Mail = $tribunale->Mail;

$trib_recapiti = "";
if($tribunale->Telefono!="")
    $trib_recapiti.= "Tel: ".$tribunale->Telefono;
if($tribunale->Fax!="")
    $trib_recapiti.= " - Fax: ".$tribunale->Fax;
if($tribunale->Mail!="")
    $trib_recapiti.= " - Mail: ".$tribunale->Mail;
if($tribunale->PEC!="")
    $trib_recapiti.= " - PEC: ".$tribunale->PEC;

if($trib_recapiti!="")
    $trib_recapiti = "( ".$trib_recapiti." )";

$giudice_pace = new ufficio_giudiziario($c, "giudice");
if($giudice_pace->Comune == "")
{
    alert("GIUDICE DI PACE assente!!! Inserire autorita' nei parametri per procedere con la stampa.");
    die;
}
$gdp = strtoupper("GIUDICE DI PACE DI ".$giudice_pace->Comune);
$gdp_indirizzo = $giudice_pace->righe_indirizzo();
$gdp_PEC = $giudice_pace->PEC;
$gdp_Mail = $giudice_pace->Mail;
$gdp_recapiti = "";
if($giudice_pace->Telefono!="")
    $gdp_recapiti.= "Tel: ".$giudice_pace->Telefono;
if($giudice_pace->Fax!="")
    $gdp_recapiti.= " - Fax: ".$giudice_pace->Fax;
if($giudice_pace->Mail!="")
    $gdp_recapiti.= " - Mail: ".$giudice_pace->Mail;
if($giudice_pace->PEC!="")
    $gdp_recapiti.= " - PEC: ".$giudice_pace->PEC;

if($gdp_recapiti!="")
    $gdp_recapiti = "( ".$gdp_recapiti." )";

$chiudi = "";

//CONTROLLO TESTO
$para_ing = new parametri_testo_sollecito_ingiunzione(null);
$myID = $para_ing->CercaParametroData($c, date("Y-m-d"));
if($myID==null)
    $chiudi = "chiudi_finestra()";

$testo = new parametri_testo_sollecito_ingiunzione($myID);

//$informazioni = $testo->Informazioni_Testo;
//$data_testo = $testo->Data_Creazione_Parametri;
//
//if($stampa_select=="PROVVISORIA" || $stampa_select == "DEFINITIVA")
//{
//    if($informazioni=="")
//    {
//        alert("Il campo Informazioni non e' stato compilato! Stampa annullata!");
//        echo "<script>window.close();</script>";
//    }
//
//    if( date('Y-m-d') > date( "Y-m-d" , strtotime( $data_testo."+1 month" )) ){
//        alert("Il salvataggio del testo e' stato effettuato da piu' di 30 giorni. Ricontrollare il campo Informazioni e salvare!");
//        echo "<script>window.close();</script>";
//    }
//}

if($stampa_select == "PROVVISORIA")
{

    $stampa_dir = crea_dir( ATTI ."/". $c . "/Solleciti/STAMPE PROVVISORIE" );

    $file_stampa = $stampa_dir."/Solleciti_Provvisori_".$c."_".$data_file."_".$ora_file.".pdf";
    $vedi_file = mostra_file_path($file_stampa);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
    <title>Stampa Avviso</title>

    <link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
    <link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
    <style> .ui-datepicker { font-size:11px; } </style>


    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
    <script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

    <script>
        function inizio()
        {
            $('#progressbar').progressbar({
                value: false
            });
            $( "#barlabel" ).text("Inizio elaborazione...");
        }

        function update(valore)
        {
            $( "#progressbar" ).progressbar({value: parseInt(valore) });
            $( "#barlabel" ).text( valore + "%" );
        }

        function nessun_risultato()
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text("Nessun risultato trovato");
        }

        function fine(value)
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text( value );

            sleep(1000);

            mostra_file();
            //$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Stampe ingiunzioni' onclick='mostra_file();'>");
        }

        function fine2(value)
        {
            $( "#progressbar" ).progressbar({value: 100 });
            $( "#barlabel" ).text( value );

            sleep(1000);
        }

        function mostra_file()
        {
            window.name = "Stampa";
            window.open('<?php echo $vedi_file; ?>',"Stampa");
        }

        function merge()
        {
            $('#progressbar2').progressbar({
                value: false
            });
            $( "#barlabel2" ).text("Inizio creazione file di stampa...");
        }

        function update_merge(valore)
        {
            $( "#progressbar2" ).progressbar({value: parseInt(valore) });
            $( "#barlabel2" ).text( valore + "%" );
        }

        function fine_merge(value)
        {
            $( "#progressbar2" ).progressbar({value: false });
            $( "#barlabel2" ).text( value );
        }

        function fine_e_apri(value, value2)
        {
            $( "#progressbar2" ).progressbar({value: 100 });
            $( "#barlabel2" ).text( value );

            sleep(1000);

            window.name = "Stampa";
            window.open(value2,"Stampa");

        }

        function atti_stampati(value)
        {
            $('#flusso_form').submit();
        }

        function cronologici(value)
        {
            $('#crono_form').submit();
        }

        function chiudi_finestra()
        {
            window.close();
        }
        <?php echo $chiudi; ?>
    </script>

</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
    <tr>
        <td width=1%><br></td>
        <td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
        <td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
        <td width=1%><br></td>
    </tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
    <tr>
        <td valign=top>
            <br><br><br>
            <font class="titolo font18 text_center">Stampa Solleciti di pagamento</font>

            <br><br>

            <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>

            <br>

            <div id=vedi_file></div>

        </td>
    </tr>
</table>

<?php

$data_definitiva = from_mysql_date(get_var('data_definitiva'));

$daco  = strtoupper(get_var('daco'));
$anom  = strtoupper(get_var('anom'));

$dano  = strtoupper(get_var('dano'));
$acog  = strtoupper(get_var('acog'));

$da_partita  = get_var('da_n_elenco');
$a_partita  = get_var('a_n_elenco');

$da_elab = from_mysql_date(get_var('da_elab'));
$a_elab = from_mysql_date(get_var('a_elab'));

$da_notif = from_mysql_date(get_var('da_notif'));
$a_notif = from_mysql_date(get_var('a_notif'));

$da_stampa = from_mysql_date(get_var('da_stampa'));
$a_stampa = from_mysql_date(get_var('a_stampa'));

$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$stato_esec = get_var('stato_esec');
$stato_notif = get_var('stato_notif');
$stato_stampa = get_var('stato_stampa');

$tipo_partita = get_var('tipo_partita');
$a_taxType = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT Id FROM tax_type WHERE Name=\"".$tipo_partita."\""));
$TaxTypeId = $a_taxType['Id'];

$ctrl_responsabili = new parametri_responsabili($c, null);
$verifica_parametri_resp = $ctrl_responsabili->controllo_parametri($c,$tipo_partita);
if($verifica_parametri_resp!==true)
{
    alert('Parametri Responsabili '.$verifica_parametri_resp.' incompleti!');
    echo "<script>chiudi_finestra();</script>";
}

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
sleep(2);


/**		SELEZIONE UTENTI 			*/
$query_utente = da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = mysql_array( $query_utente );


/** 	SELEZIONE PARTITE			*/
$where_anno = null;
if( $da_anno != null && $ad_anno != null )
    $where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' AND Flag_Blocco_Coazione != 'si' ";

$query_partita = da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = mysql_array( $query_partita );


/** 	SELEZIONE ATTI	*/
$campi_stati = array("Stato" , "Stato_Stampa" , "Stato_Esecuzione");
$valori_stati = array ( $stato_notif , $stato_stampa , $stato_esec );

$query_stati = where_campi($campi_stati, $valori_stati);

$campi_array = array ("Data_Elaborazione" , "Data_Notifica" , "Data_Stampa" );
$array_da_data = array( to_mysql_date($da_elab) , to_mysql_date($da_notif) , to_mysql_date($da_stampa) );
$array_a_data = array( to_mysql_date($a_elab) , to_mysql_date($a_notif) , to_mysql_date($a_stampa) );

$query_date = da_a_data_array( $c , "Sollecito di pagamento" , $campi_array , $array_da_data , $array_a_data , $query_stati );
$array_atti = mysql_array($query_date);

$num_atti = count($array_atti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

$anno_current = date("Y");
$array_stampati = array();
$array_cronologici = array();

if($stampa_select == "FLUSSO")
{

}
else
{
    if($stampa_select == "PROVVISORIA")
    {

        /**
        ///////////////////////////////		PDF	    //////////////////////////////////
         */

        $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
        $pdf->SetLineWidth(0.2);
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $width_page = $pdf->getPageWidth() - 7;

        /**
        //////////////////////////////////////////////////////////////////////////////
         */
    }
    else if($stampa_select == "DEFINITIVA")
    {
        $stampa_dir = crea_dir( ATTI ."/". $c . "/Solleciti/STAMPE DEFINITIVE" );
        $concat_dir = crea_dir( ATTI ."/". $c . "/Solleciti/STAMPE CONCATENATE" );
        $arrayConcat = Array();
    }
}

//PARAMETRI PAGAMENTO
$par_pagamento = new parametri_pagamento( $c, $tipo_partita );
$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
$iban = $par_pagamento->IBAN;	//IBAN
if($iban!="")
    $iban_testo = " (IBAN ".$iban.")";
else
    $iban_testo = "";

$autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
$ctrl_importo_1 = $par_pagamento->Importo_1;

$stemma = $par_pagamento->Stemma;
if($stemma == "")
    $stemma_bol_1 = $stemma_image_file;
else if($stemma == "ente")
    $stemma_bol_1 = $stemma_image_comune;
else if($stemma == "gestore")
    $stemma_bol_1 = $stemma_image_gestore;
else
    $stemma_bol_1 = "";

$cont_result = 0;
for( $l=0; $l < $num_atti; $l++ )//FOR ATTI
{
    echo "<script>update(".ceil($l*100/$num_atti).");</script>";

    flush();
    ob_flush();
    flush();
    ob_flush();

    for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
    {
        if( $array_atti[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF ATTO/PARTITA
        {

            for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
            {
                if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
                {
                    set_time_limit(30);


                    //INGIUNZIONE
                    $soll = new atto( $array_atti[$l]['ID'], $c );

                    $ID_ing = $soll->Comune_ID;
                    $anno_crono = $soll->Anno_Cronologico;
                    $id_crono = $soll->ID_Cronologico;
                    $rif = $soll->Riferimento;
                    $info_cart = strtoupper($soll->Info_Cartella);
                    $tipoUfficiale = $soll->Tipo_Ufficiale;
                    $modalitaStampa = $soll->Modalita_Stampa;
                    $protocollo = $soll->Protocollo;
                    $data_protocollo = $soll->Data_Protocollo;

                    //PARTITA
                    $partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
                    $ID_partita = $partita->Comune_ID;
                    $anno_rif = $partita->Anno_Riferimento;
                    $settore = $partita->Tipo;
                    $sottosettore = $partita->Sottotipo;

                    $rif_ing = $ID_partita."/".$anno_rif;


                    if($stato_stampa!="Stampato")
                        $data_stampa = $data_definitiva;
                    else
                        $data_stampa = from_mysql_date($soll->Data_Stampa);

                    //ESCLUSIONI
                    if($stampa_select == "PROVVISORIA")
                    {
                        if($soll->Data_Notifica != null && $soll->Data_Notifica != '0000-00-00')
                            break;
                    }
                    else if($stampa_select == "CRONOLOGICI")
                    {
                        if($soll->ID_Cronologico == "0" && $soll->Anno_Cronologico == "0")
                        {
                            $array_cronologici[] = $soll->ID;
                            $cont_result++;
                        }

                        break;
                    }
                    else if($stampa_select == "DEFINITIVA")
                    {
                        if($soll->ID_Cronologico == "0" || $soll->Anno_Cronologico == "0")
                            break;

                        if($stato_stampa == "Stampato")
                        {
                            if($soll->Cronologico_Vecchio!="si")
                            {
                                $file_stampa_singola = $stampa_dir."/Sollecito_".$c."_".$anno_crono."_".$id_crono."_".to_mysql_date($data_stampa).".pdf";
                                $arrayConcat[] = $file_stampa_singola;

                                $array_stampati[] = $soll->ID;
                                $cont_result++;
                            }

                            break;
                        }
                    }
                    else if($stampa_select == "FLUSSO")
                    {

                    }


                    switch($settore)
                    {
                        case "CDS":

                            $tipo_ing = "Riscossione sanzioni amministrative";
                            $utente_ing = "soggetto";
                            $causale = "VIOL CDS";

                            break;

                        case "IMMOBILI":

                            if($c=="U003")
                                $tipo_ing = "Servizio riscossione entrate provinciali";
                            else
                                $tipo_ing = "Servizio riscossione entrate comunali";
                            $utente_ing = "contribuente";
                            $causale = "ICI";

                            break;

                        case "RIFIUTI":

                            if($c=="U003")
                                $tipo_ing = "Servizio riscossione entrate provinciali";
                            else
                                $tipo_ing = "Servizio riscossione entrate comunali";
                            $utente_ing = "contribuente";
                            $causale = "TSRSU";

                            break;

                        case "PATRIMONIALE":

                            if($c=="U003")
                                $tipo_ing = "Servizio riscossione entrate provinciali";
                            else
                                $tipo_ing = "Servizio riscossione entrate comunali";
                            $utente_ing = "soggetto";
                            $causale = "";

                            break;

                        case "OSAP":

                            if($c=="U003")
                                $tipo_ing = "Servizio riscossione entrate provinciali";
                            else
                                $tipo_ing = "Servizio riscossione entrate comunali";
                            $utente_ing = "soggetto";
                            $causale = "OSAP";

                            break;

                        case "PUBBLICITA":

                            if($c=="U003")
                                $tipo_ing = "Servizio riscossione imposte provinciali sulla pubblicita'";
                            else
                                $tipo_ing = "Servizio riscossione imposte comunali sulla pubblicita'";
                            $utente_ing = "soggetto";
                            $causale = "";

                            break;

                        default:

                            $tipo_ing = "Servizio di XXXXXXXXX";
                            $causale = "XXXXXXXXX";
                            $utente_ing = "XXXXXXXX";

                            break;
                    }

                    $parametri = new parametri_annuali( $c , date("Y-m-d") , $settore);
                    $CAD = $parametri->CAD;//CAD
                    $para_diritto_min = $parametri->Diritto_Riscossione_Minimo;
                    $para_diritto_max = $parametri->Diritto_Riscossione_Massimo;
                    $CAN = $parametri->CAN;
                    $spese_notifica = $parametri->Spese_Notifica;
                    $spese_postali_ag = $parametri->Spese_Postali_AG;
                    $spese_postali = $parametri->Spese_Postali;
                    $giorni_diritto = $parametri->Giorni_Diritto;

                    //UTENTE
                    $utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
                    $nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
                    $utente_id = $utente->Comune_ID;
                    $PEC = $utente->PEC;
                    $a_addressRows = $utente->postalAddressRows();
                    $indirizzo_destinatario = $utente->righe_indirizzo();
                    $indirizzo_completo = $indirizzo_destinatario['Completo'];
                    $indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

                    if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
                        if($partita->Tipo == "CDS")
                            break;


                    //PARAMETRI RESPONSABILI
                    $par_responsabili = new parametri_responsabili($c, $settore);
                    $firme_responsabili = $par_responsabili->firme_responsabili();
                    $firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");


                    $atti = explode(" ", $soll->Note);
                    $ing_id = single_answer_query("SELECT ID FROM atto WHERE CC = '".$c."' AND Comune_ID = '".$atti[1]."' AND Atto = 'Ingiunzione'");

                    $ing = new atto( $ing_id , $c);
                    $anno_crono_ing = $ing->Anno_Cronologico;
                    $id_crono_ing = $ing->ID_Cronologico;
                    $data_notif_ing = $ing->Data_Notifica;
                    $protocollo_ing = $ing->Protocollo;

                    $a_codici = $partita->totaleCodici();
                    $totaleCheck = $a_codici["TOTALE"]+$soll->Spese_Notifica_Precedenti+$soll->Interessi;
                    $totaleCheck+= $soll->Interessi_Precedenti+$soll->Spese_Notifica+$soll->CAN+$soll->CAD;
                    if( number_format($totaleCheck,2)!=number_format($soll->Totale_Dovuto,2)){
                        alert("Il sollecito della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' stampato a causa di incoerenza dei dati!");
                        continue;
                    }

                    //IMPORTI
                    $tot_pagamenti_atti = $ing->pagamenti_completi();
                    $tot_pagamenti = $soll->pagamenti_completi() + $a_codici['PAGAMENTO'];
                    $diritto_min = $soll->Diritto_Riscossione_Minimo;
                    $diritto_max = $soll->Diritto_Riscossione_Massimo;

                    //TOTALI
                    $testo_tot_unico = "TOTALE COMPLESSIVO";

                    $tot_2 = number_format($diritto_max + $soll->Totale_Dovuto - $tot_pagamenti_atti,2,".","");

                    $conta_imp = 0;
                    $a_importiStampa = array();
                    $a_importiStampa[$conta_imp][0] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $a_importiStampa[$conta_imp][1] = $soll->Spese_Notifica_Precedenti;
                    $conta_imp++;
                    $a_importiStampa[$conta_imp][0] = "Interessi Ingiunzioni/Avvisi di Messa in Mora";
                    $a_importiStampa[$conta_imp][1] = $soll->Interessi+$soll->Interessi_Precedenti;
                    $conta_imp++;

                    if($diritto_max>0){
                        $a_importiStampa[$conta_imp][0] = "Oneri di riscossione Ingiunzioni/Avvisi di Messa in Mora";
                        $a_importiStampa[$conta_imp][1] = $diritto_max;
                        $conta_imp++;
                    }

                    $a_importiStampa[$conta_imp][0] = "per Spese postali/notifica del presente Sollecito di pagamento";
                    $a_importiStampa[$conta_imp][1] = $soll->Spese_Notifica;
                    $conta_imp++;
                    $a_importiStampa[$conta_imp][0] = "Pagamenti dei precedenti atti di accertamento";
                    $a_importiStampa[$conta_imp][1] = $tot_pagamenti;
                    $conta_imp++;
                    $a_importiStampa[$conta_imp][0] = $testo_tot_unico;
                    $a_importiStampa[$conta_imp][1] = $tot_2;

                    $comune_luogo = $gestore->Comune;
                    if($ufficio->Comune!="")
                        $comune_luogo = $ufficio->Comune;
                    $luogo_data = $comune_luogo.", li ".$data_stampa;

                    $para_ing = new parametri_testo_sollecito_ingiunzione(null);
                    $myID = $para_ing->CercaParametroData($c, date("Y-m-d"));
                    $testo = new parametri_testo_sollecito_ingiunzione($myID);

                    $oggetto = $testo->Oggetto;
                    SostituisciTestoTraGraffe ($oggetto, "{IDCRONOLOGICO}", $id_crono );
                    SostituisciTestoTraGraffe ($oggetto, "{ANNOCRONOLOGICO}", $anno_crono );
                    SostituisciTestoTraGraffe ($oggetto, "{RIFERIMENTO}", $rif_ing );

                    $sottotitolo = $testo->Sottotitolo;
                    SostituisciTestoTraGraffe ($sottotitolo, "{TIPORISCOSSIONE}", $tipo_ing);
                    SostituisciTestoTraGraffe ($sottotitolo, "{ENTEGESTITO}", $nome_com);

                    $primoTesto = $testo->Primo_Testo;
                    $info_atto = "";
                    if($id_crono_ing>0){
                        $info_atto.= "di ".strtoupper($ing->Atto)." N.".$id_crono_ing;
                        if($protocollo_ing!="")
                            $info_atto.= " ".$protocollo_ing;
                        $info_atto.= " / ".$anno_crono_ing;
                    }

                    if(from_mysql_date( $data_notif_ing )!="")
                        $info_atto.= ", notificata/o il ".from_mysql_date( $data_notif_ing ).", ";
                    SostituisciTestoTraGraffe ($primoTesto, "{INFOATTO}", $info_atto );
                    SostituisciTestoTraGraffe ($primoTesto, "{INFOCARTELLA}", $info_cart );

                    $pagamentoTesto = $testo->Pagamento;
                    SostituisciTestoTraGraffe ($pagamentoTesto, "{NUMEROCONTO}", $numeroContoCorrente );
                    SostituisciTestoTraGraffe ($pagamentoTesto, "{IBAN}", $iban );
                    SostituisciTestoTraGraffe ($pagamentoTesto, "{INTESTATARIOCONTO}", $intestatarioConto  );

                    $coazione = $testo->Coazione;
                    $coazione1 = $testo->Coazione_Caso_1;
                    $coazione2 = $testo->Coazione_Caso_2;
                    $coazione3 = $testo->Coazione_Caso_3;
                    $coazione4 = $testo->Coazione_Caso_4;

                    $datiGestore = $testo->Dati_Gestore;
                    SostituisciTestoTraGraffe ($datiGestore, "{FAX}", $gestore->Fax );

                    $rateizzazione = $testo->Rateizzazione;
                    SostituisciTestoTraGraffe ($rateizzazione, "{EMAIL}", $gestore->Mail );
                    SostituisciTestoTraGraffe ($rateizzazione, "{FAX}", $gestore->Fax );

                    $alternativa = $testo->Alternativa;
                    SostituisciTestoTraGraffe ($alternativa, "{TELEFONO}", $gestore->Telefono );
                    SostituisciTestoTraGraffe ($alternativa, "{SEDE}", $gestore->IndirizzoCompleto );

                    $informativa = $testo->Informativa;
                    $saluti = $testo->Saluti;

                    $qual_firma1 = $testo->Primo_Responsabile;
                    $firma1 = $testo->Nome_Primo_Responsabile;
                    $qual_firma2 = $testo->Secondo_Responsabile;
                    $firma2 = $testo->Nome_Secondo_Responsabile;

                    $testo_firma = array();

                    $array_variabili = array('{FUNZIONARIORESPONSABILE}','{RESPONSABILEPROCEDIMENTO}');

                    $variabile = estraiVariabile($qual_firma1, $array_variabili);
                    if($variabile == "{FUNZIONARIORESPONSABILE}")
                    {
                        if($gestore->Tipo == "Concessionario")
                            $testo_firma[1]['intestazione'] = "Il Legale Rappresentante";
                        else
                            $testo_firma[1]['intestazione'] = "Il Funzionario Responsabile";
                    }
                    else if($variabile == "{RESPONSABILEPROCEDIMENTO}")		$testo_firma[1]['intestazione'] = $firma_resp[2]['intestazione'];
                    else	$testo_firma[1]['intestazione'] = "";

                    $variabile = estraiVariabile($firma1, $array_variabili);
                    if($variabile == "{FUNZIONARIORESPONSABILE}")
                    {
                        $testo_firma[1]['nome'] = $firma_resp[1]['nome'];
                        $testo_firma[1]['firma'] = $firma_resp[1]['firma'];
                    }
                    else if($variabile == "{RESPONSABILEPROCEDIMENTO}")
                    {
                        $testo_firma[1]['nome'] = $firma_resp[2]['nome'];
                        $testo_firma[1]['firma'] = $firma_resp[2]['firma'];
                    }
                    else
                    {
                        $testo_firma[1]['nome'] = "";
                        $testo_firma[1]['firma'] = "";
                    }

                    $variabile = estraiVariabile($qual_firma2, $array_variabili);
                    if($variabile == "{FUNZIONARIORESPONSABILE}")
                    {
                        if($gestore->Tipo == "Concessionario")
                            $testo_firma[2]['intestazione'] = "Il Legale Rappresentante";
                        else
                            $testo_firma[2]['intestazione'] = "Il Funzionario Responsabile";
                    }
                    else if($variabile == "{RESPONSABILEPROCEDIMENTO}")		$testo_firma[2]['intestazione'] = $firma_resp[2]['intestazione'];
                    else	$testo_firma[2]['intestazione'] = "";

                    $variabile = estraiVariabile($firma2, $array_variabili);
                    if($variabile == "{FUNZIONARIORESPONSABILE}")
                    {
                        $testo_firma[2]['nome'] = $firma_resp[1]['nome'];
                        $testo_firma[2]['firma'] = $firma_resp[1]['firma'];
                    }
                    else if($variabile == "{RESPONSABILEPROCEDIMENTO}")
                    {
                        $testo_firma[2]['nome'] = $firma_resp[2]['nome'];
                        $testo_firma[2]['firma'] = $firma_resp[2]['firma'];
                    }
                    else
                    {
                        $testo_firma[2]['nome'] = "";
                        $testo_firma[2]['firma'] = "";
                    }

                    $firmaAutografa = $testo->Firma_Autografa;

                    if ($stampa_select == "FLUSSO")
                    {

                    }	//fine FLUSSO
                    else
                    {

                        /**
                        ///////////////////////////////		PDF	    //////////////////////////////////
                         */

                        /**
                         * 		//////////////	PAGINA 1	//////////////
                         */

                        if ($stampa_select == "DEFINITIVA")
                        {
                            $file_stampa = $stampa_dir."/Sollecito_".$c."_".$anno_crono."_".$id_crono."_".to_mysql_date($data_definitiva).".pdf";

                            /**
                            ///////////////////////////////		PDF	    //////////////////////////////////
                             */

                            $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
                            $pdf->SetLineWidth(0.2);
                            $pdf->SetMargins(7.0, 10.0, 7.0);
                            $width_page = $pdf->getPageWidth() - 7;

                            /**
                            //////////////////////////////////////////////////////////////////////////////
                             */

                        }

                        $fontSize = 9;
                        $pdf->setPrintHeader(false);
                        $pdf->SetAutoPageBreak(false);
                        $pdf->SetCellPadding(0);
                        $pdf->AddPage('P');

                        if($stampa_select == "PROVVISORIA")
                            $pdf->stampa_provvisoria();

//////////////	CORPO Pagina 1	//////////////
                        $cfpi = "";
                        if($utente->Codice_Fiscale!=""){
                            $cfpi = $utente->Codice_Fiscale;
                            $cfpiType = "CODICE FISCALE";
                        }
                        else{
                            if($utente->Partita_Iva!="" && $utente->Partita_Iva!="00000000000")
                                $cfpi = $utente->Partita_Iva;
                            $cfpiType = "PARTITA IVA";
                        }

                        $pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);
                        $params = array(
                            "Utente_ID"  => $utente_id,
                            "CC" => $c,
                            "IndirizzoDestinatario" => $indirizzo_destinatario,
                            "NomeUtente" => $indirizzo_destinatario['Destinatario'],
                            "Partita_ID" => $ID_partita,
                            "Anno_Riferimento" => $anno_rif,
                            "Luogo_Data" => $luogo_data,
                            "TipoCodice" => null,
                            "Protocol_ID" => $protocollo,
                            "Protocol_Date" => $data_protocollo,
                            "CF_PI" => $cfpi,
                            "UserCode_Type" => $cfpiType
                        );
                        $pdf->recipientHeaderPdf($params);
//                        $pdf->destinatario_intestazione_pdf($utente_id, $c, $indirizzo_destinatario['Destinatario'], $ID_partita, $anno_rif, $indirizzo_destinatario,
//                            $luogo_data, null, $protocollo, $data_protocollo );
                        $pdf->oggetto_pdf($oggetto, $sottotitolo, $primoTesto);

                        //IMPORTI SOLLECITO
                        $array_width = array(160,10,10,15);
                        $array_align = array("R","C","R","R");

                        $pdf->SetFont('Arial', '', $fontSize);
                        for($countTrib = 0; $countTrib<count($partita->a_tributi);$countTrib++){
                            if($partita->a_tributi[$countTrib]->Tipo_Codice=="PAGAMENTO")
                                continue;

                            $pdf->Ln(1);
                            $array_value = array($partita->a_tributi[$countTrib]->Testo_Codice, "+","Euro" ,number_format($partita->a_tributi[$countTrib]->Imposta,2,",","."));
                            crea_riga($pdf, $array_width, $array_value, $linea = "no" , $style=array() , $array_align);
                        }

                        $tot = array_sum( $array_width );
                        for($countImporti = 0; $countImporti<count($a_importiStampa);$countImporti++){
                            if($a_importiStampa[$countImporti][1]>0) {
                                $pdf->Ln(1);

                                $operando = "+";
                                if ($countImporti == ($conta_imp-1))
                                    $operando = "-";
                                else if ($countImporti > ($conta_imp-1)) {
                                    if ($countImporti == $conta_imp) {
                                        $margine = $pdf->getMargins();
                                        $pdf->Line(7, $pdf->getY(), 203, $pdf->getY(), array());
                                        $pdf->Ln(1);
                                        $pdf->SetFont('Arial', 'B', $fontSize);
                                    }
                                    $operando = "=";
                                }


                                $array_value = array($a_importiStampa[$countImporti][0], $operando, "Euro", number_format($a_importiStampa[$countImporti][1], 2, ",", "."));
                                crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
                            }

                        }

                        $pdf->Ln(3);

                        //PAGAMENTO
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $pagamentoTesto."\n" , 0, 'J', 0, 1);
                        $pdf->Ln(3);
                        $pdf->MultiCell(0, 0, $coazione."\n", 0, 'J', 0, 1);
                        $pdf->MultiCell(0, 0, $coazione1, 0, 'L', 0, 1);
                        $pdf->MultiCell(0, 0, $coazione2, 0, 'L', 0, 1);
                        $pdf->MultiCell(0, 0, $coazione3, 0, 'L', 0, 1);
                        $pdf->MultiCell(0, 0, $coazione4, 0, 'L', 0, 1);
                        $pdf->Ln(3);

                        $pdf->MultiCell(0, 0, $datiGestore."\n" , 0, 'J', 0, 1);
                        $pdf->MultiCell(0, 0, $rateizzazione."\n" , 0, 'J', 0, 1);
                        $pdf->MultiCell(0, 0, $alternativa."\n" , 0, 'J', 0, 1);
                        $pdf->Ln(3);

                        $pdf->MultiCell(0, 0, $informativa."\n" , 0, 'J', 0, 1);
                        $pdf->MultiCell(0, 0, $saluti."\n" , 0, 'J', 0, 1);
                        $pdf->Ln(4);

                        //RESPONSABILI
                        $pdf->firma_pdf($testo_firma);

                        //PIE DI PAGINA 1
                        $pdf->SetY(-15);
                        $pdf->SetFont('helvetica', 'N', 7);
                        $pdf->Cell(0, 5, "Pag. 1/1 - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');

                        $pdf->SetMargins(7,10,7);
                        $pdf->AddPage('P');
                        $pdf->setPrintHeader(false);
                        if($stampa_select == "PROVVISORIA")
                            $pdf->stampa_provvisoria();


                        $riga1causale = "Sollecito di ingiunzione n.".$id_crono." del ".$anno_crono." Rif.".$rif_ing;
                        $riga2causale = "PAGAMENTO ENTRO 10 GIORNI DAL RICEVIMENTO";
                        $quinto_campo = $soll->quinto_campo();//CODICE CLIENTE (QUINTO CAMPO)

                        /**
                         * 		//////////////	PAGINA 3 BOLLETTINO	//////////////
                         */
                        if(($autorizzazione_1!=false || $td_1=="123"))
                        {

                            $pdf->setPrintHeader(false);
                            $pdf->setPrintFooter(false);
                            $pdf->AddPage('L');
                            if($stampa_select == "PROVVISORIA")
                                $pdf->stampa_provvisoria();

                            $pdf->SetMargins(0, 0, 0);

//////////////	CORPO Pagina 3	//////////////

                            if($autorizzazione_1!=false || $td_1=="123")
                            {
                                $pdf->crea_bollettino();
                                if($stemma == "")
                                    $pdf->logo_bollettino($image_file);
                                else if($stemma == "ente")
                                    $pdf->logo_bollettino($stemmaComune);
                                else if($stemma == "gestore")
                                    $pdf->logo_bollettino($stemmaGestore);

                                $pdf->scelta_td_bollettino($td_1, $quinto_campo , number_format($tot_2,2,",","") , $ctrl_importo_1 , $numeroContoCorrente );
                                $pdf->iban_bollettino($iban);
                                $pdf->intestatario_bollettino($intestatarioConto);
                                $pdf->causale_bollettino($riga1causale, $riga2causale);
                                $pdf->payerZone($indirizzo_destinatario['Destinatario'], $a_addressRows);
                                $pdf->autorizzazione_bollettino($autorizzazione_1);
                            }

                            if($stampa_select == "PROVVISORIA")
                                $pdf->stampa_provvisoria();

                            /**
                             * 		//////////////	PAGINA VUOTA	//////////////
                             */

                            $pdf->setPrintHeader(false);
                            $pdf->SetAutoPageBreak(false);
                            $pdf->SetCellPadding(0);
                            $pdf->AddPage('L');

                        }

                        /**
                        //////////////////////////////////////////////////////////////////////////////
                         */
                        if($stampa_select=="DEFINITIVA")
                        {
                            mysql_query('BEGIN');

                            $salva = new atto($soll->ID, $c);

                            if($salva->Stato_Stampa == "Da stampare")
                            {
                                $salva->Data_Stampa = to_mysql_date($data_stampa);
                                $salva->Stato_Stampa = "Stampato";

                                $control_salva = $salva->Update($soll->ID, true);

                                if( $control_salva )
                                {
                                    mysql_query('COMMIT');

                                    $pdf->Output( $file_stampa , 'F');

                                    $arrayConcat[] = $file_stampa;
                                }
                                else
                                {
                                    mysql_query('ROLLBACK');
                                }
                            }
                        }

                    }  // fine PDF

                    $array_stampati[] = $array_atti[$l]['ID'];
                    $cont_result++;

                    break;		//Una partita pu� avere un solo intestatario per cui una volta trovato si pu� uscire dal ciclo degli utenti

                }//CHIUSURA IF PARTITA/UTENTE

            }//CHIUSURA FOR UTENTI

            break;		//Un atto pu� corrispondere ad una sola partita per cui una volta trovato si pu� uscire dal ciclo delle partite

        }//CHIUSURA IF ATTO/PARTITA

    }//CHIUSURA PARTITE

}//CHIUSURA ATTII

if ($stampa_select == "FLUSSO")
{

}
else if($stampa_select=="PROVVISORIA")
{
    cancella_files($stampa_dir, 7);
    if($cont_result == 0)
    {
        echo "<script>nessun_risultato();</script>";
    }
    else
    {
        $pdf->Output( $file_stampa, 'F' );
        echo "<script>fine('Elaborazione completata');</script>";
    }

}
else if($stampa_select == "DEFINITIVA")
{
    cancella_files($concat_dir, 7);
    if($cont_result == 0)
    {
        echo "<script>nessun_risultato();</script>";
    }
    else
    {
        function getmicrotime(){
            list($usec, $sec) = explode(" ",microtime());
            return ((float)$usec + (float)$sec);
        }

        $fileCompletoUnito = $concat_dir."/Solleciti_Merge_".$c."_".to_mysql_date($data_definitiva)."_".$ora_file.".pdf";

        echo "<script>merge();</script>";
        flush(); ob_flush(); flush(); ob_flush();
        sleep(1);

        $mergepdf = new Concat_Pdf();
        $mergepdf->setFiles($arrayConcat);

        $time_start = getmicrotime();//sec iniziali
        $mergepdf->Concat(true);
        $time_end = getmicrotime();//sec finali
        $time = $time_end - $time_start;//differenza in secondi

        $tempo_previsto_sec = $time * 20;
        if($tempo_previsto_sec<55)
            $tempo_previsto = "1 minuto";
        else
            $tempo_previsto = floor($tempo_previsto_sec/60+1)." minuti";

        echo "<script>fine_merge(\"Creazione file in corso... Il tempo previsto per le operazioni e' di circa ".$tempo_previsto.".\");</script>";
        flush(); ob_flush(); flush(); ob_flush();

        set_time_limit($tempo_previsto_sec+200);
        flush(); ob_flush(); flush(); ob_flush();
        $mergepdf->Output($fileCompletoUnito, "F");

        $vedi_file = mostra_file_path($fileCompletoUnito);

        echo "<script>fine_e_apri('Elaborazione completata',\"".$vedi_file."\");</script>";

        // 		$Text = json_encode($array_stampati);
        // 		$RequestText = urlencode($Text);
        // 		echo "<script>fine2('Elaborazione completata');</script>";
        // 		echo "<script>atti_stampati('".$RequestText."');</script>";

    }
}
else if($stampa_select == "CRONOLOGICI")
{
    if($cont_result == 0)
    {
        echo "<script>nessun_risultato();</script>";
    }
    else
    {

        echo "<form name='crono_form' id='crono_form' method='post' action='cronologici.php'>";
        echo "<input type=hidden name=atto_val value='Ingiunzione'>";
        echo "<input type=hidden name='c' value=".$c.">";
        echo "<input type=hidden name='a' value=".$a.">";
        for($t=0; $t<count($array_cronologici);$t++)
        {
            echo "<input type=hidden name=array_crono[] value='".$array_cronologici[$t]."'>";
        }

        echo "</form>";

        // 			$Text = json_encode($array_cronologici);
        // 			$RequestText = urlencode($Text);

        echo "<script>fine2('Elaborazione completata');</script>";
        echo "<script>cronologici('');</script>";
    }
}



?>

</body>
</html>