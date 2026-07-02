<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");

$cls_db = new cls_db();
$cls_help = new cls_help();

$cf = "";                                                       // parametro ricerca cf/p.iva
//$admin_S = "";                                                  // parametro ricerca ente
/*
if(!(int)$_POST['all_city'])                                    // controllo su checkbox ricerca tutti i comuni
    $admin_S = " AND U.CC_Comune LIKE '".$_POST['admin']."' ";  // setto il codice dell'ente come parametro di ricerca
*/
if(isset($_POST['cf']))                                         // controllo sull'input non vuoto
    $cf = $_POST['cf'];                                         // setto l'input dell'utente come parametro di ricerca se non vuoto

//echo ($cf);                                                      OK: arriva qui e cf è settato

//Query per ricerca su ditte
$query = "(SELECT DISTINCT U.ID, U.Comune_ID, U.CC_Comune, U.Partita_Iva AS CF , U.Ditta AS Utente , U.Genere, PT.ID AS P_ID, PT.Anno_Riferimento AS Anno, CONCAT('(',U.Comune_ID,')',' ',U.Ditta,' ',coalesce(FGS.Sigla,'')) AS Ins ";
$query .= "FROM utente AS U LEFT JOIN partita_tributi AS PT ON U.ID = PT.Utente_ID AND U.CC_Comune LIKE '".$_POST['admin']."' ";
$query .= "LEFT JOIN forma_giuridica_societa AS FGS ON U.Forma_Giuridica = FGS.ID AND FGS.CC = '*****' AND U.CC_Comune LIKE '".$_POST['admin']."' ";
$query .= "WHERE U.Genere = 'D' AND U.Partita_Iva LIKE '%".$cf."%' AND U.CC_Comune LIKE '".$_POST['admin']."' GROUP BY U.ID) ";
//Union
$query .= "UNION ";
//Query per ricerca su persone
$query .= "(SELECT DISTINCT U.ID, U.Comune_ID, U.CC_Comune, U.Codice_Fiscale AS CF , CONCAT(U.Cognome,' ', U.Nome) AS Utente, U.Genere , PT.ID AS P_ID, PT.Anno_Riferimento AS Anno, CONCAT('(',U.Comune_ID,')',' ',U.Cognome,' ',U.Nome) AS Ins ";
$query .= "FROM utente AS U LEFT JOIN partita_tributi AS PT ON U.ID = PT.Utente_ID AND U.CC_Comune LIKE '".$_POST['admin']."' ";
$query .= "WHERE U.Genere != 'D' AND U.Codice_Fiscale LIKE '%".$cf."%' AND U.CC_Comune LIKE '".$_POST['admin']."' GROUP BY U.ID) ORDER BY Utente";

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
    $tableElem[$i]["action_row"] = 'initialId("owner_cf",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"owner_cf\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);