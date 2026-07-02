<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

$surname = '';
$business = '';
$name = '';

if(isset($_POST['surname']))
    $surname = $cls_help->getVar('surname');
if(isset($_POST['business']))
    $business = $cls_help->getVar('business');
if(isset($_POST['name']))
    $name = $cls_help->getVar('name');
if(isset($_POST['admin']))
    $admin = $cls_help->getVar('admin');
if(isset($_POST['type']))
    $type = $cls_help->getVar('type');

//echo $type;die;

$surname_ = explode(" ", $surname);
$business_ = explode(" ", $business);
$name_ = explode(" ", $name);

$surname_o = "Cognome like '%' ";
$business_o = "Ditta like '%' ";
$name_o = "Nome like '%' ";
// crea where su cognome e su ditta
if($surname != ''){
    $surname_o = "Cognome like '%".$surname."%' and Nome like '%' or ";
    $business_o = "Ditta like '%".$business."%' or ";

    if(count($surname_)>1){
        for($i=0; $i<count($surname_); $i++){
            $surname_o.= "Cognome like '%".$surname_[$i]."%' or ";
            $business_o.= "Ditta like '%".$business_[$i]."%' or ";

        }
    }
}
// crea where su nome
if($name != ''){
    $name_o = "Cognome like '%' and Nome like '%".$name."%' or ";

    if(count($name_)>1){
        for($i=0; $i<count($name_); $i++){
            $name_o.= "Nome like '%".$name_[$i]."%' or ";
        }
    }
}
// toglie or finale
if($surname != ''){
    $surname_o = substr($surname_o, 0, -3);
    $business_o = substr($business_o, 0, -3);
}
if($name != '')
    $name_o = substr($name_o, 0, -3);
// errore dovuto al fatto che il campo Con_Nome per le ditte e' nullo e crea problemi nella query di selezione
if ($surname_[0] == NULL or $surname_[0] == '')
{
    $surname_o = "Cognome like '%' ";
    $business_o = "Ditta like '%' ";
}

$surname_o.= "AND Cognome != '' ";
$name_o.= "AND Nome != '' ";
$business_o.= "AND Ditta != '' ";

// crea query
$query = "SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, IF(Genere = 'D', Ditta, CONCAT(Cognome,' ',Nome)) AS Ins, ";
                                                /*IF(Codice_Fiscale = '' or Codice_Fiscale is null, Partita_Iva, Codice_Fiscale )  AS CF, ";*/
$query.= "Cognome, Nome, Ditta, Genere , IF(Genere = 'D', Partita_IVA, Codice_Fiscale) AS CF, Codice_Fiscale, Partita_IVA, Forma_Giuridica ";
                                                /*, IF( Ditta = '' or Ditta is null, Cognome, Ditta ) AS Utente ";*/
$query.= "FROM utente JOIN partita_tributi ON utente.ID = partita_tributi.Utente_ID  AND CC_Comune='".$admin."' ";

if($type == 'all'){
    $query.= "WHERE ((".$surname_o.") AND (".$name_o.")) OR (".$business_o.") AND CC_Comune='".$admin."' ";
} else if($type == 'person'){
    $query.= "WHERE (".$surname_o.") AND (".$name_o.") AND CC_Comune='".$admin."' ";
} else if($type == 'business'){
    $query.= "WHERE (".$business_o.") AND CC_Comune='".$admin."' ";
}

//echo($query);die;

// risultato query
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));
// creazione tabella
$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    if($tableElem[$i]["Genere"] == "M"){
        $tableElem[$i]["Genere"] = "Maschio";
    }
    else if($tableElem[$i]["Genere"] == "F"){
        $tableElem[$i]["Genere"] = "Femmina";
    }
    else if($tableElem[$i]["Genere"] == "D"){
        $tableElem[$i]["Genere"] = "Ditta";
    }
    $tableElem[$i]["action_row"] = 'initialId("user_sel",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"user_sel\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);