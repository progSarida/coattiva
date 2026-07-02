<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();

// Costruzione query

$last_name = $cls_help->getVar('company_name');
$allCities = $cls_help->getVar('all_city');
$admin = $cls_help->getVar('admin');

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

$query = "SELECT ID, Comune_ID, Partita_Iva AS CF, Ditta AS Utente, Genere , Forma_Giuridica, CC_Comune FROM utente ";
$query.= "WHERE Genere = 'D' AND (".$ditta.") ";
if($allCities!="y")
    $query.= "and CC_Comune='".$admin."' ";
$query.= " ORDER BY Utente";


$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$tableElem = array();
$count = count($result);
for($i = 0;$i<$count;$i++){
    $tableElem[$i] = $result[$i];
    $tableElem[$i]["Genere"] = "Ditta";
    $tableElem[$i]["action_row"] = 'initialId("company_n",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"company_n\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);
