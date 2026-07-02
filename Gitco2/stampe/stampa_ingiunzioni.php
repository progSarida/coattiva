<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

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

ini_set('memory_limit', '1024M');


$a = get_var('a');
$c = get_var('c');
$stampa_select = strtoupper(get_var('stampa_select'));

$PrinterId = get_var("PrinterId");
$PrintTypeId = get_var("PrintTypeId");
$cls_db = new cls_db();
$cls_params = new cls_printer_params();
$a_printerParams = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_params->getPrinterChargeQuery($PrinterId,$PrintTypeId)));
$a_printTypes = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_params->getPrintTypes($PrintTypeId)));
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
if($ufficio->Fax!="")
    $faxGestore = $ufficio->Fax;
else
    $faxGestore = $gestore->Fax;

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
$para_ing = new parametri_testo_ingiunzione(NULL);
$myID = $para_ing->CercaParametroData($c, date("Y-m-d"),"si");
if($myID==null)
    $chiudi = "chiudi_finestra()";

$testo = new parametri_testo_ingiunzione($myID);
$informazioni = $testo->Informazioni_Testo;
$data_testo = $testo->Data_Creazione_Parametri;

if($stampa_select=="PROVVISORIA" || $stampa_select == "DEFINITIVA")
{
    if($informazioni=="")
    {
        alert("Il campo Informazioni non e' stato compilato! Stampa annullata!");
        echo "<script>window.close();</script>";
    }

    if( date('Y-m-d') > date( "Y-m-d" , strtotime( $data_testo."+1 month" )) ){
        alert("Il salvataggio del testo e' stato effettuato da piu' di 30 giorni. Ricontrollare il campo Informazioni e salvare!");
        echo "<script>window.close();</script>";
    }
}

if($stampa_select == "PROVVISORIA")
{

    $stampa_dir = crea_dir( ATTI ."/". $c . "/Ingiunzioni/STAMPE PROVVISORIE" );

    $file_stampa = $stampa_dir."/Ingiunzioni_Provvisorie_".$c."_".$data_file."_".$ora_file.".pdf";
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
            <font class="titolo font18 text_center">Stampa Ingiunzioni</font>

            <br><br>

            <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>

            <br>

            <div class="table_interna text_center" id="progressbar2" style="height:55px;"><div class="text_center" id="barlabel2"></div></div>

        </td>
    </tr>
</table>

<?php
$flag_ristampa = get_var('flag_ristampa');
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

$tipo_partita = get_var('tipo_partita');
$a_taxType = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT Id FROM tax_type WHERE Name=\"".$tipo_partita."\""));
$TaxTypeId = $a_taxType['Id'];

$par_generali = new parametri_generali($c, $tipo_partita);
if($stampa_select=="FLUSSO"){
    if($par_generali->Spese_Anticipate==""){
        alert('Le Spese anticipate devono essere impostate nei parametri generali '.$tipo_partita.'!');
        echo "<script>chiudi_finestra();</script>";
    }
    if($par_generali->SMA==""){
        alert('La distinta SMA deve essere impostata nei parametri generali '.$tipo_partita.'!');
        echo "<script>chiudi_finestra();</script>";
    }
    if($par_generali->Restituzione1=="" || $par_generali->Restituzione2=="" || $par_generali->Restituzione3=="" || $par_generali->Restituzione4=="" || $par_generali->Restituzione5==""){
        alert('I campi della restituzione devono essere impostati nei parametri generali '.$tipo_partita.'!');
        echo "<script>chiudi_finestra();</script>";
    }
}


$ctrl_responsabili = new parametri_responsabili($c, null);
$verifica_parametri_resp = $ctrl_responsabili->controllo_parametri($c,$tipo_partita);
if($verifica_parametri_resp!==true)
{
    alert('Parametri Responsabili '.$verifica_parametri_resp.' incompleti!');
    echo "<script>chiudi_finestra();</script>";
}

$stato_esec = get_var('stato_esec');
$stato_notif = get_var('stato_notif');
$stato_stampa = get_var('stato_stampa');

$ordinamento = get_var('ordinamento');

$flag = get_var('atto_precedente');
if($flag!="si")	$flag="no";

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
if( $stampa_select != "FLUSSO" )
{
    $campi_stati = array("atto.Stato" , "atto.Stato_Stampa" , "atto.Stato_Esecuzione");
    $valori_stati = array ( $stato_notif , $stato_stampa , $stato_esec );

    $query_stati = where_campi($campi_stati, $valori_stati)." AND Cronologico_Vecchio != 'si' ";
}
else
{
    $campi_stati = array("atto.Stato" , "atto.Stato_Stampa" , "atto.Stato_Esecuzione");
    $valori_stati = array ( $stato_notif , "Stampato" , $stato_esec );

    $query_stati = where_campi($campi_stati, $valori_stati);

    if($stato_stampa == "Da stampare")
        $query_stati.= " AND ( atto.Data_Flusso = '0000-00-00' OR atto.Data_Flusso is null ) AND Cronologico_Vecchio != 'si' ";
    else if($stato_stampa == "Stampato")
        $query_stati.= " AND ( atto.Data_Flusso != '0000-00-00' AND atto.Data_Flusso is not null ) AND Cronologico_Vecchio != 'si' ";

}

$campi_array = array ("Data_Elaborazione" , "Data_Notifica" , "Data_Stampa" );
$array_da_data = array( to_mysql_date($da_elab) , to_mysql_date($da_notif) , to_mysql_date($da_stampa) );
$array_a_data = array( to_mysql_date($a_elab) , to_mysql_date($a_notif) , to_mysql_date($a_stampa) );

$wherePrinter = "PrinterId=".$PrinterId." AND PrintTypeId=".$PrintTypeId;

$query_date = da_a_data_array_order( $c , "Ingiunzione" , $campi_array , $array_da_data , $array_a_data , $query_stati, $ordinamento, $wherePrinter );
$array_atti = mysql_array($query_date);

$num_atti = count($array_atti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

$anno_current = date("Y");
$array_stampati = array();
$array_cronologici = array();


if($stampa_select == "FLUSSO")
{
    if($stato_stampa=="Da stampare")
    {

        mysql_query('BEGIN');

        $flusso_dir = crea_dir( ATTI ."/". $c . "/Ingiunzioni/FLUSSI/" );

        //INTESTAZIONE FLUSSO
        $pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
        $array_intestazione = array();

        $array_intestazione[] = "CODICE_CATASTALE";
        $array_intestazione[] = "TIPOLOGIA_STAMPA";
        $array_intestazione[] = "TIPOLOGIA_ATTO";
        $array_intestazione[] = "ID_CRONOLOGICO";
        $array_intestazione[] = "ANNO_CRONOLOGICO";

        $array_intestazione[] = "QUINTO_CAMPO";
        $array_intestazione[] = "STEMMA";

        $array_intestazione[] = "GESTORE_1";
        $array_intestazione[] = "GESTORE_2";
        $array_intestazione[] = "GESTORE_3";
        $array_intestazione[] = "GESTORE_4";
        $array_intestazione[] = "GESTORE_5";
        $array_intestazione[] = "GESTORE_6";
        $array_intestazione[] = "GESTORE_7";

        $array_intestazione[] = "UFFICIO_1";
        $array_intestazione[] = "UFFICIO_2";
        $array_intestazione[] = "UFFICIO_3";
        $array_intestazione[] = "UFFICIO_4";
        $array_intestazione[] = "UFFICIO_5";
        $array_intestazione[] = "UFFICIO_6";

        $array_intestazione[] = "CODICE_UTENTE";
        $array_intestazione[] = "NUMERO_PARTITA";
        $array_intestazione[] = "LUOGO_DATA";
        $array_intestazione[] = "DESTINATARIO";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_1";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_2";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_3";
        $array_intestazione[] = "INDIRIZZO_DESTINATARIO_4";

        $array_intestazione[] = "OGGETTO";
        $array_intestazione[] = "SOTTOTITOLO_OGGETTO";
        $array_intestazione[] = "TESTO_1";

        $array_intestazione[] = "PREMESSO";
        $array_intestazione[] = "PREMESSO_TESTO";
        $array_intestazione[] = "INFO_CARTELLA";
        $array_intestazione[] = "TESTO_2";
        $array_intestazione[] = "ATTO_PRECEDENTE";
        $array_intestazione[] = "TESTO_3";

        $array_intestazione[] = "INGIUNGE";
        $array_intestazione[] = "INGIUNGE_TESTO";

        for($i_header=1;$i_header<=12;$i_header++){
            $array_intestazione[] = "TESTO_IMPORTO_".$i_header;
            $array_intestazione[] = "OPERANDO_".$i_header;
            $array_intestazione[] = "IMPORTO_".$i_header;
        }
//
//	$array_intestazione[] = "TESTO_IMPORTO_SANZIONE_ORIGINALE";
//	$array_intestazione[] = "SANZIONE_ORIGINALE";
//	$array_intestazione[] = "TESTO_IMPORTO_MAGGIORAZIONE_SANZIONE_ORIGINALE";
//	$array_intestazione[] = "MAGGIORAZIONE_SANZIONE_ORIGINALE";
//	$array_intestazione[] = "TESTO_IMPORTO_SPESE_PRECEDENTI";
//	$array_intestazione[] = "SPESE_PRECEDENTI";
//	$array_intestazione[] = "TESTO_IMPORTO_MAGGIORAZIONE";
//	$array_intestazione[] = "MAGGIORAZIONE";
//	$array_intestazione[] = "TESTO_IMPORTO_SPESE_NOTIFICA";
//	$array_intestazione[] = "SPESE_NOTIFICA";
//	$array_intestazione[] = "TESTO_IMPORTO_TOTALE_PARZIALE";
//	$array_intestazione[] = "TOTALE_PARZIALE";
//	$array_intestazione[] = "TESTO_IMPORTO_TOTALE_PAGAMENTI";
//	$array_intestazione[] = "TOTALE_PAGAMENTI";
//	$array_intestazione[] = "TESTO_IMPORTO_TOTALE_1";
//	$array_intestazione[] = "TOTALE_COMPLESSIVO_1";
//	$array_intestazione[] = "TESTO_IMPORTO_TOTALE_2";
//	$array_intestazione[] = "TOTALE_COMPLESSIVO_2";

        $array_intestazione[] = "FINALE_PAGINA_1";

        $array_intestazione[] = "FIRMA_INTESTAZIONE_SINISTRA";
        $array_intestazione[] = "FIRMA_SINISTRA";
        $array_intestazione[] = "FIRMA_NOME_SINISTRA";
        $array_intestazione[] = "FIRMA_INTESTAZIONE_DESTRA";
        $array_intestazione[] = "FIRMA_DESTRA";
        $array_intestazione[] = "FIRMA_NOME_DESTRA";

        $array_intestazione[] = "INFORMAZIONI";
        $array_intestazione[] = "INFORMAZIONI_TESTO";

        $array_intestazione[] = "TOT_1";
        $array_intestazione[] = "TESTO_TOT_1";
        $array_intestazione[] = "TOT_2";
        $array_intestazione[] = "TESTO_TOT_2";
        $array_intestazione[] = "TOT_COMPLESSIVO";
        $array_intestazione[] = "TESTO_TOT_COMPLESSIVO";
        $array_intestazione[] = "DIRITTO_RISCOSSIONE";
        $array_intestazione[] = "DIRITTO_RISCOSSIONE_TESTO";

        $array_intestazione[] = "OPPOSIZIONE";
        $array_intestazione[] = "TESTO_OPPOSIZIONE";
        $array_intestazione[] = "CREDITI_TRIBUTARI";
        $array_intestazione[] = "CREDITI_NON_TRIBUTARI";

        $array_intestazione[] = "PROVVEDIMENTO";
        $array_intestazione[] = "TESTO_PROVVEDIMENTO";

        $array_intestazione[] = "ESECUTIVITA";
        $array_intestazione[] = "TESTO_ESECUTIVITA";

        $array_intestazione[] = "PAGAMENTO";
        $array_intestazione[] = "TESTO_PAGAMENTO_1";
        $array_intestazione[] = "TESTO_PAGAMENTO_2";

        $array_intestazione[] = "AVVERTENZA";
        $array_intestazione[] = "TESTO_AVVERTENZA_1";
        $array_intestazione[] = "TESTO_AVVERTENZA_2";
        $array_intestazione[] = "TESTO_AVVERTENZA_3";

        $array_intestazione[] = "INTESTAZIONE_RELATA";
        $array_intestazione[] = "SOTTOINTESTAZIONE_RELATA";
        $array_intestazione[] = "RELATA";
        $array_intestazione[] = "FIRMA_INTESTAZIONE_NOTIFICA";
        $array_intestazione[] = "FIRMA_NOTIFICA";
        $array_intestazione[] = "FIRMA_NOME_NOTIFICA";

        $array_intestazione[] = "BOLL_1_STEMMA";
        $array_intestazione[] = "BOLL_1_TD";
        $array_intestazione[] = "BOLL_1_AUTORIZZAZIONE";
        $array_intestazione[] = "BOLL_1_CONTO";
        $array_intestazione[] = "BOLL_1_INTESTATARIO";
        $array_intestazione[] = "BOLL_1_IBAN";
        $array_intestazione[] = "BOLL_1_IMPORTO";
        $array_intestazione[] = "BOLL_1_IMPORTO_LETTERE";
        $array_intestazione[] = "BOLL_1_PAGANTE_RIGA_1";
        $array_intestazione[] = "BOLL_1_PAGANTE_RIGA_2";
        $array_intestazione[] = "BOLL_1_PAGANTE_RIGA_3";
        $array_intestazione[] = "BOLL_1_CAUSALE_RIGA_1";
        $array_intestazione[] = "BOLL_1_CAUSALE_RIGA_2";
        $array_intestazione[] = "BOLL_1_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_1_BC_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_1_BC_IMPORTO";
        $array_intestazione[] = "BOLL_1_BC_CONTO";
        $array_intestazione[] = "BOLL_1_BC_TD";

        $array_intestazione[] = "BOLL_2_STEMMA";
        $array_intestazione[] = "BOLL_2_TD";
        $array_intestazione[] = "BOLL_2_AUTORIZZAZIONE";
        $array_intestazione[] = "BOLL_2_CONTO";
        $array_intestazione[] = "BOLL_2_INTESTATARIO";
        $array_intestazione[] = "BOLL_2_IBAN";
        $array_intestazione[] = "BOLL_2_IMPORTO";
        $array_intestazione[] = "BOLL_2_IMPORTO_LETTERE";
        $array_intestazione[] = "BOLL_2_PAGANTE_RIGA_1";
        $array_intestazione[] = "BOLL_2_PAGANTE_RIGA_2";
        $array_intestazione[] = "BOLL_2_PAGANTE_RIGA_3";
        $array_intestazione[] = "BOLL_2_CAUSALE_RIGA_1";
        $array_intestazione[] = "BOLL_2_CAUSALE_RIGA_2";
        $array_intestazione[] = "BOLL_2_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_2_BC_CODICE_CLIENTE";
        $array_intestazione[] = "BOLL_2_BC_IMPORTO";
        $array_intestazione[] = "BOLL_2_BC_CONTO";
        $array_intestazione[] = "BOLL_2_BC_TD";

        $array_intestazione[] = "PROTOCOLLO";
        $array_intestazione[] = "DATA_PROTOCOLLO";

        $array_intestazione[] = "INTESTATARIO_SMA";
        $array_intestazione[] = "NUMERO_SMA";
        $array_intestazione[] = "SPESE_ANTICIPATE";

        $array_intestazione[] = "MOD23_SOGGETTO_MITTENTE";
        $array_intestazione[] = "MOD23_ENTE_GESTITO";
        $array_intestazione[] = "MOD23_RECAPITO_SOGGETTO";
        $array_intestazione[] = "MOD23_INDIRIZZO_SOGGETTO";
        $array_intestazione[] = "MOD23_CITTA_SOGGETTO";

        $myFlusso = new flussi ($flusso_dir, "flusso", "ingiunzioni", $c, $anno_current, "ultimoFlusso atto", to_mysql_date($data_definitiva), $ora_file, "txt");

        $myFlusso->AggiungiIntestazioneFlusso($array_intestazione);

        $num_flusso = array();
        $anno_flusso = array();
        $data_flusso = array();

    }
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
        $stampa_dir = crea_dir( ATTI ."/". $c . "/Ingiunzioni/STAMPE DEFINITIVE" );
        $concat_dir = crea_dir( ATTI ."/". $c . "/Ingiunzioni/STAMPE CONCATENATE" );
        $arrayConcat = Array();
    }

}

$cont_result = 0;
for( $l=0; $l < $num_atti; $l++ )//FOR ATTI
{
    set_time_limit(200);
    echo "<script>update(".ceil($l*100/$num_atti).");</script>";

    flush();
    ob_flush();
    flush();
    ob_flush();

    for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
    {
        if($tipo_partita != "")
            if($array_partite[$k]['Tipo']!=$tipo_partita)
                continue;

        if( $array_atti[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF ATTO/PARTITA
        {
            for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
            {
                if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
                {
                    set_time_limit(200);

                    //INGIUNZIONE
                    $ing = new atto( $array_atti[$l]['ID'], $c );

                    if($stato_stampa=="Da stampare")
                        if($ing->Rettifica_Flag == "si" || $ing->Rielabora_Flag == "si")
                            break;

                    $ID_ing = $ing->Comune_ID;
                    $anno_crono = $ing->Anno_Cronologico;
                    $id_crono = $ing->ID_Cronologico;
                    $rif = $ing->Riferimento;
                    $info_cart = strtoupper($ing->Info_Cartella);

                    if($stato_stampa!="Stampato")
                        $data_stampa = $data_definitiva;
                    else
                        $data_stampa = from_mysql_date($ing->Data_Stampa);

                    //ESCLUSIONI
                    if($stampa_select == "PROVVISORIA")
                    {
                        if($ing->Data_Notifica != null && $ing->Data_Notifica != '0000-00-00')
                            break;
                    }
                    else if($stampa_select == "CRONOLOGICI")
                    {
                        if($ing->ID_Cronologico == "0" && $ing->Anno_Cronologico == "0" && $ing->Cronologico_Vecchio != "si")
                        {
                            $array_cronologici[] = $ing->ID;
                            $cont_result++;
                        }

                        break;
                    }
                    else if($stampa_select == "DEFINITIVA")
                    {
                        if($ing->ID_Cronologico == "0" || $ing->Anno_Cronologico == "0" || $ing->Cronologico_Vecchio == "si")
                            break;

                        if($stato_stampa == "Stampato" && $flag_ristampa!="y")
                        {
                            if($ing->Cronologico_Vecchio!="si")
                            {
                                $file_stampa_singola = $stampa_dir."/Ingiunzione_".$c."_".$anno_crono."_".$id_crono."_".to_mysql_date($data_stampa).".pdf";
                                $arrayConcat[] = $file_stampa_singola;

                                $array_stampati[] = $ing->ID;
                                $cont_result++;
                            }

                            break;
                        }
                    }
                    else if($stampa_select == "FLUSSO")
                    {
                        if($stato_stampa == "Stampato")
                        {
                            $array_stampati[] = $ing->ID;
                            $cont_result++;

                            break;
                        }
                        else {
                            if($ing->Rettifica_Flag == "si" || $ing->Rielabora_Flag == "si")
                                break;

                            if($ing->Motivo_Notifica>0)
                                break;
                        }
                    }

                    //PARTITA
                    $partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
                    $ID_partita = $partita->Comune_ID;
                    $anno_rif = $partita->Anno_Riferimento;
                    $settore = $partita->Tipo;
                    $sottosettore = $partita->Sottotipo;

                    $a_codici = $partita->totaleCodici();

                    $totaleCheck = $a_codici["TOTALE"]+$ing->Spese_Notifica_Precedenti+$ing->Interessi;
                    $totaleCheck+= $ing->Interessi_Precedenti+$ing->Spese_Notifica+$ing->CAN+$ing->CAD;
                    if( number_format($totaleCheck,2)!=number_format($ing->Totale_Dovuto,2)){
                        alert("L'Ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' stampata a causa di incoerenza dei dati!");
                        break;
                    }

                    if (($key = array_search("5243", $partita->a_tributi)) !== false) {

                        unset($partita->a_tributi[$key]);
                    }


                    //UTENTE
                    $utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
                    $nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$utente->Sigla_Forma_Giuridica;
                    $utente_id = $utente->Comune_ID;
                    $PEC = $utente->PEC;
                    $indirizzo_destinatario = $utente->righe_indirizzo();
                    $a_addressRows = $utente->postalAddressRows();
                    $indirizzo_completo = $indirizzo_destinatario['Completo'];
                    $indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

                    if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
                        break;

                    //PARAMETRI RESPONSABILI
                    $par_responsabili = new parametri_responsabili($c, $settore);
                    $firme_responsabili = $par_responsabili->firme_responsabili();
                    $firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");
// 						if($firma_resp[1]['firma']=="" || $firma_resp[2]['firma']=="")
// 						{
// 							alert('Parametri Responsabili '.$settore.' incompleti!');
// 							$chiudi = "<script>chiudi_finestra();</script>";
// 						}

                    echo $chiudi;

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
                            $causale = "TOSAP";

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

                    //PARAMETRI ANNUALI
                    $parametri = new parametri_annuali( $c , date("Y-m-d") , $settore);
                    $CAD = $parametri->CAD;//CAD
                    $para_diritto_min = $parametri->Diritto_Riscossione_Minimo;
                    $para_diritto_max = $parametri->Diritto_Riscossione_Massimo;
                    $CAN = $parametri->CAN;
                    $spese_notifica = $parametri->Spese_Notifica;
                    $spese_postali_ag = $parametri->Spese_Postali_AG;
                    $spese_postali = $parametri->Spese_Postali;
                    $giorni_diritto = $parametri->Giorni_Diritto;

                    $parametri_ricorso = new parametri_ricorso($c);
                    $giorni_ctp = $parametri_ricorso->Termini_Commissione_Tributaria_Provinciale;
                    $giorni_giust_ord = $parametri_ricorso->Termini_Giustizia_Ordinaria;

                    $terminiCommTrib = " (entro ".$giorni_ctp." giorni dalla notifica)";
                    $terminiGiustiziaOrd = " (entro ".$giorni_giust_ord." giorni dalla notifica)";

                    //PARAMETRI PAGAMENTO
                    $par_pagamento = new parametri_pagamento( $c, $settore );
                    $numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
                    $intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
                    $iban = $par_pagamento->IBAN;	//IBAN
                    if($iban!="")
                        $iban_testo = " (IBAN ".$iban.")";
                    else
                        $iban_testo = "";

                    $autorizzazione_1 = $par_pagamento->testo_autorizzazione(1);//AUTORIZZAZIONE BOLLETTINO 1
                    $autorizzazione_2 = $par_pagamento->testo_autorizzazione(2);//AUTORIZZAZIONE BOLLETTINO 2
                    $td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
                    $td_2 = $par_pagamento->Bollettino_2;//TD BOLLETTINO 2
                    $ctrl_importo_1 = $par_pagamento->Importo_1;
                    $ctrl_importo_2 = $par_pagamento->Importo_2;

                    $stemma = $par_pagamento->Stemma;
                    if($stemma == "")
                        $stemma_bol_1 = $stemma_image_file;
                    else if($stemma == "ente")
                        $stemma_bol_1 = $stemma_image_comune;
                    else if($stemma == "gestore")
                        $stemma_bol_1 = $stemma_image_gestore;
                    else
                        $stemma_bol_1 = "";

                    $stemma_2 = $par_pagamento->Stemma_2;
                    if($stemma_2 == "")
                        $stemma_bol_2 = $stemma_image_file;
                    else if($stemma_2 == "ente")
                        $stemma_bol_2 = $stemma_image_comune;
                    else if($stemma_2 == "gestore")
                        $stemma_bol_2 = $stemma_image_gestore;
                    else
                        $stemma_bol_2 = "";

                    $giorni_sanz = $par_pagamento->Scadenza_Sanzione;
                    $giorni_ing = $par_pagamento->Scadenza_Ingiunzione;
                    if($giorni_ing>0)
                        $terminiIng = " (entro ".$giorni_ing." giorni dalla notifica)";
                    else
                        $terminiIng = "";

                    $giorni_avv = $par_pagamento->Scadenza_Avviso;
                    $riga2causale = "PAGAMENTO ENTRO ".$giorni_diritto." GIORNI DALLA DATA DI NOTIFICA";


                    //INGIUNZIONE
                    $tipoUfficiale = $ing->Tipo_Ufficiale;
                    $modalitaStampa = $ing->Modalita_Stampa;
                    $ID_ing = $ing->Comune_ID;
                    $anno_crono = $ing->Anno_Cronologico;
                    $id_crono = $ing->ID_Cronologico;
                    $rif = $ing->Riferimento;
                    $protocollo = $ing->Protocollo;
                    $data_protocollo = $ing->Data_Protocollo;

                    $rif_ing = $ID_partita."/".$anno_rif;

                    $quinto_campo = $ing->quinto_campo();//CODICE CLIENTE (QUINTO CAMPO)
                    $atto_precedente = $ing->info_atto_precedente($flag , $partita);

                    $tributo = $partita->Tributo;

                    $maggiorazione_sanz_amministrativa = 0.00;

                    $data_decorrenza_interessi = $partita->data_decorrenza_interessi();

                    $tot_pagamenti_atti = $ing->pagamenti_completi();
                    $tot_pagamenti = $ing->pagamenti_completi() + $a_codici['PAGAMENTO'];
                    $diritto_min = $ing->Diritto_Riscossione_Minimo;
                    $diritto_max = $ing->Diritto_Riscossione_Massimo;

                    if($diritto_min>0)
                        $riga3causale = "PAGAMENTO OLTRE ".$giorni_diritto." GIORNI DALLA DATA DI NOTIFICA";
                    else
                        $riga3causale = $riga2causale;

                    //TOTALI
                    $testo_tot_1	 		= "TOTALE COMPLESSIVO (1) [ Entro ".$giorni_diritto." giorni dalla notifica - Oneri Riscossione ".conv_num(number_format($para_diritto_min,2))."% - Euro ".number_format($diritto_min,2,',','.')." ]";
                    $testo_tot_2	 		= "TOTALE COMPLESSIVO (2) [ Oltre ".$giorni_diritto." giorni dalla notifica - Oneri Riscossione ".conv_num(number_format($para_diritto_max,2))."% - Euro ".number_format($diritto_max,2,',','.')." ]";
                    $testo_tot_unico		= "TOTALE COMPLESSIVO";

                    $tot_1 = $diritto_min + $ing->Totale_Dovuto - $tot_pagamenti_atti;
                    $tot_2 = $diritto_max + $ing->Totale_Dovuto - $tot_pagamenti_atti;

                    $a_importiStampa = array();
                    $a_importiStampa[0][0] = "Spese di procedura, postali/notifica per precedenti Ingiunzioni/Avvisi di Intimazione";
                    $a_importiStampa[0][1] = $ing->Spese_Notifica_Precedenti+$a_codici['SPESE_INGIUNZIONE'];

                    $dateInteressi = "";
                    if(from_mysql_date($partita->Data_Inizio_Interessi)!=null)
                        $dateInteressi.=  " dal ".from_mysql_date($partita->Data_Inizio_Interessi);
//                        else
//                            $dateInteressi.= " il ";
//                        if(from_mysql_date($ing->Data_Calcolo_Interessi)!=null)
//                            $dateInteressi.= from_mysql_date($ing->Data_Calcolo_Interessi);
//                        else
//                            $dateInteressi = "";

                    if($partita->Tipo=="CDS")
                        $a_importiStampa[1][0] = "Maggiorazione del 10% semestrale (ex art. 27 L. 689/81) calcolata";
                    else
                        $a_importiStampa[1][0] = "Nuovi Interessi per Ingiunzioni/Avvisi di Intimazione calcolati";

                    $a_importiStampa[1][0].= $dateInteressi;
                    $a_importiStampa[1][1] = $ing->Interessi+$ing->Interessi_Precedenti;
                    $a_importiStampa[2][0] = "Spese di procedura, postali/notifica per la presente Ingiunzione di Pagamento";
                    $a_importiStampa[2][1] = $ing->Spese_Notifica;
                    $a_importiStampa[3][0] = "Pagamenti ricevuti per precedenti Atti Impositivi";
                    $a_importiStampa[3][1] = $tot_pagamenti;


                    if($diritto_min>0){
                        $a_importiStampa[4][0] = $testo_tot_1;
                        $a_importiStampa[4][1] = $tot_1;
                        $a_importiStampa[5][0] = $testo_tot_2;
                        $a_importiStampa[5][1] = $tot_2;
                    }
                    else{
                        $a_importiStampa[4][0] = $testo_tot_unico;
                        $a_importiStampa[4][1] = $tot_1;
                    }

                    //TOTALI
                    $tot_compl_1_punto = number_format( $diritto_min + $ing->Totale_Dovuto - $tot_pagamenti_atti, 2,",","." );
                    $tot_compl_2_punto = number_format( $diritto_max + $ing->Totale_Dovuto - $tot_pagamenti_atti, 2,",","." );

                    $tot_compl_1 = conv_num( number_format( $diritto_min + $ing->Totale_Dovuto - $tot_pagamenti_atti, 2 ) );
                    $tot_compl_2 = conv_num( number_format( $diritto_max + $ing->Totale_Dovuto - $tot_pagamenti_atti, 2 ) );

                    $dovuto_totale_1 = number_format($tot_1,2);
                    $dovuto_totale_2 = number_format($tot_2,2);

                    $NW = new numero_letterale();
                    $numeroLetterale_1 = $NW->converti_numero_bollettino($dovuto_totale_1);
                    $numeroLetterale_2 = $NW->converti_numero_bollettino($dovuto_totale_2);
                    if($giorni_sanz>0)
                        $giorni_sanz = $NW->intero_in_lettere($giorni_sanz);
                    else
                        $giorni_sanz = null;


                    /**
                    PARAMETRI TESTO INGIUNZIONE
                     */

                    $comune_luogo = $gestore->Comune;
                    if($ufficio->Comune!="")
                        $comune_luogo = $ufficio->Comune;
                    $luogo_data = $comune_luogo.", li ".$data_stampa;

                    //TITOLO
                    $titoloIngiunzione = $testo->Titolo_Ingiunzione;

                    if($ing->Tipo_Ufficiale=="rettifica"){
                        $ingiunzioneTesto = "RETTIFICA";
                    }
                    else {
                        $ingiunzioneTesto = "INGIUNZIONE DI PAGAMENTO";
                    }

                    SostituisciTestoTraGraffe ($titoloIngiunzione, "{INGIUNZIONE}", $ingiunzioneTesto );
                    SostituisciTestoTraGraffe ($titoloIngiunzione, "{IDCRONOLOGICO}", $id_crono );
                    SostituisciTestoTraGraffe ($titoloIngiunzione, "{ANNOCRONOLOGICO}", $anno_crono );

                    if($c=="A446"){
                        $explodeInfoCart = explode("/",$info_cart);
                        $annoVerb = substr($explodeInfoCart[1],0,4);
                        if($annoVerb==2011){
                            $info_cart = $explodeInfoCart[0]."/".$a." RITUALMENTE NOTIFICATO";
                            SostituisciTestoTraGraffe ($titoloIngiunzione, "RIF. {RIFERIMENTO}", "" );
                        }
                    }
                    SostituisciTestoTraGraffe ($titoloIngiunzione, "{RIFERIMENTO}", $rif_ing );

                    if($ing->Tipo_Ufficiale=="rettifica"){
                        $titoloIngiunzione.= " DELL'INGIUNZIONE DI PAGAMENTO N.".$partita->Atto[count($partita->Atto)-2]->ID_Cronologico;
                        $titoloIngiunzione.= " DEL ".$partita->Atto[count($partita->Atto)-2]->Anno_Cronologico;
                    }


                    $riga1causale = "Ingiunzione n.".$id_crono." del ".$anno_crono." Rif.".$rif_ing;

                    //SOTTOTITOLO
                    $sottotitoloIngiunzione = $testo->Sottotitolo_Ingiunzione;
                    SostituisciTestoTraGraffe ($sottotitoloIngiunzione, "{TIPORISCOSSIONE}", $tipo_ing);
                    SostituisciTestoTraGraffe ($sottotitoloIngiunzione, "{ENTEGESTITO}", $comune_completo);

                    //PRIMO TESTO
                    $primoTesto = $testo->Primo_Testo;
                    SostituisciTestoTraGraffe ($primoTesto, "{GESTORE}", $intest_gestore['Riga1'] );

                    //PREMESSO
                    $premesso = $testo->Premesso;
                    $premessoTesto = $testo->Premesso_Testo;
//						if($data_decorrenza_interessi==null && count($partita->Atto)==1){
//							SostituisciTestoTraGraffe ($premessoTesto, "{SOGGETTO}", "il soggetto" );
//							SostituisciTestoTraGraffe ($premessoTesto, "{INFORMAZIONINOTIFICA}", "debitore di quanto segue:" );
//						}
//						else {
                    SostituisciTestoTraGraffe ($premessoTesto, "{SOGGETTO}", "al ".$utente_ing );
                    SostituisciTestoTraGraffe ($premessoTesto, "{INFORMAZIONINOTIFICA}", "stato notificato nelle forme previste dalle disposizioni legislative il sottoindicato atto:" );
//						}

                    $secondoTesto = $testo->Secondo_Testo;
                    if($giorni_sanz==null)
                        $testo_giorni_sanz = "un congruo termine";
                    else
                        $testo_giorni_sanz = "il termine di ".$giorni_sanz." giorni";

//						if($data_decorrenza_interessi==null && count($partita->Atto)==1)
//							$secondoTesto = "";

                    SostituisciTestoTraGraffe ($secondoTesto, "{NUMGIORNI}", $testo_giorni_sanz );
                    $terzoTesto = $testo->Terzo_Testo;
                    SostituisciTestoTraGraffe ($terzoTesto, "{GESTORE}", $intest_gestore['Riga1'] );

                    //INGIUNGE
                    $ingiunge = $testo->Ingiunge;
                    $ingiungeTesto = $testo->Ingiunge_Testo;
                    SostituisciTestoTraGraffe ($ingiungeTesto, "{SOGGETTO}", $utente_ing );
                    SostituisciTestoTraGraffe ($ingiungeTesto, "{NUMEROCONTO}", $numeroContoCorrente  );
                    SostituisciTestoTraGraffe ($ingiungeTesto, "{INTESTATARIOCONTO}", $intestatarioConto  );
                    SostituisciTestoTraGraffe ($ingiungeTesto, "{IBAN}", $iban  );
                    SostituisciTestoTraGraffe ($ingiungeTesto, "{NUMGIORNI}", $giorni_ing );

                    if($ing->Tipo_Ufficiale=="rettifica")
                        $dataInizioTermini = "data di emissione della presente rettifica di ingiunzione";
                    else
                        $dataInizioTermini = "data di notifica della presente ingiunzione";

                    SostituisciTestoTraGraffe ($ingiungeTesto, "{DATANOTIFICA}", $dataInizioTermini );

                    //FINALE PAGINA 1
                    $finalePagina1 = $testo->Finale_Pagina_1;

                    //FIRME PAGINA 1
                    $qual_firma1 = stripslashes($testo->Qualifica_Firma_Sinistra);
                    $qual_firma2 = stripslashes($testo->Qualifica_Firma_Destra);
                    $firma1 = stripslashes($testo->Firma_Sinistra);
                    $firma2 = stripslashes($testo->Firma_Destra);

                    //INFORMAZIONI
                    $informazioni = $testo->Informazioni;
                    $informazioni_testo = $testo->Informazioni_Testo;

                    //PARAMETRI RESPONSABILI
                    $par_responsabili = new parametri_responsabili($c, $settore);
                    $testo_sostitutivo = $par_responsabili->Testo_Sostitutivo;
                    $firma_resp = $par_responsabili->carica_firme("Funzionario", "Responsabile", "Ufficiale");

                    //IMPOSTAZIONI FIRME PAGINA 1
                    $testo_firma = array();

                    $array_variabili = array('{FUNZIONARIORESPONSABILE}','{RESPONSABILEPROCEDIMENTO}');

                    $variabile = estraiVariabile($qual_firma1, $array_variabili);
                    if($variabile == "{FUNZIONARIORESPONSABILE}")
                    {
                        if($gestore->Tipo == "Concessionario")
                            $testo_firma[1]['intestazione'] = "Il Legale Rappresentante";
                        else if($c=="L570")
                            $testo_firma[1]['intestazione'] = "Il Dirigente";
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
                        else if($c=="L570")
                            $testo_firma[2]['intestazione'] = "Il Dirigente";
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

                    //TOTALI
                    $totaleComplex1 = $testo->Totale_1;
                    $testoTotaleComplex1 = $testo->Testo_Totale_1;
                    SostituisciTestoTraGraffe ($testoTotaleComplex1, "{GIORNIDIRITTO}", $giorni_diritto);

                    $totaleComplex2 = $testo->Totale_2;
                    $testoTotaleComplex2 = $testo->Testo_Totale_2;
                    SostituisciTestoTraGraffe ($testoTotaleComplex2, "{GIORNIDIRITTO}", $giorni_diritto);
                    $totComplessivo = $testo->Totale_Complessivo;
                    $testoTotComplessivo = $testo->Totale_Complessivo_Testo;
                    SostituisciTestoTraGraffe ($testoTotComplessivo, "{SPESEATTIGIUDIZIARI}", conv_num(number_format($spese_postali_ag,2)));
                    SostituisciTestoTraGraffe ($testoTotComplessivo, "{SPESENOTIFICA}", conv_num(number_format($spese_notifica,2)));
                    SostituisciTestoTraGraffe ($testoTotComplessivo, "{CAN}", conv_num(number_format($CAN,2)));
                    SostituisciTestoTraGraffe ($testoTotComplessivo, "{CAD}", conv_num(number_format($CAD,2)));

                    $dirittoRiscossione = $testo->Diritto_Riscossione;
                    $dirittoRiscossioneTesto = $testo->Diritto_Riscossione_Testo;
                    SostituisciTestoTraGraffe ($dirittoRiscossioneTesto, "{GIORNIDIRITTO}", $giorni_diritto);
                    SostituisciTestoTraGraffe ($dirittoRiscossioneTesto, "{DIRITTOMINIMO}", conv_num(number_format($para_diritto_min,2))."%");
                    SostituisciTestoTraGraffe ($dirittoRiscossioneTesto, "{GIORNIDIRITTO}", $giorni_diritto);
                    SostituisciTestoTraGraffe ($dirittoRiscossioneTesto, "{DIRITTOMASSIMO}", conv_num(number_format($para_diritto_max,2))."%");

                    //OPPOSIZIONE
                    $opposizione = $testo->Opposizione;
                    $testOpposizione = $testo->Opposizione_Testo;

                    $creditiTributari = $testo->Crediti_Tributari;
                    SostituisciTestoTraGraffe ($creditiTributari, "{TERMINICTP}", $terminiCommTrib);
                    SostituisciTestoTraGraffe ($creditiTributari, "{CTP}", $ctp);
                    SostituisciTestoTraGraffe ($creditiTributari, "{SEDECTP}", $ctp_indirizzo['Completo']);
                    SostituisciTestoTraGraffe ($creditiTributari, "{RECAPITICTP}", $ctp_recapiti);


                    $creditiNonTributari = $testo->Crediti_Non_Tributari;
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{TERMINIGIUSTORD}", $terminiGiustiziaOrd);
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{TRIBUNALE}", $trib);
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{SEDETRIBUNALE}", $trib_indirizzo['Completo']);
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{RECAPITITRIBUNALE}", $trib_recapiti);
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{GDP}", $gdp);
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{SEDEGDP}", $gdp_indirizzo['Completo']);
                    SostituisciTestoTraGraffe ($creditiNonTributari, "{RECAPITIGDP}", $gdp_recapiti);

                    //PROVVEDIMENTO
                    $provvedimento = $testo->Provvedimento;
                    $testoProvvedimento = $testo->Provvedimento_Testo;
                    SostituisciTestoTraGraffe ($testoProvvedimento, "{NUMGIORNI}", $giorni_ing);

                    //ESECUTIVITA
                    $esecutivita = $testo->Esecutivita;
                    $testoEsecutivita = $testo->Esecutivita_Testo;

                    //PAGAMENTO
                    $pagamento = "PAGAMENTO";
                    $primoTestoPagamento = $testo->Pagamento_Primo_Testo;
                    SostituisciTestoTraGraffe ($primoTestoPagamento, "{TERMINIINGIUNZIONE}", $terminiIng );
                    SostituisciTestoTraGraffe ($primoTestoPagamento, "{NUMEROCONTO}", $numeroContoCorrente );
                    SostituisciTestoTraGraffe ($primoTestoPagamento, "{INTESTATARIOCONTO}", $intestatarioConto );
                    SostituisciTestoTraGraffe ($primoTestoPagamento, "{IBAN}", $iban  );
                    SostituisciTestoTraGraffe ($primoTestoPagamento, "{CODICEUTENTE}", $utente_id."/".$c  );

                    $secondoTestoPagamento = $testo->Pagamento_Secondo_Testo;
                    SostituisciTestoTraGraffe ($secondoTestoPagamento, "{TIPOTRIBUTO}", $causale );
                    SostituisciTestoTraGraffe ($secondoTestoPagamento, "{IDCRONOLOGICO}", $id_crono );
                    SostituisciTestoTraGraffe ($secondoTestoPagamento, "{ANNOCRONOLOGICO}", $anno_crono );
                    SostituisciTestoTraGraffe ($secondoTestoPagamento, "{RIFERIMENTO}", $rif_ing );
                    SostituisciTestoTraGraffe ($secondoTestoPagamento, "{COMUNEGESTITO}", $comune_completo );

                    //AVVERTENZA
                    $avvertenza = "AVVERTENZA IMPORTANTE";
                    $primoTestoAvvertenza = $testo->Avvertenza_Primo_Testo;
                    SostituisciTestoTraGraffe ($primoTestoAvvertenza, "{NUMGIORNI}", $giorni_ing );
                    $secondoTestoAvvertenza = $testo->Avvertenza_Secondo_Testo;
                    SostituisciTestoTraGraffe ($secondoTestoAvvertenza, "{NUMEROFAX}", $faxGestore  );
                    SostituisciTestoTraGraffe ($secondoTestoAvvertenza, "{SOGGETTO}", $utente_ing );
                    $terzoTestoAvvertenza = $testo->Avvertenza_Terzo_Testo;

                    //RELAZIONE DI NOTIFICAZIONE
                    if($ing->Tipo_Ufficiale == "riscossione")
                    {
                        $intestazioneRelata = $testo->Intestazione_Relata_Ufficiale_Riscossione;
                        $sottointestazioneRelata = "";
                        $relata = $testo->Relata_Ufficiale_Riscossione;

                        SostituisciTestoTraGraffe ($relata, "{TIPOENTE}",  $intest_gestore['Riga1']);
                        SostituisciTestoTraGraffe ($relata, "{DESTINATARIO}", $nome_utente);
                    }
                    else if($ing->Tipo_Ufficiale == "giudiziario")
                    {
                        $intestazioneRelata = $testo->Intestazione_Relata_Ufficiale_Giudiziario;
                        $sottointestazioneRelata = $testo->Sottointestazione_Relata_Ufficiale_Giudiziario;
                        $relata = $testo->Relata_Ufficiale_Giudiziario;

                        SostituisciTestoTraGraffe ($relata, "{DESTINATARIO}", $nome_utente);
                    }
                    else if($ing->Tipo_Ufficiale == "diretta")
                    {
                        $intestazioneRelata = $testo->Intestazione_Riscossione_Diretta;
                        $sottointestazioneRelata = "";
                        $relata = $testo->Riscossione_Diretta;

                        SostituisciTestoTraGraffe ($relata, "{SOGGETTO}",  $nome_utente);
                    }
                    else
                    {
                        $intestazioneRelata = "";
                        $sottointestazioneRelata = "";
                        $relata = "";
                    }

//                        <option value="posta">Raccomandata A.G.</option>
//                        <option value="ordinaria">Posta ordinaria</option>
//                        <option value="raccomandata">Raccomandata</option>
//                        <option value="mani">A mani</option>
//                        <option value="PEC">Tramite PEC</option>

                    if($modalitaStampa=="posta")
                        SostituisciTestoTraGraffe ($relata, "{TIPOINVIO}", "in ".$indirizzo_senza_provincia." tramite posta.");
                    else if($modalitaStampa=="mani")
                        SostituisciTestoTraGraffe ($relata, "{TIPOINVIO}", "in ".$indirizzo_senza_provincia." mediante consegna a mani.");
                    else if($modalitaStampa=="PEC")
                        SostituisciTestoTraGraffe ($relata, "{TIPOINVIO}", "al seguente indirizzo di posta elettronica certificata ".$PEC." ai sensi di legge." );

                    //FIRME PAGINA 2
                    $qual_firma_notifica = stripslashes($testo->Qualifica_Firma_Notifica);
                    $firma_notifica = stripslashes($testo->Firma_Notifica);

                    //IMPOSTAZIONI FIRME PAGINA 2
                    if($ing->Tipo_Ufficiale == "riscossione")
                    {
                        $testo_firma[3]['intestazione'] = $firma_resp[3]['intestazione'];
                        $testo_firma[3]['nome'] = $firma_resp[3]['nome'];
                        $testo_firma[3]['firma'] = $firma_resp[3]['firma'];
                    }
                    else if($ing->Tipo_Ufficiale == "giudiziario")
                    {
                        $testo_firma[3]['intestazione'] = "Ufficiale Giudiziario";
                        $testo_firma[3]['nome'] = "";
                        $testo_firma[3]['firma'] = "";
                    }
                    else if($ing->Tipo_Ufficiale == "diretta" && $intestazioneRelata!="")
                    {
                        $testo_firma[3]['intestazione'] = "Il Responsabile della Notifica";
                        $testo_firma[3]['nome'] = $firma_resp[2]['nome'];
                        $testo_firma[3]['firma'] = $firma_resp[2]['firma'];
                    }
                    else
                    {
                        $testo_firma[3]['intestazione'] = "";
                        $testo_firma[3]['nome'] = "";
                        $testo_firma[3]['firma'] = "";
                    }

                    if ($stampa_select == "FLUSSO")
                    {

                        if($stato_stampa == "Da stampare")
                        {

                            $array_flusso = array();

                            //FLUSSO GENERALE
                            $array_flusso[] = $c;
                            $array_flusso[] = $a_printTypes['Name'];
                            $array_flusso[] = "Ingiunzione";
                            $array_flusso[] = $id_crono;
                            $array_flusso[] = $anno_crono;

                            $array_flusso[] = $quinto_campo;
                            $array_flusso[] = $stemma_image_file;

                            //FLUSSO GESTORE

                            $array_flusso[] = $intest_gestore['Riga1'];
                            $array_flusso[] = $intest_gestore['Riga2'];
                            $array_flusso[] = $intest_gestore['Riga3'];
                            $array_flusso[] = $intest_gestore['Riga4'];
                            $array_flusso[] = $intest_gestore['Riga5'];
                            $array_flusso[] = $intest_gestore['Riga6'];
                            $array_flusso[] = $intest_gestore['Riga7'];

                            //FLUSSO UFFICIO

                            $array_flusso[] = $intest_ufficio['Riga1'];
                            $array_flusso[] = $intest_ufficio['Riga2'];
                            $array_flusso[] = $intest_ufficio['Riga3'];
                            $array_flusso[] = $intest_ufficio['Riga4'];
                            $array_flusso[] = $intest_ufficio['Riga5'];
                            $array_flusso[] = $intest_ufficio['Riga6'];

                            //FLUSSO DESTINATARIO
                            $array_flusso[] = "Partita numero: ".$ID_partita." / ".$anno_rif;
                            $array_flusso[] = "Codice utente: ".$utente_id;

                            $array_flusso[] = $luogo_data;
                            $array_flusso[] = "Spett.le ".$indirizzo_destinatario['Destinatario'];
                            $array_flusso[] = $indirizzo_destinatario['Riga1'];
                            $array_flusso[] = $indirizzo_destinatario['Riga2'];
                            $array_flusso[] = $indirizzo_destinatario['Riga3'];
                            $array_flusso[] = $indirizzo_destinatario['Riga4'];

                            //FLUSSO OGGETTO

                            $array_flusso[] = "OGGETTO: ".$titoloIngiunzione;
                            $array_flusso[] = $sottotitoloIngiunzione;
                            $array_flusso[] = $primoTesto;

                            //FLUSSO PREMESSO

                            $array_flusso[] = $premesso;
                            $array_flusso[] = $premessoTesto;
                            $array_flusso[] = $info_cart;
                            $array_flusso[] = $secondoTesto;
                            $array_flusso[] = $atto_precedente;
                            $array_flusso[] = $terzoTesto;

                            //FLUSSO INGIUNGE

                            $array_flusso[] = $ingiunge;
                            $array_flusso[] = $ingiungeTesto;

                            $contaRighe = 0;
                            for($countTrib = 0; $countTrib<count($partita->a_tributi);$countTrib++){
                                if($partita->a_tributi[$countTrib]->Tipo_Codice=="PAGAMENTO" || $partita->a_tributi[$countTrib]->Codice_Tributo=="S_03")
                                    continue;

                                if($partita->a_tributi[$countTrib]->Imposta>0){
                                    $array_flusso[] = $partita->a_tributi[$countTrib]->Testo_Codice;
                                    $array_flusso[] = "+ Euro";
                                    $array_flusso[] = number_format($partita->a_tributi[$countTrib]->Imposta,2,",",".");
                                    $contaRighe++;
                                }
                            }

                            for($countImporti = 0; $countImporti<count($a_importiStampa);$countImporti++){

                                if($a_importiStampa[$countImporti][1]>0) {
                                    $operando = "+";
                                    if ($countImporti == 3)
                                        $operando = "-";
                                    else if ($countImporti > 3)
                                        $operando = "=";

                                    $array_flusso[] = $a_importiStampa[$countImporti][0];
                                    $array_flusso[] = $operando." Euro";
                                    $array_flusso[] = number_format($a_importiStampa[$countImporti][1], 2, ",", ".");
                                    $contaRighe++;
                                }
                            }

                            for($i_righe=$contaRighe;$i_righe<12;$i_righe++){
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            $array_flusso[] = $finalePagina1;

                            //FLUSSO RESPONSABILI
                            $array_flusso[] = $testo_firma[1]['intestazione'];
                            $array_flusso[] = $testo_firma[1]['firma'];
                            $array_flusso[] = $testo_firma[1]['nome'];
                            $array_flusso[] = $testo_firma[2]['intestazione'];
                            $array_flusso[] = $testo_firma[2]['firma'];
                            $array_flusso[] = $testo_firma[2]['nome'];

                            if($informazioni_testo!=""){
                                $array_flusso[] = $informazioni;
                                $array_flusso[] = $informazioni_testo;
                            }
                            else{
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            //FLUSSO TOTALI
                            if($diritto_min>0)
                            {
                                $array_flusso[] = $totaleComplex1;
                                $array_flusso[] = $testoTotaleComplex1;
                                $array_flusso[] = $totaleComplex2;
                                $array_flusso[] = $testoTotaleComplex2;
                            }
                            else
                            {
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            $array_flusso[] = $totComplessivo;
                            $array_flusso[] = $testoTotComplessivo;

                            if($diritto_min>0)
                            {
                                $array_flusso[] = $dirittoRiscossione;
                                $array_flusso[] = $dirittoRiscossioneTesto;
                            }
                            else
                            {
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            //FLUSSO OPPOSIZIONE/RIESAME/ESECUTIVITA

                            $array_flusso[] = $opposizione;
                            $array_flusso[] = $testOpposizione;
                            $array_flusso[] = $creditiTributari;
                            $array_flusso[] = $creditiNonTributari;
                            $array_flusso[] = $provvedimento;
                            $array_flusso[] = $testoProvvedimento;
                            $array_flusso[] = $esecutivita;
                            $array_flusso[] = $testoEsecutivita;

                            //FLUSSO PAGAMENTO/AVVERTENZA/TRIBUNALE

                            $array_flusso[] = $pagamento;
                            $array_flusso[] = $primoTestoPagamento." ".$secondoTestoPagamento;
                            $array_flusso[] = "";
                            $array_flusso[] = $avvertenza;
                            $array_flusso[] = $primoTestoAvvertenza;
                            $array_flusso[] = $secondoTestoAvvertenza;
                            $array_flusso[] = $terzoTestoAvvertenza;
                            $array_flusso[] = $intestazioneRelata;
                            $array_flusso[] = $sottointestazioneRelata;
                            $array_flusso[] = $relata;
                            $array_flusso[] = $testo_firma[3]['intestazione'];
                            $array_flusso[] = $testo_firma[3]['firma'];
                            $array_flusso[] = $testo_firma[3]['nome'];

                            //FLUSSO BOLLETTINO 1
                            if($autorizzazione_1!=false || $td_1=="123")
                            {
                                if($td_1=="896")
                                {
                                    $dovuto_bollettino = $tot_compl_1;
                                    $dovuto_letterale = "";
                                }
                                else if($ctrl_importo_1=="si")
                                {
                                    $dovuto_bollettino = $tot_compl_1;
                                    $dovuto_letterale = $numeroLetterale_1;
                                }
                                else
                                {
                                    $dovuto_bollettino = "";
                                    $dovuto_letterale = "";
                                }

                                $array_flusso[] = $stemma_bol_1;
                                $array_flusso[] = $td_1;
                                $array_flusso[] = $autorizzazione_1;
                                $array_flusso[] = $numeroContoCorrente;
                                $array_flusso[] = $intestatarioConto;
                                $array_flusso[] = $iban;
                                $array_flusso[] = $dovuto_bollettino;
                                $array_flusso[] = $dovuto_letterale;
                                $array_flusso[] = $indirizzo_destinatario['Destinatario'];

                                $array_flusso[] = $a_addressRows[0];
                                $array_flusso[] = $a_addressRows[1];
                                $array_flusso[] = $riga1causale;
                                $array_flusso[] = $riga2causale;
                                $array_flusso[] = $pdf->set_quinto_campo( $td_1, $quinto_campo);
                                $array_flusso[] = $pdf->set_quinto_campo( $td_1, $quinto_campo, true);
                                $array_flusso[] = $pdf->barcode_importo_bollettino($td_1, $tot_compl_1);
                                $array_flusso[] = $pdf->barcode_conto_bollettino($td_1, $numeroContoCorrente);
                                $array_flusso[] = $td_1.">";
                            }
                            else
                            {
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            //FLUSSO BOLLETTINO 2
                            if($autorizzazione_2!=false || $td_2=="123")
                            {
                                if($td_2=="896")
                                {
                                    $dovuto_bollettino = $tot_compl_2;
                                    $dovuto_letterale = "";
                                }
                                else if($ctrl_importo_2=="si")
                                {
                                    $dovuto_bollettino = $tot_compl_2;
                                    $dovuto_letterale = $numeroLetterale_2;
                                }
                                else
                                {
                                    $dovuto_bollettino = "";
                                    $dovuto_letterale = "";
                                }

                                $array_flusso[] = $stemma_bol_2;
                                $array_flusso[] = $td_2;
                                $array_flusso[] = $autorizzazione_2;
                                $array_flusso[] = $numeroContoCorrente;
                                $array_flusso[] = $intestatarioConto;
                                $array_flusso[] = $iban;
                                $array_flusso[] = $dovuto_bollettino;
                                $array_flusso[] = $dovuto_letterale;
                                $array_flusso[] = $indirizzo_destinatario['Destinatario'];
                                $array_flusso[] = $a_addressRows[0];
                                $array_flusso[] = $a_addressRows[1];
                                $array_flusso[] = $riga1causale;
                                $array_flusso[] = $riga3causale;
                                $array_flusso[] = $pdf->set_quinto_campo( $td_2, $quinto_campo);
                                $array_flusso[] = $pdf->set_quinto_campo( $td_2, $quinto_campo, true);
                                $array_flusso[] = $pdf->barcode_importo_bollettino($td_2, $tot_compl_2);
                                $array_flusso[] = $pdf->barcode_conto_bollettino($td_2, $numeroContoCorrente);
                                $array_flusso[] = $td_2.">";
                            }
                            else
                            {
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            if($protocollo!=""){
                                $array_flusso[] = "PROTOCOLLO : ".strtoupper($protocollo);
                                $array_flusso[] = "DEL : ".from_mysql_date($data_protocollo);
                            }
                            else{
                                $array_flusso[] = "";
                                $array_flusso[] = "";
                            }

                            $array_flusso[] = $par_generali->Intestatario_SMA;
                            $array_flusso[] = $par_generali->Numero_SMA;
                            $array_flusso[] = $par_generali->Testo_Spese_Anticipate;

                            $array_flusso[] = $par_generali->Restituzione1;
                            $array_flusso[] = $par_generali->Restituzione2;
                            $array_flusso[] = $par_generali->Restituzione3;
                            $array_flusso[] = $par_generali->Restituzione4;
                            $array_flusso[] = $par_generali->Restituzione5;

                            $myFlusso->AggiungiRigaFlusso($array_flusso);

                            $salva = new atto($ing->ID, $c);

                            $salva->Data_Flusso = $myFlusso->myData;
                            $salva->Anno_Flusso = $myFlusso->myAnno;
                            $salva->Numero_Flusso = $myFlusso->myNumero;

                            $control_salva = $salva->Update($ing->ID, true);

                        }

                    }	//fine FLUSSO
                    else
                    {
                        if ($stampa_select == "DEFINITIVA")
                        {
                            $file_stampa = $stampa_dir."/Ingiunzione_".$c."_".$anno_crono."_".$id_crono."_".to_mysql_date($data_stampa).".pdf";

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
                        /**
                        ///////////////////////////////		PDF	    //////////////////////////////////
                         */

                        /**
                         * 		//////////////	PAGINA 1	//////////////
                         */

                        $fontSize = 8.5;
                        $pdf->setPrintHeader(false);
                        $pdf->SetAutoPageBreak(false);
                        $pdf->SetCellPadding(0);
                        $pdf->AddPage('P');

                        if($stampa_select == "PROVVISORIA")
                            $pdf->stampa_provvisoria();

//////////////	CORPO Pagina 1	//////////////

                        $pdf->intestazione_pdf($tipo_gestore, $image_file, $intest_gestore, $intest_ufficio);
                        $pdf->destinatario_intestazione_pdf($utente_id, $c, $indirizzo_destinatario['Destinatario'], $ID_partita, $anno_rif, $indirizzo_destinatario, $luogo_data, null, $protocollo, $data_protocollo );
                        $pdf->oggetto_pdf($titoloIngiunzione, $sottotitoloIngiunzione, $primoTesto);

                        //PREMESSO
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $premesso , 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $premessoTesto , 0, 'L', 0, 1);
                        $pdf->Ln(2);
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $info_cart , 0, 'L', 0, 1);
                        $pdf->Ln(2);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $secondoTesto, 0, 'L', 0, 1);

                        if( $atto_precedente!="" )		$pdf->MultiCell(0, 0, $atto_precedente , 0, 'L', 0, 1);

                        $pdf->MultiCell(0, 0, $terzoTesto , 0, 'L', 0, 1);
                        $pdf->Ln(3);

                        //INGIUNGE
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $ingiunge , 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $ingiungeTesto."\n", 0, 'J', 0, 1);

                        //IMPORTI INGIUNZIONE
                        $array_width = array(160,8,8,19);
                        $array_align = array("R","C","R","R");

                        $pdf->SetFont('Arial', '', $fontSize);
                        for($countTrib = 0; $countTrib<count($partita->a_tributi);$countTrib++){
                            if($partita->a_tributi[$countTrib]->Tipo_Codice=="PAGAMENTO" || $partita->a_tributi[$countTrib]->Codice_Tributo=="S_03")
                                continue;

                            if($partita->a_tributi[$countTrib]->Imposta>0) {
                                $pdf->Ln(1.5);
                                $array_value = array($partita->a_tributi[$countTrib]->Testo_Codice, "+", "Euro", number_format($partita->a_tributi[$countTrib]->Imposta, 2, ",", "."));
                                crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
                            }
                        }

                        $tot = array_sum( $array_width );
                        for($countImporti = 0; $countImporti<count($a_importiStampa);$countImporti++){
                            if($a_importiStampa[$countImporti][1]>0) {
                                $pdf->Ln(1.5);

                                $operando = "+";
                                if ($countImporti == 3)
                                    $operando = "-";
                                else if ($countImporti > 3) {
                                    if ($countImporti == 4) {
                                        $margine = $pdf->getMargins();
                                        $pdf->Line(7, $pdf->getY(), 203, $pdf->getY(), array());
                                        $pdf->Ln(1.5);
                                        $pdf->SetFont('Arial', 'B', $fontSize);
                                    }
                                    $operando = "=";
                                }


                                $array_value = array($a_importiStampa[$countImporti][0], $operando, "Euro", number_format($a_importiStampa[$countImporti][1], 2, ",", "."));
                                crea_riga($pdf, $array_width, $array_value, $linea = "no", $style = array(), $array_align);
                            }

                        }

                        $pdf->Ln(3);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $finalePagina1, 0, 'L', 0, 1);
                        $pdf->Ln(3);

                        //RESPONSABILI
                        $pdf->firma_pdf($testo_firma);
                        $pdf->Ln(3);

                        if($informazioni_testo!="")
                        {
                            //INFORMAZIONI
                            $pdf->SetFont('Arial', 'B', $fontSize);
                            $pdf->MultiCell(0, 0, $informazioni , 0, 'C', 0, 1);
                            $pdf->SetFont('Arial', '', $fontSize);
                            $pdf->MultiCell(0, 0, $informazioni_testo."\n", 0, 'J', 0, 1);
                        }

//////////////	FINE CORPO Pagina 1	//////////////

                        //PIE DI PAGINA 1
                        $pdf->SetY(-10);
                        $pdf->SetFont('helvetica', 'N', 7);

                        $dataFooter = "Pag. 1/2";
                        if($flag_ristampa!="y")
                            $dataFooter.= " - ".date("d/m/Y H\hi:s");
                        $pdf->Cell(0, 5, $dataFooter, 0, false, 'C', 0, '', 0, false, 'T', 'M');

                        /**
                         * 		//////////////	PAGINA 2	//////////////
                         */

                        $pdf->SetMargins(7,10,7);
                        $pdf->AddPage('P');
                        $pdf->setPrintHeader(false);
                        if($stampa_select == "PROVVISORIA")
                            $pdf->stampa_provvisoria();


//////////////	CORPO Pagina 2	//////////////

                        if($diritto_min > 0)
                        {

                            //TOTALE COMPLESSIVO (1)
                            $pdf->SetFont('Arial', 'B', $fontSize);
                            $pdf->Cell(0, 0, $totaleComplex1 , 0, 1, 'C', 0, '', 0);
                            $pdf->SetFont('Arial', '', $fontSize);
                            $pdf->MultiCell(0, 0, $testoTotaleComplex1."\n", 0, 'J', 0, 1);
                            $pdf->Ln(5);

                            //TOTALE COMPLESSIVO (2)
                            $pdf->SetFont('Arial', 'B', $fontSize);
                            $pdf->Cell(0, 0, $totaleComplex2 , 0, 1, 'C', 0, '', 0);
                            $pdf->SetFont('Arial', '', $fontSize);
                            $pdf->MultiCell(0, 0, $testoTotaleComplex2."\n", 0, 'J', 0, 1);
                            $pdf->ln(5);
                        }

                        //TOTALE COMPLESSIVO
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->Cell(0, 0, $totComplessivo , 0, 1, 'C', 0, '', 0);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $testoTotComplessivo."\n", 0, 'J', 0, 1);
                        $pdf->Ln(5);

                        if($diritto_min > 0)
                        {
                            //DIRITTO RISCOSSIONE
                            $pdf->SetFont('Arial', 'B', $fontSize);
                            $pdf->Cell(0, 0, $dirittoRiscossione , 0, 1, 'C', 0, '', 0);
                            $pdf->SetFont('Arial', '', $fontSize);
                            $pdf->MultiCell(0, 0, $dirittoRiscossioneTesto."\n", 0, 'J', 0, 1);
                            $pdf->Ln(5);
                        }

                        //OPPOSIZIONE
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $opposizione, 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $testOpposizione."\n", 0, 'J', 0, 1);
                        $pdf->MultiCell(0, 0, $creditiTributari."\n", 0, 'J', 0, 1);
                        $pdf->MultiCell(0, 0, $creditiNonTributari."\n", 0, 'J', 0, 1);
                        $pdf->ln(5);

                        //RIESAME
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $provvedimento, 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $testoProvvedimento."\n", 0, 'J', 0, 1);
                        $pdf->ln(5);

                        //ESECUTIVITA'
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $esecutivita, 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $testoEsecutivita."\n" , 0, 'J', 0, 1);
                        $pdf->ln(5);

                        //PAGAMENTO
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $pagamento , 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $primoTestoPagamento." ".$secondoTestoPagamento."\n" , 0, 'J', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, "" , 0, 'J', 0, 1);
                        $pdf->ln(5);

                        //AVVERTENZA
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $avvertenza , 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $primoTestoAvvertenza."\n" , 0, 'J', 0, 1);
                        $pdf->ln(2);
                        $pdf->MultiCell(0, 0, $secondoTestoAvvertenza."\n" , 0, 'J', 0, 1);
                        $pdf->ln(2);
                        $pdf->MultiCell(0, 0, $terzoTestoAvvertenza."\n" , 0, 'J', 0, 1);
                        $pdf->Ln(5);

                        //TRIBUNALE
                        $pdf->SetFont('Arial', 'B', $fontSize);
                        $pdf->MultiCell(0, 0, $intestazioneRelata , 0, 'C', 0, 1);
                        $pdf->MultiCell(0, 0, $sottointestazioneRelata , 0, 'C', 0, 1);
                        $pdf->SetFont('Arial', '', $fontSize);
                        $pdf->MultiCell(0, 0, $relata , 0, 'L', 0, 1);
                        $pdf->Ln(2);
                        $pdf->firma_destra($testo_firma[3]);

//////////////	FINE CORPO Pagina 2	//////////////

                        //PIE DI PAGINA 2
                        $pdf->SetY(-10);
                        $pdf->SetFont('helvetica', 'N', 7);

                        $dataFooter = "Pag. 2/2";
                        if($flag_ristampa!="y")
                            $dataFooter.= " - ".date("d/m/Y H\hi:s");

                        $pdf->Cell(0, 5, $dataFooter, 0, false, 'C', 0, '', 0, false, 'T', 'M');

                        /**
                         * 		//////////////	PAGINA 3 BOLLETTINO	//////////////
                         */
                        if(($autorizzazione_1!=false || $td_1=="123") || ($autorizzazione_2!=false || $td_2=="123"))
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

                                $pdf->scelta_td_bollettino($td_1, $quinto_campo , $tot_compl_1 , $ctrl_importo_1 , $numeroContoCorrente );
                                $pdf->iban_bollettino($iban);
                                $pdf->intestatario_bollettino($intestatarioConto);
                                $pdf->causale_bollettino($riga1causale, $riga2causale);
                                $pdf->payerZone($indirizzo_destinatario['Destinatario'], $a_addressRows);
                                $pdf->autorizzazione_bollettino($autorizzazione_1);
                            }

                            if($autorizzazione_2!=false || $td_2=="123")
                            {
                                $pdf->crea_bollettino_inverso();
                                if($stemma_2 == "")
                                    $pdf->logo_bollettino($image_file,'due');
                                else if($stemma_2 == "ente")
                                    $pdf->logo_bollettino($stemmaComune,'due');
                                else if($stemma_2 == "gestore")
                                    $pdf->logo_bollettino($stemmaGestore,'due');

                                $pdf->scelta_td_bollettino($td_2, $quinto_campo , $tot_compl_2 , $ctrl_importo_2 , $numeroContoCorrente, 'due');
                                $pdf->iban_bollettino($iban,'due');
                                $pdf->intestatario_bollettino($intestatarioConto,'due');
                                $pdf->causale_bollettino($riga1causale, $riga3causale,'due');
                                $pdf->payerZone($indirizzo_destinatario['Destinatario'], $a_addressRows,'due');
                                $pdf->autorizzazione_bollettino($autorizzazione_2,'due');
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

                            $salva = new atto($ing->ID, $c);

                            if($salva->Stato_Stampa == "Da stampare")
                            {
                                $salva->Data_Stampa = to_mysql_date($data_stampa);
                                $salva->Stato_Stampa = "Stampato";

                                if($tipoUfficiale == "rettifica")
                                    $salva->Data_Notifica = $partita->Atto[count($partita->Atto)-2]->Data_Notifica;

                                $control_salva = $salva->Update($ing->ID, true);

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
                            else if($flag_ristampa=="y"){
                                $pdf->Output( $file_stampa , 'F');
                                $arrayConcat[] = $file_stampa;
                            }
                        }



                    }  // fine PDF

                    $array_stampati[] = $array_atti[$l]['ID'];
                    $cont_result++;

                    break;		//Una partita puo' avere un solo intestatario per cui una volta trovato si puo' uscire dal ciclo degli utenti

                }//CHIUSURA IF PARTITA/UTENTE

            }//CHIUSURA FOR UTENTI

            break;		//Un atto puo' corrispondere ad una sola partita per cui una volta trovato si puo' uscire dal ciclo delle partite

        }//CHIUSURA IF ATTO/PARTITA

    }//CHIUSURA PARTITE

}//CHIUSURA ATTII

if ($stampa_select == "FLUSSO")
{
    if($cont_result == 0)
    {
        echo "<script>nessun_risultato();</script>";
    }
    else
    {

        if($stato_stampa == "Da stampare")
        {

            mysql_query('COMMIT');

            if($PrinterId>1){
                if(is_file($percorso_stemma_comune))
                    $myFlusso->AllegaImmagine($percorso_stemma_comune);
                if(is_file($percorso_stemma_gestore))
                    $myFlusso->AllegaImmagine($percorso_stemma_gestore);
                if(is_file($firme_responsabili['Funzionario_Path']))
                    $myFlusso->AllegaImmagine($firme_responsabili['Funzionario_Path']);
                if(is_file($firme_responsabili['Responsabile_Path']))
                    $myFlusso->AllegaImmagine($firme_responsabili['Responsabile_Path']);
            }

            $myFlusso->PrinterId = $a_printerParams['PrinterId'];
            $myFlusso->PrintTypeId = $PrintTypeId;
            $myFlusso->PrintCost = $a_printerParams['PrintCost'];
            $myFlusso->Zone0Postage = $a_printerParams['Zone0Postage'];
            $myFlusso->Zone1Postage = $a_printerParams['Zone1Postage'];
            $myFlusso->Zone2Postage = $a_printerParams['Zone2Postage'];
            $myFlusso->Zone3Postage = $a_printerParams['Zone3Postage'];
            $myFlusso->TaxType = $TaxTypeId;

            $myFlusso->ChiudiFlusso($PrinterId);
            $flowId = mysql_insert_id();
            if($flowId>0){
                $query = "UPDATE atto SET FlowId=".$flowId." WHERE Data_Flusso='".$myFlusso->myData."' ";
                $query.= "AND Anno_Flusso=".$myFlusso->myAnno." AND Numero_Flusso=".$myFlusso->myNumero;

                mysql_query($query);

            }
        }


        echo "<form name='flusso_form' id='flusso_form' method='post' action='gestione_flussi.php'>";
        echo "<input type=hidden name=tipo_atto value='Ingiunzione'>";
        echo "<input type=hidden name='c' value=".$c.">";
        echo "<input type=hidden name='a' value=".$a.">";

        for($t=0; $t<count($array_stampati);$t++)
        {
            echo "<input type=hidden name=array_flussi[] value='".$array_stampati[$t]."'>";
        }

        echo "</form>";

        echo "<script>fine2('Elaborazione completata');</script>";
        echo "<script>atti_stampati('');</script>";

    }
}
else if($stampa_select=="PROVVISORIA" )
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

        $fileCompletoUnito = $concat_dir."/Ingiunzione_Merge_".$c."_".to_mysql_date($data_definitiva)."_".$ora_file.".pdf";

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