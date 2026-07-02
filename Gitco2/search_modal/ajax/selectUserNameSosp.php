<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

/*
$name = "";                                                     // parametro ricerca nome

$admin_S = "";                                                    // parametro ricerca ente

if(!(int)$_POST['all_city'])                                    // controllo su checkbox ricerca tutti i comuni
    $admin_S = " AND U.CC_Comune LIKE '".$_POST['admin']."' ";    // setto il codice dell'ente come parametro di ricerca

if(isset($_POST['name']))                                       // controllo sull'input non vuoto
    $name = $_POST['name'];                                     // setto l'input dell'utente come parametro di ricerca se non vuoto
*/
// Costruzione query

$last_name = $cls_help->getVar('user_name');
$city = $cls_help->getVar('city');
//echo($allCities);die;

if($last_name == null)
    $last_name="";

$termine = explode(" ", $last_name);

$nomeCognome = "Cognome like '%".addslashes($last_name)."%' or ";
$ditta = "Ditta like '%".addslashes($last_name)."%' or ";

for($i=0; $i<count($termine); $i++)
{
    $ditta.= "Ditta like '".addslashes($termine[$i])."%' or ";
    if(count($termine) == 1)
    {
        $nomeCognome .= "Cognome like '".addslashes($termine[$i])."%' and Nome like '%' or ";
        $nomeCognome .= "Cognome like '%' and Nome like '".addslashes($termine[$i])."%' or ";
    }
    else
    {
        for($y=0; $y<count($termine); $y++)
        {
            if($i!=$y)
            {
                $nomeCognome .= "Cognome like '".addslashes($termine[$i])."%' and Nome like '".addslashes($termine[$y])."%' or ";
            }
        }
    }
}

$nomeCognome = substr($nomeCognome, 0, -3);
$ditta = substr($ditta, 0, -3);

// errore dovuto al fatto che il campo Con_Nome per le ditte e' nullo e crea problemi nella query di selezione
if ($termine[0] == NULL or $termine[0] == '')
{
    $nomeCognome = "Cognome like '%'";
    $ditta = "Ditta like '%'";
}

$query = "(SELECT ID, Comune_ID, Codice_Fiscale AS CF, CONCAT(Cognome,' ', Nome) AS Utente, Genere , Forma_Giuridica, CC_Comune, Ditta, Cognome, Nome  FROM utente ";
$query.= "WHERE Cognome != '' AND (".$nomeCognome.") ";
if($city!="")
    $query.= "and CC_Comune='".$city."' ";
$query.= ") UNION ";
$query.= "(SELECT ID, Comune_ID, Partita_Iva AS CF, Ditta AS Utente, Genere , Forma_Giuridica, CC_Comune, Ditta, Cognome, Nome FROM utente ";
$query.= "WHERE Ditta != '' AND (".$ditta.") ";
if($city!="")
    $query.= "and CC_Comune='".$city."' ";
$query.= ") ORDER BY Utente";
//var_dump($query);die;
//Query di prova
/*
$query = "(SELECT ID, Comune_ID, Codice_Fiscale AS CF, CONCAT(Cognome,' ', Nome) AS Utente, Genere , Forma_Giuridica, CC_Comune 
FROM utente 
WHERE Cognome != '' AND (Cognome like '%conia%antonio%' or Cognome like '%conia%' and Nome like '%antonio%' or Cognome like '%antonio%' and Nome like '%conia%') and CC_Comune='F682' ) 
UNION 
(SELECT ID, Comune_ID, Partita_Iva AS CF, Ditta AS Utente, Genere , Forma_Giuridica, CC_Comune
FROM utente 
WHERE Ditta != '' AND (Ditta like '%conia%antonio%' or Ditta like '%conia%' or Ditta like '%antonio%' ) and CC_Comune='F682') 
ORDER BY Utente;";
*/
//alert($query);die;
//var_dump($query);die;
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
