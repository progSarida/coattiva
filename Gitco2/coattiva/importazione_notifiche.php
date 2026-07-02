<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_help.php";
include_once CLS . "/cls_file.php";
include_once CLS . "/cls_query.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_mercurioService.php";
include_once(CLS . "/cls_ftp.php");
include_once(CLS . "/cls_DateTimeInLine.php");
include_once(CLS . "/cls_elaborazioniUtils.php");
include_once(CLS . "/cls_Utils.php");

include(INC . "/header.php");
include(INC . "/menu.php");
include_once CLS . "/cls_storico.php";													// inclusione classe

$storico = new storico('storicoImportazione','4');

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

ini_set('max_execution_time', 0);

$FileList = "";
$Cont = 0;

$analizzatutto = $cls_help->getVar('analizzatutto');
$importatutto = $cls_help->getVar('importatutto');

$folderToDownload = $cls_help->getVar('folderToDownload');
$folderToArchive = $cls_help->getVar('folderToArchive');
$extractArchives = $cls_help->getVar('extractArchives');
$deleteArchives = $cls_help->getVar('deleteArchives');

$importType = $cls_help->getVar('importType');
if($importType==null)
    $importType="AG";

$scrittaBarra = "Elaborazione completata";

$cls_file = new cls_file();
$cls_date = new cls_DateTimeI("DB",false);
$cls_elab = new cls_elaborazioniUtils();
$cls_utils = new cls_Utils();
$a_path = array();

$path = $cls_file->folderCreation(SUPER_ROOT . "/archivio/Importazioni_Notifiche/IMPORT/");
$downloadPath = $cls_file->folderCreation(SUPER_ROOT . "/archivio/Importazioni_Notifiche/DOWNLOAD/");

$a_path['importDir'] = $path;
$a_path['toImgDir'] = $cls_file->folderCreation(SUPER_ROOT . "/archivio/Notifiche");
$a_path['backupDir'] = $cls_file->folderCreation(SUPER_ROOT . "/archivio/Notifiche/Backup");
$a_path['imgDir'] = $path;

$queryMercurio = "SELECT mercurio.Articolo, mercurio.Tipo, mercurio.Descrizione, par.Tipo_Dato, par.ID FROM parametri_notifica AS mercurio ";
$queryMercurio .= "JOIN parametri_notifica AS par ON par.ID = mercurio.Collegamento WHERE mercurio.Tipo_Dato = 'MERCURIO' ORDER BY mercurio.ID";
$a_mercurioParams = $cls_db->getResults($cls_db->SelectQuery($queryMercurio));

if($importType=="AG")
    $folder_mercurio = '/Recupero_Immagini/AG/';
else if($importType=="AR")
    $folder_mercurio = '/Recupero_Immagini/AR/';

define('FTP_HOST', 'ftp.mercurioservice.it');
define('FTP_USER', 'sarida');
define('FTP_PASS', '1ftp4sarida');
$ftp = new cls_ftp(FTP_HOST, FTP_USER, FTP_PASS,true);

if($folderToDownload!=""){
    mkdir($downloadPath.$folderToDownload,0777);
    $ftp->changeDir($folder_mercurio.$folderToDownload);
    $ftp->downloadFolder($downloadPath.$folderToDownload."/","verbali");
}
else if($folderToArchive!=""){
    $ftp->changeDir($folder_mercurio);
    $ftp->moveFiles($folder_mercurio,$folderToArchive,"NOTIFICHE IMPORTATE/COATTIVA/".$folderToArchive,"VERBALI");
}

$ftp->changeDir($folder_mercurio);
$a_ftpTempFolders = $ftp->getDirListing();
$a_ftpFolders = array();
foreach($a_ftpTempFolders as $folder){
    $ftp->changeDir($folder_mercurio.$folder);
    $a_ftpFiles = $ftp->getDirListing();

    if($folder=="NOTIFICHE IMPORTATE")
        continue;

    if(count($a_ftpFiles)==0){
        $ftp->deleteDirectory($folder_mercurio.$folder);
    }


    foreach ($a_ftpFiles as $file){
        if(strpos($file,"VERBALI")===false){
            $a_ftpFolders[] = $folder;
            break;
        }
    }
}

$downloadedFolder = "";
if($directory_handle = opendir($downloadPath)){
    while (($file = readdir($directory_handle)) !== false) {
        if($file!="." && $file!=".."){
            $downloadedFolder = $file;
            break;
        }
    }
    closedir($directory_handle);
}
$a_downloadedFiles = array();
if($downloadedFolder!=""){
    chmod($downloadPath.$downloadedFolder."/",0777);
    if($directory_handle = opendir($downloadPath.$downloadedFolder)){
        while (($file = readdir($directory_handle)) !== false) {
            if($file!="." && $file!=".."){
                $expFile = explode(".",$file);
                chmod($downloadPath.$downloadedFolder."/".$file,0777);
                if($deleteArchives==1 && is_file($downloadPath.$downloadedFolder."/".$file))
                    unlink($downloadPath.$downloadedFolder."/".$file);
                else if(strtolower($expFile[count($expFile)-1])=="zip")
                    $a_downloadedFiles[] = $file;

            }
        }
        closedir($directory_handle);
    }
    if($deleteArchives==1){
        if(is_dir($downloadPath.$downloadedFolder))
            rmdir($downloadPath.$downloadedFolder);
        $downloadedFolder = "";
    }
}

if($extractArchives==1){
    $zip = new ZipArchive();
    foreach($a_downloadedFiles as $archiveToExtract){
        $zip->open($downloadPath.$downloadedFolder."/".$archiveToExtract, ZipArchive::CREATE);
        $exts=array('jpg','jpeg','png','csv','txt','TXT','JPG','JPEG','PNG','CSV');
        for($i = 0; $i < $zip->numFiles; $i++) {
            $file_name = $zip->getNameIndex($i);

            if(strpos($file_name,"_MACOS")===false){
                $ext = pathinfo( $file_name, PATHINFO_EXTENSION );
                $basename = pathinfo( $file_name, PATHINFO_BASENAME );

                /* store a reference to the file name for extraction or copy */
                if( in_array( $ext, $exts ) ) {
                    $files[]=$file_name;
                    $checkFileInsideZip=true;
                    if($ext=="csv" || $ext=="CSV" || $ext=="txt" || $ext=="TXT"){
                        $expZipName = explode(".",$archiveToExtract);
                        $replaceName = str_replace(" ","",$expZipName[0]);
                        $handle = fopen('zip://'.$downloadPath.$downloadedFolder."/".$archiveToExtract.'#'.$file_name, 'r');
                        $result = fread($handle,100);
                        fclose($handle);
                        $expFile = explode(";",$result);
                        echo substr($result,0,10);
                        if(count($expFile)>2){
                            for($z=0;$z<3;$z++){
                                switch($z){
                                    case 0: if($expFile[$z]!="cod_comune")   $checkFileInsideZip = false;  break;
                                    case 1: if($expFile[$z]!="num_viol")   $checkFileInsideZip = false;  break;
                                    case 2: if($expFile[$z]!="REC_NOME")   $checkFileInsideZip = false;  break;
                                    default: $checkFileInsideZip = false;  break;
                                }
                            }
                        }
                        else if(substr($result,0,10)!="cod_comune"){
                            $checkFileInsideZip = false;
                        }

                        $basename = $replaceName."_".date("Y-m-d_h-i-s").".csv";
                        sleep(1);
                    }

                    /* To extract files and ignore directory structure */
                    if($checkFileInsideZip===true){
                        $res = copy( 'zip://'.$downloadPath.$downloadedFolder."/".$archiveToExtract.'#'.$file_name, $path . $basename );
                    }
//                echo ( $res ? 'Copiato: '.$basename : 'Impossibile copiare: '.$basename ) . '<br />';
                }
            }
        }
        $zip->close();
    }
}

$checkFile = false;
if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "csv") {
            $checkFile = true;
            $Cont++;
            $FileList .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . $Cont . '</div>
            <div class="table_caption_H col-sm-11">' . $file . '</div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}

$str_download = '<div class="col-sm-6" style="margin-right: 0px;padding-right: 0px;border-right: 2px solid whitesmoke;">
                    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>CARTELLA NOTIFICHE SCARICATE '.$downloadedFolder.'</b></div>
				    <div class="clean_row HSpace4"></div>
				    <div class="table_caption_H col-sm-12" style="height:35rem;overflow:auto">';
foreach($a_downloadedFiles as $donwloadedFile){
    $str_download.='<div class="table_caption_H col-sm-12">'.$donwloadedFile.'</div>';
}
if($checkFile===true || count($a_downloadedFiles)==0){
    $disabled = "disabled";
    $backgroundcolor = "lightgrey";
    $backgroundcolor2 = "lightgrey";
}
else{
    $disabled = "";
    $backgroundcolor = "#78ba71";
    $backgroundcolor2 = "#de9eb4";
}

$str_download.= '</div>
<div class="table_label_H col-sm-12" style="display: flex; justify-content: center;">
    <button id="extract_btn" style="background-color: ' .$backgroundcolor.'; width: 15rem; height:95%;" '.$disabled.'>ESTRAI</button>
    <button id="delete_btn" style="background-color: '.$backgroundcolor2.'; width: 15rem; height:95%;" '.$disabled.'>ELIMINA</button>
</div>
</div>';

if($downloadedFolder!=""){
    $disabled = "disabled";
    $backgroundcolor = "lightgrey";
    $backgroundcolor2 = "lightgrey";
}
else{
    $disabled = "";
    $backgroundcolor = "#78ba71";
    $backgroundcolor2 = "#de9eb4";
}

$str_ftp = '<div class="col-sm-6" style="margin-left: 0px;padding-left: 0px;">
                    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>FTP MERCURIO 
                    <select name"importType" id="importType" class="table_label_H" style="font-weight: bold;"><option>AG</option><option>AR</option></select></b></div>
				<div class="clean_row HSpace4"></div>
				<div class="table_caption_H col-sm-12" style="height:35rem;overflow:auto">';
foreach($a_ftpFolders as $ftpFolder){
    $str_ftp.='<div class="table_caption_H col-sm-6">'.$ftpFolder.'</div>
                <div class="table_caption_H col-sm-6">
                <button id="download-'.$ftpFolder.'" class="ftp_download_btn" style="background-color: '.$backgroundcolor.'; width: 8rem; height:95%;" '.$disabled.'>SCARICA</button>
                <button id="store-'.$ftpFolder.'" class="ftp_store_btn" style="background-color: '.$backgroundcolor2.'; width: 8rem; height:95%;" '.$disabled.'>ARCHIVIA</button>
</div>
';
}

$str_ftp.='</div>
<div class="table_label_H col-sm-12">
</div>
</div>';

$str_out = '
	<div class="container-fluid">	
		<div class="row-fluid">
		    '.$str_download.$str_ftp.'
        </div>
        <div class="clean_row HSpace16"></div>
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">ELENCO FILE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
' . $FileList;

$allaFineSpostaCsv = null;

if ($analizzatutto == "SI") {
    $a_img = array();
    $a_csv = array();
    $a_header = array("cc", "crono", "taxPayer", "notificationId", "flowNumber",
        "notificationDate", "notificationNumber", "notificationType", "notificationStatus", "notificationNotes",
        "frontImg", "backImg", "logDate", "sendNumber", "sendDate", "box", "lot", "position",
        "printType", "documentType"
    );


    $handle = opendir($path);
    $row = 0;
    $a_csvFiles = array();
    while (($file = readdir($handle)) != false) {
        if ($file != "." && $file != ".." && $file != "Thumbs.db")  //  queste sono cartelle
        {
            $checkNewFile = true;

            $esplEstensione = explode(".", $file);
            $estensione = strtoupper($esplEstensione[1]);

            if ($estensione == "TXT" || $estensione == "CSV") {

                if (($handleCSV = fopen($path . "/" . $file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handleCSV, 1000, ";")) !== FALSE) {
                        if($checkNewFile===true){
                            $checkNewFile = false;
                            $a_csvFiles[] = $file;
                        }
                        else{
                            $num = count($data);
                            for ($i_count = 0; $i_count < $num; $i_count++) {
                                $a_csv[$row][$a_header[$i_count]] = $data[$i_count];
                            }
                            $a_csv[$row]['fileName'] = $file;
                            $row++;
                        }
                    }
                    fclose($handleCSV);
                }

                for ($i = 0; $i < count($a_csv); $i++) {
                    $taxPayer = strtoupper($a_csv[$i]['taxPayer']);
                    $pos = strpos($taxPayer, "SPETT.LE ");
                    if ($pos !== false) {
                        $a_csv[$i]['taxPayer'] = substr($taxPayer, 9);
                    }

                    $a_csv[$i]['printType'] = strtoupper($a_csv[$i]['printType']);
                    $a_csv[$i]['documentType'] = strtoupper(trim($a_csv[$i]['documentType']));
                    $a_csv[$i]['notificationTypeId'] = $a_csv[$i]['notificationType'];
                    $a_csv[$i]['notificationStatusId'] = $a_csv[$i]['notificationStatus'];
//                    $a_csv[$i]['fileName'] = $file;
                }
            }
            else{
                $a_img[] = $path . $file;
            }
        }
    }
    closedir($handle);

    if (count($a_csv) == 0) {
        alert("Nessun file TXT o CSV e' stato trovato");
        echo "<script>history.back();</script>";
        return;
    }
}

//var_dump($a_img);

?>
<link rel=StyleSheet href="<?= CSS ?>/sarida.css" type="text/css" media=screen>
<script>

    //F3
    switchMenuImg("F3");
    F3_button = function () {
        var strLink = "importazione_notifiche.php?";
        strLink += "c=" + "<?=$cls_help->getVar("c");?>";
        strLink += "&a=" + "<?=$cls_help->getVar("a");?>";
        strLink += "&analizzatutto=" + "SI";
        if ("<?=$analizzatutto?>" == "SI") strLink += "&importatutto=" + "SI";
        location.href = strLink;
    }

    function inizio() {
        $('#progressbar').progressbar({
            value: false
        });
        $("#barlabel").text("Inizio elaborazione...");
    }

    function update(valore) {
        $("#progressbar").progressbar({value: parseInt(valore)});
        $("#barlabel").text(valore + "%");
    }

    function nessun_risultato() {
        $("#progressbar").progressbar({value: 100});
        $("#barlabel").text("Nessun risultato trovato");
    }

    function fine(value) {
        $("#progressbar").progressbar({value: 100});
        $("#barlabel").text(value);

    }
</script>


<table class="table_interna text_center" border="0">
    <tr class="pheight40">
        <td><span class="titolo font18 text_center">Importazione Notifiche</span></td>
    </tr>

    <?php if ($analizzatutto != "SI") { ?>


        <tr>
            <td>
                <?php echo $str_out; ?>
            </td>
        </tr>

    <?php } else { ?>
        <tr class="pheight40">
            <td valign=top>
                <div class="table_interna text_center" id="progressbar" style="height:55px;">
                    <div class="text_center" id="barlabel">
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <table class="table_interna text_center" border="0">
                    <tr>
                        <td>

                        </td>
                        <td>
                            <span class="color_red">Atto</span>
                        </td>
                        <td>
                            <span class="color_red">Comune</span>
                        </td>
                        <td>
                            <span class="color_red">Crono</span>
                        </td>
                        <td>
                            <span class="font11 color_red ">Fronte</span>
                        </td>
                        <td>
                            <span class="font11 color_red">Retro</span>
                        </td>
                        <td>
                            <span class="font11 color_red">Data</span>
                        </td>
                        <td>
                            <span class="font11 color_red">Tipo</span>
                        </td>
                        <td>
                            <span class="font11 color_red">Stato</span>
                        </td>
                        <td>
                            <span class="color_red">Esito</span>
                        </td>
                    </tr>
                    <?php

                    echo "<script>inizio();</script>";

                    flush();
                    ob_flush();
                    flush();
                    ob_flush();

                    sleep(1);

                    for ($k = 0; $k < count($a_csv); $k++) {

                        echo "<script>update(" . ceil($k * 100 / count($a_csv)) . ");</script>";

                        set_time_limit(100);

                        flush();
                        ob_flush();
                        flush();
                        ob_flush();

                       $printType = null;
                        switch ($a_csv[$k]['printType']) {
                            case "ATTOGIUDIZIARIO":     $printType = 1;     break;
                            case "RACCOMANDATA":        $printType = 2;     break;
                            case "POSTAORDINARIA":      $printType = 3;     break;
                        }

                        $documentType = null;
                        $actType = null;
                        switch ($a_csv[$k]['documentType']) {
                            case "AV_MORA":
                                $actType = 1;
                                $documentType = 12;
                                break;
                            case "SOLL_PRE":
                                $actType = 1;
                                $documentType = 11;
                                break;
                            case "INGIUNZIONE":
                                $actType = 1;
                                $documentType = 2;
                                break;
                            case "AVVISOINTIMAZIONE":
                                $actType = 1;
                                $documentType = 4;
                                break;
                            case "PIGNOBANCA":
                                $actType = 2;
                                $documentType = 8;
                                break;
                            case "PIGNOLAVORO":
                                $actType = 2;
                                $documentType = 7;
                                break;
                            case "PIGNOVEICOLO":
                                $actType = 2;
                                $documentType = 6;
                                break;
                        }

                        $a_csv[$k]['rowStyle'] = "sfondo_azzurro";
                        $a_csv[$k]['typeStyle'] = "sfondo_azzurro";
                        $a_csv[$k]['attoId'] = null;

                        if($actType==1){
                            $a_atto = $cls_elab->getDocumentFromCronoAtto($documentType, $a_csv[$k]['crono'], $a_csv[$k]['cc']);

                            $a_csv[$k]['attoId'] = $a_atto['ID'];
                            $a_csv[$k]['FlowId'] = $a_atto['FlowId'];
                            unset($atto);
                        }
                        else if($actType==2){
                            if($a_csv[$k]['documentType']=="PIGNOVEICOLO")
                                $tipo_pignoramento = "veicolo";
                            else
                                $tipo_pignoramento = "terzi";

                            //$pigno = new pignoramento(null, $a_csv[$k]['cc']);

                            $a_pigno = $cls_elab->getDocumentFromCronoPigno($documentType, $a_csv[$k]['crono'], $a_csv[$k]['cc']);
                            $a_csv[$k]['attoId'] = $a_pigno['ID'];
                            $a_csv[$k]['FlowId'] = $a_pigno['FlowId'];
                            unset($pigno);

                        }
                        else{
                            alert("TIPO ATTO NON GESTITO!!! ".$a_csv[$k]['documentType']);
                        }

//                        foreach ($a_csv[$k] as $key=>$value){
//                            echo "<br>".$key.": ";
//                            if(is_array($value))
//                                var_dump($value);
//                            else
//                                echo "<br>".$value."<br>";
//                        }
//

                        $expLog = explode(" ", $a_csv[$k]['logDate']);

                        $query = "SELECT * FROM notifiche_importate WHERE 1 = 2";

                        $myImportazione = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"notifiche_importate");//new notifiche_importate(null);
                        $myImportazione->Tipo_Spedizione = addslashes($a_csv[$k]['printType']);
                        $myImportazione->Tipo_Atto = addslashes($a_csv[$k]['documentType']);
                        $myImportazione->Riferimento = $a_csv[$k]['attoId'];
                        $myImportazione->FlowId = $a_csv[$k]['FlowId'];
                        $myImportazione->DocumentId = $a_csv[$k]['attoId'];
                        $myImportazione->DocumentTypeId = $documentType;
                        $myImportazione->PrintTypeId = $printType;
                        $myImportazione->CC_Comune = $a_csv[$k]['cc'];
                        $myImportazione->Num_Viol = $a_csv[$k]['crono'];
                        $myImportazione->Rec_Nome = addslashes($a_csv[$k]['taxPayer']);
                        $myImportazione->Progressivo_Notifica = addslashes($a_csv[$k]['notificationId']);
                        $myImportazione->Ms_Lotto = $a_csv[$k]['flowNumber'];
                        $myImportazione->Data_Notifica = $cls_date->GetDateDB($a_csv[$k]['notificationDate'],"IT");
                        $myImportazione->Ms_Ric_Num = $a_csv[$k]['notificationNumber'];
                        $myImportazione->Tipo_Notifica = addslashes($a_csv[$k]['notificationType']);
                        $myImportazione->Stato_Notifica = addslashes($a_csv[$k]['notificationStatus']);
                        $myImportazione->Note = addslashes($a_csv[$k]['notificationNotes']);
                        $myImportazione->Immagine_Fronte = $a_csv[$k]['frontImg'];
                        $myImportazione->Immagine_Retro = $a_csv[$k]['backImg'];
                        $myImportazione->Log_Modificato_Data = $cls_date->GetDateDB($expLog[0],"IT");
                        $myImportazione->Log_Modificato_Ora = $expLog[1];
                        $myImportazione->Ms_Rac_Num = $a_csv[$k]['sendNumber'];
                        $myImportazione->Data_Spedizione = $cls_date->GetDateDB($a_csv[$k]['sendDate'],"IT");
                        $myImportazione->Scatola = $a_csv[$k]['box'];
                        $myImportazione->Lotto = $a_csv[$k]['lot'];
                        $myImportazione->Posizione = $a_csv[$k]['position'];
                        $myImportazione->Nome_File = $a_csv[$k]['fileName'];
                        $myImportazione->Data_Importazione = date('Y-m-d');
                        $myImportazione->Operatore = $_SESSION['username'];

                        $a_csv[$k]['importCheck'] = $cls_elab->NotifImportataGiaPresente($myImportazione);

                        $myImportazione->ID = $a_csv[$k]['importCheck'];

                        $a_path['toImgPath'] = $cls_file->folderCreation($a_path['toImgDir'] . "/" . $a_csv[$k]['cc'] . "/");

                        $cls_mercurio = new cls_mercurioService();
                        $cls_mercurio->checkNotificationRow($a_csv[$k], $a_mercurioParams, $a_path);

                        if ($importatutto == "SI" && $a_csv[$k]['imgProblem'] == 0) {
                            $scrittaBarra = "Importazione completata";
                            $saveCheck = false;

                            $query = "BEGIN";
                            $cls_db->ExecuteQuery($query) or die ($cls_db->GetError());

                            if($actType==1){
                                if($a_csv[$k]['attoId']>0){
                                    $myImportazione->Riferimento = $a_csv[$k]['attoId'];

                                    $rispImport = false;

                                    if(is_null($myImportazione->ID))
                                        $rispImport = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$myImportazione,"notifiche_importate"));
                                    else
                                        $rispImport = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$myImportazione,"notifiche_importate",array("ID"=>$myImportazione->ID)));

                                    if ($rispImport) {
                                        $query = "SELECT * FROM atto WHERE ID = ".$a_csv[$k]['attoId']." AND CC = '".$a_csv[$k]['cc']."'";
                                        $myNotifica = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($a_csv[$k]['attoId'], $a_csv[$k]['cc']);

                                        $myNotifica->Data_Notifica = $cls_date->GetDateDB($a_csv[$k]['notificationDate'],"IT");
                                        $myNotifica->Modalita_Notifica = $a_csv[$k]['Modalita_Notifica'];
                                        $myNotifica->Stato_Notifica = $a_csv[$k]['Stato_Notifica'];
                                        $myNotifica->Motivo_Notifica = $a_csv[$k]['Motivo_Notifica'];

                                        if($cls_db->DbSave($cls_utils->GetObjectQuery($myNotifica,"atto", array("ID"=>$a_csv[$k]['attoId']))))
                                            $saveCheck = true;
                                    }
                                    else if($rispImport!="CAMPI_UGUALI"){
                                        echo "ERRORE ".$rispImport." INSERIMENTO NOTIFICA <br>";
                                        print_r($a_csv[$k]);
                                        echo "<br><br>";
                                    }
                                }
                                else{
                                    echo "ERROR ".$a_csv[$k]['documentType']." NOT FOUND<br>";
                                    print_r($a_csv[$k]);
                                    echo "<br><br>";
                                }
                            }
                            else if($actType==2){
                                if($a_csv[$k]['attoId']>0){
                                    $myImportazione->Riferimento = $a_csv[$k]['attoId'];

                                    $rispImport = false;
                                    if(is_null($myImportazione->ID))
                                        $rispImport = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$myImportazione,"notifiche_importate"));
                                    else
                                        $rispImport = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$myImportazione,"notifiche_importate",array("ID"=>$myImportazione->ID)));

                                    if($rispImport) {
                                        $query = "SELECT * FROM notifica_atto WHERE CC = '".$a_csv[$k]['cc']."' AND Atto_Notificato_ID = '".$a_csv[$k]['attoId']."' AND Tipo_Atto_Notificato = 'pignoramento' AND Tipo_Notifica = 'debitore'";
                                        $result = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"notifica_atto","object");

                                        $myNotifica = $result[count($result)-1];


                                        $myNotifica->Data_Notifica = $cls_date->GetDateDB($a_csv[$k]['notificationDate'],"IT");
                                        $myNotifica->Modalita_Notifica = $a_csv[$k]['Modalita_Notifica'];
                                        $myNotifica->Stato_Notifica = $a_csv[$k]['Stato_Notifica'];
                                        $myNotifica->Motivo_Notifica = $a_csv[$k]['Motivo_Notifica'];

                                        if($cls_db->DbSave($cls_utils->GetObjectQuery((array) $myNotifica,"notifica_atto", array("ID"=>$myNotifica->ID))))
                                            $saveCheck = true;
                                    }
                                    else if($rispImport!="CAMPI_UGUALI"){
                                        echo "ERRORE ".$rispImport." INSERIMENTO NOTIFICA <br>";
                                        print_r($a_csv[$k]);
                                        echo "<br><br>";
                                    }

                                }
                                else{
                                    echo "ERROR ".$a_csv[$k]['documentType']." NOT FOUND<br>";
                                    print_r($a_csv[$k]);
                                    echo "<br><br>";
                                }
                            }
                            else{
                                alert("TIPO ATTO NON GESTITO!!!" .$a_csv[$k]['documentType']);
                            }

                            if ($saveCheck) {
                                $frontImgCheck = true;
                                $backImgCheck = true;
                                if (file_exists($a_path['imgDir'] . $a_csv[$k]['frontImg']) && $a_csv[$k]['newFrontImg'] != "") {
                                    $im = new imagick($a_path['imgDir'] .  $a_csv[$k]['frontImg']);

                                    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                                    $im->setImageCompressionQuality(1);
                                    $im->writeImage($a_csv[$k]['newFrontImg']);
                                }
                                else
                                    $frontImgCheck = false;

                                if (file_exists($a_path['imgDir'] .  $a_csv[$k]['backImg']) && $a_csv[$k]['newBackImg'] != "") {
                                    //echo "<br>       --  " . $imgDir . $imgRetroAR;
                                    $im = new imagick($a_path['imgDir'] .  $a_csv[$k]['backImg']);

                                    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                                    $im->setImageCompressionQuality(1);
                                    $im->writeImage($a_csv[$k]['newBackImg']);
                                }
                                else
                                    $backImgCheck = false;

                                $query = "COMMIT";
                                $cls_db->ExecuteQuery($query) or die ($cls_db->GetError());

                                if ($frontImgCheck)
                                    unlink($a_path['imgDir'] .  $a_csv[$k]['frontImg']);

                                if ($backImgCheck)
                                    unlink($a_path['imgDir'] .  $a_csv[$k]['backImg']);

                                if ($allaFineSpostaCsv == "")
                                    $allaFineSpostaCsv = "SI";
                            } else{
                                $query = "ROLLBACK";
                                $cls_db->ExecuteQuery($query) or die ($cls_db->GetError());
                                $allaFineSpostaCsv = "NO";
                            }


                        } else
                            $allaFineSpostaCsv = "NO";


                        ?>

                        <tr class="<?= $a_csv[$k]['rowStyle'] ?>">
                            <td>
                                <span class="font11"><?= ($k + 1) ?></span>
                            </td>
                            <td class="<?= $a_csv[$k]['typeStyle'] ?>">
                                <label class="font11"
                                       title="<?= $a_csv[$k]['documentType'] ?>"><?= $a_csv[$k]['documentTypeAcronym'] ?></label>
                            </td>
                            <td>
                                <span class="font11"><i><?= $a_csv[$k]['cc'] ?></i></span>
                            </td>
                            <td>
                                (<?= $a_csv[$k]['attoId'] ?>) <?= $a_csv[$k]['crono'] ?>
                            </td>
                            <td>
                                <img src="<?= $a_csv[$k]['frontImgIcon'] ?>" class="pwidth20 pheigth20"
                                     title="<?= $a_csv[$k]['frontImg'] ?>">
                            </td>
                            <td>
                                <img src="<?= $a_csv[$k]['backImgIcon'] ?>" class="pwidth20 pheigth20"
                                     title="<?= $a_csv[$k]['backImg'] ?>">
                            </td>
                            <td>
                                <span class="font11"><?= $a_csv[$k]['notificationDate'] ?></span>
                            </td>
                            <td>
                                <span class="font11"><?= $a_csv[$k]['notificationType'] ?></span>
                            </td>
                            <td>
                                <span class="font11"><?= $a_csv[$k]['notificationStatus'] ?></span>
                            </td>
                            <td>
                                <img src="<?= $a_csv[$k]['esito']['img'] ?>" class="pwidth20 pheigth20"
                                     title="<?= $a_csv[$k]['esito']['text'] ?>">
                            </td>
                        </tr>

                        <?php
                    }


                    ?>
                </table>
            </td>
        </tr>

    <?php } ?>

</table>


</body>


<?php

if($scrittaBarra == "Elaborazione completata")
    $storico->insRow('E', "Elaborate notifiche ente ".$nome_ente."[".$c."]");
if($scrittaBarra == "Importazione completata")
    $storico->insRow('P', "Importate notifiche ente ".$nome_ente."[".$c."]");

echo "<script>fine('$scrittaBarra');</script>";

if ($allaFineSpostaCsv == "SI") {
    foreach($a_csvFiles as $file)
        rename($a_path['importDir']. "/" . $file, $a_path['backupDir'] ."/". $file);

    ?>
    <script>location.href = "importazione_notifiche.php?"+getString+"&importType=<?php echo $importType; ?>"</script>

<?php

}

?>

<script>
    var folderToDownload = "<?php if(isset($a_ftpFolders[0])) echo $a_ftpFolders[0]; else '';?>";
    var importType = "<?php echo $importType; ?>";
    // When the document is ready
    $(document).ready(function () {

        $("#importType").val(importType);
        $("#importType").change(function(){
            location.href = "importazione_notifiche.php?"+getString+"&importType="+$("#importType").val();
        });

        $("#extract_btn").click(function(){
            alert("EXTRACT");
            location.href = "importazione_notifiche.php?"+getString+"&extractArchives=1&importType="+$("#importType").val();
        });

        $("#delete_btn").click(function(){
            alert("DELETE");
            location.href = "importazione_notifiche.php?"+getString+"&deleteArchives=1&importType="+$("#importType").val();
        });

        $(".ftp_download_btn").click(function(){
            var download_id = $(this).attr("id");
            var result = download_id.split('-');
            if(confirm("Vuoi scaricare la cartella "+result[1]+"?")===true){
                location.href = "importazione_notifiche.php?"+getString+"&folderToDownload="+result[1]+"&importType="+$("#importType").val();
                return true;
            }
            return false;
        });

        $(".ftp_store_btn").click(function(){
            var download_id = $(this).attr("id");
            var result = download_id.split('-');
            if(confirm("Vuoi archiviare la cartella "+result[1]+"?")===true){
                location.href = "importazione_notifiche.php?"+getString+"&folderToArchive="+result[1]+"&importType="+$("#importType").val();
                return true;
            }
            return false;
        });


    });

</script>

</html>