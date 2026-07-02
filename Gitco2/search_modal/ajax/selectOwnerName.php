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

$admin_S = "";                                                  // parametro ricerca ente

if(!(int)$_POST['all_city'])                                    // controllo su checkbox ricerca tutti i comuni
    $admin_S = " AND U.CC_Comune LIKE '".$_POST['admin']."' ";  // setto il codice dell'ente come parametro di ricerca
*/
if(isset($_POST['name']))                                       // controllo sull'input non vuoto
    $name = $_POST['name'];                                     // setto l'input dell'utente come parametro di ricerca se non vuoto

// Copia query originale                                        // aggiungere i CONCAT della nuova nelle due SELECT
$a = $cls_help->getVar('a');                                    //
$last_name = $cls_help->getVar('name');                         // input utente correggere getVar
$admin = $cls_help->getVar('admin');                            // ente

if($last_name==null)
    $last_name="";

$termine = explode(" ", $last_name);

$nomeCognome = "Cognome like '%".addslashes($last_name)."%' and Nome like '%' or ";
$nomeCognome .= "Cognome like '%' and Nome like '%".addslashes($last_name)."%' or ";

$ditta = "Ditta like '%".addslashes($last_name)."%' or ";

for($i=0; $i<count($termine); $i++)
{
    $ditta = "Ditta like '%".addslashes($termine[$i])."%' or ";
    if(count($termine) == 1)
    {
        $nomeCognome .= "Cognome like '%".addslashes($termine[$i])."%' and Nome like '%' or ";
        $nomeCognome .= "Cognome like '%' and Nome like '%".addslashes($termine[$i])."%' or ";
    }
    else
    {
        for($y=0; $y<count($termine); $y++)
        {
            if($i!=$y)
            {
                $nomeCognome .= "Cognome like '%".addslashes($termine[$i])."%' and Nome like '%".addslashes($termine[$y])."%' or ";
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

//$query = "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Codice_Fiscale AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Cognome AS utente_nome FROM utente, partita_tributi ";
$query = "(SELECT DISTINCT U.ID, U.Comune_ID, U.CC_Comune, U.Partita_Iva AS CF , U.Ditta AS Utente , U.Genere, PT.ID AS P_ID, PT.Anno_Riferimento AS Anno, CONCAT('(',U.Comune_ID,')',' ',U.Ditta,' ',coalesce(FGS.Sigla,'')) AS Ins ";
$query .= "FROM utente AS U LEFT JOIN partita_tributi AS PT ON U.ID = PT.Utente_ID AND U.CC_Comune='".$admin."' ";
$query .= "LEFT JOIN forma_giuridica_societa AS FGS ON U.Forma_Giuridica = FGS.ID AND FGS.CC = '*****' AND U.CC_Comune='".$admin."' ";
$query .= "WHERE U.Genere = 'D' AND (".$ditta.") AND U.CC_Comune='".$admin."' GROUP BY U.ID) ";
$query .= "UNION ";
//$query.= "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome FROM utente, partita_tributi ";
$query .= "(SELECT DISTINCT U.ID, U.Comune_ID, U.CC_Comune, U.Codice_Fiscale AS CF , CONCAT(U.Cognome,' ', U.Nome) AS Utente, U.Genere , PT.ID AS P_ID, PT.Anno_Riferimento AS Anno, CONCAT('(',U.Comune_ID,')',' ',U.Cognome,' ',U.Nome) AS Ins ";
$query .= "FROM utente AS U LEFT JOIN partita_tributi AS PT ON U.ID = PT.Utente_ID AND U.CC_Comune='".$admin."' ";
$query .= "WHERE U.Genere != 'D' AND (".$nomeCognome.") AND U.CC_Comune='".$admin."' GROUP BY U.ID) ";
$query .= "ORDER BY Utente";


// Query nuova con problema input multiplo

//Query per ricerca su ditte
/*
$query = "(SELECT DISTINCT U.ID, U.Comune_ID, U.CC_Comune, U.Partita_Iva AS CF , U.Ditta AS Utente , U.Genere, PT.ID AS P_ID, PT.Anno_Riferimento AS Anno, CONCAT('(',U.Comune_ID,')',' ',U.Ditta,' ',coalesce(FGS.Sigla,'')) AS Ins ";
$query .= "FROM utente AS U LEFT JOIN partita_tributi AS PT ON U.ID = PT.Utente_ID LEFT JOIN forma_giuridica_societa AS FGS ON U.Forma_Giuridica = FGS.ID AND FGS.CC = '*****' ";
$query .= "WHERE U.Genere = 'D' AND U.Ditta LIKE '%".$name."%' AND U.CC_Comune LIKE '".$_POST['admin']."' GROUP BY U.ID) ";
//Union
$query .= "UNION ";
//Query per ricerca su persone
$query .= "(SELECT DISTINCT U.ID, U.Comune_ID, U.CC_Comune, U.Codice_Fiscale AS CF , CONCAT(U.Cognome,' ', U.Nome) AS Utente, U.Genere , PT.ID AS P_ID, PT.Anno_Riferimento AS Anno, CONCAT('(',U.Comune_ID,')',' ',U.Cognome,' ',U.Nome) AS Ins ";
$query .= "FROM utente AS U LEFT JOIN partita_tributi AS PT ON U.ID = PT.Utente_ID ";
$query .= "WHERE U.Genere != 'D' AND (CONCAT(COALESCE(U.Cognome,''),' ',COALESCE(U.Nome,'')) LIKE '%".$name."%') AND U.CC_Comune LIKE '".$_POST['admin']."' GROUP BY U.ID) ORDER BY Utente";
*/
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
    $tableElem[$i]["action_row"] = 'initialId("owner_n",'.str_replace("'","&apos;",json_encode($result[$i])).');$(".offcanvas").modal("hide");';
    $tableElem[$i]["select"] = "<i onclick='initialId(\"owner_n\",".str_replace("'","&apos;",json_encode($result[$i]))."); $(\".offcanvas\").modal(\"hide\");' style='cursor: pointer;color: #0c63e4;' class='fas fa-arrow-right'></i>";
}

echo json_encode($tableElem);