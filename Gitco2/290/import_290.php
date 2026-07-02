<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

ini_set('display_errors',1);

include_once(CLS."/cls_290.php");
include_once(CLS."/cls_registry.php");
include_once(INC."/header.php");
include_once(INC."/menu.php");

$Import_Id = $cls_help->getVar('Import_Id');

$query = "SELECT * FROM imports WHERE Id=".$Import_Id;
$a_import = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$query = "SELECT * FROM import_status";
$a_importStatus = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");
$query = "SELECT * FROM import_types";
$a_importTypes = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");

$a_ente = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$a_import['CC']."'") );

$imp_date = "";
$imp_operator = "";
if(!is_null($a_import['Import_Datetime'])){
    $imp_date = date('d/m/Y H:i',strtotime($a_import['Import_Datetime']));
    if($a_import['Import_User_Id']>0)
        $imp_operator = $a_usersAdmin[$a_import['Import_User_Id']]['User'];
}
$a_params = array(
    "CC" => $a_ente['CC'],
    "290Code" => $a_ente['Codice_290'],
    "Ruolo_ID" => $a_import['Ruolo_ID']
);

$cls_registry = new cls_registry();
$cls_290 = new cls_290($a_params);
$cls_290->setClass("cls_registry", $cls_registry);
$cls_290->setClass("cls_db", $cls_db);

$html = "";
if($a_import['Import_Type_Id']==1){
    $cls_290->getFile(DUENOVANTA."/toImport/".$a_import['Filename']);
    $cls_290->read290();
    $cls_290->check290();
}
else{
    $cls_290->readXlsxModel(DUENOVANTA."/toImport/".$a_import['Filename']);
}

?>
<script>
    function backMgmt(){
        sleep(2);
        location.href = "<?=WEB_ROOT?>/290/mgmt_290.php?c=<?=$c?>&a=<?=$a?>&Import_Id=<?=$Import_Id;?>";
    }

    function caricamento() {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text("Inizializzazione");
    }
</script>
    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <span class="titolo font22 under_decor">Gestione Importazione Ruolo</span>
        </div>
    </div>

    <div class="row-fluid gitco-container">
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Ente/comune
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_ente['Denominazione'];?>
        </div>
        <div class="col-lg-2 RowLabel">
            Codice catastale
        </div>
        <div class="col-lg-1 RowInput">
            <?= $a_import['CC'];?>
        </div>
        <div class="col-lg-1 RowLabel">
            Codice 290
        </div>
        <div class="col-lg-1 RowInput">
            <?=$a_ente['Codice_290'];?>
        </div>
        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Denominazione
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_import['Name'];?>
        </div>
        <div class="col-lg-2 RowLabel">
            Status
        </div>
        <div class="col-lg-3 RowInput">
            <?= $a_importStatus[$a_import['Import_Status_Id']]['Name'];?>
        </div>
        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Tipo
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_importTypes[$a_import['Import_Type_Id']]['Name'];?>
        </div>
        <div class="col-lg-2 RowLabel">
            File registrato
        </div>
        <div class="col-lg-3 RowInput">
            <a title="<?=$a_import['Filename'];?>" href="<?=DUENOVANTA_WEB.'/toImport/'.$a_import['Filename']?>"><?=$a_import['Filename'];?></a>
        </div>
        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Data Upload
        </div>
        <div class="col-lg-3 RowInput">
            <?= date('d/m/Y H:i',strtotime($a_import['Upload_Datetime']));?>
        </div>
        <div class="col-lg-2 RowLabel">
            Operatore upload
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_usersAdmin[$a_import['Upload_User_Id']]['User'];?>
        </div>

        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Data Importazione
        </div>
        <div class="col-lg-3 RowInput">
            <?= $imp_date; ?>
        </div>
        <div class="col-lg-2 RowLabel">
            Operatore importazione
        </div>
        <div class="col-lg-3 RowInput">
            <?=$imp_operator;?>
        </div>

        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-offset-1 col-lg-2 RowLabel">
            Posizioni importate
        </div>
        <div class="col-lg-3 RowInput">
            <?= (int)$a_import['Imported_Positions']; ?>
            /
            <?php
            if($a_import['Total_Positions']>0)
                echo (int)$a_import['Total_Positions'];
            else
                echo (int)$cls_290->a_count["N2"];
            ?>
        </div>
        <div class="col-lg-2 RowLabel">
            Scarti
        </div>
        <div class="col-lg-3 RowInput">
            <?php
            if($a_import['Total_Positions']>0)
                echo (int)($a_import['Total_Positions']-$a_import['Imported_Positions']);
            else
                echo 0; ?>
            /
            <?php
            if($a_import['Total_Positions']>0)
                echo (int)$a_import['Total_Positions'];
            else
                echo (int)$cls_290->a_count["N2"];
            ?>
        </div>

        <div class="HSpace4 clean_row"></div>
        <div class="col-lg-offset-1 col-lg-10 RowInput RowInputBtnHeight5 text-center">
            <input type="button" id=backPage class="btn btn-gitco" value="Elenco File">
            <?=$html;?>
        </div>
        <div class="HSpace4 clean_row"></div>
        <br>
        <div class="clean_row HSpace4"></div>
        <div class="col-lg-12 RowLabel RowLabelHeight4 text-center">
            IMPORTAZIONE FILE
        </div>
        <div class="clean_row HSpace1"></div>
        <br>
        <div class="col-lg-12 text-center">
            <div class="table_interna text_center" id="progressbar" style="height:55px;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </div>
    </div>

<?php

flush();ob_flush();
if($a_import['Import_Status_Id']>1){
    echo "<script>backMgmt();</script>";
    die;
}

    flush();ob_flush();
    echo "<script>caricamento();</script>";


$a_count = array("discard"=>0, "import"=>0);
$importDatetime = date("Y-m-d H:i:s");
$query = "SELECT MAX(Comune_ID) as Comune_ID FROM partita_tributi WHERE CC = '".$cls_290->a_params['CC']."'";
$a_partitaTemp = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if(!is_null($a_partitaTemp)){
    $comuneIdPartita = (int)$a_partitaTemp["Comune_ID"]+1;
}
else
    $comuneIdPartita = 1;

$a_ruoloDataTypes = $cls_db->getColumnDataTypes("ruolo");
$a_utenteDataTypes = $cls_db->getColumnDataTypes("utente");
$a_indirizzoDataTypes = $cls_db->getColumnDataTypes("indirizzo");
$a_toponimoDataTypes = $cls_db->getColumnDataTypes("toponimo");
$a_partitaDataTypes = $cls_db->getColumnDataTypes("partita_tributi");
$a_tributoDataTypes = $cls_db->getColumnDataTypes("tributo");
$a_partitaUtenteDataTypes = $cls_db->getColumnDataTypes("partita_utente");

if($a_import['Import_Type_Id']==2) {

    $a_ruolo = array(
        "Data_Inserimento" => $importDatetime,
        "CC" => $cls_290->a_params['CC'],
        "Data_Fornitura" => date("Y-m-d"),
        "Descrizione" => explode(".", $a_import['Name'])[0],
    );

    $Ruolo_ID = $cls_db->DbSave($cls_db->GetObjectQuery("ruolo", $a_ruolo, $a_ruoloDataTypes));

    foreach ($cls_290->a_model as $row => $a_row) {
        flush();
        ob_flush();
        echo "<script>$( \"#progressbar\" ).progressbar({value: " . intval(($row + 1) * 100 / $cls_290->a_count['N2']) . " });$( \"#barlabel\" ).text(" . intval(($row + 1) * 100 / $cls_290->a_count['N2']) . "+'%');</script>";
        flush();
        ob_flush();

        if ($a_row['STATUS']['check'] == "scarto" || $a_row['STATUS']['check'] == "imported") {
            //TODO registrazione scarti in file excel
            $a_count['discard']++;
            continue;
        }

        $Utente_ID = null;
        if (isset($a_row['STATUS']['Utente_ID']) && $a_row['STATUS']['Utente_ID'] > 0)
            $Utente_ID = $a_row['STATUS']['Utente_ID'];
        else {
            $query = "SELECT MAX(Comune_ID) as Comune_ID FROM utente WHERE CC_Comune = '" . $cls_290->a_params['CC'] . "'";
            $a_comuneId = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
            if (!is_null($a_comuneId))
                $Comune_ID = (int)$a_comuneId['Comune_ID'] + 1;
            else
                $Comune_ID = 1;

            if ($a_row['TIPO_SOGGETTO'] == "DITTA") {
                $ditta = $a_row['COGNOME_DITTA'];
                $cognome = "";
                $nome = "";
            } else {
                $cognome = $a_row['COGNOME_DITTA'];
                $nome = $a_row['NOME'];
                $ditta = "";
            }

            $a_utente = array(
                "CC_Comune" => $cls_290->a_params['CC'],
                "Comune_ID" => $Comune_ID,
                "Codice_Fiscale" => (string)$a_row['CODICE_FISCALE'],
                "Partita_Iva" => (string)$a_row['PARTITA_IVA'],
                "Ditta" => (string)$ditta,
                "Cognome" => (string)$cognome,
                "Nome" => (string)$nome,
                "Data_Registrazione" => date('Y-m-d'),
            );

            if ($a_row['TIPO_SOGGETTO'] != "DITTA") {
                $a_cf = $cls_registry->decode_CF($a_utente['Codice_Fiscale']);
                $a_utente["Genere"] = $a_cf['SESSO'];
                $a_utente["CC_Nascita"] = $a_cf['CC_NASCITA'];
                $a_utente["Paese_Nascita"] = $a_cf['PAESE_NASCITA'];
                $a_utente["Comune_Nascita"] = $a_cf['COMUNE_NASCITA'];
                $a_utente["Provincia_Nascita"] = $a_cf['PROVINCIA_NASCITA'];
                $a_utente["Data_Nascita"] = $a_cf['DATA_NASCITA'];
            } else {
                $a_utente["Genere"] = "D";
            }

            $Utente_ID = $cls_db->DbSave($cls_db->GetObjectQuery("utente", $a_utente, $a_utenteDataTypes));

            $a_toponimo = array(
                "CC_Comune" => $cls_290->a_params['CC'],
                "CC_Toponimo" => $a_row['CODICE_CATASTALE_DESTINATARIO'],
                "Nome" => $a_row['VIA_DESTINATARIO'],
                "Cap" => $a_row['CAP_DESTINATARIO']
            );

            $a_indirizzo = array(
                "Utente_ID" => $Utente_ID,
                "CC_Indirizzo" => $a_row['CODICE_CATASTALE_DESTINATARIO'],
                "Via_Cap_ID" => 1,
                "Tipo" => "res",
                "Cap" => $a_row['CAP_DESTINATARIO'],
                "Data_Inizio_Residenza" => "1900-01-01"
            );

            if (substr($a_toponimo["CC_Toponimo"], 0, 1) == "Z") {
                $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '" . $a_toponimo["CC_Toponimo"] . "'";
                $a_paese = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                if (!is_null($a_paese)) {
                    $a_toponimo["Paese"] = $a_paese["Nome"];
                    $a_indirizzo["Paese"] = $a_paese["Nome"];
                }
                $a_toponimo["Comune"] = $a_row['COMUNE_DESTINATARIO'];
                $a_indirizzo["Comune"] = $a_row['COMUNE_DESTINATARIO'];
                $a_indirizzo["Provincia"] = "";

                if ($a_row['CIVICO_DESTINATARIO'] != "")
                    $a_toponimo["Nome"] .= " " . $a_row['CIVICO_DESTINATARIO'];
                if ($a_row['ESPONENTE_DESTINATARIO'] != "")
                    $a_toponimo["Nome"] .= $a_row['ESPONENTE_DESTINATARIO'];
                if ($a_row['INTERNO_DESTINATARIO'] != "")
                    $a_toponimo["Nome"] .= "/" . $a_row['INTERNO_DESTINATARIO'];
                if ($a_row['DETTAGLI_DESTINATARIO'] != "")
                    $a_toponimo["Nome"] .= " " . $a_row['DETTAGLI_DESTINATARIO'];
            } else {
                $a_toponimo["Paese"] = "Italia";
                $a_indirizzo["Paese"] = "Italia";
                $query = "SELECT C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                $query .= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Codice_Catastale = '" . $a_toponimo['CC_Toponimo'] . "'";
                $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                if (!is_null($a_comune)) {
                    $a_toponimo["Comune"] = $a_comune['Com_Nome'];
                    $a_indirizzo["Comune"] = $a_comune['Com_Nome'];
                    $a_indirizzo["Provincia"] = $a_comune['Pro_Sigla'];
                } else {
                    $query = "SELECT C.Com_Codice_Catastale, C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                    $query .= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Nome = '" . trim(strtolower(ucwords($a_row['COMUNE_DESTINATARIO']))) . "'";
                    $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                    if (!is_null($a_comune)) {
                        $a_toponimo['CC_Toponimo'] = $a_comune['Com_Codice_Catastale'];
                        $a_toponimo["Comune"] = $a_comune['Com_Nome'];
                        $a_indirizzo["Comune"] = $a_comune['Com_Nome'];
                        $a_indirizzo["Provincia"] = $a_comune['Pro_Sigla'];
                    } else {
                        $a_toponimo["Comune"] = $a_row['COMUNE_DESTINATARIO'];
                        $a_indirizzo["Comune"] = $a_row['COMUNE_DESTINATARIO'];
                        $a_indirizzo["Provincia"] = "";
                    }
                }

                if ($a_row['CIVICO_DESTINATARIO'] != "")
                    $a_indirizzo["Civico"] = $a_row['CIVICO_DESTINATARIO'];
                if ($a_row['ESPONENTE_DESTINATARIO'] != "")
                    $a_indirizzo["Esponente"] = $a_row['ESPONENTE_DESTINATARIO'];
                if ($a_row['INTERNO_DESTINATARIO'] != "")
                    $a_indirizzo["Interno"] = $a_row['INTERNO_DESTINATARIO'];
                if ($a_row['DETTAGLI_DESTINATARIO'] != "")
                    $a_indirizzo["Dettagli"] = $a_row['DETTAGLI_DESTINATARIO'];
            }


            $toponimoId = $cls_db->DbSave($cls_db->GetObjectQuery("toponimo", $a_toponimo, $a_toponimoDataTypes));
            $a_indirizzo["Via_ID"] = $toponimoId;
            $indirizzoId = $cls_db->DbSave($cls_db->GetObjectQuery("indirizzo", $a_indirizzo, $a_indirizzoDataTypes));
        }

        $a_docTributo = array("INGIUNZIONE"=>2,"ACCERTAMENTO_ESECUTIVO"=>42,""=>null);
        $a_partita = array(
            "CC" => $cls_290->a_params['CC'],
            "Comune_ID" => $comuneIdPartita,
            "Ruolo_ID" => $Ruolo_ID,
            "Utente_ID" => $Utente_ID,
            "Anno_Riferimento" => $a_row['ANNO_RIFERIMENTO'],
            "DocumentTypeId" => $a_docTributo[$a_row['TIPO_ATTO']],
            "Tipo" => $a_row['TIPO_PARTITA'],
            "Sottotipo" => $a_row['SOTTOTIPO_PARTITA'],
            "Flag_Blocco_Diritto_Riscossione" => null
        );
//    var_dump($a_partita);
        $PartitaId = $cls_db->DbSave($cls_db->GetObjectQuery("partita_tributi", $a_partita, $a_partitaDataTypes));
        foreach ($a_row['CODICI_TRIBUTO'] as $key => $a_codice) {
            $a_tributo = array(
                "CC" => $cls_290->a_params['CC'],
                "Partita_ID" => $PartitaId,
                "Anno_Tributo" => $a_codice['year'],
                "Codice_Tributo" => $a_codice['code'],
                "Imposta" => round($a_codice['amount'], 2),
                "Data_Decorrenza_Interessi" => $a_row['DATA_DECORRENZA_INTERESSI'],
                "Info_Cartella" => $a_row['INFORMAZIONI_CARTELLA']
            );
            if ($a_partita['Tipo'] == "CDS")
                $a_tributo["Tipo_Info"] = "S";
            else
                $a_tributo["Tipo_Info"] = "E";

            switch ($a_tributo["Tipo_Info"]) {
                case "E":
                    $a_tributo['Titolo_Entrata'] = "";
                    $a_tributo['Descrizione_Entrata'] = "";
                    break;
                case "S":
                    $a_tributo['Tipo_Sanzione'] = "VE";
                    $a_tributo['Titolo_Sanzione'] = "";
                    $a_tributo['Data_Sanzione'] = "";
                    $a_tributo['Targa_Sanzione'] = "";
                    break;
            }
//        var_dump($a_tributo);
            $cls_db->DbSave($cls_db->GetObjectQuery("tributo", $a_tributo, $a_tributoDataTypes));
        }

        $comuneIdPartita++;
        $a_count['import']++;

    }
}
else{
    flush();ob_flush();flush();ob_flush();
    $cls_290->getHtmlFileChecks();
    flush();ob_flush();
    foreach($cls_290->a_290['N1'] as $n1=>$a_n1){
        $a_ruolo = array(
            "Data_Inserimento" => $importDatetime,
            "CC" => $cls_290->a_params['CC'],
            "Ruolo" => $a_n1['Ruolo']['value'],
            "Data_Fornitura" => $cls_290->a_290['N0']['DataFornitura']['value'],
            "Descrizione" => explode(".",$a_import['Name'])[0],
            "Num_Rate" => $a_n1['NumeroRate']['value'],
            "Num_Ruolo" => $a_n1['NumeroRuolo']['value'],
            "Tipo_Compenso" => $a_n1['TipoCompenso']['value'],
            "Codice_Sede" => $a_n1['CodiceSede']['value'],
            "ICIAP" => $a_n1['ICIAP']['value'],
            "Num_Convenzione" => $a_n1['NumeroConvenzione']['value'],
            "Flag_Articolo" => $a_n1['FlagArticoli']['value']
        );

        $Ruolo_ID = $cls_db->DbSave($cls_db->GetObjectQuery("ruolo", $a_ruolo, $a_ruoloDataTypes));

        foreach ($cls_290->a_290['N2'][$n1] as $n2=>$a_n2){

            flush();ob_flush();
            echo "<script>$( \"#progressbar\" ).progressbar({value: " .intval(($n2+1)*100/$cls_290->a_count['N2']). " });$( \"#barlabel\" ).text(" .intval(($n2+1)*100/$cls_290->a_count['N2']). "+'%');</script>";
            flush();ob_flush();

            if($a_n2['Check']['scarto']==1){
                //TODO registrazione scarti in file excel
                $a_count['discard']++;
                continue;
            }

            if($a_n2['Check']['omonimia']==0){
                $Utente_ID = null;
                if($a_n2['Utente']['NaturaGiuridica']['value']==1)
                    $query = "SELECT ID FROM utente WHERE CC_Comune = '".$cls_290->a_params['CC']."' AND Codice_Fiscale = '".$a_n2['Utente']['CF']['value']."'";
                else
                    $query = "SELECT ID FROM utente WHERE CC_Comune = '".$cls_290->a_params['CC']."' AND Partita_Iva = '".$a_n2['Utente']['PI']['value']."'";

                $a_utente = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                if(!is_null($a_utente)){
                    $Utente_ID = $a_utente['ID'];
                    $a_n2['Check']['omonimia'] = 1;
                }
                else{
                    $query = "SELECT MAX(Comune_ID) as Comune_ID FROM utente WHERE CC_Comune = '".$cls_290->a_params['CC']."'";
                    $a_comuneId = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                    if(!is_null($a_comuneId))
                        $Comune_ID = (int)$a_comuneId['Comune_ID']+1;
                    else
                        $Comune_ID = 1;

                    $a_utente = array(
                        "CC_Comune" => $cls_290->a_params['CC'],
                        "Comune_ID" => $Comune_ID,
                        "Codice_Fiscale" => (string)$a_n2['Utente']['CF']['value'],
                        "Partita_Iva" => (string)$a_n2['Utente']['PI']['value'],
                        "Ditta" => $a_n2['Utente']['Ditta']['value'],
                        "Cognome" => (string)$a_n2['Utente']['Cognome']['value'],
                        "Nome" => (string)$a_n2['Utente']['Nome']['value'],
                        "Data_Registrazione" => date('Y-m-d')
                    );

                    if($a_n2['Utente']['NaturaGiuridica']['value']==1){
                        if((int)substr($a_utente['Codice_Fiscale'],9,2)<40)
                            $a_utente["Genere"] = "M";
                        else
                            $a_utente["Genere"] = "F";

                        $a_utente["CC_Nascita"] = substr($a_utente['Codice_Fiscale'],11,4);
                        if(substr($a_utente['CC_Nascita'],11,1)!="Z"){
                            $a_utente["Paese_Nascita"] = "Italia";
                            $query = "SELECT C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                            $query.= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Codice_Catastale = '".$a_utente['CC_Nascita']."'";
                            $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                            if(!is_null($a_comune)){
                                $a_utente["Comune_Nascita"] = $a_comune['Com_Nome'];
                                $a_utente["Provincia_Nascita"] = $a_comune['Pro_Sigla'];
                            }

                        }
                        else{
                            $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$a_utente['CC_Nascita']."'";
                            $a_paese = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                            if(!is_null($a_paese))
                                $a_utente["Paese_Nascita"] = $a_paese["Nome"];
                        }

                        $a_utente["Data_Nascita"] = $a_n2['Utente']['DataNascita']['value'];
                    }
                    else{
                        $a_utente["Genere"] = "D";
                    }

                    $Utente_ID = $cls_db->DbSave($cls_db->GetObjectQuery("utente", $a_utente, $a_utenteDataTypes));

                    $a_toponimo = array(
                        "CC_Comune" => $cls_290->a_params['CC'],
                        "CC_Toponimo" => $a_n2['Residenza']['CC']['value'],
                        "Nome" => $a_n2['Residenza']['Indirizzo']['value'],
                        "Cap" => $a_n2['Residenza']['CAP']['value']
                    );

                    $a_indirizzo = array(
                        "Utente_ID" => $Utente_ID,
                        "CC_Indirizzo" => $a_n2['Residenza']['CC']['value'],
                        "Via_Cap_ID" => 1,
                        "Tipo" => "res",
                        "Cap" => $a_n2['Residenza']['CAP']['value'],
                        "Data_Inizio_Residenza" => "1900-01-01"
                    );

                    if(substr($a_toponimo["CC_Toponimo"],0,1)=="Z"){
                        $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$a_toponimo["CC_Toponimo"]."'";
                        $a_paese = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                        if(!is_null($a_paese)){
                            $a_toponimo["Paese"] = $a_paese["Nome"];
                            $a_indirizzo["Paese"] = $a_paese["Nome"];
                        }
                        $a_toponimo["Comune"] = $a_n2['Residenza']['Localita']['value'];
                        $a_indirizzo["Comune"] = $a_n2['Residenza']['Localita']['value'];
                        $a_indirizzo["Provincia"] = "";

                        if($a_n2['Residenza']['Civico']['value']!="")
                            $a_toponimo["Nome"].= " ".$a_n2['Residenza']['Civico']['value'];
                        if($a_n2['Residenza']['Lettera']['value']!="")
                            $a_toponimo["Nome"].= " ".$a_n2['Residenza']['Lettera']['value'];

                    }
                    else{
                        $a_toponimo["Paese"] = "Italia";
                        $a_indirizzo["Paese"] = "Italia";
                        $query = "SELECT C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                        $query.= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Codice_Catastale = '".$a_toponimo['CC_Toponimo']."'";
                        $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                        if(!is_null($a_comune)){
                            $a_toponimo["Comune"] = $a_comune['Com_Nome'];
                            $a_indirizzo["Comune"] = $a_comune['Com_Nome'];
                            $a_indirizzo["Provincia"] = $a_comune['Pro_Sigla'];
                        }
                        else{
                            $query = "SELECT C.Com_Codice_Catastale, C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                            $query.= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Nome = '".trim(strtolower(ucwords($a_n2['Residenza']['Localita']['value'])))."'";
                            $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                            if(!is_null($a_comune)) {
                                $a_toponimo['CC_Toponimo'] = $a_comune['Com_Codice_Catastale'];
                                $a_toponimo["Comune"] = $a_comune['Com_Nome'];
                                $a_indirizzo["Comune"] = $a_comune['Com_Nome'];
                                $a_indirizzo["Provincia"] = $a_comune['Pro_Sigla'];
                            }
                            else{
                                $a_toponimo["Comune"] = $a_n2['Residenza']['Localita']['value'];
                                $a_indirizzo["Comune"] = $a_n2['Residenza']['Localita']['value'];
                                $a_indirizzo["Provincia"] = "";
                            }
                        }

                        if(!empty($a_n2['Residenza']['Civico']['value']) && is_numeric($a_n2['Residenza']['Civico']['value']))
                            $a_indirizzo["Civico"] = $a_n2['Residenza']['Civico']['value'];
                        if($a_n2['Residenza']['Lettera']['value']!="")
                            $a_indirizzo["Esponente"] = $a_n2['Residenza']['Lettera']['value'];
                    }

                    $toponimoId = $cls_db->DbSave($cls_db->GetObjectQuery("toponimo", $a_toponimo, $a_toponimoDataTypes));
                    $a_indirizzo["Via_ID"] = $toponimoId;
                    $indirizzoId = $cls_db->DbSave($cls_db->GetObjectQuery("indirizzo", $a_indirizzo, $a_indirizzoDataTypes));
                }
            }
            else{
                $Utente_ID = $a_n2['Check']['Utente_ID'];
            }

            $a_partita = array(
                "CC" => $cls_290->a_params['CC'],
                "Comune_ID" => $comuneIdPartita,
                "Ruolo_ID" => $Ruolo_ID,
                "Utente_ID" => $Utente_ID,
                "Anno_Riferimento" => $cls_290->a_290['N4'][$n1][$n2][0]['AnnoTributo']['value'],
                "Tipo" => $cls_290->a_codiciTributo['DB'][$cls_290->a_290['N4'][$n1][$n2][0]['CodiceTributo']['value']]['Settore'],
                "Sottotipo" => $cls_290->a_codiciTributo['DB'][$cls_290->a_290['N4'][$n1][$n2][0]['CodiceTributo']['value']]['Sottosettore'],
                "Flag_Blocco_Diritto_Riscossione" => null
            );

            $PartitaId = $cls_db->DbSave($cls_db->GetObjectQuery("partita_tributi", $a_partita, $a_partitaDataTypes));
            foreach($cls_290->a_290['N4'][$n1][$n2] as $n4=>$a_n4){
                $checkTipoInfo = true;
                if(empty($a_n4['TipoInformazioni']['value'])){
                    $checkTipoInfo = false;
                    if ($a_partita['Tipo'] == "CDS")
                        $a_n4["TipoInformazioni"]['value'] = "S";
                    else
                        $a_n4["TipoInformazioni"]['value'] = "E";
                }
                
                $a_tributo = array(
                    "CC" => $cls_290->a_params['CC'],
                    "Partita_ID" => $PartitaId,
                    "Anno_Tributo" => $a_n4['AnnoTributo']['value'],
                    "Codice_Tributo" => $a_n4['CodiceTributo']['value'],
                    "Imposta" => $a_n4['Imposta']['value'],
                    "Data_Decorrenza_Interessi" => $a_n4['DataDecorrenzaInteressi']['value'],
                    "Info_Cartella" => $a_n4['InformazioniCartella']['value'],
                    "Tipo_Info" => $a_n4['TipoInformazioni']['value']
                );

                if($checkTipoInfo){
                    switch($a_tributo["Tipo_Info"]){
                        case "E":
                            $a_tributo['Titolo_Entrata'] = $a_n4[$a_tributo["Tipo_Info"]]['Titolo']['value'];
                            $a_tributo['Descrizione_Entrata'] = $a_n4[$a_tributo["Tipo_Info"]]['Descrizione']['value'];
                            break;
                        case "S":
                            $a_tributo['Tipo_Sanzione'] = $a_n4[$a_tributo["Tipo_Info"]]['Tipo']['value'];
                            $a_tributo['Titolo_Sanzione'] = $a_n4[$a_tributo["Tipo_Info"]]['Titolo']['value'];
                            $a_tributo['Data_Sanzione'] = $a_n4[$a_tributo["Tipo_Info"]]['Data']['value'];
                            $a_tributo['Targa_Sanzione'] = $a_n4[$a_tributo["Tipo_Info"]]['Targa']['value'];
                            break;
    
                        case "M":
                            $a_tributo['Matricola'] = $a_n4[$a_tributo["Tipo_Info"]]['Matricola']['value'];
                            break;
                    }
                }
                

                $cls_db->DbSave($cls_db->GetObjectQuery("tributo", $a_tributo, $a_tributoDataTypes));
            }

            $comuneIdPartita++;
            $a_count['import']++;

            if(!empty($cls_290->a_290['N3'][$n1][$n2])){
                if(count($cls_290->a_290['N3'][$n1][$n2])>0){
                    $query = "UPDATE partita_tributi SET Flag_Coobbligati=1 WHERE ID=".$PartitaId;
                    $cls_db->ExecuteQuery($query);
                }
                foreach($cls_290->a_290['N3'][$n1][$n2] as $n3=>$a_n3){
                    $Coobbligato_Id = null;
                    // if($a_n3['Check']['scarto']==1){
                    //     $a_count['discard']++;
                    //     continue;
                    // }
                    if(empty($a_n3['Check']['omonimia']) || $a_n3['Check']['omonimia']==0){
                        
                        if($a_n3['Utente']['NaturaGiuridica']['value']==1)
                            $query = "SELECT ID FROM utente WHERE CC_Comune = '".$cls_290->a_params['CC']."' AND Codice_Fiscale = '".$a_n3['Utente']['CF']['value']."'";
                        else
                            $query = "SELECT ID FROM utente WHERE CC_Comune = '".$cls_290->a_params['CC']."' AND Partita_Iva = '".$a_n3['Utente']['PI']['value']."'";

                        $a_utente = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                        if(!is_null($a_utente)){
                            $Coobbligato_Id = $a_utente['ID'];
                            $a_n3['Check']['omonimia'] = 1;
                        }
                        else{
                            $query = "SELECT MAX(Comune_ID) as Comune_ID FROM utente WHERE CC_Comune = '".$cls_290->a_params['CC']."'";
                            $a_comuneId = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                            if(!is_null($a_comuneId))
                                $Comune_ID = (int)$a_comuneId['Comune_ID']+1;
                            else
                                $Comune_ID = 1;

                            $a_utente = array(
                                "CC_Comune" => $cls_290->a_params['CC'],
                                "Comune_ID" => $Comune_ID,
                                "Codice_Fiscale" => (string)$a_n3['Utente']['CF']['value'],
                                "Partita_Iva" => (string)$a_n3['Utente']['PI']['value'],
                                "Ditta" => $a_n3['Utente']['Ditta']['value'],
                                "Cognome" => (string)$a_n3['Utente']['Cognome']['value'],
                                "Nome" => (string)$a_n3['Utente']['Nome']['value'],
                                "Data_Registrazione" => date('Y-m-d')
                            );

                            if($a_n3['Utente']['NaturaGiuridica']['value']==1){
                                if((int)substr($a_utente['Codice_Fiscale'],9,2)<40)
                                    $a_utente["Genere"] = "M";
                                else
                                    $a_utente["Genere"] = "F";

                                $a_utente["CC_Nascita"] = substr($a_utente['Codice_Fiscale'],11,4);
                                if(substr($a_utente['CC_Nascita'],11,1)!="Z"){
                                    $a_utente["Paese_Nascita"] = "Italia";
                                    $query = "SELECT C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                                    $query.= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Codice_Catastale = '".$a_utente['CC_Nascita']."'";
                                    $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                                    if(!is_null($a_comune)){
                                        $a_utente["Comune_Nascita"] = $a_comune['Com_Nome'];
                                        $a_utente["Provincia_Nascita"] = $a_comune['Pro_Sigla'];
                                    }

                                }
                                else{
                                    $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$a_utente['CC_Nascita']."'";
                                    $a_paese = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                                    if(!is_null($a_paese))
                                        $a_utente["Paese_Nascita"] = $a_paese["Nome"];
                                }

                                $a_utente["Data_Nascita"] = $a_n3['Utente']['DataNascita']['value'];
                            }
                            else{
                                $a_utente["Genere"] = "D";
                            }

                            $Coobbligato_Id = $cls_db->DbSave($cls_db->GetObjectQuery("utente", $a_utente, $a_utenteDataTypes));

                            $a_toponimo = array(
                                "CC_Comune" => $cls_290->a_params['CC'],
                                "CC_Toponimo" => $a_n3['Residenza']['CC']['value'],
                                "Nome" => $a_n3['Residenza']['Indirizzo']['value'],
                                "Cap" => $a_n3['Residenza']['CAP']['value']
                            );

                            $a_indirizzo = array(
                                "Utente_ID" => $Coobbligato_Id,
                                "CC_Indirizzo" => $a_n3['Residenza']['CC']['value'],
                                "Via_Cap_ID" => 1,
                                "Tipo" => "res",
                                "Cap" => $a_n3['Residenza']['CAP']['value'],
                                "Data_Inizio_Residenza" => "1900-01-01"
                            );

                            if(substr($a_toponimo["CC_Toponimo"],0,1)=="Z"){
                                $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$a_toponimo["CC_Toponimo"]."'";
                                $a_paese = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                                if(!is_null($a_paese)){
                                    $a_toponimo["Paese"] = $a_paese["Nome"];
                                    $a_indirizzo["Paese"] = $a_paese["Nome"];
                                }
                                $a_toponimo["Comune"] = $a_n3['Residenza']['Localita']['value'];
                                $a_indirizzo["Comune"] = $a_n3['Residenza']['Localita']['value'];
                                $a_indirizzo["Provincia"] = "";

                                if($a_n3['Residenza']['Civico']['value']!="")
                                    $a_toponimo["Nome"].= " ".$a_n3['Residenza']['Civico']['value'];
                                if($a_n3['Residenza']['Lettera']['value']!="")
                                    $a_toponimo["Nome"].= " ".$a_n3['Residenza']['Lettera']['value'];

                            }
                            else{
                                $a_toponimo["Paese"] = "Italia";
                                $a_indirizzo["Paese"] = "Italia";
                                $query = "SELECT C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                                $query.= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Codice_Catastale = '".$a_toponimo['CC_Toponimo']."'";
                                $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                                if(!is_null($a_comune)){
                                    $a_toponimo["Comune"] = $a_comune['Com_Nome'];
                                    $a_indirizzo["Comune"] = $a_comune['Com_Nome'];
                                    $a_indirizzo["Provincia"] = $a_comune['Pro_Sigla'];
                                }
                                else{
                                    $query = "SELECT C.Com_Codice_Catastale, C.Com_Nome, P.Pro_Sigla FROM comuni_lista C ";
                                    $query.= "JOIN province_lista P ON P.Pro_Codice=C.Com_Codice_Provincia WHERE C.Com_Nome = '".trim(strtolower(ucwords($a_n3['Residenza']['Localita']['value'])))."'";
                                    $a_comune = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                                    if(!is_null($a_comune)) {
                                        $a_toponimo['CC_Toponimo'] = $a_comune['Com_Codice_Catastale'];
                                        $a_toponimo["Comune"] = $a_comune['Com_Nome'];
                                        $a_indirizzo["Comune"] = $a_comune['Com_Nome'];
                                        $a_indirizzo["Provincia"] = $a_comune['Pro_Sigla'];
                                    }
                                    else{
                                        $a_toponimo["Comune"] = $a_n3['Residenza']['Localita']['value'];
                                        $a_indirizzo["Comune"] = $a_n3['Residenza']['Localita']['value'];
                                        $a_indirizzo["Provincia"] = "";
                                    }
                                }

                                if(!empty($a_n3['Residenza']['Civico']['value']) && is_numeric($a_n3['Residenza']['Civico']['value']))
                                    $a_indirizzo["Civico"] = $a_n3['Residenza']['Civico']['value'];
                                if($a_n3['Residenza']['Lettera']['value']!="")
                                    $a_indirizzo["Esponente"] = $a_n3['Residenza']['Lettera']['value'];
                            }

                            $toponimoId = $cls_db->DbSave($cls_db->GetObjectQuery("toponimo", $a_toponimo, $a_toponimoDataTypes));
                            $a_indirizzo["Via_ID"] = $toponimoId;
                            $indirizzoId = $cls_db->DbSave($cls_db->GetObjectQuery("indirizzo", $a_indirizzo, $a_indirizzoDataTypes));
                        }
                    }
                    else{
                        $Coobbligato_Id = $a_n3['Check']['Utente_ID'];
                    }

                    $a_coobbligato = array(
                        "Partita_ID" => $PartitaId,
                        "Utente_ID" => $Coobbligato_Id,
                        "Tipo_Utente" => 1
                    );
                    $cls_db->DbSave($cls_db->GetObjectQuery("partita_utente", $a_coobbligato, $a_partitaUtenteDataTypes));
                    
                }
            }
        }
    }
}

if($a_count['import']>0){
    if($a_count['discard']>0)
        $importStatusId = 3;
    else
        $importStatusId = 2;
    $a_import = array(
        "Total_Positions" => $a_count['import']+$a_count['discard'],
        "Imported_Positions" => $a_count['import'],
        "Ruolo_ID" => $Ruolo_ID,
        "Import_Status_Id" => $importStatusId,
        "Import_User_Id" => $_SESSION['aut_progr'],
        "Import_Datetime" => $importDatetime
    );

    $cls_db->DbSave($cls_db->GetObjectQuery("imports", $a_import, null, array("Id"=>$Import_Id)));
}

echo "<script>backMgmt();</script>";

include_once(INC."/footer.php");