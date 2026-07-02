<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";
include_once(CLS . "/cls_db.php");
include_once(CLS . "/cls_help.php");
//Classe per cambio formato data
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();


//Inizializzazione valori da chiamata Ajax

$last_name = $cls_help->getVar('user_name');                    // input
$lock = $cls_help->getVar('lock');                              // su visura_massiva.php gestisce il blocco dellla ricerca del secondo input
$admin = $cls_help->getVar('admin');                            // codice catastale comune in uso

//Costruzione query

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
// se non c'è blocco
//if($lock == 'N'){
    $query = "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Codice_Fiscale AS CF, CONCAT(Cognome,' ', Nome) AS Ins, Cognome, Nome, Ditta, Genere AS Genere, Forma_Giuridica ";
    $query.= "FROM utente JOIN partita_tributi ON utente.ID = partita_tributi.Utente_ID AND CC_Comune='".$admin."'";
    $query.= "WHERE Genere != 'D' AND (".$nomeCognome.") AND CC_Comune='".$admin."') ";
    $query.= "UNION ";
    $query.= "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Partita_Iva AS CF, Ditta AS Ins, Cognome, Nome, Ditta, Genere AS Genere, Forma_Giuridica ";
    $query.= "FROM utente JOIN partita_tributi ON utente.ID = partita_tributi.Utente_ID ";
    $query.= "WHERE Genere = 'D' AND (".$ditta.") AND CC_Comune='".$admin."') ";
/*}

// se è bloccato su persona
if($lock == 'M' || $lock == 'F'){
    $query = "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Codice_Fiscale AS CF, CONCAT(Cognome,' ', Nome) AS Ins, Cognome, Nome, Ditta, Genere AS Genere, Forma_Giuridica ";
    $query.= "FROM utente JOIN partita_tributi ON utente.ID = partita_tributi.Utente_ID AND CC_Comune='".$admin."'";
    $query.= "WHERE Genere != 'D' AND (".$nomeCognome.") AND CC_Comune='".$admin."') ";
}

// se è bloccato su ditta
if($lock == 'D'){
    $query = "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Partita_Iva AS CF, Ditta AS Ins, Cognome, Nome, Ditta, Genere AS Genere, Forma_Giuridica ";
    $query.= "FROM utente JOIN partita_tributi ON utente.ID = partita_tributi.Utente_ID AND CC_Comune='".$admin."'";
    $query.= "WHERE Genere = 'D' AND (".$ditta.") AND CC_Comune='".$admin."') ";
}*/

$query.= "ORDER BY Ins ";

//echo($lock."        ");echo($query);die;

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
    $tableElem[$i]["action_row"] = 'initialId("user",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"user\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);