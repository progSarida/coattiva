<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php");//dati database

include(INC . "/header.php");
include(INC . "/menu.php");
include_once(CLS . "/cls_Utils.php");
include_once(CLS . "/cls_DateTimeInLine.php");
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_CoazioneUtils.php";

if ($_SESSION['username'] == NULL) {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB", false);
$cls_registry = new cls_registry();
$cls_coaz = new cls_Coazione();

//echo "first";

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$progr_n0 = $cls_help->getVar('id_n0');
$control_submit = $cls_help->getVar('submit_file');

$solo_rate = $cls_help->getVar('solo_rate');


//flush();
//ob_flush();
?>


    <script>
        var solo_rate = <?php echo $solo_rate; ?>;

        function controlli_inizio() {
            $('#progressbar').progressbar({
                value: false
            });
            $("#barlabel").text("Inizio controlli...");
        }

        function caricamento() {
            $('#progressbar_caricamento').progressbar({
                value: false
            });
            $("#barlabel_caricamento").text("Caricamento dati...");
        }

        function fine() {
            if (solo_rate == 0) {
                $("#progressbar").progressbar({value: 100});
                $("#barlabel").text("Controlli effettuati!");
                $("div#importazione").append("<div class='row' style='margin-top: 3%;'><div class='col-lg-10 col-lg-offset-1'><input type=button name=riepilogo class='btn btn-danger' value='Scarti' onclick='lista_scarti();'><input type=button name=avanti id=btn_importazione class='btn btn-primary' value='Importazione' onclick='importa();'></div></div>");
                $("#btn_importazione").trigger("click");
            } else
                location.href = "importazione_rate_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0=<?php echo $progr_n0; ?>&posted=true";
        }

        function fine_caricamento() {
            $("#progressbar_caricamento").progressbar({value: 100});
            $("#barlabel_caricamento").text("Dati caricati!");
        }

        function importa() {
           // ritorno = confirm("Sei sicuro di voler procedere con l'importazione?");
           // if (ritorno)
                location.href = "importazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0=<?php echo $progr_n0; ?>";
           // else
           //     return false;
        }

        function lista_scarti() {
            window.open("lista_scarti_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0=<?php echo $progr_n0; ?>", "Lista_scarti", "width=900, height=600");
        }

        function errore(value) {
            $("#barlabel").text(value);
        }
    </script>


    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <span class="titolo font18">Controlli Importazione</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="table_interna text_center" id="progressbar_caricamento" style="height:55px;">
                <div class="text_center" id="barlabel_caricamento"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="table_interna text_center" id="progressbar" style="height:55px;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div id=importazione></div>
        </div>
    </div>

<?php

$omoN2 = array();
$omoN3 = array();
//echo "second --> ".$progr_n0;

flush();
ob_flush();
flush();
ob_flush();

echo "<script>caricamento();</script>";

flush();
ob_flush();
flush();
ob_flush();
//sleep(2);

ini_set('memory_limit', '-1');

//set_time_limit(800);

//$duenovanta = $cls_coaz->getData_290($progr_n0);

$query = "SELECT * FROM 290_n0_n9 WHERE ID = '" . $progr_n0 . "'";
$duenovanta = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "290_n0_n9");

$query = "SELECT ID FROM 290_n1_n5 WHERE N0_ID = '" . $duenovanta["ID"] . "'";
$n1id = $cls_db->getResults($cls_db->ExecuteQuery($query));

$Tot100 = 0;
$count = 0;
for ($i = 0; $i < $duenovanta["Record_N1"]; $i++) {

    $query = "SELECT * FROM 290_n1_n5 WHERE ID = '" . $n1id[$i]['ID'] . "'";
    $duenovanta["n1"][$i] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "290_n1_n5");//new N1N5($n1id[$i]['ID']);

    $Tot100 += $duenovanta["n1"][$i]["Record_N2"];
    //$cls_help->alert($duenovanta["n1"][$i]["Record_N2"]);
}
//$cls_help->alert($Tot100);

for ($i = 0; $i < $duenovanta["Record_N1"]; $i++) {
    $query = "SELECT ID FROM 290_n2 WHERE N1_ID = '" . $duenovanta["n1"][$i]['ID'] . "' AND N0_ID = '" . $duenovanta["n1"][$i]['N0_ID'] . "'";
    $n2id = $cls_db->getResults($cls_db->ExecuteQuery($query));

    for ($x = 0; $x < $duenovanta["n1"][$i]["Record_N2"]; $x++) {
        $query = "SELECT * FROM 290_n2 WHERE ID = '" . $n2id[$x]['ID'] . "'";
        $duenovanta["n1"][$i]["n2"][$x] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "290_n2");//new N2($n2id[$i]['ID']);


        $query = "SELECT * FROM 290_n3 WHERE Codice_Partita = '" . $duenovanta["n1"][$i]["n2"][$x]['Codice_Partita'] . "' AND N0_ID = '" . $duenovanta["n1"][$i]["n2"][$x]['N0_ID'] . "'";
        $a_n3 = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "290_n3","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
        $duenovanta["n1"][$i]["n2"][$x]["num_n3"] = count($a_n3);


        for ($y = 0; $y < count($a_n3); $y++)
            $duenovanta["n1"][$i]["n2"][$x]["n3"][$y] = $a_n3[$y];


        $query = "SELECT * FROM 290_n4 WHERE Codice_Partita = '" . $duenovanta["n1"][$i]["n2"][$x]['Codice_Partita'] . "' AND N0_ID = '" . $duenovanta["n1"][$i]["n2"][$x]['N0_ID'] . "'";
        $a_n4 = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "290_n4","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
        $duenovanta["n1"][$i]["n2"][$x]["num_n4"] = count($a_n4);


        for ($z = 0; $z < count($a_n4); $z++)
            $duenovanta["n1"][$i]["n2"][$x]["n4"][$z] = $a_n4[$z];
        //var_dump($a_n4);
        //die;

        /*$parziale = 100/$duenovanta["Record_N1"];
        $percPar = ($parziale * $x) / $duenovanta["n1"][$i]["Record_N2"];
        $percOK = $parziale + $percPar;
        echo "<script>$( \"#progressbar_caricamento\" ).progressbar({value: " .intval($percOK). " });$( \"#barlabel_caricamento\" ).text(" .intval($percOK). "+'%');</script>";*/

        flush();
        ob_flush();
        flush();
        ob_flush();
        echo "<script>$( \"#progressbar_caricamento\" ).progressbar({value: " . intval($count * 100 / $Tot100) . " }); $( \"#barlabel_caricamento\" ).text( '" . intval($count * 100 / $Tot100) . "'+'%');</script>";
        flush();
        ob_flush();
        flush();
        ob_flush();

        $count++;
    }
    //$cls_help->alert("qui");

}

echo "<script>fine_caricamento();</script>";
//return $duenovanta;


//CICLO I RUOLI N1
for ($i = 0; $i < $duenovanta["Record_N1"]; $i++) {
    echo "<script>controlli_inizio();</script>";

    flush();
    ob_flush();
    flush();
    ob_flush();
    sleep(2);

    $enne1 = $duenovanta["n1"][$i];

    $progressivoN1 = $enne1["ID"];
    $comune_ruolo_N1 = $enne1["Codice_Ente"];
    $progr_minuta_N1 = $enne1["Progressivo_Minuta"];
    $num_ruolo_N1 = $enne1["Num_Ruolo"];
    $num_rate_N1 = $enne1["Num_Rate"];
    $ruolo_N1 = $enne1["Ruolo"];
    $cod_sede_N1 = $enne1["Codice_Sede"];
    $tipo_compenso_N1 = $enne1["Tipo_Compenso"];
    $ICIAP_N1 = $enne1["Ruolo_ICIAP"];
    $convenzione_N1 = $enne1["Num_Convenzione"];
    $flag_N1 = $enne1["Flag_Articoli"];

    //CICLO ANAGRAFICHE INTESTATARI N2
    for ($y = 0; $y < $duenovanta["Record_N2"]; $y++) {

        set_time_limit(30);

        flush();
        ob_flush();

        echo "<script>$( \"#progressbar\" ).progressbar({value: " . intval($y * 100 / $duenovanta["Record_N2"]) . " }); $(\"#barlabel\").text( '" . intval($y * 100 / $duenovanta["Record_N2"]) . "'+'%');</script>";


        //qui piazzo
        $control_anagrafica = 0;

        $enne2 = $enne1["n2"][$y];

        $progressivoN2 = $enne2["ID"];

        $query = "UPDATE 290_n2 SET Flag_Importazione = 'Importare', Flag_Partita = 'Importare' WHERE ID = '" . $progressivoN2 . "'";
        $cls_db->ExecuteQuery($query);//safe_query($query);

        /**************************************************************************************************
         *
         *                  AGGIUNTA ERRORI FILE JSON
         *
         *************************************************************************************************/
        $errori_N2 = json_decode($enne2["Json_Error"]);
        //echo "<br>N2 status --> ".$errori_N2[0]->status;

        if ($errori_N2[0]->status != "ok") {
            //echo "<br>dentro errore N2";
            $query = "UPDATE 290_n2 SET Flag_Importazione = 'FSCV', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
            $cls_db->ExecuteQuery($query);
        }

        //die;
        $comune_partita_N2 = $enne2["Codice_Ente"];
        $progr_minuta_N2 = $enne2["Progressivo_Minuta"];
        $codice_partita_N2 = $enne2["Codice_Partita"];

        //echo "<br>Codice partita N2 --> ".$enne2["Codice_Partita"]." --- ".$enne2["Cognome"]." ".$enne2["Nome"]." ".$enne2["Ditta"]." ".$enne2["ID"]."<br>";

        $indirizzo_res_N2 = $enne2["Indirizzo_Res"];
        $civico_res_N2 = $enne2["Civico_Res"];
        $let_civico_res_N2 = $enne2["Lettera_Civico_Res"];
        $interno_res_N2 = $enne2["Interno_Res"];
        $km_res_N2 = $enne2["Km_Res"];
        $cap_res_N2 = $enne2["Cap_Res"];
        $cc_res_N2 = $enne2["CC_Indirizzo_Res"];
        $frazione_res_N2 = $enne2["Frazione_Res"];

        $indirizzo_dom_N2 = $enne2["Indirizzo_Dom"];
        $civico_dom_N2 = $enne2["Civico_Dom"];
        $let_civico_dom_N2 = $enne2["Lettera_Civico_Dom"];
        $interno_dom_N2 = $enne2["Interno_Dom"];
        $km_dom_N2 = $enne2["Km_Dom"];
        $cap_dom_N2 = $enne2["Cap_Dom"];
        $cc_dom_N2 = $enne2["CC_Indirizzo_Dom"];
        $frazione_dom_N2 = $enne2["Frazione_Dom"];


        if (($indirizzo_res_N2 == "" || $cap_res_N2 == "" || $cap_res_N2 == "00000" || $cc_res_N2 == "") && ($indirizzo_dom_N2 == "" || $cap_dom_N2 == "" || $cap_dom_N2 == "00000" || $cc_dom_N2 == "")) {
            $query = "UPDATE 290_n2 SET Flag_Importazione = 'N2_IND', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
            $cls_db->ExecuteQuery($query);//safe_query($query);
        }

        $natura_giuridica_N2 = $enne2["Natura_Giuridica"];
        $codice_fiscale_N2 = $enne2["Codice_Fiscale"];

        $cognome_N2 = $enne2["Cognome"];
        $nome_N2 = $enne2["Nome"];
        $ditta_N2 = $enne2["Ditta"];
        $sesso_N2 = $enne2["Sesso"];
        $data_nascita_N2 = $cls_date->GetDateDB($enne2["Data_Nascita"], "DB");// to_mysql_date($enne2->Data_Nascita);
        $cc_nascita_N2 = $enne2["CC_Nascita"];

        if ($natura_giuridica_N2 == 2) {
            if ($cognome_N2 != "" && $nome_N2 != "") {
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N2_NG', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
            }
        } else if ($natura_giuridica_N2 == 1) {
            if ($ditta_N2 != "") {
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N2_NG', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
            }
        } else {
            $query = "UPDATE 290_n2 SET Flag_Importazione = 'N2_NG', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
            $cls_db->ExecuteQuery($query);//safe_query($query);
        }

        if ($natura_giuridica_N2 == 2) {
            $control_CF = true;
        } else {

            $a_CF = $cls_utils->decode_CF($codice_fiscale_N2);

            if ($a_CF === false) {
                $codice_fiscale_N2 = $cls_registry->compute_CF($cognome_N2, $nome_N2, $sesso_N2, $data_nascita_N2, $cc_nascita_N2);
                $a_CF = $cls_utils->decode_CF($codice_fiscale_N2);
            }

            if ($a_CF === false) {
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N2_CF', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $codice_fiscale_N2 = false;
            } else {
                $query = "UPDATE 290_n2 SET Codice_Fiscale = \"" . $codice_fiscale_N2 . "\" WHERE ID = " . $progressivoN2;
                $cls_db->ExecuteQuery($query);//safe_query($query);
            }
        }

        if ($cognome_N2 != "") {
            if (strlen($codice_fiscale_N2) > 11) {
                $ctrl_sesso = number_format(substr($codice_fiscale_N2, 9, 2));
                if ($ctrl_sesso > 40) $sesso_N2 = "F";
                else    $sesso_N2 = "M";
            } else
                $sesso_N2 = $enne2["Sesso"];
        } else
            $sesso_N2 = $enne2["Sesso"];

        $cointestatari_N2 = $enne2["Cointestatari"];

        //CICLO ANAGRAFICHE COOBBLIGATI O COOINTESTATARI N3
        for ($z = 0; $z < $enne2["num_n3"]; $z++) {
            $enne3 = $enne2["n3"][$z];

            $progressivoN3 = $enne3["ID"];

            $query = "UPDATE 290_n3 SET Flag_Importazione = 'Importare' WHERE ID = '" . $progressivoN3 . "'";
            $cls_db->ExecuteQuery($query);//safe_query($query);


            /**************************************************************************************************
             *
             *                  AGGIUNTA ERRORI FILE JSON
             *
             *************************************************************************************************/
            $errori_N3 = json_decode($enne3["Json_Error"]);
            // echo "<br>N3 status --> ".$errori_N3[0]->status;
            if ($errori_N3[0]->status != "ok") {
                // echo "<br>dentro errore N3";
                $query = "UPDATE 290_n3 SET Flag_Importazione = 'FSCV' WHERE ID = '" . $progressivoN3 . "'";
                $cls_db->ExecuteQuery($query);
                $query = "UPDATE 290_n2 SET Flag_Partita = 'no', Json_Error = '" . $enne3["Json_Error"] . "' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);
            }


            $comune_partita_N3 = $enne3["Codice_Ente"];
            $progr_minuta_N3 = $enne3["Progressivo_Minuta"];
            $codice_partita_N3 = $enne3["Codice_Partita"];

            $indirizzo_res_N3 = $enne3["Indirizzo_Res"];
            $civico_res_N3 = $enne3["Civico_Res"];
            $let_civico_res_N3 = $enne3["Lettera_Civico_Res"];
            $interno_res_N3 = $enne3["Interno_Res"];
            $km_res_N3 = $enne3["Km_Res"];
            $cap_res_N3 = $enne3["Cap_Res"];
            $cc_res_N3 = $enne3["CC_Indirizzo_Res"];
            $frazione_res_N3 = $enne3["Frazione_Res"];

            $indirizzo_dom_N3 = $enne3["Indirizzo_Dom"];
            $civico_dom_N3 = $enne3["Civico_Dom"];
            $let_civico_dom_N3 = $enne3["Lettera_Civico_Dom"];
            $interno_dom_N3 = $enne3["Interno_Dom"];
            $km_dom_N3 = $enne3["Km_Dom"];
            $cap_dom_N3 = $enne3["Cap_Dom"];
            $cc_dom_N3 = $enne3["CC_Indirizzo_Dom"];
            $frazione_dom_N3 = $enne3["Frazione_Dom"];

            if (($indirizzo_res_N3 == "" || $cap_res_N3 == "" || $cap_res_N3 == "00000" || $cc_res_N3 == "") && ($indirizzo_dom_N3 == "" || $cap_dom_N3 == "" || $cap_dom_N3 == "00000" || $cc_dom_N3 == "")) {
                $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_IND' WHERE ID = '" . $progressivoN3 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
            }

            $natura_giuridica_N3 = $enne3["Natura_Giuridica"];
            $codice_fiscale_N3 = $enne3["Codice_Fiscale"];

            $cognome_N3 = $enne3["Cognome"];
            $nome_N3 = $enne3["Nome"];
            $ditta_N3 = $enne3["Ditta"];

            if ($natura_giuridica_N3 == 2) {
                if ($cognome_N3 != "" && $nome_N3 != "") {
                    $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_NG' WHERE ID = '" . $progressivoN3 . "'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);
                    $query = "UPDATE 290_n2 SET Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);
                }
            } else if ($natura_giuridica_N3 == 1) {
                if ($ditta_N3 != "") {
                    $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_NG' WHERE ID = '" . $progressivoN3 . "'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);
                    $query = "UPDATE 290_n2 SET Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);
                }
            } else {
                $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_NG' WHERE ID = '" . $progressivoN3 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
            }

            if ($natura_giuridica_N3 == 2 && $codice_fiscale_N3 == "")
                $control_CF = true;
            else
                $control_CF = check_CFPI($codice_fiscale_N3, $natura_giuridica_N3);

            if ($control_CF != 1) {
                $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_CF' WHERE ID = '" . $progressivoN3 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
            }

            if ($cognome_N3 != "") {
                if (strlen($codice_fiscale_N3) > 11) {
                    $ctrl_sesso = number_format(substr($codice_fiscale_N3, 9, 2));
                    if ($ctrl_sesso > 40) $sesso_N3 = "F";
                    else    $sesso_N3 = "M";
                }
            } else
                $sesso_N3 = $enne3["Sesso"];

            $data_nascita_N3 = $enne3["Data_Nascita"];
            $cc_nascita_N3 = $enne3["CC_Nascita"];

            if ($comune_partita_N3 != $comune_partita_N2 || $progr_minuta_N3 != $progr_minuta_N2) {
                $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_N2_MINUTA' WHERE ID = '" . $progressivoN3 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N3_N2_MINUTA', SET Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);

                continue;
            }

            if ($codice_partita_N3 != $codice_partita_N2 && intval($codice_partita_N3) != intval($codice_partita_N2)) {
                $query = "UPDATE 290_n3 SET Flag_Importazione = 'N3_N2_PARTITA' WHERE ID = '" . $progressivoN3 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N3_N2_PARTITA', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);

                continue;
            }

            if ($sesso_N3 == "") $sesso = "D";
            else $sesso = $sesso_N3;

            $omonimi = check_omonimi($sesso, $codice_fiscale_N3, $ditta_N3, $codice_fiscale_N3, $nome_N3, $cognome_N3, $cc_nascita_N3, $data_nascita_N3, $c);
            if ($omonimi != "no") {
                $query = "SELECT Flag_Importazione FROM 290_n3 WHERE ID = '" . $progressivoN3 . "'";
                $control_errore = $cls_db->ExecuteQuery($query);//single_query($query);

                if ($control_errore == "Importare") {
                    $query = "UPDATE 290_n3 SET Flag_Importazione = 'Omonimia' WHERE ID = '" . $progressivoN3 . "'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);
                    $query = "UPDATE 290_n2 SET Flag_Partita = 'omoN3' WHERE ID = '" . $progressivoN2 . "'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);

                    continue;
                }
            }

        }

        $conta_info = 0;
        $conta_anno = 0;
        $a_info_cart = array();
        for ($x = 0; $x < $enne2["num_n4"]; $x++) {
            set_time_limit(60);
            $enne4 = $enne2["n4"][$x];
            if ($x == 0) {
                $temp_anno_tributo = $enne4["Anno_Tributo"];
                $temp_info_cartella = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['info'] = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['anno'][$conta_anno] = $enne4["Anno_Tributo"];
            }

            if ($enne4["Anno_Tributo"] != $temp_anno_tributo) {
                if ($enne4["Info_Cartella"] != $temp_info_cartella) {
                    $conta_info++;
                    $conta_anno = 0;
                } else {
                    $conta_anno++;
                }
                $a_info_cart[$conta_info]['info'] = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['anno'][$conta_anno] = $enne4["Anno_Tributo"];
            } else if ($enne4["Info_Cartella"] != $temp_info_cartella) {
                $conta_info++;
                $conta_anno = 0;

                $a_info_cart[$conta_info]['info'] = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['anno'][$conta_anno] = $enne4["Anno_Tributo"];
            }

            $temp_anno_tributo = $enne4["Anno_Tributo"];
            $temp_info_cartella = $enne4["Info_Cartella"];
        }

//        print_r($a_info_cart);
//        continue;
        $info_cartella = "";
        for ($x = 0; $x < count($a_info_cart); $x++) {
            if ($x > 0) {
                $info_cartella .= " - ";
            }

            if (count($a_info_cart[$x]['anno']) > 1)
                $info_cartella .= $a_info_cart[$x]['info'] . " ANNI";
            else
                $info_cartella .= $a_info_cart[$x]['info'] . " ANNO";

            for ($x_anno = 0; $x_anno < count($a_info_cart[$x]['anno']); $x_anno++) {
                $info_cartella .= " " . $a_info_cart[$x]['anno'][$x_anno];
            }
        }

        $control_N4 = 0;
        //CICLO INFO CONTABILI N4
        for ($x = 0; $x < $enne2["num_n4"]; $x++) {
            $enne4 = $enne2["n4"][$x];

            $progressivoN4 = $enne4["ID"];

            $query = "UPDATE 290_n4 SET Flag_Importazione = 'Importare' WHERE ID = '" . $progressivoN4 . "'";
            $cls_db->ExecuteQuery($query);//safe_query($query);


            /**************************************************************************************************
             *
             *                  AGGIUNTA ERRORI FILE JSON
             *
             *************************************************************************************************/
            $errori_N4 = json_decode($enne4["Json_Error"]);
            //echo "<br>N4 status --> ".$errori_N4[0]->status;
            if ($errori_N4[0]->status != "ok") {
                //echo "<br>dentro errore N4";

                $query = "UPDATE 290_n4 SET Flag_Importazione = 'FSCV' WHERE ID = '" . $progressivoN4 . "'";
                $cls_db->ExecuteQuery($query);
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'FSCV', Flag_Partita = 'no', Json_Error = '" . $enne4["Json_Error"] . "' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);
            }

            $comune_partita_N4 = $enne4["Codice_Ente"];
            $progr_minuta_N4 = $enne4["Progressivo_Minuta"];
            $codice_partita_N4 = $enne4["Codice_Partita"];

            //echo "<br>Codice partita N4 --> ".$enne4["Codice_Partita"]." ".$enne4["N2_ID"];

            $anno_tributo_N4 = $enne4["Anno_Tributo"];
            $codice_tributo_N4 = $enne4["Codice_Tributo"];
            $imponibile_N4 = $enne4["Imponibile"];
            $imposta_N4 = $enne4["Imposta"];
            $num_semestri_interessi_N4 = $enne4["Num_Semestri_Interessi"];
            $data_decorrenza_interessi_N4 = $enne4["Data_Decorrenza_Interessi"];
            $codice_reparto_N4 = $enne4["Codice_Reparto"];
            $info_cartella_N4 = $enne4["Info_Cartella"];

            $query = "SELECT ID FROM tributo WHERE CC = '" . $c . "' ";
            $query .= "AND Info_Cartella = \"" . $info_cartella . "\" AND Imposta = '" . $imposta_N4 . "' ";

            $result_cartella = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//single_answer_query($query);

            if ($result_cartella != null) {
                $query = "UPDATE 290_n4 SET Flag_Importazione = 'N4_INSERITO' WHERE ID = '" . $progressivoN4 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N4_INSERITO' , Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);

                $control_N4 = 1;

                break;
            }

            $tipo_info = $enne4["Tipo_Info"];

            $titolo_entrata_N4 = $enne4["Titolo_Entrata"];
            $descrizione_entrata_N4 = $enne4["Descrizione_Entrata"];

            $tipo_sanzione_N4 = $enne4["Tipo_Sanzione"];
            $titolo_sanzione_N4 = $enne4["Titolo_Sanzione"];
            $data_sanzione_N4 = $enne4["Data_Sanzione"];
            $targa_sanzione_N4 = $enne4["Targa_Sanzione"];

            $matricola_N4 = $enne4["Matricola"];

            if ($tipo_info == "S" and $tipo_sanzione_N4 == "IN") {
//				$stato_N4 = $enne4->Stato_Ingiunzione;
//				switch( $stato_N4 )
//				{
//					case "SALDA":
//
//						$query = "UPDATE 290_n4 SET Flag_Importazione = 'SALDATO_ING' WHERE ID = '".$progressivoN4."'";
//						safe_query($query);
//						$query = "UPDATE 290_n2 SET Flag_Importazione = 'SALDATO_ING' , Flag_Partita = 'no' WHERE ID = '".$progressivoN2."'";
//						safe_query($query);
//
//						$control_N4 = 1;
//
//						break;
//					case "ANNUL":
//
//						$query = "UPDATE 290_n4 SET Flag_Importazione = 'ANNULLATO_ING' WHERE ID = '".$progressivoN4."'";
//						safe_query($query);
//						$query = "UPDATE 290_n2 SET Flag_Importazione = 'ANNULLATO_ING' , Flag_Partita = 'no' WHERE ID = '".$progressivoN2."'";
//						safe_query($query);
//
//						$control_N4 = 1;
//
//						break;
//					case "NOTFE":
//
//						$query = "UPDATE 290_n4 SET Flag_Importazione = 'FERMO_ING' WHERE ID = '".$progressivoN4."'";
//						safe_query($query);
//						$query = "UPDATE 290_n2 SET Flag_Importazione = 'FERMO_ING' , Flag_Partita = 'no' WHERE ID = '".$progressivoN2."'";
//						safe_query($query);
//
//						$control_N4 = 1;
//
//						break;
//					case "DECED":
//
//						$query = "UPDATE 290_n4 SET Flag_Importazione = 'DECEDUTO_ING' WHERE ID = '".$progressivoN4."'";
//						safe_query($query);
//						$query = "UPDATE 290_n2 SET Flag_Importazione = 'DECEDUTO_ING' , Flag_Partita = 'no' WHERE ID = '".$progressivoN2."'";
//						safe_query($query);
//
//						$control_N4 = 1;
//
//						break;
//				}
//
//				if($control_N4 == 1)
//				{
//					break;
//				}
            }

            if ($comune_partita_N4 != $comune_partita_N2 || $progr_minuta_N4 != $progr_minuta_N2) {
                $query = "UPDATE 290_n4 SET Flag_Importazione = 'N4_N2_MINUTA' WHERE ID = '" . $progressivoN4 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N4_N2_MINUTA', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);

                continue;
            }
            if ($codice_partita_N4 != $codice_partita_N2 && intval($codice_partita_N4) != intval($codice_partita_N2)) {
                $query = "UPDATE 290_n4 SET Flag_Importazione = 'N4_N2_PARTITA' WHERE ID = '" . $progressivoN4 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'N4_N2_PARTITA', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);

                continue;
            }
        }

        if ($control_N4 == 1)
            continue;

        if ($comune_ruolo_N1 != $comune_partita_N2 || $progr_minuta_N1 != $progr_minuta_N2) {
            $query = "UPDATE 290_n2 SET Flag_Importazione = 'N1_N2_MINUTA', Flag_Partita = 'no' WHERE ID = '" . $progressivoN2 . "'";
            $cls_db->ExecuteQuery($query);//safe_query($query);

            continue;
        }

        if ($sesso_N2 == "") $sesso = "D";
        else $sesso = $sesso_N2;

        $omonimi = $cls_utils->check_omonimi($sesso, $codice_fiscale_N2, $ditta_N2, $codice_fiscale_N2, $nome_N2, $cognome_N2, $cc_nascita_N2, $data_nascita_N2, $c);

        if ($omonimi != "no") {
            $query = "SELECT Flag_Importazione FROM 290_n2 WHERE ID = '" . $progressivoN2 . "'";
            $control_errore = $cls_db->ExecuteQuery($query);//single_query($query);

            if ($control_errore == "Importare") {
                $query = "UPDATE 290_n2 SET Flag_Importazione = 'Omonimia', Flag_Partita = 'omoN2' WHERE ID = '" . $progressivoN2 . "'";
                $cls_db->ExecuteQuery($query);//safe_query($query);

                continue;
            }
        }

        //CREAZIONE ARRAY CAMPI $field_utente E VALORI $value_utente PER LA TABELLA utente da N2
        /*$field_utente = array();
        $value_utente = array();

        $field_utente[] = 'CC_Comune'; 				$value_utente[] = $c;*/

        $field_utente['CC_Comune'] = $c;

        if ($natura_giuridica_N2 == 1) {
            /*$field_utente[] = 'Genere'; 				$value_utente[] = $sesso_N2;
            $field_utente[] = 'Cognome'; 				$value_utente[] = $cognome_N2;
            $field_utente[] = 'Nome'; 					$value_utente[] = $nome_N2;
            $field_utente[] = 'CC_Nascita'; 			$value_utente[] = $cc_nascita_N2;*/


            $field_utente['Genere'] = $sesso_N2;
            $field_utente['Cognome'] = $cognome_N2;
            $field_utente['Nome'] = $nome_N2;
            $field_utente['CC_Nascita'] = $cc_nascita_N2;

            if ($cognome_N2 != "" && $nome_N2 != "" && $codice_fiscale_N2 != "") {
                $control_anagrafica = 1;
            }

            if (substr($cc_nascita_N2, 0, 1) != "Z") {
                $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '" . $cc_nascita_N2 . "'";
                $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "comuni_lista");

                $query = "SELECT * FROM province_lista WHERE Pro_Codice='" . $result['Com_Codice_Provincia'] . "'";
                $result1 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "province_lista");
                //$comune = new comune($cc_nascita_N2);

                //print_r($result);

                $paese_nascita = "Italia";
                $comune_nascita = $result["Com_Nome"];
                $provincia_nascita = $result1["Pro_Sigla"];

                /*$field_utente[] = 'Paese_Nascita'; 			$value_utente[] = $paese_nascita;
                $field_utente[] = 'Comune_Nascita'; 		$value_utente[] = $comune_nascita;
                $field_utente[] = 'Provincia_Nascita'; 		$value_utente[] = $provincia_nascita;*/

                $field_utente['Paese_Nascita'] = $paese_nascita;
                $field_utente['Comune_Nascita'] = $comune_nascita;
                $field_utente['Provincia_Nascita'] = $provincia_nascita;

            } else {
                //single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$cc_nascita_N2."'");
                $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '" . $cc_nascita_N2 . "'";
                $paese_nascita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "paesi_esteri_lista");

                //$field_utente[] = 'Paese_Nascita'; 			$value_utente[] = $paese_nascita;

                $field_utente['Paese_Nascita'] = $paese_nascita["Nome"];
            }

            /*$field_utente[] = 'Data_Nascita'; 			$value_utente[] = $data_nascita_N2;
            $field_utente[] = 'Codice_Fiscale'; 		$value_utente[] = $codice_fiscale_N2;*/

            $field_utente['Data_Nascita'] = $data_nascita_N2;
            $field_utente['Codice_Fiscale'] = $codice_fiscale_N2;
        } else {

            /*$field_utente[] = 'Genere'; 				$value_utente[] = "D";
            $field_utente[] = 'Ditta'; 					$value_utente[] = $ditta_N2;
            $field_utente[] = 'Partita_Iva'; 			$value_utente[] = $codice_fiscale_N2;*/

            $field_utente['Genere'] = "D";
            $field_utente['Ditta'] = $ditta_N2;
            $field_utente['Partita_Iva'] = $codice_fiscale_N2;


            if ($ditta_N2 != "" && $codice_fiscale_N2 != "") {
                $control_anagrafica = 1;
            }

        }

        /*$field_utente[] = 'Note';					$value_utente[] = "eyeofthetiger";
        $field_utente[] = 'Data_Registrazione'; 	$value_utente[] = date("Y-m-d");*/

        $field_utente['Note'] = "eyeofthetiger";
        $field_utente['Data_Registrazione'] = date("Y-m-d");

        //$comune_id = single_answer_query("SELECT MAX(Comune_ID) FROM utente WHERE CC_Comune = '".$c."'");
        $query = "SELECT MAX(Comune_ID) as CI FROM utente WHERE CC_Comune = '" . $c . "'";
        $comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query), "utente")["CI"];
//echo "<h1>max ".$comune_id."</h1>";
        //print_r($comune_id);

        //$field_utente[] = 'Comune_ID'; 				$value_utente[] = $comune_id+1;

        $field_utente['Comune_ID'] = $comune_id + 1;

        if ($control_anagrafica != 1)
            continue;

        $a_Params = $cls_utils->GetObjectQuery($field_utente, "utente");

        //print_r($a_Params);
        //echo "</br></br>";

        $new_ID_utenteN2 = $cls_db->DbSave($a_Params);
        //$new_ID_utenteN2 = table_insert_record('utente', $field_utente, $value_utente);

    }

    $query = "DELETE FROM utente WHERE Note = 'eyeofthetiger'";
    $cls_db->ExecuteQuery($query);//safe_query($query);

    //"eye of the tiger" viene inserito l'utente per controllare l'omonimia
    //dopodichè viene eliminato

}

echo "<script>fine();</script>";

?>

<?php include(INC . "/footer.php"); ?>