<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include_once CLS."/cls_help.php";
include_once CLS."/cls_db.php";
include_once CLS."/cls_Utils.php";
include_once CLS . "/cls_storico.php";													// inclusione classe

$storico = new storico('storicoComunicazioniEnte','9');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();

$c = $cls_help->getVar('c');
$anno = $cls_help->getVar("anno");
$procId = $cls_help->getVar("procedureTypeId");
$sovrascrivi = $cls_help->getVar("sovrascrivi");

$procedura = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM procedure_types WHERE Id = '".$procId."'") );

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];
$array = explode('\\', $cls_help->getVar('file_name'));
$file_name = array_pop($array);                


$query = "SELECT Id FROM procedures WHERE CC = '".$c."' AND Anno_Riferimento=$anno AND Procedure_Type_Id = ".$procId;
$res = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if($res !== null && $sovrascrivi != "1"){
    header("Location: procedure.php?c={$c}&a={$anno}&error=2&msg=Attenzione procedura già inserita per l\'anno {$anno}");
    die;
}

function checkHoliday($date)
{

    if (date('l', strtotime($date)) == 'Saturday') {
        return true;
    } else if (date('l', strtotime($date)) == 'Sunday') {
        return true;
    } else {
        $receivedDate = date('d M', strtotime($date));
        //$pasqua = date('d M',easter_date($date));
        //$pasquetta = date('d M', strtotime($pasqua. ' + 1 days'));
        $holiday = array(
            '01 Jan' => 'New Year Day',
            '06 Jan' => 'Epifania',
            '25 Apr' => 'Festa Liberazione',
            '02 Jun' => 'Festa Repubblica',
            '15 Aug' => 'Ferragosto',
            '01 Nov' => 'Santi',
            '08 Dec' => 'Immacolata',
            '25 Dec' => 'Christmas Day',
            '26 Dec' => 'Santo Stefano',
            '31 Dec' => 'New Year Eve',
            // $pasqua => 'Pasqua',
            //$pasquetta =>'Pasquetta'
        );

        foreach ($holiday as $key => $value) {
            if ($receivedDate == $key) {
                return true;
            }
        }
    }
    return false;
}

function GetWorkingDay($date)
{
    $i = 0;
    while (checkHoliday($date) && ($i < 10)) {

        $date = date("Y-m-d", strtotime($date . ' - 1 days'));
        $i++;
    }
    return $date;
}
$monthAndDay = "";
switch($procId){
    case 5:
        $monthAndDay = "-06-30";
        break;
    case 7:
    case 8:
        $monthAndDay = "-01-31";
        break;
    default:
        $monthAndDay = "-01-30";
        break;
}

$data_stampa = GetWorkingDay(($anno + 1) . $monthAndDay);

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

$msg = "Dati aggiornati correttamente!";
$error = 0;

if($sovrascrivi == "1" && $res != null) {

    $a_dbParams = array(
        'table' => 'procedures',
        'fields' => array(
            //array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => $procId),
            //array('name' => 'Datetime', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            //array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $data_stampa),
            //array('name' => 'CC', 'type' => 'string', 'value' => $c),
            //array('name' => 'Anno_Riferimento', 'type' => 'int', 'value' => $anno),
            array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr'])
            //array('name' => 'Description', 'type' => 'string', 'value' => $anno),
        ),
        'updateField' => array("name" => "Id", "type" => "int", "value" => $res["Id"])
    );

    $check = $cls_db->DbSave($a_dbParams);

    if(!$check){
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore impossibile aggiornare i dati!";
    }

    $procedure_id = $res["Id"];

}
else{
    $a_dbParams = array(
        'table' => 'procedures',
        'fields' => array(
            array('name' => 'Procedure_Type_Id', 'type' => 'int', 'value' => $procId),
            array('name' => 'Datetime', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('name' => 'Procedure_Date', 'type' => 'date', 'value' => $data_stampa),
            array('name' => 'CC', 'type' => 'string', 'value' => $c),
            array('name' => 'Anno_Riferimento', 'type' => 'int', 'value' => $anno),
            array('name' => 'User_Id', 'type' => 'int', 'value' => $_SESSION['aut_progr']),
            array('name' => 'Description', 'type' => 'string', 'value' => $anno),
        )
    );

    $procedure_id = $cls_db->DbSave($a_dbParams);

    if(!$procedure_id){
        $cls_db->Rollback();
        $error = 1;
        $msg = "Errore impossibile salvare i dati!";
    }
}
$cls_db->End_Transaction();


if(isset($_FILES['file_choice'])) {

    if($res != null && $sovrascrivi == "1") {
        $newExt = pathinfo($_FILES['file_choice']['name'],PATHINFO_EXTENSION);
        if ($handle = opendir(PROCEDURE . $procedure_id)) {

            while (false !== ($nameFile = readdir($handle))) {

                if ($nameFile != "." && $nameFile != "..") {

                    $ext = pathinfo($nameFile, PATHINFO_EXTENSION);
                    if($ext == $newExt)
                        unlink(PROCEDURE . $procedure_id . "/" . $nameFile);
                }
            }
            closedir($handle);
        }
    }

    //die;
    $pathFILE = $cls_utils->crea_dir(PROCEDURE . $procedure_id);
    $pdfFileName = "Conto_Giudiziale_".$procedure_id."_" . $c . "_" . date("H-i-s") . ".pdf";

    $tmpFile = $_FILES['file_choice']['tmp_name'];
    $newFile = $pathFILE . '/' . $_FILES['file_choice']['name'];
    $result = move_uploaded_file($tmpFile, $newFile);
}

if($error == 0)
$storico->insRow('I', "Inserito manualmente file procedure '".$procedura['Name']."' '".$file_name."' per ente ".$nome_ente."[".$c."]");

header("Location: procedure.php?c={$c}&a={$anno}&msg={$msg}&error={$error}");