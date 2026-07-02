<?php
if (!session_id()) session_start();



include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
$submenuPageNo = 2;
include(INC."/submenu_partita.php");


include(CLS."/cls_registry.php");
include_once(CLS."/cls_GestionePartita.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_math.php");

$cls_partita = new cls_GP();
$cls_date = new cls_DateTimeI("IT",false);
$cls_mathF = new cls_math();

$layout = "";
$layout.= "<script>$('#pdf_link').hide();$('#flusso').hide();</script>";
$note_blocco = "";

//if($partita_ID>0){
//    $codici_tributo = $cls_db->getObjectLine($cls_db->SelectQuery("SELECT SUM(Imposta) AS Totale_Codici_Tributo FROM tributo WHERE Partita_ID=".$partita_ID));
//    $totale_codici = $codici_tributo->Totale_Codici_Tributo;
//}

$a_printer = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM printer"));
$cls_html = new cls_html();
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>null, "text"=>array("[Name]"));
$optPrinter = $cls_html->getOptions($a_printer,$a_selection);

if($utente==null){
    $PEC_utente = "";
}
else{
    $PEC_utente = $utente->PEC;
}

//echo "<h1>".$partita_ID."</h1>";

$partita = $cls_partita->getDataPartita($partita_ID, $c, $a);// new partita($partita_ID, $c, $a);

$ultimoAtto = null;
if(isset($partita["Atto"]))
    if(count($partita["Atto"]) > 0)
        $ultimoAtto = $partita["Atto"][count($partita["Atto"])-1]["ID"];

//print_r($partita);
//die;
//echo "<h1>59 --> ".print_r($partita)."</h1>";
//$codice_tributo = new codice_tributo(null);
$totale_codici = 0;
$count = 0;
if(isset($partita["Tributo"])) $count = count($partita["Tributo"]);

for($i=0;$i<$count;$i++)
{
  $query = "SELECT Tipo_Codice FROM codice_tributo WHERE Codice_Tributo = '".$partita["Tributo"][$i]["Codice_Tributo"]."'";
  $tipo_tributo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query))["Tipo_Codice"];

//  echo "<h1>70 --> ".$tipo_tributo."</h1>";

  if($tipo_tributo=="PAGAMENTO")
      $totale_codici -= $partita["Tributo"][$i]["Imposta"];
  else
      $totale_codici += $partita["Tributo"][$i]["Imposta"];
}
//echo $totale_codici;
//die;
$note_blocco = $partita["Note_Blocco"];
if($partita["Flag_Blocco_Coazione"]=="si")
{
    $layout.= "<script>$('#flag_blocco').prop('checked',true);</script>";
    $layout.= "<script>$('#motivo_blocco').val('".$partita["Motivo_Blocco"]."');cambia_title('motivo_blocco');</script>";

}
if($partita["Flag_Blocco_Maggiorazioni"]=="si")
    $layout.= "<script>$('#flag_maggiorazione').prop('checked',true);</script>";
if($partita["Flag_Blocco_Diritto_Riscossione"]=="si")
    $layout.= "<script>$('#flag_diritto_riscossione').prop('checked',true);</script>";

$atto = array();
if(isset($partita["Atto"])) $atto = $partita["Atto"];
//var_dump($atto);
$numero_atti = count($atto);

$atto_ID = "";
$doc="";
$rate_previste = "0";
$data_richiesta = "";
$control_spedizione = "no";
$no_mod = 0;
if(count($atto)!=0)
{
    $ing = $atto[count($atto)-1];
    $atto_ID = $ing["ID"];


//    $ing = new atto($ing->ID,$c);
    $doc = $ing["Atto"];
    $rif = $ing["Riferimento"];
    $note = $ing["Note"];

    $data_elaborazione = $ing["Data_Elaborazione"];
    $data_interessi = $ing["Data_Decorrenza_Interessi"];
    $data_calcolo = $ing["Data_Calcolo_Interessi"];
    $data_stampa = $ing["Data_Stampa"];
    $data_richiesta = $ing["Data_Richiesta_Rate"];

    //$query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID < ".$ing["ID"]." AND Partita_ID = ".$ing["Partita_ID"]." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
    //$results = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    $pagamenti_precedenti = $cls_partita->pagamenti_precedenti($ing["ID"], $ing["Partita_ID"]); // $results["TOTALE_PAGAMENTI"];
    $data_notifica = $ing["Data_Notifica"];
    $note_notifica = $ing["Note_Notifica"];
    $protocollo = $ing["Protocollo"];
    $data_protocollo = $ing["Data_Protocollo"];
    $atto_rettificato = $ing["Atto_Rettificato"];
    $testo_cronologico = "";
    if($atto_rettificato==1){
        $testo_cronologico.= "RETTIFICA";
        if($ing["ID_Cronologico"]>0){
            $testo_cronologico.= strtoupper(" n. ".$ing["ID_Cronologico"]." del ".$ing["Anno_Cronologico"])." ";
            $readonlyProt = "";
            $pickerProt = " picker ";
        }
        else{
            $testo_cronologico.= " (Crono. da assegnare) ";
            $readonlyProt = " readonly ";
            $pickerProt = "";
        }
        $testo_cronologico.= strtoupper(substr($ing["Atto"], 0,25));
        $ing_prec = $atto[count($atto)-2];
        $testo_cronologico.= strtoupper(" n. ".$ing_prec["ID_Cronologico"]." del ".$ing_prec["Anno_Cronologico"]);
    }
    else{
        $testo_cronologico.= strtoupper(substr($ing["Atto"], 0,25));
        if($ing["ID_Cronologico"]>0){
            $testo_cronologico.= strtoupper(" n. ".$ing["ID_Cronologico"]." del ".$ing["Anno_Cronologico"]);
            $readonlyProt = "";
            $pickerProt = " picker ";
        }
        else{
            $testo_cronologico.= " (Crono. da assegnare)";
            $readonlyProt = " readonly ";
            $pickerProt = "";
        }
    }

// 		$testo_cronologico = strtoupper($testo_cronologico);

    $pdf_def = $cls_partita->attoStampato($ing["Atto"], "DEFINITIVA", $ing);// $ing->attoStampato ( $ing->Atto , "DEFINITIVA" );

    if($pdf_def!="notFound")
    {
        $path_pdf = $pdf_def[0];
        $apri_pdf = SUPER_WEB_ROOT.substr( $path_pdf , strpos( $path_pdf , "/archivio/" ));
        $layout.= "<script>$('#pdf_link').show();</script>";
    }
    else
    {
        $path_pdf = "";
        $apri_pdf = "";
    }

    $flusso = $cls_partita->attoStampato( $ing["Atto"] , "FLUSSO" , $ing);
    //echo "<h1>171 --> ".print_r($flusso)."</h1>";
    if($flusso!="notFound")
    {
        $path_txt = $flusso[0];
        $apri_txt = SUPER_WEB_ROOT.substr( $flusso[0] , strpos( $flusso[0] , "/archivio/" ));//mostra_file_path($flusso[0]);
        $path_rar = $flusso[1];
        $apri_rar = SUPER_WEB_ROOT.substr( $flusso[1] , strpos( $flusso[1] , "/archivio/" ));//mostra_file_path($flusso[1]);
        $layout.= "<script>$('#flusso').show();</script>";
    }
    else
    {
        $path_txt = "";
        $apri_txt = "";
        $path_rar = "";
        $apri_rar = "";
    }

    if($data_notifica!=null && $data_notifica!="0000-00-00")
    {
        $data_termini = $data_notifica;
        $control_doc = $doc;
        if($doc=="Sollecito di pagamento")
        {
            for( $i = count($atto)-1; $i>0; $i-- )
            {
                $atto_current = $atto[$i-1];
                $tipo_ultimo_atto = $atto_current["Atto"];
                if($tipo_ultimo_atto=="Sollecito di pagamento")
                {
                    continue;
                }
                else
                {
                    $data_termini = $atto_current["Data_Notifica"];
                    $control_doc = $tipo_ultimo_atto;

                    break;
                }
            }
        }

        switch($control_doc)
        {
            case "Ingiunzione":

                $data_control_1 = date("Y-m-d" , strtotime( $data_termini."+30 day" ));
                $data_control_2 = date("Y-m-d" , strtotime( $data_termini."+1 year" ));

                break;

            case "Avviso di intimazione ad adempiere":

                $data_control_1 = date("Y-m-d" , strtotime( $data_termini."+5 day" ));
                $data_control_2 = date("Y-m-d" , strtotime( $data_termini."+6 month" ));

                break;

            case "Avviso di messa in mora":

                $data_control_1 = date("Y-m-d" , strtotime( $data_termini."+0 day" ));
                $data_control_2 = date("Y-m-d" , strtotime( $data_termini."+3 year" ));

                break;
        }

        $today = date('Y-m-d');
        if($today > $data_control_1 && $today < $data_control_2)
            $stato_esecutivo = "Esecutivo";
        else
            $stato_esecutivo = "Non esecutivo";
    }
    else
        $stato_esecutivo = "Non esecutivo";

    $stato_notifica = $ing["Stato_Notifica"];
    $layout .= "<script>$('#stato_not').val('".$stato_notifica."');cambia_title('stato_not');</script>";

    $indirizzo_validato = $ing["Indirizzo_Validato"];
    if($indirizzo_validato=="si")
        $layout .= "<script>$('#indirizzo_validato').prop('checked',true);</script>";

    if($stato_notifica>0)
        $layout .= "<script>$('#indirizzo_validato').prop('disabled',false);</script>";
    else
        $layout .= "<script>$('#indirizzo_validato').prop('disabled',true);</script>";

    $motivo_notifica = $ing["Motivo_Notifica"];
    $layout .= "<script>$('#motivo_not').val('".$motivo_notifica."');cambia_title('motivo_not');</script>";

    $modalita_notifica = $ing["Modalita_Notifica"];
    $layout .= "<script>$('#modalita_not').val('".$modalita_notifica."');cambia_title('modalita_not');</script>";

    $tipo_ufficiale = $ing["Tipo_Ufficiale"];
    $layout .= "<script>$('#tipo_ufficiale').val('".$tipo_ufficiale."');cambia_title('tipo_ufficiale');</script>";

    $modalita_stampa = $ing["Modalita_Stampa"];
    $layout .= "<script>$('#modalita_stampa').val('".$modalita_stampa."');cambia_title('modalita_stampa');</script>";

    $printerId = $ing["PrinterId"];
    $layout .= "<script>$('#PrinterId').val('".$printerId."');cambia_title('PrinterId');</script>";
//    if($tipo_ufficiale=="rettifica")
//        $layout .= "<script>$('#spese_not_precedenti').attr('readonly',false);</script>";

    $rielabora_flag = $ing["Rielabora_Flag"];
    if($rielabora_flag=="si")
        $layout .= "<script>$('#rielabora').prop('checked',true);</script>";

    $rettifica_flag = $ing["Rettifica_Flag"];
    if($rettifica_flag=="si")
        $layout .= "<script>$('#rettifica').prop('checked',true);</script>";

    $date_preavvisi = array();
    $data_preav = explode("**", $ing["Date_Stampe_Preavvisi_Ing"] );
    //echo "<h1>283 --> ".$ing["Date_Stampe_Preavvisi_Ing"]."</h1><br>";
		for( $i=0; $i<count($data_preav); $i++)
		{
			$date_preavvisi[$i] = $cls_date->Get_DateNewFormat($data_preav[$i],"DB");
		}

    //$date_preavvisi = $ing["Date_Preavvisi"];

    if($date_preavvisi[0]=="")
    {
        $num_preavvisi = 0;
        $data_preavviso = "";
    }
    else
    {
        $num_preavvisi = count($date_preavvisi);
        $data_preavviso = $date_preavvisi[$num_preavvisi-1];
    }


    $layout.="<script>$('#AR_fronte').hide();</script>";
    $layout.="<script>$('#AR_retro').hide();</script>";
    $layout.="<script>$('#CAD_fronte').hide();</script>";
    $layout.="<script>$('#CAD_retro').hide();</script>";

    $pathAR = $PathImmaginiNotifiche.$c."/";
    $pathcompletoAR = $PathCompletoImmaginiNotifiche.$c."/";
    $info_spedizione = $cls_partita->info_spedizione($ing);

    //echo "<h1>info spedizione 313 --> ".print_r($info_spedizione)."</h1>";

    if($info_spedizione!=null)
    {
        $control_spedizione = "si";

        if(file_exists($pathcompletoAR.$info_spedizione["Immagine_Fronte"]))
        {
            $layout.="<script>$('#AR_fronte').show();</script>";
            $layout.="<script>$('#AR_fronte').attr('onclick','apri_notifica(\"".$pathcompletoAR.$info_spedizione["Immagine_Fronte"]."\")')</script>";
        }

        if(file_exists($pathcompletoAR.$info_spedizione["Immagine_Retro"]))
        {
            $layout.="<script>$('#AR_retro').show();</script>";
            $layout.="<script>$('#AR_retro').attr('onclick','apri_notifica(\"".$pathcompletoAR.$info_spedizione["Immagine_Retro"]."\")')</script>";
        }
    }
    else
        $control_spedizione = "no";


    $rate_previste = $ing["Rate_Previste"];
    $tipo_totale_rate = $ing["Tipo_Totale_Rate"];

    $disable_radio_1 = "";
    $disable_radio_2 = "";

    $checked_radio_1 = " checked ";
    $checked_radio_2 = "";

    if($rate_previste==0)
    {
        $rate_previste = null;
        $disable = ' disabled ';
        $rateizza = '';
    }
    else
    {
        $rateizza = ' checked ';
        $disable = '';

        if($tipo_totale_rate==2)
        {
            $disable_radio_1 = " disabled ";
            $checked_radio_1 = "";
            $checked_radio_2 = " checked ";
        }
        else
            $disable_radio_2 = " disabled ";
    }

//var_dump($ing["Importo"]);

    $importo = $ing["Importo"];
    $sanzione = $ing["Sanzione"];
    $spese_prec = $ing["Spese_Precedenti"];
    $spese_not_prec = $ing["Spese_Notifica_Precedenti"];
    $addizionale = $ing["Addizionale"];
    $interessi = $ing["Interessi"];
    $interessi_prec = $ing["Interessi_Precedenti"];
    $interessi_cod = $ing["Interessi_Codici_Tributo"];



    $spese_not = $ing["Spese_Notifica"];

    $can = $ing["CAN"];
    $cad = $ing["CAD"];

    $periodoInteressi = $partita["Atto"][count($partita["Atto"])-1]["Semestri"];

    $val_can_cad = $can + $cad;

    if($can>0)
        $layout.= "<script>$('#can_sel').attr('selected','selected');</script>";
    else if($cad>0)
        $layout.= "<script>$('#cad_sel').attr('selected','selected');</script>";

    $tot_spese = $spese_not + $can + $cad;

    $tot_dovuto = $ing["Totale_Dovuto"];

    $totaleCheck = $totale_codici+$spese_not_prec+$interessi+$interessi_prec+$tot_spese;
    //var_dump($importo);
    if($importo==null) $importo = 0;
    //$cls_help->alert($totaleCheck." ".$tot_dovuto." ".$totale_codici. " ".($importo+$sanzione+$addizionale+$interessi_cod) );
    if( number_format($totaleCheck,2)!=number_format($tot_dovuto,2) && $importo>0){
        $layout.= "<script>$('.new_version').hide();</script>";
        $layout.= "<script>$('.old_version').show();</script>";
        $no_mod = 1;
    }

    $totale_pagamenti = $cls_partita->totale_pagamenti($ing["ID"],$ing["Partita_ID"],$c);
    $pagamenti_precedenti = $cls_partita->pagamenti_precedenti($ing["ID"],$ing["Partita_ID"]);

    //echo "<h1>aa ".$pagamenti_precedenti." bb ".$totale_pagamenti."</h1>";

    $diritto_riscossione_min = $ing["Diritto_Riscossione_Minimo"];
    $totale_1 = $tot_dovuto + $diritto_riscossione_min - $pagamenti_precedenti;
    $diritto_riscossione_max = $ing["Diritto_Riscossione_Massimo"];
    $totale_2 = $tot_dovuto + $diritto_riscossione_max - $pagamenti_precedenti;

    $parametri =  $cls_partita->getDataParametri($c, date('Y/m/d'), $partita["Tipo"]); // new parametri_annuali( $c, date('d/m/Y'), $partita["Tipo"] );
    //print_r($parametri);

    $para_can = $parametri["CAN"];
    $para_cad = $parametri["CAD"];
    if($parametri["Diritto_Riscossione_Minimo"]>0 && $partita["Flag_Blocco_Diritto_Riscossione"]!="si"){
        $para_diritto_min = number_format($parametri["Diritto_Riscossione_Minimo"],2, ",","");
        $para_diritto_max = number_format($parametri["Diritto_Riscossione_Massimo"],2, ",","");
    }
    else{
        $para_diritto_min = "";
        $para_diritto_max = "";
    }


}
else
{
    $para_diritto_min = 0;
    $para_diritto_max = 0;
    $para_can = 0;
    $para_cad = 0;
}
//echo $atto[$y]["Date_Preavvisi"][0];

$parametri_notifica = $cls_partita->array_notifica();// new parametri_notifica(null);
//$parametri_notifica->array_notifica();
//echo "<h1>".print_r($parametri_notifica["Stati"])."</h1>";
//echo "<h1>Qui</h1>";
$options_stati = $cls_partita->options_select_array($parametri_notifica["Stati"]);
$options_motivi = $cls_partita->options_select_array($parametri_notifica["Motivi"]);
$options_a_mani = $cls_partita->options_select_array($parametri_notifica["Mode_A_Mani"], "Descrizione" , "Articolo");
$options_per_posta = $cls_partita->options_select_array($parametri_notifica["Mode_Per_Posta"], "Descrizione" , "Articolo");
$options_eccezionali = $cls_partita->options_select_array($parametri_notifica["Mode_Eccezionali"], "Descrizione" , "Articolo");
$options_blocco = $cls_partita->options_select_array($parametri_notifica["BloccoCoattiva"]);

//echo "<h1>lkasdc".$options_blocco."</h1>"

?>

    <!-- ********** GESTIONE LINK MENU ********** -->
    <script>
        var no_mod = "<?php echo $no_mod; ?>";

        //F3
        switchMenuImg("F3");
        F3_button = function(){
            if( ultimo_id == atto_corrente )
            {
                if(no_mod==1){
                    alert("ATTENZIONE!!! Non e' possibile modificare i dati degli atti! La versione dei dati in archivio e' obsoleta e disponibile solo in consultazione.");
                    return false;
                }

                if($('#flag_blocco').is(":checked") && $('#rielabora').is(":checked"))
                {
                    alert("Non e' possibile inserire il FLAG Rielabora in presenza del FLAG Blocco Coazione e viceversa!");
                    return false;
                }


                control = submit_buttons('Update');
                if(control)
                    $("#btnSub").trigger("click");
            }
            else
            {
                alert("Le notifiche precedenti non possono essere modificate. Selezionare l'ultima notifica.");
            }
        }

        //F4
        switchMenuImg("F4");
        F4_button = function(){
            if( ultimo_id == atto_corrente )
            {
                control = submit_buttons('Delete');
                if(control)
                    $("#btnSub").trigger("click");
            }
            else
            {
                alert("Le notifiche precedenti non possono essere modificate. Selezionare l'ultima notifica.");
            }
        }

        //F5
        switchMenuImg("F5");
        F5_button = function(){
            location.href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        }

        //F6
        switchMenuImg("F6");
        F6_button = function(){
            if( modifica == 0 )
            {
                top.location.href = "gestione_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            }
            else
                alert("salvare i dati o annullare prima di procedere");

        }
    </script>

    <!-- ********** ARRAY DA PHP ********** -->
    <script>
    //alert("qui");
        var tipo_atto = "<?php echo isset($ing["Atto"])?$ing["Atto"]:""; ?>";

        var PEC = "<?php echo $PEC_utente; ?>";
        var control_spedizione = "<?php echo $control_spedizione; ?>";

        var perc_diritto_minimo = "<?php echo $para_diritto_min; ?>";
        var perc_diritto_massimo = "<?php echo $para_diritto_max; ?>";
        var blocco_coazione = "<?php echo $partita["Flag_Blocco_Coazione"]; ?>";

        var protocollo = new Array();
        var data_protocollo = new Array();
        var data_elaborazione = new Array();
        var data_calcolo = new Array();
        var data_notifica = new Array();
        var data_stampa = new Array();
        var stato_ing = new Array();
        var stato_not = new Array();
        var ind_valid = new Array();
        var motivo_not = new Array();
        var modalita_not = new Array();
        var tipo_ufficiale = new Array();
        var modalita_stampa = new Array();
        var printer_id = new Array();
        var atto_rettificato = new Array();
        var note_not = new Array();
        var rielabora = new Array();
        var rettifica = new Array();
        var stato_ing = new Array();
        var stato_esec = new Array();
        var stampa_ing = new Array();
        var spese_not = new Array();
        var spese_prec = new Array();
        var spese_not_prec = new Array();
        var can = new Array();
        var cad = new Array();
        var importo_dov = new Array();
        var sanzione = new Array();
        var interessi = new Array();
        var interessi_prec = new Array();
        var addizionale = new Array();
        var old_ing = new Array();
        var totale_interessi = new Array();
        var tot_dovuto = new Array();
        var tot_dovuto_meno_pag = new Array();

        var tot_pagato = new Array();
        var pagamenti_precedenti = new Array();
        var tot_spese = new Array();
        var rif_atto = new Array();
        var num_rate = new Array();
        var data_rate = new Array();
        var num_preav = new Array();
        var data_preav = new Array();
        var diritto_min = new Array();
        var diritto_max = new Array();
        var tot_1 = new Array();
        var tot_2 = new Array();

        var info_spedizione = new Array();
        var AR_fronte = new Array();
        var AR_retro = new Array();

        var crono = new Array();
        var pdf = new Array();
        var rar = new Array();
        var txt = new Array();

        var id_atti = new Array();
        var num_atti = "<?php echo $numero_atti; ?>";
        var ultimo_id = "<?php if($numero_atti !=0 ){ echo  $atto[ $numero_atti - 1 ]["ID"]; } ?>";
        var pariodoInteressi = new Array();

        //alert('<?= count($atto); ?>');

        <?php
        //echo "<h1>count ".count($atto)."</h1>";
        for($y=0; $y < count($atto); $y++)
        {
        // 	alert($y);
        $rimanenza = $cls_partita->dovuto_senza_pagamenti($atto[$y],$c);// $atto[$y]->dovuto_senza_pagamenti();

        $spedizione = $cls_partita->info_spedizione($atto[$y]); //$atto[$y]->info_spedizione();
        //print_r($rimanenza);
        //print_r($spedizione);
        //echo "alert('".$spedizione["Immagine_Fronte"]."');";
        if($spedizione!=null)
        {
            $js_spedizione = "si";
            if(file_exists($pathcompletoAR.$spedizione["Immagine_Fronte"]))
                $js_AR_fronte = $pathcompletoAR.$spedizione["Immagine_Fronte"];
            else
                $js_AR_fronte = "";
            if(file_exists($pathcompletoAR.$spedizione["Immagine_Retro"]))
                $js_AR_retro = $pathcompletoAR.$spedizione["Immagine_Retro"];
            else
                $js_AR_retro = "";
        }
        else
        {
            $js_spedizione = "no";
            $js_AR_fronte = "";
            $js_AR_retro = "";
        }
        ?>

        pariodoInteressi[<?php echo $y; ?>] = "<?php echo $atto[$y]["Semestri"]; ?>";
        info_spedizione[<?php echo $y; ?>] = "<?php echo $js_spedizione; ?>";
        AR_fronte[<?php echo $y; ?>] = "<?php echo $js_AR_fronte; ?>";
        AR_retro[<?php echo $y; ?>] = "<?php echo $js_AR_retro; ?>";

        id_atti[<?php echo $y; ?>] = "<?php echo $atto[$y]["ID"]; ?>";
        protocollo[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Protocollo"],"DB"); ?>";
        data_protocollo[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Data_Protocollo"],"DB"); ?>";
        data_elaborazione[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Data_Elaborazione"],"DB"); ?>";
        data_calcolo[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Data_Calcolo_Interessi"], "DB"); ?>";
        data_stampa[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Data_Stampa"], "DB"); ?>";
        data_notifica[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Data_Notifica"], "DB"); ?>";
        stato_ing[<?php echo $y; ?>] = "<?php echo $atto[$y]["Stato"]; ?>";

        stato_not[<?php echo $y; ?>] = "<?php echo $atto[$y]["Stato_Notifica"]; ?>";
        ind_valid[<?php echo $y; ?>] = "<?php echo $atto[$y]["Indirizzo_Validato"]; ?>";
        motivo_not[<?php echo $y; ?>] = "<?php echo $atto[$y]["Motivo_Notifica"]; ?>";
        modalita_not[<?php echo $y; ?>] = "<?php echo $atto[$y]["Modalita_Notifica"]; ?>";
        note_not[<?php echo $y; ?>] = "<?php echo $atto[$y]["Note_Notifica"]; ?>";
        rielabora[<?php echo $y; ?>] = "<?php echo $atto[$y]["Rielabora_Flag"]; ?>";
        rettifica[<?php echo $y; ?>] = "<?php echo $atto[$y]["Rettifica_Flag"]; ?>";
        tipo_ufficiale[<?php echo $y; ?>] = "<?php echo $atto[$y]["Tipo_Ufficiale"]; ?>";
        modalita_stampa[<?php echo $y; ?>] = "<?php echo $atto[$y]["Modalita_Stampa"]; ?>";
        printer_id[<?php echo $y; ?>] = "<?php echo $atto[$y]["PrinterId"]; ?>";
        atto_rettificato[<?php echo $y; ?>] = "<?php echo $atto[$y]["Atto_Rettificato"]; ?>";
        stato_esec[<?php echo $y; ?>] = "<?php echo $atto[$y]["Stato_Esecuzione"]; ?>";
        stampa_ing[<?php echo $y; ?>] = "<?php echo $atto[$y]["Stato_Stampa"]; ?>";
        spese_not[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Spese_Notifica"],2,",",""); ?>";
        spese_prec[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Spese_Precedenti"],2,",",""); ?>";
        spese_not_prec[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Spese_Notifica_Precedenti"],2,",",""); ?>";
        can[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["CAN"],2,",",""); ?>";
        cad[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["CAD"],2,",",""); ?>";
        importo_dov[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Importo"],2,",",""); ?>";
        sanzione[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Sanzione"],2,",",""); ?>";
        interessi[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Interessi"],2,",",""); ?>";
        addizionale[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Addizionale"],2,",",""); ?>";
        interessi_prec[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Interessi_Precedenti"],2,",",""); ?>";
        totale_interessi[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Interessi_Precedenti"] + $atto[$y]["Interessi"],2,',',''); ?>";
        if(totale_interessi[<?php echo $y; ?>]=="")	totale_interessi[<?php echo $y; ?>] = "0,00";
        tot_dovuto[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Totale_Dovuto"],2,',',''); ?>";
        if(tot_dovuto[<?php echo $y; ?>]=="")	tot_dovuto[<?php echo $y; ?>] = "0,00";
        tot_pagato[<?php echo $y; ?>] = "<?php echo number_format( $cls_partita->totale_pagamenti($atto[$y]["ID"], $atto[$y]["Partita_ID"],$c) ,2,',',''); ?>";
        if(tot_pagato[<?php echo $y; ?>]=="")	tot_pagato[<?php echo $y; ?>] = "0,00";
        pagamenti_precedenti[<?php echo $y; ?>] = "<?php echo number_format($cls_partita->pagamenti_precedenti($atto[$y]["ID"], $atto[$y]["Partita_ID"]),2,",",""); ?>";
        if(pagamenti_precedenti[<?php echo $y; ?>]=="")	pagamenti_precedenti[<?php echo $y; ?>] = "0,00";

        tot_dovuto_meno_pag[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Totale_Dovuto"]-$cls_partita->pagamenti_precedenti($atto[$y]["ID"], $atto[$y]["Partita_ID"]),2,',',''); ?>";
        if(tot_dovuto_meno_pag[<?php echo $y; ?>]=="")	tot_dovuto_meno_pag[<?php echo $y; ?>] = "0,00";

        diritto_min[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Diritto_Riscossione_Minimo"],2, ",",""); ?>";
        diritto_max[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Diritto_Riscossione_Massimo"],2, ",",""); ?>";

        tot_1[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Totale_Dovuto"]-$cls_partita->pagamenti_precedenti($atto[$y]["ID"], $atto[$y]["Partita_ID"])+$atto[$y]["Diritto_Riscossione_Minimo"],2, ",",""); ?>";
        tot_2[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Totale_Dovuto"]-$cls_partita->pagamenti_precedenti($atto[$y]["ID"], $atto[$y]["Partita_ID"])+$atto[$y]["Diritto_Riscossione_Massimo"],2, ",",""); ?>";

        tot_spese[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Spese_Notifica"] + $atto[$y]["CAN"] + $atto[$y]["CAD"],2, ",",""); ?>";
        rif_atto[<?php echo $y; ?>] = "<?php echo $cls_mathF->conv_num($atto[$y]["Riferimento"]); ?>";
        num_rate[<?php echo $y; ?>] = "<?php echo $cls_mathF->conv_num($atto[$y]["Rate_Previste"]); ?>";
        data_rate[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($atto[$y]["Data_Richiesta_Rate"], "DB"); ?>";

        //alert('<?= $atto[$y]["Date_Preavvisi"][0]; ?>');

        num_preav[<?php echo $y; ?>] = "<?php if($atto[$y]["Date_Preavvisi"][0]=="")echo 0; else echo count($atto[$y]["Date_Preavvisi"]); ?>";
        data_preav[<?php echo $y; ?>] = "<?php echo $atto[$y]["Date_Preavvisi"][(count($atto[$y]["Date_Preavvisi"])-1)]; ?>";
        old_ing[<?php echo $y; ?>] = "<?php echo number_format($atto[$y]["Importo"]+$atto[$y]["Sanzione"]+$atto[$y]["Addizionale"]+$atto[$y]["Interessi_Codici_Tributo"],2,',',''); ?>";
        <?php
        $apri_pdf_js="";
        $apri_txt_js="";
        $apri_rar_js="";
        $pdf_def_js = $cls_partita->attoStampato( $atto[$y]["Atto"] , "DEFINITIVA" , $atto[$y]);
        //echo "alert('".$pdf_def_js[0]."');";
        if($pdf_def_js!="notFound")
        {
            $path_pdf_js = $pdf_def_js[0];
            $apri_pdf_js = substr( $path_pdf_js , strpos( $path_pdf_js , "/archivio/" ));
        }

        $flusso_js = $cls_partita->attoStampato ( $atto[$y]["Atto"] , "FLUSSO" , $atto[$y]);
        if($flusso_js!="notFound")
        {
            $path_txt_js = $flusso_js[0];
            $apri_txt_js = substr( $flusso_js[0] , strpos( $flusso_js[0] , "/archivio/" ));
            $path_rar_js = $flusso_js[1];
            $apri_rar_js = substr( $flusso_js[1] , strpos( $flusso_js[1] , "/archivio/" ));
        }

        $crono_js = "";
        if($atto[$y]["Atto_Rettificato"]==1){
            $crono_js.= "RETTIFICA";
            if($atto[$y]["ID_Cronologico"]>0){
                $crono_js.= strtoupper(" n. ".$atto[$y]["ID_Cronologico"]." del ".$atto[$y]["Anno_Cronologico"])." ";
            }
            else{
                $crono_js.= " (Crono. da assegnare) ";
            }
            $crono_js.= strtoupper(substr($atto[$y]["Atto"], 0,25));
            $crono_js.= strtoupper(" n. ".$atto[$y-1]["ID_Cronologico"]." del ".$atto[$y-1]["Anno_Cronologico"]);
        }
        else{
            $crono_js.= strtoupper(substr($atto[$y]["Atto"], 0,25));
            if($atto[$y]["ID_Cronologico"]>0){
                $crono_js.= strtoupper(" n. ".$atto[$y]["ID_Cronologico"]." del ".$atto[$y]["Anno_Cronologico"]);
            }
            else{
                $crono_js.= " (Crono. da assegnare)";
            }
        }

        ?>

        pdf[<?php echo $y; ?>] = "<?php echo $apri_pdf_js; ?>";
        txt[<?php echo $y; ?>] = "<?php echo $apri_txt_js; ?>";
        rar[<?php echo $y; ?>] = "<?php echo $apri_rar_js; ?>";
        crono[<?php echo $y; ?>] = "<?php echo $crono_js; ?>";
        <?php
        }
        ?>

    </script>

    <!-- ********** NUOVA ELABORAZIONE ********** -->
    <script>

        $(document).ready(function(){

            $(".new_elabo").hide();
            $(".dati_elabo").click(function(){
                $(".new_elabo").toggle();
            });

        });

    </script>

    <!-- ********** CALENDARIO ********** -->
    <script>
        $( function() {

            $( ".picker" ).datepicker();

        } );

    </script>

    <!-- ********** AGGIORNAMENTO PAGINA E CALCOLO ********** -->
    <script>

        var atto_corrente = id_atti[num_atti-1];

        function dettagli_not(value)
        {
            atto_corrente = id_atti[value];

            for(var num_righe = 0;num_righe<id_atti.length;num_righe++)
            {
                $('#riga_atto_'+num_righe).removeClass('color_red');
            }

            $('#riga_atto_'+value).addClass('color_red');

            $('#periodoInteressi').attr("title",pariodoInteressi[value]);
            $('#protocollo').text(protocollo[value]);
            $('#data_protocollo').text(data_protocollo[value]);

            $('#data_elaborazione').text(data_elaborazione[value]);
            $('#data_calcolo').text(data_calcolo[value]);
            $('#data_stampa').text(data_stampa[value]);

            $('#data_notifica').val(data_notifica[value]);
            $('#esec_ing').val(stato_esec[value]);
            $('#stato_ing').val(stato_ing[value]);
            $('#stato_not').val(stato_not[value]);
            if(ind_valid[value]=="si")
                $('#indirizzo_validato').prop('checked',true);
            else
                $('#indirizzo_validato').prop('checked',false);

            if(stato_not[value]>0)
                $('#indirizzo_validato').prop('disabled',false);
            else
                $('#indirizzo_validato').prop('disabled',true);

            $('#motivo_not').val(motivo_not[value]);
            $('#modalita_not').val(modalita_not[value]);
            $('#note_notifica').val(note_not[value]);
            if(rielabora[value]=="si")
                $('#rielabora').prop('checked',true);
            else
                $('#rielabora').prop('checked',false);

            $('#tipo_ufficiale').val(tipo_ufficiale[value]);
            $('#modalita_stampa').val(modalita_stampa[value]);
            $('#PrinterId').val(printer_id[value]);

            if(rielabora[value]=="si")
                $('#rielabora').prop('checked',true);
            else
                $('#rielabora').prop('checked',false);
            if(rettifica[value]=="si")
                $('#rettifica').prop('checked',true);
            else
                $('#rettifica').prop('checked',false);

            $('#stampa_ing').val(stampa_ing[value]);
            $('#spese_ing').val(spese_not[value]);
//            $('#addizionale').val(addizionale[value]);

            if( can[value] != '0,00' )
            {
                $('#can_cad').val(can[value]);
                $('#CAN_CAD').val('CAN');
            }
            else if( cad[value] != '0,00' )
            {
                $('#can_cad').val(cad[value]);
                $('#CAN_CAD').val('CAD');
            }
            else
            {
                $('#can_cad').val('');
                $('#CAN_CAD').val('');
            }

            $('#spese_prec_ing').val(spese_prec[value]);
            $('#spese_not_precedenti').val(spese_not_prec[value]);

            $('#importo_ing').val(old_ing[value]);
//            $('#sanzione_ing').text(sanzione[value]);
            $('#interessi_ing').val(interessi[value]);
            $('#interessi_prec_ing').val(interessi_prec[value]);
            $('#tot_interessi_ing').val(totale_interessi[value]);
            $('#tot_dovuto_ing').val(tot_dovuto[value]);
            $('#tot_dovuto_display').val(tot_dovuto_meno_pag[value]);
            $('#tot_pagamenti_ing').val(tot_pagato[value]);
            $('#pagamenti_precedenti').val(pagamenti_precedenti[value]);
            $('#tot_spese_ing').val(tot_spese[value]);

            $('#diritto_min').val(diritto_min[value]);
            $('#tot_1').val(tot_1[value]);
            $('#diritto_max').val(diritto_max[value]);
            $('#tot_2').val(tot_2[value]);

            $('#control_rif').val(rif_atto[value]);
            $('#num_rate').val(num_rate[value]);
            $('#data_richiesta').val(data_rate[value]);
            if(num_rate[value]==0)	$('#num_rate').val("");

            $('#num_preavviso').text(num_preav[value]);
            $('#data_preavviso').text(data_preav[value]);

            if(num_rate[value]!=0)
            {
                $('#num_rate').prop('disabled',false);
                $('#data_richiesta').prop('disabled',false);
                $('#rate_id').prop('checked',true);
            }
            else
            {
                $('#num_rate').prop('disabled',true);
                $('#data_richiesta').prop('disabled',true);
                $('#rate_id').prop('checked',false);
            }

            if(pdf[value]!="")
            {
                $('#file_pdf').attr('onclick',"apri('"+pdf[value]+"')");
                $('#pdf_link').show();
            }
            else
            {
                $('#file_pdf').attr('onclick',"");
                $('#pdf_link').hide();
            }

            if(rar[value]!="")
            {
                $('#flusso_txt').attr('onclick',"apri('"+txt[value]+"')");
                $('#flusso_rar').attr('onclick',"apri('"+rar[value]+"')");
                $('#flusso').show();
            }
            else
            {
                $('#flusso_txt').attr('onclick',"");
                $('#flusso_rar').attr('onclick',"");
                $('#flusso').hide();
            }

            $('#cronologico').text(crono[value]);

            control_spedizione = info_spedizione[value];
            if(AR_fronte[value]!="")
            {
                $('#AR_fronte').show();
                $('#AR_fronte').attr('onclick',"apri_notifica('"+AR_fronte[value]+"')");
            }
            else
            {
                $('#AR_fronte').attr('onclick','');
                $('#AR_fronte').hide();
            }

            if(AR_retro[value]!="")
            {
                $('#AR_retro').show();
                $('#AR_retro').attr('onclick',"apri_notifica('"+AR_retro[value]+"')");
            }
            else
            {
                $('#AR_retro').attr('onclick','');
                $('#AR_retro').hide();
            }
        }

        function inserisci_spese()
        {
            valore = $('#CAN_CAD').val();

            if(valore=='CAN')
            {
                $('#can_cad').val('<?php echo number_format($para_can,2, ",",""); ?>');
                spese = parseFloat($('#spese_ing').val().replace(",","."));
                cancad = parseFloat($('#can_cad').val().replace(",","."));

                tot_spese = spese + cancad;
                numero = number_format(tot_spese,2,",",".");

                $('#tot_spese_ing').text(numero);
            }
            else if(valore=='CAD')
            {
                $('#can_cad').val('<?php echo number_format($para_cad,2, ",",""); ?>');
                spese = parseFloat($('#spese_ing').val().replace(",","."));
                cancad = parseFloat($('#can_cad').val().replace(",","."));

                tot_spese = spese + cancad;
                numero = number_format(tot_spese,2,",","");

                $('#tot_spese_ing').text(numero);
            }
            else
            {
                tot_spese = parseFloat($('#spese_ing').val().replace(",","."));;
                $('#can_cad').val('0,00');
                $('#tot_spese_ing').text($('#spese_ing').val());
            }

            spese_manuali();
        }

        function spese_manuali()
        {
            inter = control_numero ( 'interessi_ing' );
            if(inter=="")
                inter = "0,00";
            $('#interessi_ing').val(inter);
            inter = parseFloat( inter.replace(",",".") );

            inter_prec = control_numero ( 'interessi_prec_ing' );
            if(inter_prec=="")
                inter_prec = "0,00";
            $('#interessi_prec_ing').val(inter_prec);
            inter_prec = parseFloat( inter_prec.replace(",",".") );

            spese_prec = $('#spese_not_precedenti').val();
            if(spese_prec=="")
                spese_prec = "0,00";
            $('#spese_not_precedenti').val(spese_prec);
            spese_prec = parseFloat( spese_prec.replace(",",".") );

            tot_interessi = number_format( inter + inter_prec ,2, ",","" );

            importo = control_numero ( 'importo_codici' );
            importo = parseFloat( importo.replace(",",".") );

            tot_inter_importo = inter + inter_prec + importo;

            valore_can_cad = $('#can_cad').val().replace(",",".");
            if(valore_can_cad == "" )
                valore_can_cad = "0,00";
            $('#can_cad').val(valore_can_cad);
            spese_can_cad = parseFloat( valore_can_cad.replace(",",".") );
            spese_not = control_numero ( 'spese_ing' );
            if(spese_not == "" )
                spese_not = "0,00";
            $('#spese_ing').val(spese_not);
            spese_not = parseFloat( spese_not.replace(",",".") );

            tot_spese = number_format( spese_can_cad + spese_not , 2, ",","" ) ;
            tot_parziale = spese_can_cad + spese_not + tot_inter_importo +spese_prec;

            totale_dovuto = number_format( tot_parziale,2, ",","" ) ;

            pagamenti_precedenti = $('#pagamenti_precedenti').val().replace(",",".");
            tot_dovuto_pag = number_format( tot_parziale-pagamenti_precedenti,2, ",","" );

            if(blocco_coazione!="si" && parseFloat( perc_diritto_minimo.replace(",","."))>0){
                diritto_riscossione_min = (tot_parziale-spese_can_cad-pagamenti_precedenti)/100*parseFloat( perc_diritto_minimo.replace(",",".") );
                diritto_riscossione_max = (tot_parziale-spese_can_cad-pagamenti_precedenti)/100*parseFloat( perc_diritto_massimo.replace(",",".") );
            }
            else{
                diritto_riscossione_min = 0.00;
                diritto_riscossione_max = 0.00;
            }


            tot_1 = tot_parziale+diritto_riscossione_min;
            tot_2 = tot_parziale+diritto_riscossione_max;

            diritto_riscossione_min = number_format( diritto_riscossione_min,2, ",","" ) ;
            diritto_riscossione_max = number_format( diritto_riscossione_max,2, ",","" ) ;
            tot_1 = number_format( tot_1,2, ",","" ) ;
            tot_2 = number_format( tot_2,2, ",","" ) ;


            $('#tot_interessi_ing').val(tot_interessi);
            $('#tot_spese_ing').val(tot_spese);

            $('#tot_dovuto_ing').val(totale_dovuto);
            $('#tot_dovuto_display').val(tot_dovuto_pag);
            $('#diritto_min').val(diritto_riscossione_min);
            $('#diritto_max').val(diritto_riscossione_max);
            $('#tot_1').val(tot_1);
            $('#tot_2').val(tot_2);


        }

        function rateo()
        {
            check = $( "#rate_id:checked" ).attr('name');

            if(check=='rateizza')
            {
                $('#num_rate').prop('disabled',false);
                $('#data_richiesta').prop('disabled',false);
            }
            else
            {
                $('#num_rate').val('').prop('disabled',true);
                $('#data_richiesta').val('').prop('disabled',true);
            }
        }

        function cambia_title(value)
        {
            testo = $('#'+value+ ' option:selected').text();
            $('#'+value).attr('title',testo);
        }

        function edit_validato(value){
            if($('#'+value+ ' option:selected').val()!="")
                $('#indirizzo_validato').prop('disabled',false);
            else
                $('#indirizzo_validato').prop('disabled',true);
        }
    </script>

    <!-- ********** MODALI ********** -->
    <script>

        function Dim_Alert ( sWidth, sHeight )
        {
            setupPagina = "dialogWidth:" + sWidth + "px; ";
            setupPagina += "dialogHeight:" + sHeight + "px; ";
            setupPagina += "dialogLeft:80px; dialogTop:80px;";

            return setupPagina;
        }

        function mod_rate()
        {
            if($('#num_rate').val()!="" && $('#data_richiesta').val() != "" )
            {
                if("<?php echo $rate_previste ?>" == "")
                {
                    alert("Prima di accedere alla gestione rate salvare l'ingiunzione.");
                }
                else if( "<?php echo $rate_previste ?>" != "" )
                {
                    //strDim = Dim_Alert(900, 800);
                    var stringa = "<?= WEB_ROOT; ?>/search/coattiva/gestione_rate.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>&atto="+atto_corrente;
                    openWindowSearch(stringa,{width:900, height:800, left:screen.width/2-450, top:screen.height/2-400}); 
                    //valorediritorno = window.showModalDialog(stringa,"", strDim);
                }
            }
            else
            {
                alert("E' necessario selezionare la Rateizzazione e salvare sia il numero di rate sia la data di richiesta rateizzazione prima di poter accedere alla gestione.");
            }

        }

        function change_num_rate()
        {
            if($('#num_rate').val()!="" && $('#data_richiesta').val() != "" && "<?php echo $rate_previste ?>" != "")
            {
                if( $('#num_rate').val() != "<?php echo $rate_previste ?>" )
                {
                    alert('Eliminare la rateizzazione per modificare il numero di rate.');
                    $('#num_rate').val("<?php echo $rate_previste ?>");
                }
            }
        }

        function change_data_rate()
        {
            if($('#num_rate').val()!="" && $('#data_richiesta').val() != "" && "<?php echo $rate_previste ?>" != "")
            {
                if( $('#data_richiesta').val() != "<?php echo $cls_date->Get_DateNewFormat($data_richiesta,"DB"); ?>" )
                {
                    alert('Eliminare la rateizzazione per modificare la data di richiesta rateizzazione.');
                    $('#data_richiesta').val("<?php echo $cls_date->Get_DateNewFormat($data_richiesta,"DB"); ?>");
                }
            }
        }

        function info_sped()
        {
            if(control_spedizione == "si")
            {
              //  strDim = Dim_Alert(600, 500);
                var stringa = "<?= WEB_ROOT; ?>/search/coattiva/info_spedizione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>&atto="+atto_corrente;
                //valorediritorno = window.showModalDialog(stringa,"", strDim);
                openWindowSearch(stringa,{width:600, height:500, left:((screen.width/2)-300), top:((screen.height/2)-250)});
            }
            else
            {
                alert("Non e' stata effettuata nessuna importazione di notifica per questo atto.");
            }
        }

        function lista_mail()
        {
            strDim = Dim_Alert(850, 600);
            var stringa = "modali/info_email.php?c=<?php echo $c; ?>&partita=<?php echo $partita_ID; ?>";
            valorediritorno = window.showModalDialog(stringa,"", strDim);
        }

        function visura(value)
        {
            link = "<?= SUPER_WEB_ROOT; ?>/gitco2/elaborazioni/utente_visura.php?richiesta_singola=si&c=<?php echo $c; ?>&a=<?php echo $a?>&ID_Atto="+value;
            location.href= link;
        }

        function avviso()
        {
            alert("ATTENZIONE! Data di Notifica modificata!");
        }

        function apri(link)
        {
            window.open(link);
        }

        function apri_notifica(link)
        {
            link = "apri_notifica.php?link="+link+"";
            window.open(link,"Notifica_AR","width=900,height=600");
        }
    </script>

    <!-- ********** AJAX FORM / SUBMIT ********** -->
    <script>

        $(document).ready(function(){

            $('#cerca_id').ajaxForm(

                function(value) {
                    var array_ritorno = value.split(' ');
                    if(array_ritorno[0]=='NO')
                    {
                        alert('Codice partita non trovato!');
                        top.location.href = "ingiunzione.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    }
                    else
                    {
                        top.location.href = "ingiunzione.php?partita="+value+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
                    }
                });

            /*$('#form_ingiunzione').ajaxForm(

                function(value) {
                    var array_ritorno = value.split(' ');

                    if(array_ritorno[0]=='OK')
                    {
                        alert('Salvataggio effettuato correttamente!');
                        top.location.href = "ingiunzione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    }
                    else if(array_ritorno[0]=='ERROR')
                    {
                        alert("Errore nel salvataggio dell'atto. "+value);
                    }
                    else if(array_ritorno[0]=='NUOVO')
                    {
                        alert('Nuova notifica creata correttamente!');
                        top.location.href = "ingiunzione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    }
                    else if(array_ritorno[0]=='ERRORNUOVO')
                    {
                        alert("Errore nel salvataggio dell'ingiunzione.");
                        top.location.href = "ingiunzione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    }
                    else if(array_ritorno[0]=='ERRORATE')
                    {
                        alert("Errore nel salvataggio delle rate.");
                        top.location.href = "ingiunzione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    }
                    else if(array_ritorno[0]=='PAGATO')
                    {
                        alert("L'atto e' stato pagato completamente!\n\nImpossibile creare una nuova notifica!\n\n");

                    }
                    else if(array_ritorno[0]=='DELETE')
                    {
                        alert('Atto eliminato correttamente!');
                        top.location.href = "ingiunzione.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    }
                    else if(array_ritorno[0]=='PAGAMENTO')
                    {
                        alert("ATTENZIONE!\n\nSono stati rilevati dei pagamenti collegati all'atto che si desidera eliminare.\nPer eliminare l'atto � necessario procedere alla cancellazione dei relativi pagamenti.\n\n");

                    }
                    else
                    {
                        alert("Errore nella procedura: "+value);
                    }

                });*/

            $("#submit_click").click( F3_button );

            $("#delete_click").click( F4_button );

            $("#elimina_interessi").click(function cancella_interessi() {

                ritorno = confirm("Si stanno modificando i dati del database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
                $('#invia_submit').val('Interessi');

                if(ritorno)
                {$("#btnSub").trigger("click");}
                else
                {return	false;}

            });

            $("#elimina_sanzione").click(function cancella_sanzione() {

                ritorno = confirm("Si stanno modificando i dati del database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
                $('#invia_submit').val('Sanzione');

                if(ritorno)
                {$("#btnSub").trigger("click");}
                else
                {return	false;}

            });

        });

        function controllo_salva(value)
        {
            if(value=='Insert')
            {
                control = submit_buttons(value);
                if(control)
                {
                    $('#invia_submit').val(value);
                    $('form#form_ingiunzione').submit();
                }
            }
            else if( num_atti == rif_atto.length )
            {
                control = submit_buttons(value);
                if(control)
                {
                    $('#invia_submit').val(value);
                    $('form#form_ingiunzione').submit();
                }
            }
            else
            {
                alert("Le notifiche precedenti non possono essere modificate. Selezionare l'ultima notifica.");
                alert(num_atti);
                alert(rif_atto.length);
            }
        }
    </script>

    <script>
        function elabora_nuovo_atto(value)
        {
            if(value=="Avviso di intimazione ad adempiere")		scelta_atto = "avv_intimazione";
            else if(value=="Ingiunzione")						scelta_atto = "Ingiunzione";
            else	return false;

            link = "<?= SUPER_WEB_ROOT; ?>/gitco2/elaborazioni/elabora_atto.php?richiesta_singola=si&tipo_atto="+scelta_atto;
            link+= "&partita_ID=<?php echo $partita_ID; ?>&partita=<?php echo $partita["Comune_ID"]; ?>&tipo_partita=<?php echo $partita["Tipo"]; ?>&anno_rif=<?php echo $partita["Anno_Riferimento"]; ?>&p=<?php echo $partita["Utente_ID"]; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            location.href= link;
        }

        function stampa_richiesta(value)
        {
            link = "<?= SUPER_WEB_ROOT; ?>/gitco2/stampe/richiesta_validazione_notifica.php?richiesta_singola=si&c=<?php echo $c; ?>&a=<?php echo $a?>&ID_Atto="+value;
            location.href= link;
        }

        function archivia(value)
        {
            link = "<?= SUPER_WEB_ROOT; ?>/gitco2/stampe/archiviazione_atto.php?richiesta_singola=si&c=<?php echo $c; ?>&a=<?php echo $a?>&ID_Atto="+value;
            location.href= link;
        }

        function control_stampa()
        {
            if($('#data_stampa').text()!="")
            {
                alert("Data di stampa presente in archivio. Impossibile modificare Ufficiale e modalita' di invio.");
                annulla();
            }
            else{
                modalita_stampa = $('#modalita_stampa').val();
                tipo_ufficiale = $('#tipo_ufficiale').val();
                switch(tipo_atto){
                    case "Sollecito pre ingiunzione":
                        $('#modalita_stampa').val("ordinaria");
                        $('#tipo_ufficiale').val("diretta");
                        break;
                    case "Avviso di messa in mora":
                        if(modalita_stampa=="ordinaria" || modalita_stampa=="PEC")
                            $('#modalita_stampa').val("raccomandata");
                        break;
                    case "Ingiunzione":
                        if(modalita_stampa=="ordinaria" || modalita_stampa=="PEC")
                            $('#modalita_stampa').val("posta");
                        break;
                    case "Avviso di intimazione ad adempiere":
                        if(modalita_stampa=="ordinaria" || modalita_stampa=="PEC")
                            $('#modalita_stampa').val("posta");
                        break;
                    case "Sollecito di pagamento":
                        $('#modalita_stampa').val("ordinaria");
                        $('#tipo_ufficiale').val("diretta");
                        break;
                }

                if($('#tipo_ufficiale').val()=="rettifica")
                    $('#modalita_stampa').val('ordinaria');

                if($('#tipo_ufficiale').val()=="giudiziario" ){
                    if($('#modalita_stampa').val()=="ordinaria"){
                        alert("L'Ufficiale Giudiziario non dispone della modalita' di invio tramite posta ordinaria. Effettuare un'altra selezione.");
                        $('#modalita_stampa').val('posta');
                    }
                }

            }
        }



        $(document).ready(function() {
            $('#trigger').click(function() {
                $('#overlay').fadeIn(300);
            });
            $('#close').click(function() {
                $('#overlay').fadeOut(300);
            });
        });


    </script>



    <form id=form_ingiunzione name=form_ingiunzione action="ingiunzione_salva.php" method=post style="z-index: 1;" >
        <input name=invia_submit  id=invia_submit	type=hidden	value="" >

        <input type=hidden name=c value="<?php echo $c; ?>" >
        <input type=hidden name=a value="<?php echo $a; ?>" >
        <input type=hidden name=p value="<?php echo $p; ?>" >
        <input type=hidden name=partita value="<?php echo $partita_ID; ?>" >
        <input type=hidden name=atto value="<?php echo $atto_ID; ?>" >
        <input type=hidden name=ultimoAtto id=ultimoAtto value="<?php echo $ultimoAtto; ?>" >
        <input type=hidden name=nomePagina id=nomePagina value="ingiunzione" >

    <?php if(count($atto)!=0)
    {?>

        <input name=control_rif id=control_rif type=hidden value="<?php echo $rif; ?>">

        <table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">

            <tr class="text_left riga_dispari" style="height:30px;" >
                <td class="width4"><br></td>
                <td class="width1"><br></td>
                <td class="text_center width12"><b>Cronologico</b></td>
                <td class="width1"><br></td>
                <td class="text_left width24"><b>Atto</b></td>
                <td class="width1"><br></td>
                <td class="text_center width14"><b>Data Notifica</b></td>
                <td class="width1"><br></td>
                <td class="text_center width13"><b>Dovuto (&euro;)</b></td>
                <td class="width1"><br></td>
                <td class="text_center width13"><b>Pagato (&euro;)</b></td>
                <td class="width1"><br></td>
                <td class="text_center width13"><b>Residuo (&euro;)</b></td>
                <td class="width1"><br></td>
            </tr>

        <?php

        for($i=0; $i<count($atto); $i++)
        {
            $y = $i;

            $aggiunta_rosso = "";
            if($i==count($atto)-1)
                $aggiunta_rosso = " color_red";

            if ($y++ % 2)
            {$stile_riga = 'class="riga_dispari text_left'.$aggiunta_rosso.'"'	;	}
            else
            {$stile_riga = 'class="riga_pari text_left'.$aggiunta_rosso.'"'	;	}

            $dovuto_table = $atto[$i]["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($atto[$i]["ID"], $atto[$i]["Partita_ID"]);
            $pagato_table = $cls_partita->totale_pagamenti($atto[$i]["ID"], $atto[$i]["Partita_ID"],$c);
            $residuo_table = $atto[$i]["Totale_Dovuto"] - $cls_partita->pagamenti_precedenti($atto[$i]["ID"], $atto[$i]["Partita_ID"]) - $pagato_table;
            if($atto[$i]["ID_Cronologico"]!=0)
                $crono_table = $atto[$i]["ID_Cronologico"]."/".$atto[$i]["Anno_Cronologico"];
            else
                $crono_table = "Assente";

            ?>

            <tr <?php echo $stile_riga; ?> id=riga_atto_<?php echo $i; ?>>
                <td class="text_center">
                    <a onMouseover="title='Dettagli Notifica'" href="#" style="text-decoration:none;" onClick="dettagli_not('<?php echo $i; ?>');" >
                        <img src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" >
                    </a>
                <td><br></td>
                <td class="text_center"><?php echo $crono_table; ?></td>
                <td><br></td>
                <td class="text_left"><?php echo substr($atto[$i]["Atto"],0,25);?></td>
                <td><br></td>
                <td class="text_center"><?php echo $cls_date->Get_DateNewFormat($atto[$i]["Data_Notifica"],"DB"); ?></td>
                <td><br></td>
                <td class="text_center"><?php echo number_format($dovuto_table,2,",",""); ?></td>
                <td><br></td>
                <td class="text_center"><?php echo number_format($pagato_table,2,",",""); ?></td>
                <td><br></td>
                <td class="text_center"><?php echo number_format($residuo_table,2,",",""); ?></td>
                <td><br></td>
            </tr>

        <?php }?>
        </table>

        <?php }?>

        <div id="overlay" style="z-index: 100;">
            <div id="popup">
                <div id="close">X</div>
                <h2>Dettaglio Codici Tributo</h2>
                <?php
                //print_r($partita["Tributo"]);
                $countPT = isset($partita["Tributo"])?count($partita["Tributo"]):0;
                for($i=0;$i<$countPT;$i++){
                    echo "<p class='text_left'>".$partita["Tributo"][$i]["Codice_Tributo"]." - ".$partita["Tributo"][$i]["Tipo_Tributo"]." - ".number_format($partita["Tributo"][$i]["Imposta"],2,',','')." &euro; </p>";
                }

                ?>

            </div>
        </div>

        <?php if(count($atto)!=0)
        {?>

          <div class="row" style="margin-top: 10px;">
          	<div class="col col-lg-10 col-lg-offset-1">
          		<div class="form-group">
          			<div class="col-lg-8 control-label resize" style="text-align: left;"><p id=cronologico class="color_titolo font16 font_bold"><?php echo $testo_cronologico; ?></p></div>
          			<div class="col-lg-2">
                  <a onMouseover="title='Elabora nuovo atto'" href="#" style="text-decoration:none;" onClick="elabora_nuovo_atto('<?php echo $ing["Atto"]; ?>');" >
                      <img src="<?= IMMAGINIWEB; ?>/elabora.png" width=20 height=20 border=0 >
                  </a>
                  <a onMouseover="title='Stampa richieste validazione notifica'" href="#" style="text-decoration:none;" onClick="stampa_richiesta('<?php echo $ing["ID"]; ?>');" >
                      <img src="<?= IMMAGINIWEB; ?>/printer.png" width=20 height=20 border=0 >
                  </a>
                  <a onMouseover="title='Archiviazione'" href="#" style="text-decoration:none;" onClick="archivia('<?php echo $ing["ID"]; ?>')" >
                      <img src="<?= IMMAGINIWEB; ?>/archiviazione.png" width=20 height=20 border=0 >
                  </a>
                  <a onMouseover="title='Controllo email'" href="#" style="text-decoration:none;" onClick="lista_mail();" >
                      <img src="<?= IMMAGINIWEB; ?>/email_mini.png" width=25 height=20 border=0 >
                  </a>
                  <a onMouseover="title='Visura motorizzazione'" href="#" style="text-decoration:none;" onClick="visura('<?php echo $ing["ID"]; ?>')" >
                      <img src="<?= IMMAGINIWEB; ?>/car_icon.png" width=25 height=20 border=0 >
                  </a>
          			</div>
                <div class="col-lg-2 text_right">
                  <a id=pdf_link href="#" style="text-decoration:none;">
                      <img id=file_pdf src="<?= IMG;?>/icon_pdf.png" style="text-decoration:none; border:none; " width="20" height="20" onclick="apri('<?php echo $apri_pdf; ?>');" title="File PDF definitivo">
                  </a>
                  <a id=flusso href="#" style="text-decoration:none;">
                      <img id=flusso_txt src="<?= IMMAGINIWEB; ?>/txt.png" style="text-decoration:none; border:none; " width="20" height="20" onclick="apri('<?php echo $apri_txt; ?>');" title="File TXT Flusso">
                      <img id=flusso_rar src="<?= IMMAGINIWEB; ?>/rar.png" style="text-decoration:none; border:none; " width="20" height="20" onclick="apri('<?php echo $apri_rar; ?>');" title="Archivio RAR flusso">
                  </a>
          			</div>
          		</div>
          	</div>
          </div>

          <div class="row">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-4 control-label resize" style="text-align: left;"><span class="color_titolo font16 font_bold">PROTOCOLLO</span></label>
          			<div class="col-lg-8">
                  <input type="text" style="width: 50%;" class="form-control resize <?php echo $readonlyProt;?>" <?php echo $readonlyProt;?> id=protocollo name=protocollo value="<?php echo $protocollo; ?>">
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-5">
          		<div class="form-group">
          			<label class="col-lg-4 control-label resize" style="text-align: left;"><span class="color_titolo font16 font_bold">DATA</span></label>
          			<div class="col-lg-8">
                  <input type="text" style="width: 50%;" class="text_center form-control resize vld_date <?php echo $pickerProt;?><?php echo $readonlyProt;?>" size=9 id=data_protocollo name=data_protocollo <?php echo $readonlyProt;?> value="<?php echo $cls_date->Get_DateNewFormat($data_protocollo,"DB"); ?>">
              	</div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 3%;">
          	<div class="col col-lg-3 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-7 control-label resize" style="text-align: left;">Data elaborazione</label>
          			<div class="col-lg-5">
                  <span id=data_elaborazione class="font_bold resize"><?php echo $cls_date->Get_DateNewFormat($data_elaborazione,"DB"); ?></span>
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-3">
          		<div class="form-group">
          			<label class="col-lg-7 control-label resize" style="text-align: left;">Data calcolo</label>
          			<div class="col-lg-5">
                  <span id=data_calcolo class="font_bold resize"><?php echo $cls_date->Get_DateNewFormat($data_calcolo,"DB"); ?></span>
                </div>
          		</div>
          	</div>
            <div class="col col-lg-3">
              <div class="form-group">
                <label class="col-lg-7 control-label resize" style="text-align: left;">Data stampa</label>
                <div class="col-lg-5">
                  <span id=data_stampa class="font_bold resize"><?php echo $cls_date->Get_DateNewFormat($data_stampa,"DB"); ?></span>
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-top: 1%;">
          	<div class="col col-lg-3 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-5 control-label resize" style="text-align: left;">Data Notifica</label>
          			<div class="col-lg-7">
                  <input id=data_notifica class="form-control resize vld_data picker text_center" name=data_notifica type=text value='<?php echo $cls_date->Get_DateNewFormat($data_notifica,"DB"); ?>' size=9 onchange = "avviso();">
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-7">
          		<div class="form-group">
          			<label class="col-lg-2 control-label resize" style="text-align: left;">Modalita</label>
          			<div class="col-lg-10">
                  <select id=modalita_not name=modalita_not class=" form-control resize " onchange="cambia_title('modalita_not');">
                      <option></option>
                      <optgroup label="Tramite soggetto preposto"><?php echo $options_a_mani; ?></optgroup>
                      <optgroup label="Per posta"><?php echo $options_per_posta; ?></optgroup>
                      <optgroup label="Eccezionali"><?php echo $options_eccezionali; ?></optgroup>
                  </select>
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 1%;">
          	<div class="col col-lg-3 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-5 control-label resize" style="text-align: left;">Giacienza</label>
          			<div class="col-lg-7">
                  <select id=stato_not name=stato_not class="form-control resize" onchange="cambia_title('stato_not');edit_validato('stato_not');" >
                      <option></option>
                      <?php echo $options_stati; ?>
                  </select>
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-3">
          		<div class="form-group">
          			<label class="col-lg-4 control-label resize" style="text-align: left;">Anomalie</label>
          			<div class="col-lg-8">
                  <select id=motivo_not name=motivo_not class="form-control resize" onchange="cambia_title('motivo_not');">
                      <option ></option>
                      <?php echo $options_motivi; ?>
                  </select>
                </div>
          		</div>
          	</div>
            <div class="col col-lg-3 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-6 control-label resize" style="text-align: left;"><span id=stato_atto class="font_bold"><?php echo $stato_esecutivo; ?></span></label>
          			<div class="col-lg-6">
                  <input type=button class="form-control resize btn-primary" id=preavvisi name=preavvisi value="Preavvisi" onclick="">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 1%;">
          	<div class="col col-lg-10 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-2 control-label resize" style="text-align: left;">Note</label>
          			<div class="col-lg-10">
                  <input id=note_notifica class="form-control resize" name=note_notifica type=text value="<?php echo $note_notifica; ?>">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 2%;">
      			<div class="form-group resize">
              <div class=" col-lg-2 col-lg-offset-1">
                  <label>
                      <input  type=checkbox id=indirizzo_validato name=indirizzo_validato value="si" title="Ind. validato - Flag di verifica dell'indirizzo del destinatario. E' necessaria la verifica nel caso sia selezionato uno Stato di Giacenza"> Ind. validato
                  </label>
              </div>
              <div class=" col-lg-2">
                  <label>
                      <input  type=checkbox id=rielabora name=rielabora title="Rielabora - Flag che permette di elaborare un nuovo atto prima della scadenza dei termini." value="si"> Rielabora
                  </label>
              </div>
              <div class=" col-lg-2">
                  <label>
                      <input  type=checkbox id=rettifica name=rettifica title="Rettifica - Flag che permette di elaborare un atto di rettifica." value="si"> Rettifica
                  </label>
              </div>
            </div>
        	</div>

          <div class="row" style="margin-top: 3%;">
            <div class="col col-lg-4 col-lg-offset-1">
              <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Stampatore</label>
                <div class="col-lg-8">
                  <select id="PrinterId" name="PrinterId" class="form-control resize">
                      <?php echo $optPrinter; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col col-lg-3">
              <div class="form-group">
                <label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di invio</label>
                <div class="col-lg-7">
                  <select id="modalita_stampa" name="modalita_stampa" class="form-control resize" onchange="control_stampa();">
                      <option value="posta">Raccomandata A.G.</option>
                      <option value="ordinaria">Posta ordinaria</option>
                      <option value="raccomandata">Raccomandata</option>
                      <option value="mani">A mani</option>
                      <option value="PEC">Tramite PEC</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col col-lg-3">
              <div class="form-group">
                <label class="col-lg-6 control-label resize" style="text-align: left;">Modalita' modifica</label>
                <div class="col-lg-6">
                  <select id="tipo_ufficiale" name="tipo_ufficiale" class="form-control resize" onchange="control_stampa();">
                      <option value="diretta">Diretta</option>
                      <option value="riscossione">Uff. Riscossione</option>
                      <option value="giudiziario">Uff. Giudiziario</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-top: 3%;">
            <div class="col col-lg-2 col-lg-offset-1">
              <div class="form-group">
                <!--<label class="col-lg-4 control-label resize" style="text-align: left;">Stampatore</label>-->
                <div class="col-lg-12">
                  <input type=button class="form-control resize btn-primary" id=spedizione name=spedizione value="Spedizione" onclick="info_sped();">
                </div>
              </div>
            </div>
            <div class="col col-lg-2 col-lg-offset-1">
              <div class="form-group">
                <!--<label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di invio</label>-->
                <div class="col-lg-12">
                  <a id="AR_fronte" href="#" onMouseover="title='AR Fronte'" style="text-decoration:none;">
                      <font class="color_titolo font16 font_bold under_decor">AR Fronte</font>
                  </a>
                  <a id="CAD_fronte" href="#" onMouseover="title='CAD Fronte'" style="text-decoration:none;">
                      <font class="color_titolo font16 font_bold under_decor">CAD Fronte</font>
                  </a>
                </div>
              </div>
            </div>
            <div class="col col-lg-2">
              <div class="form-group">
                <!--<label class="col-lg-4 control-label resize" style="text-align: left;">Tipo di invio</label>-->
                <div class="col-lg-12">
                  <a id="AR_retro" href="#" onMouseover="title='AR Retro'" style="text-decoration:none;">
                      <font class="color_titolo font16 font_bold under_decor">AR Retro</font>
                  </a>
                  <a id="CAD_retro" href="#" onMouseover="title='CAD Retro'" style="text-decoration:none;">
                      <font class="color_titolo font16 font_bold under_decor">CAD Retro</font>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-top: 4%;">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Totale Codici Tributo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=importo_codici name=importo_codici size=7 value="<?php echo number_format($totale_codici,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-2 col-lg-offset-3">
          		<div class="form-group">
          			<!--<label class="col-lg-2 control-label resize" style="text-align: left;">Modalita</label>-->
          			<div class="col-lg-12">
                  <input type="button" class="form-control btn-primary resize" id="trigger" value="Dettaglio Codici Tributo">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row old_version" style="display: none;">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Importi Ingiunzione&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="corrige_numero form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=importo_ing name=importo_ing size=7 value="<?php echo number_format($importo+$sanzione+$addizionale+$interessi_cod,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-4 col-lg-offset-1">
          		<div class="form-group">
          			<!--<label class="col-lg-2 control-label resize" style="text-align: left;">Modalita</label>-->
          			<div class="col-lg-12">
                  <p>OLD VERSION Imp. +Sanz. +Addiz. +Int. Cod.Trib.</p>
                </div>
          		</div>
          	</div>
          </div>

          <div class="row">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> + &nbsp;&nbsp;&nbsp;&nbsp; Spese Notifica Ing. Prec.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" readonly style="background-color: rgb(153, 204, 255); border: 2px solid black;" id=spese_not_precedenti name=spese_not_precedenti onchange="spese_manuali();" size=7 value="<?php echo number_format($spese_not_prec,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> + &nbsp;&nbsp;&nbsp;&nbsp;
                  <b id=periodoInteressi title="<?php echo $periodoInteressi; ?>">
                    Totale Interessi Ing.<img  src="<?= IMMAGINIWEB; ?>/info.png" width=15px height=15px>
                  </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=tot_interessi_ing name=tot_interessi_ing size=7 value="<?php echo number_format($interessi + $interessi_prec,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-2 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-5 control-label resize" style="text-align: left;">Preced&nbsp;&euro;</label>
          			<div class="col-lg-7">
                  <input class="form-control resize corrige_numero" id=interessi_prec_ing name=interessi_prec_ing size=7 value="<?php echo number_format($interessi_prec,2,",",""); ?>" onchange="spese_manuali();">
                </div>
          		</div>
          	</div>
            <div class="col col-lg-2">
          		<div class="form-group">
          			<label class="col-lg-5 control-label resize" style="text-align: left;">Nuovi&nbsp;&euro;</label>
          			<div class="col-lg-7">
                  <input class="form-control resize corrige_numero" id=interessi_ing name=interessi_ing size=7 value="<?php echo number_format($interessi,2,",",""); ?>" onchange="spese_manuali();">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> + &nbsp;&nbsp;&nbsp;&nbsp;Totale Spese Notifica&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=tot_spese_ing name=tot_spese_ing size=7 value="<?php echo number_format($tot_spese,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          	<div class="col col-lg-2 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-5 control-label resize" style="text-align: left;">Spese&nbsp;&euro;</label>
          			<div class="col-lg-7">
                  <input class="form-control resize corrige_numero" id=spese_ing name=spese_ing size=7 value="<?php echo number_format($spese_not,2,",",""); ?>" onchange="spese_manuali();">
                </div>
          		</div>
          	</div>
            <div class="col col-lg-2">
          		<div class="form-group">
          			<div class="col-lg-5" style="text-align: left;">
                  <select class="form-control resize" id=CAN_CAD name=CAN_CAD onchange=inserisci_spese();>
                      <option></option>
                      <option id=cad_sel >CAD</option>
                      <option id=can_sel >CAN</option>
                  </select>
                </div>
          			<div class="col-lg-7">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=can_cad name=can_cad size=7 value="<?php echo number_format($val_can_cad,2,",",""); ?>" onchange="spese_manuali();">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> - &nbsp;&nbsp;&nbsp;&nbsp; Pagamenti Prec.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=pagamenti_precedenti name=pagamenti_precedenti size=7 value="<?php echo number_format($pagamenti_precedenti,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 3%;">
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> = &nbsp;&nbsp;&nbsp;&nbsp;Totale Parziale&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input type="hidden" name="tot_dovuto_ing" id="tot_dovuto_ing" value="<?php echo number_format($tot_dovuto,2,",",""); ?>">
                  <input class="form-control resize corrige_numero" readonly style="background-color: rgb(153, 204, 255); border: 2px solid black;" id=tot_dovuto_display name=tot_dovuto_display size=7 value="<?php echo number_format($tot_dovuto-$pagamenti_precedenti,2,",",""); ?>">
                </div>
          		</div>
          	</div>
            <div class="col col-lg-3 col-lg-offset-2">
          		<div class="form-group">
          			<label class="col-lg-6 control-label resize" style="text-align: right; color: green;">Pagamenti&nbsp;&euro;</label>
          			<div class="col-lg-6">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=tot_pagamenti_ing name=tot_pagamenti_ing size=7 value="<?php echo number_format($totale_pagamenti,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" >
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> + &nbsp;&nbsp;&nbsp;&nbsp;Diritto riscossione <?php echo $para_diritto_min; ?>%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=diritto_min name=diritto_min size=7 value="<?php echo number_format($diritto_riscossione_min,2,",",""); ?>">
                </div>
          		</div>
          	</div>
            <div class="col-lg-2"><p class="resize">Pag. entro 60 giorni</p></div>
            <div class="col col-lg-3">
          		<div class="form-group">
          			<label class="col-lg-6 control-label resize" style="text-align: right; color: red;">= Totale 1&nbsp;&euro;</label>
          			<div class="col-lg-6">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=tot_1 name=tot_1 size=7 value="<?php echo number_format($totale_1,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" >
          	<div class="col col-lg-5 col-lg-offset-1">
          		<div class="form-group">
          			<label class="col-lg-9 control-label resize" style="text-align: left;"> + &nbsp;&nbsp;&nbsp;&nbsp;Diritto riscossione <?php echo $para_diritto_max; ?>%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&euro;</label>
          			<div class="col-lg-3">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=diritto_max name=diritto_max size=7 value="<?php echo number_format($diritto_riscossione_max,2,",",""); ?>">
                </div>
          		</div>
          	</div>
            <div class="col-lg-2"><p class="resize">Pag. oltre i 60 giorni</p></div>
            <div class="col col-lg-3">
          		<div class="form-group">
          			<label class="col-lg-6 control-label resize" style="text-align: right; color: red;">= Totale 2&nbsp;&euro;</label>
          			<div class="col-lg-6">
                  <input class="form-control resize corrige_numero" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly id=tot_2 name=tot_2 size=7 value="<?php echo number_format($totale_2,2,",",""); ?>">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 2%;">
          	<div class="col col-lg-3 col-lg-offset-1">
          		<div class="form-group">
                <div class=" col-lg-6">
                    <label>
                        <span class="color_titolo resize"><b>Rateizzazione</b></span> <input type="checkbox" id=rate_id name=rateizza value=rateizza onclick="rateo();" <?php echo $rateizza; ?>>
                    </label>
                </div>
          			<div class="col-lg-6 resize">
                  Tot 1<input <?php echo $disable_radio_1; ?> type=radio id=importo_rateizzazione_1 name=importo_rateizzazione value="1" <?php echo $checked_radio_1; ?> onclick="click_rate();" checked>
                  Tot 2<input <?php echo $disable_radio_2; ?> <?php echo $checked_radio_2; ?> type=radio id=importo_rateizzazione_2 name=importo_rateizzazione value="2" onclick="click_rate();">
                </div>
          		</div>
          	</div>
            <div class="col col-lg-3">
          		<div class="form-group">
          			<label class="col-lg-6 control-label resize" style="text-align: left;">Data Richiesta</label>
          			<div class="col-lg-6">
                  <input id=data_richiesta class="form-control resize text_center picker" name=data_richiesta type=text value='<?php echo $cls_date->Get_DateNewFormat($data_richiesta,"DB"); ?>' size=9 <?php echo $disable; ?> onchange="change_data_rate();">
                </div>
          		</div>
          	</div>
            <div class="col col-lg-2">
          		<div class="form-group">
          			<label class="col-lg-7 control-label resize" style="text-align: left;">Numero Rate</label>
          			<div class="col-lg-5">
                  <input class="form-control resize" type="text" id=num_rate name=num_rate value="<?php echo $rate_previste; ?>" size=2 <?php echo $disable; ?> onchange="change_num_rate();">
                </div>
          		</div>
          	</div>
            <div class="col col-lg-2">
          		<div class="form-group">
          			<div class="col-lg-12">
                    <input type=button id=importi_rate name=importi_rate class="form-control btn-primary resize" value="Gestione rate" onclick="mod_rate();">
                </div>
          		</div>
          	</div>
          </div>

          <div class="row" style="margin-top: 2%;">
      			<div class="form-group resize">
              <div class=" col-lg-5 col-lg-offset-1">
                  <label>
                      <input type="checkbox" name=flag_maggiorazione id=flag_maggiorazione value="si">
                      <b>BLOCCO Maggiorazione</b>
                  </label>
              </div>
              <div class=" col-lg-5">
                  <label>
                      <input type="checkbox" name=flag_diritto_riscossione id=flag_diritto_riscossione value="si">
                      <b>BLOCCO Diritto Riscossione</b>
                  </label>
              </div>
            </div>
        	</div>

          <div class="row" style="margin-top: 1%;">
      			<div class=" col-lg-5 col-lg-offset-1">
              <div class="form-group">
                  <label class="control-label resize col-lg-12">
                    <input type="checkbox" name=flag_blocco id=flag_blocco value="si">
                    <b>BLOCCO Coazione</b>
                  </label>
              </div>
            </div>
            <div class=" col-lg-5">
              <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Motivi blocco</label>
                <div class="col-lg-8">
                  <select id=motivo_blocco name=motivo_blocco class="form-control resize" onchange="cambia_title('motivo_blocco');">
                      <option ></option>
                      <?php echo $options_blocco; ?>
                  </select>
              </div>
            </div>
          </div>
      	</div>

        <div class="row">
        	<div class="col col-lg-10 col-lg-offset-1">
        		<div class="form-group">
        			<label class="col-lg-2 control-label resize" style="text-align: left;">Note blocco</label>
        			<div class="col-lg-10">
                <input class="form-control resize" name="note_blocco" id="note_blocco" value="<?php echo $note_blocco; ?>" >
        			</div>
        		</div>
        	</div>
        </div>

        <?php } ?>

        <div class="form-group">
        	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
        </div>

    </form>

    <?php echo $layout; ?>

<?php

//error_reporting(E_ALL);
 include(INC."/footer.php"); ?>
