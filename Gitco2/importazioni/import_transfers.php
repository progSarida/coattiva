<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once(CLS."/cls_zip.php");
include_once(CLS."/cls_file.php");
?>

<br>
<span class="titolo">IMPORTAZIONE BONIFICI</span>
<br><br>

<?php
if($_FILES['upload_file']['tmp_name']==""){
    $cls_help->alert("Nessun file caricato!");
    ?>
    <script>
        history.back();
    </script>
<?php
    return;
}

$cls_file = new cls_file();
$expFilename = explode(".",$_FILES['upload_file']['name']);
$extension = strtoupper($expFilename[count($expFilename)-1]);
if($extension=="ZIP"){
    $cls_zip = new cls_zip();
    $cls_zip->extractZip($_FILES['upload_file']['tmp_name'],"tmp");

    $a_files = $cls_file->getFilesFromPath(getcwd()."/tmp");
    for($i=0;$i<count($a_files);$i++){
        $exp_file = explode(".",$a_files[$i]['fileName']);
        if(strtoupper($exp_file[count($exp_file)-1])=="CSV"){
            $csvFile = fopen($a_files[$i]['file'],"r");
            break;
        }
    }
    if(!isset($csvFile)){
        $cls_file->removeFiles(getcwd()."/tmp",0);
        $cls_help->alert("Nessun file CSV presente all'interno del file ZIP!");
        ?>
        <script>
            history.back();
        </script>
        <?php
        return;
    }

}
else if($extension=="CSV"){
    $csvFile = fopen($_FILES['upload_file']['tmp_name'],"r");
}
else{
    $cls_file->removeFiles(getcwd()."/tmp",0);
    $cls_help->alert("Formato file errato! Caricare un file CSV o un file ZIP con all'interno un file CSV!");
    ?>
    <script>
        history.back();
    </script>
    <?php
    return;
}

$a_payments = array();
$k = 0;
$checkDataContabile = 0;
$row = fgetcsv($csvFile, 0, ";");
while(isset($row[0])===true || $checkDataContabile!=1){
    if(isset($a_keys)){
        foreach($a_keys as $key=>$value){
            $a_payments[$k][$value] = trim($row[$key]);
        }
        $k++;
    }
    else if($row[0]=="Data Contabile"){
        $a_keys = $row;
        $checkDataContabile = 1;
    }
    else{
        switch($row[0]){
            case "IBAN":
                $a_general['IBAN'] = trim($row[1]);
                break;
            case "AZIENDA":
                $a_general['AZIENDA'] = trim($row[1]);
                break;
            case "Descrizione Conto":
                $a_general['CONTO'] = trim($row[1]);
                break;
        }
    }

    $row = fgetcsv($csvFile, 0, ";");
}

fclose($csvFile);
$cls_file->removeFiles(getcwd()."/tmp",0);

$cls_db = new cls_db();
$a_parametri = $cls_db->getResults($cls_db->ExecuteQuery("SELECT DISTINCT CC FROM parametri_pagamento WHERE IBAN='".$a_general['IBAN']."'"));
$a_cc = array();
for($i=0;$i<count($a_parametri);$i++)
    $a_cc[] = $a_parametri[$i]['CC'];

$a_row['CC'] = null;
if(count($a_parametri)==1)
    $a_row['CC'] = $a_cc[0];
?>
<table class="table_interna text_center" border="0">
    <tr>
        <td colspan="6"><hr></td>
    </tr>
    <tr>
        <td class="text_left"><span class="color_titolo"><b>IBAN</b></span></td>
        <td class="text_left" colspan="5"><?php echo $a_general['IBAN']; ?></td>
    </tr>
    <tr>
        <td class="text_left"><span class="color_titolo"><b>CONTO</b></span></td>
        <td class="text_left" colspan="5"><?php echo $a_general['CONTO']; ?></td>
    </tr>
    <tr>
        <td colspan="6"><hr></td>
    </tr>

<?php
    for($y=0;$y<count($a_payments);$y++){
        $a_row['causale'] = "";
        $a_row['trn'] = "";
        $a_row['anagrafica'] = "<span class='color_red'>Non riconosciuta</span>";
        $a_row['verso'] = "";
        $a_row['tipo_atto'] = "";
        $a_row['pigno'] = "";

        $expData = explode("/",$a_payments[$y]['Data Contabile']);
        if(strlen($expData[2])==2)
            $expData[2] = "20".$expData[2];
        $a_payments[$y]['Data Contabile'] = implode("/",$expData);

        if(isset($a_payments[$y]['Trasm. Recupero']))
            if($a_payments[$y]['Trasm. Recupero']!="")
                $a_payments[$y]['Rif.Cliente'] = $a_payments[$y]['Rif.Cliente'].";".$a_payments[$y]['Trasm. Recupero'];
        $cliente = "";
//        if($a_payments[$y]['Causale Abi']!="27")
//            continue;
        switch($a_payments[$y]['Causale Abi']){
            case "27":
                $a_row['tipo_bonifico'] = "Accredito";
                if(preg_match_all("/ACCREDITO | TRN | DA | PER /", $a_payments[$y]['Rif.Cliente'])) {
                    $exp = preg_split("/ TRN /", $a_payments[$y]['Rif.Cliente']);

                    $exp = preg_split("/ PER /", $exp[1]);
                    $a_row['verso'] = $exp[1];
                    $exp = preg_split("/ DA /", $exp[0]);
                    $a_row['anagrafica'] = $exp[1];
                    $a_row['causale'] = $a_row['verso'];

                }

                break;
            case "48":
                $a_row['tipo_bonifico'] = "Bonifico Italia";
                if(preg_match_all("/YYY| RI1| TRN | DA | PER /", $a_payments[$y]['Rif.Cliente'])) {
                    $cliente = preg_split("/YYY[0-9]{8}/", $a_payments[$y]['Rif.Cliente'])[1];
                    $newSplit = preg_split("/ RI1/", $cliente)[1];
                    $newSplit = preg_split("/ TRN /", $newSplit);
                    $a_row['causale'] = $newSplit[0];
                    if (isset($newSplit[1])) {
                        $newSplit2 = preg_split("/\sDA\s/", $newSplit[1]);
                        $newSplit3 = preg_split("/\sPER\s/", $newSplit2[1]);
                        $a_row['trn'] = $newSplit2[0];
                        $a_row['anagrafica'] = $newSplit3[0];
                        if (isset($newSplit3[1]))
                            $a_row['verso'] = $newSplit3[1];
                    }
                }
                break;
            case "ZI":
                $a_row['tipo_bonifico'] = "Bonifico Estero";
                break;
            case "68":
                $a_row['tipo_bonifico'] = "Storno";
                break;
        }



        $a_checkAtto = array("VERBALE ","AVV INTIMAZIONE ","AVV. INTIMAZIONE ","INTIMAZIONE ",
            "INGIUNZIONE ","ING ","AVV MORA ","AVV. MORA ","AVV_MORA ","SOLL_PRE ","SOLLECITO DI PAGAMENTO ",
            "PIGNO","PIGN ","PPT ");

        $a_row['act'] = "";
        foreach ($a_checkAtto as $key=>$val){
            if(preg_match("/".$val."/", strtoupper($a_row['causale']))){
                switch($val){
                    case "AVV INTIMAZIONE ":
                    case "AVV. INTIMAZIONE ":
                    case "INTIMAZIONE ":
                        $a_row['tipo_atto'] = "Avviso di intimazione ad adempiere";
                        $a_row['act'] = "INTIMAZIONE";
                        break;
                    case "AVV MORA ":
                    case "AVV. MORA ":
                    case "AVV_MORA ":
                        $a_row['tipo_atto'] = "Avviso di messa in mora";
                        $a_row['act'] = "AVV_MORA";
                        break;
                    case "INGIUNZIONE ":
                    case "ING ":
                        $a_row['tipo_atto'] = "Ingiunzione";
                        $a_row['act'] = "INGIUNZIONE";
                        break;
                    case "SOLL_PRE ":
                        $a_row['tipo_atto'] = "Sollecito pre ingiunzione";
                        $a_row['act'] = "SOLL_PRE";
                        break;
                    case "SOLLECITO DI PAGAMENTO ":
                        $a_row['tipo_atto'] = "Sollecito di pagamento";
                        $a_row['act'] = "SOLL_POST";
                        break;
                    case "VERBALE ":
                        $a_row['tipo_atto'] = "<span class='color_red'>Verbale</span>";
                        $a_row['act'] = "VERBALE";
                        break;
                    case "PIGN ":
                    case "PPT ":
                    case "PIGNO":
                        $a_row['tipo_atto'] = "Pignoramento";
                        $a_row['act'] = "PIGNORAMENTO";
                        break;
                }
                if($a_row['tipo_atto']!=""){

                    break;
                }

            }
        }

        if($a_row['tipo_atto']=="")
            $a_row['tipo_atto'] = "<span class='color_red'>Non riconosciuto</span>";

        $a_row['Comune_ID_Partita'] = null;
        $a_row['Anno_Riferimento_Partita'] = null;
        $a_row['Comune_ID_Utente'] = null;
        $a_row['ID_Cronologico'] = null;
        $a_row['Anno_Cronologico'] = null;

        $partita = "";
        $first = preg_quote('/', '/');
        if(preg_match("/[. ]+RIF+[. ]+[NUM. ]{0,5}+[0-9]{1,5}+[ ]{0,1}+[\/-]+[ ]{0,1}+[0-9]{4}/", $a_row['causale'], $rifPartita)){
            preg_match("/[0-9]{1,5}+[ ]{0,1}+[\/-]+[ ]{0,1}+[0-9]{4}/", $rifPartita[0], $numPartita);
            $partita = str_replace(" ","",$numPartita[0]);
            $partita = str_replace("-","/",$partita);
            $explode = explode("/",$partita);
            $a_row['Comune_ID_Partita'] = $explode[0];
            if(isset($explode[1]))
                $a_row['Anno_Riferimento_Partita'] = $explode[1];
        }

        $utente="";
        if(preg_match("/[. ]+[0-9]{1,5}+[ ]{0,1}+\/+[ ]{0,1}+[A-Z]{1}+[0-9]{3}/", $a_row['causale'], $codUtente)){
            preg_match("/[0-9]{1,5}+[ ]{0,1}+\/+[ ]{0,1}+[A-Za-z]{1}+[0-9]{3}/", $codUtente[0], $utente);
            $utente = str_replace(" ","",$utente[0]);
            $utente = str_replace("-","/",$utente);
            $explode = explode("/",$utente);
            $a_row['Comune_ID_Utente'] = $explode[0];
            if(isset($explode[1]))
                $a_row['CC'] = $explode[1];
        }
        else if(preg_match("/[ ]{1}+ID+[. ]{1,2}+[0-9]{1,5}+[\/ ]{0,2}+[ ]{0,1}+[A-Z]{1}+[0-9]{3}/", $a_row['causale'], $codUtente)){
            preg_match("/[0-9]{1,5}/", $codUtente[0], $comune_id);
            preg_match("/[A-Z]{1}+[0-9]{3}/", $codUtente[0], $cc);


            $a_row['Comune_ID_Utente'] = $comune_id[0];
            if($cc!="")
                $a_row['CC'] = $cc[0];
            $utente = $a_row['Comune_ID_Utente']."/".$a_row['CC'];
        }

        $crono="";
        if($a_row['act']=="VERBALE" || $a_row['act']==""){
            continue;
            if(preg_match("/[0-9]{1,5}+[ ]{0,1}+\/+[ ]{0,1}+[0-9]{4}+[ ]{0,1}+\/+[ ]{0,1}+[UVES0]{0,2}+[ ]{0,1}+[\/]{0,1}+[ ]{0,1}+[UV]{0,1}/", $a_payments[$y]['Rif.Cliente'], $cronoAtto)){
                $crono = str_replace(" ","",$cronoAtto[0]);
                $crono = str_replace("-","/",$crono);
            }
        }
        else{
            if(preg_match("/[0-9]{1,5}+[ ]{0,1}+[DEL]{2,3}+[ ]{0,1}+[0-9]{4}/", $a_row['causale'], $cronoAtto)){
                preg_match("/[0-9]{1,5}+[ ]{0,1}+[DEL]{2,3}+[ ]{0,1}+[0-9]{4}/", $cronoAtto[0], $crono);
                $crono = str_replace(" ","", str_replace("DEL","/",$crono[0]));
                $crono = str_replace(" ","", str_replace("DE","/",$crono));
                $explode = explode("/",$crono);
                $a_row['ID_Cronologico'] = $explode[0];
                if(isset($explode[1]))
                    $a_row['Anno_Cronologico'] = $explode[1];
            }
        }

        if($crono=="" && $partita!=""){
            if(explode("/",$partita)[1]>=($expData[2]-1)){
                $crono = $partita;
                $partita = "";
                $explode = explode("/",$crono);
                $a_row['ID_Cronologico'] = $explode[0];
                if(isset($explode[1]))
                    $a_row['Anno_Cronologico'] = $explode[1];

                $a_row['Comune_ID_Partita'] = null;
                $a_row['Anno_Riferimento_Partita'] = null;

            }
        }


        $a_results = null;
        if($a_row['act']!="VERBALE" && $a_row['act']!=""){

            if($a_row['tipo_atto']!="Pignoramento")
                $table = "atto";
            else
                $table = "pignoramento_generale";


            if($a_row['CC']!=""){
                if($a_row['ID_Cronologico']!=null && $a_row['Anno_Cronologico']!=null){


                    if($a_row['tipo_atto']=="Pignoramento"){
                        $query = "SELECT pignoramento_generale.*, atto.*, partita_tributi.*, utente.* ";
                        $query.= "FROM pignoramento_generale JOIN atto ON atto.ID=pignoramento_generale.Atto_ID";
                    }
                    else{
                        $query = "SELECT atto.*, partita_tributi.*, utente.* FROM atto";
                    }

                    $query.= " JOIN partita_tributi ON partita_tributi.ID=".$table.".Partita_ID";
                    $query.= " JOIN utente ON utente.ID=partita_tributi.Utente_ID";
                    $query.= " WHERE ".$table.".ID_Cronologico=".$a_row['ID_Cronologico']." AND ".$table.".Anno_Cronologico=".$a_row['Anno_Cronologico'];
                    $query.= " AND ".$table.".CC='".$a_row['CC']."'";
                    $a_results = $cls_db->getResults($cls_db->ExecuteQuery($query));

                    if(count($a_results)==0 && $a_row['tipo_atto']=="Pignoramento"){
                        $query = "SELECT pignoramento_generale.*, atto.*, partita_tributi.*, utente.* FROM atto JOIN pignoramento_generale ON pignoramento_generale.Atto_ID=atto.ID";
                        $query.= " JOIN partita_tributi ON partita_tributi.ID=atto.Partita_ID";
                        $query.= " JOIN utente ON utente.ID=partita_tributi.Utente_ID";
                        $query.= " WHERE atto.ID_Cronologico=".$a_row['ID_Cronologico']." AND atto.Anno_Cronologico=".$a_row['Anno_Cronologico']." ";
                        $query.= " AND atto.CC='".$a_row['CC']."'";
                        $a_results = $cls_db->getResults($cls_db->ExecuteQuery($query));

                    }

                }
                else if($a_row['Comune_ID_Partita']!="" && $a_row['Anno_Riferimento_Partita']!=""){
                    $query = "SELECT ".$table.".*, partita_tributi.*, utente.* FROM partita_tributi";
                    $query.= " JOIN utente ON utente.ID=partita_tributi.Utente_ID";
                    $query.= " JOIN ".$table." ON partita_tributi.ID=".$table.".Partita_ID";
                    $query.= " WHERE partita_tributi.Comune_ID=".$a_row['Comune_ID_Partita']." AND partita_tributi.Anno_Riferimento=".$a_row['Anno_Riferimento_Partita'];
                    $query.= " AND partita_tributi.CC='".$a_row['CC']."'";
                    $a_results = $cls_db->getResults($cls_db->ExecuteQuery($query));
                }
                else if($a_row['Comune_ID_Utente']!=""){
                    $query = "SELECT ".$table.".*, partita_tributi.*, utente.* FROM utente";
                    $query.= " JOIN partita_tributi ON partita_tributi.Utente_ID=utente.ID";
                    $query.= " JOIN ".$table." ON partita_tributi.ID=".$table.".Partita_ID";
                    $query.= " WHERE utente.Comune_ID=".$a_row['Comune_ID_Utente'];
                    $query.= " AND utente.CC_Comune='".$a_row['CC']."'";
                    $a_results = $cls_db->getResults($cls_db->ExecuteQuery($query));
                }
            }
            else{
//                if($a_row['Comune_ID_Partita']!=""){
//
//                    $queryPartita = "SELECT * FROM partita_tributi WHERE Comune_ID=".$a_row['Comune_ID_Partita']." AND Anno_Riferimento=".$a_row['Anno_Riferimento_Partita']." ";
//                    if($a_row['CC']!="")
//                        $queryPartita.= "AND CC='".$a_row['CC']."'";
//
//                    $a_partita = $cls_db->getResults($cls_db->ExecuteQuery($queryPartita));
//                    if(count($a_partita)>0)
//                        $a_utente = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM utente WHERE ID=".$a_partita['Utente_ID']));
//                }
//                else if(count($a_utente)>0){
//                    $queryUtente = "SELECT * FROM utente WHERE ";
//                    if($a_row['Comune_ID_Utente']!=""){
//                        $queryUtente.= "Comune_ID=".$a_row['Comune_ID_Utente']." ";
//                        if($a_row['CC']!="")
//                            $queryUtente.= "AND CC_Comune='".$a_row['CC']."'";
//                    }
//
//
//
//
//                    $a_utente = $cls_db->getResults($cls_db->ExecuteQuery($queryUtente));
//                    if(count($a_utente)==0){
//
//                    }
//                    else{
//                        $a_partita = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM partita_tributi WHERE ID=".$a_partita['Utente_ID']));
//                    }
//
//                }
            }


        }


        ?>

        <tr class="riga_dispari">
            <td class="text_left" colspan="6"><?php echo $a_payments[$y]['Rif.Cliente'];?></td>
        </tr>

        <tr>
            <td class="text_left"><span class="color_titolo"><b>Pagamento</b></span></td>
            <td class="text_left"><?=$a_row['tipo_bonifico'];?></td>
            <td class="text_center"><span class="color_titolo"><b>Data</b></span></td>
            <td class="text_right"><?=$a_payments[$y]['Data Contabile'];?></td>
            <td class="text_center"><span class="color_titolo"><b>Importo</b></span></td>
            <td class="text_right"><?=number_format($cls_help->stringToFloat($a_payments[$y]['Credito']),2,",","");?> &euro;</td>
        </tr>
        <tr>
            <td class="text_left"><span class="color_titolo"><b>Anagrafica</b></span></td>
            <td class="text_left"><?=$a_row['anagrafica'];?></td>
            <td class="text_center"><span class="color_titolo"><b>Utente</b></span></td>
            <td class="text_right"><?=$utente;?></td>
            <td class="text_center"><span class="color_titolo"><b>Partita</b></span></td>
            <td class="text_right"><?=$partita;?></td>
        </tr>
        <tr>
            <td class="text_left"><span class="color_titolo"><b>Tipo atto</b></span></td>
            <td class="text_left"><?=$a_row['tipo_atto'];?></td>
            <td class="text_center"></td>
            <td class="text_right"></td>
            <td class="text_center"><span class="color_titolo"><b>Crono</b></span></td>
            <td class="text_right"><?=$crono;?></td>
        </tr>
        <tr>
            <td colspan="6"><hr></td>
        </tr>
        <tr>
            <td colspan="6"><?php print_r($a_results);?></td>
        </tr>

        <tr>
            <td colspan="6"><hr></td>
        </tr>
<?php
    }
?>
    </table>
<?php

//print_r($a_general);
//echo "<br>----------------------------------------<br>";
//for($i=0;$i<count($a_payments);$i++){
//    echo $a_payments[$i]['Credito'];
//    echo "<br><br>";
//
//    echo $a_payments[$i]['Rif.Cliente'];
//    echo "<br><br>";
//}











include(INC."/footer.php");