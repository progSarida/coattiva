<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

/*
$cf = "";                                                       // parametro ricerca cf/p.iva

$admin_S = "";                                                  // parametro ricerca ente

if(!(int)$_POST['all_city'])                                    // controllo su checkbox ricerca tutti i comuni
    $admin_S = " AND U.CC_Comune LIKE '".$_POST['admin']."' ";  // setto il codice dell'ente come parametro di ricerca

if(isset($_POST['cf']))                                         // controllo sull'input non vuoto
    $cf = $_POST['cf'];                                         // setto l'input dell'utente come parametro di ricerca se non vuoto
*/

//Costruzione query

$ric_CDF = $cls_help->getVar('user_cf');
$city = $cls_help->getVar('city');

$query = "(SELECT ID, Comune_ID, Codice_Fiscale AS CF, CONCAT(Cognome,' ', Nome) AS Utente, Genere , Forma_Giuridica, CC_Comune, Ditta, Cognome, Nome FROM utente ";
$query.= "WHERE Genere != 'D' AND Codice_Fiscale like '%".$ric_CDF."%' ";
if($city!="")
    $query.= "and CC_Comune='".$city."' ";
$query.= ") UNION ";
$query.= "(SELECT ID, Comune_ID, Partita_Iva AS CF, Ditta AS Utente, Genere , Forma_Giuridica, CC_Comune, Ditta, Cognome, Nome FROM utente ";
$query.= "WHERE Genere = 'D' AND Partita_Iva like '%".$ric_CDF."%' ";
if($city!="")
    $query.= "and CC_Comune='".$city."' ";
$query.= ") ORDER BY CF";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

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
    $tableElem[$i]["action_row"] = 'initialId("user_sosp",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"user_sosp\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);
