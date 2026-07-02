<?php

$submenuPageNo = 2;

include("../_path.php");
include(ROOT."/_parameter.php");

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/classe_anni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/parametri.php";
include CLASSI . "/notifiche_importate.php";

include(INC."/header.php");
include(INC."/menu.php");
include(INC."/submenu_partita.php");

set_time_limit(-1);

//CORREZIONE SPESE NOTIFICA PRECEDENTI

//$partite = $cls_db->getResults($cls_db->SelectQuery("SELECT ID FROM partita_tributi ORDER BY ID"));
//for($i=0;$i<count($partite);$i++){
//    $atti = $cls_db->getResults($cls_db->SelectQuery("SELECT ID, Spese_Notifica, CAN, CAD, Rettifica_Flag FROM atto WHERE Partita_ID=".$partite[$i]['ID']." ORDER BY Data_Elaborazione ASC"));
//    $sommaSpese = 0;
//    for($y=0;$y<count($atti);$y++){
//
//        $cls_db->ExecuteQuery("UPDATE atto SET Spese_Notifica_Precedenti = ".$sommaSpese." WHERE ID=".$atti[$y]['ID']);
//        if($atti[$y]['Rettifica_Flag']!="si")
//            $sommaSpese+= $atti[$y]['Spese_Notifica']+$atti[$y]['CAN']+$atti[$y]['CAD'];
//    }
//}

//CORREZIONE TOTALI

//$partite = $cls_db->getResults($cls_db->SelectQuery("SELECT Partita_ID FROM view_atti_tributi where SOMMA_ATTO != CODICI_TRIBUTO ORDER BY Partita_ID"));
//for($i=0;$i<count($partite);$i++){
//    echo "uhuh";
//    $atti = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM atto WHERE Partita_ID=".$partite[$i]['Partita_ID']." ORDER BY Data_Elaborazione ASC"));
//
//    for($y=1;$y<count($atti);$y++){
//
//        if($atti[$y-1]['Rettifica_Flag']!="si"){
//
//            $query_atto = "UPDATE atto SET Totale_Dovuto = ".($atti[$y]['Interessi']+$atti[$y]['Spese_Notifica']+$atti[$y-1]['Totale_Dovuto']).", ";
//            $query_atto.= "Importo = ".$atti[$y-1]['Importo'].", ";
//            $query_atto.= "Sanzione = ".$atti[$y-1]['Sanzione'].", ";
//            $query_atto.= "Addizionale = ".$atti[$y-1]['Addizionale'].", ";
//            $query_atto.= "Interessi_Precedenti = ".($atti[$y-1]['Interessi']+$atti[$y-1]['Interessi_Precedenti'])." ";
//            $query_atto.= "WHERE ID=".$atti[$y]['ID'];
//
//            //        echo $query_atto;
//            $cls_db->ExecuteQuery($query_atto);
//        }
//    }
//}

//CORREZIONI TOTALI INTERESSI PRECEDENTI

//$query = "SELECT Partita_ID, SOMMA_ATTO, CODICI_TRIBUTO FROM view_atti_tributi where SOMMA_INTERESSI = CODICI_TRIBUTO AND Interessi_Precedenti>0 ";
//$query.= "AND Spese_Notifica_Precedenti=0 AND view_atti_tributi.Rettifica_Flag!=\"si\" AND view_atti_tributi.Flag_Blocco_Coazione!=\"si\" ";
//$query.= "ORDER BY Partita_ID";
//
//$partite = $cls_db->getResults($cls_db->SelectQuery($query));
//for($i=0;$i<count($partite);$i++){
//    $atti = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM atto WHERE Partita_ID=".$partite[$i]['Partita_ID']." ORDER BY Data_Elaborazione ASC"));
//
//    $interessi_codici = $partite[$i]['CODICI_TRIBUTO']-$partite[$i]['SOMMA_ATTO'];
//    for($y=0;$y<count($atti);$y++){
//        if($interessi_codici>0){
//            $query_atto = "UPDATE atto SET ";
//            $query_atto.= "Interessi_Codici_Tributo = ".$interessi_codici.", ";
//            $query_atto.= "Interessi_Precedenti = Interessi_Precedenti-".$interessi_codici." ";
//            $query_atto.= "WHERE ID=".$atti[$y]['ID'];
//
//            //        echo $query_atto;
//            $cls_db->ExecuteQuery($query_atto);
//        }
//
//    }
//}

//CORREZIONI TOTALI INTERESSI

//$query = "SELECT Partita_ID, SOMMA_ATTO, CODICI_TRIBUTO FROM view_atti_tributi where SOMMA_INTERESSI+Interessi = CODICI_TRIBUTO AND Interessi>0 ";
//$query.= "AND Spese_Notifica_Precedenti=0 AND view_atti_tributi.Rettifica_Flag!=\"si\" AND view_atti_tributi.Flag_Blocco_Coazione!=\"si\" ";
//$query.= "ORDER BY Partita_ID";
//
//$partite = $cls_db->getResults($cls_db->SelectQuery($query));
//for($i=0;$i<count($partite);$i++){
//    $atti = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM atto WHERE Partita_ID=".$partite[$i]['Partita_ID']." ORDER BY Data_Elaborazione ASC"));
//
//    $interessi_codici = $partite[$i]['CODICI_TRIBUTO']-$partite[$i]['SOMMA_ATTO'];
//    for($y=0;$y<count($atti);$y++){
//        $query_atto = "UPDATE atto SET ";
//        $query_atto.= "Interessi_Codici_Tributo = ".$interessi_codici." ";
//
//        echo $atti[$y]['Interessi']." ".$interessi_codici;
//        if($atti[$y]['Interessi']>=$interessi_codici && $y==0){
//            $query_atto.= ", Interessi = Interessi-".$interessi_codici." ";
//        }
//        if($atti[$y]['Interessi_Precedenti']>=$interessi_codici){
//            $query_atto.= ", Interessi_Precedenti = Interessi_Precedenti-".$interessi_codici." ";
//        }
//        $query_atto.= "WHERE ID=".$atti[$y]['ID'];
//
////        echo $query_atto."<br>";
//
//        //        echo $query_atto;
//        $cls_db->ExecuteQuery($query_atto);
//
//
//    }
//}


//CORREZIONI CODICI TRIBUTO
$query = "SELECT Partita_ID, Comune_ID, CC, CODICI_TRIBUTO, SOMMA_ATTO FROM view_atti_tributi where CODICI_TRIBUTO!=SOMMA_ATTO ";
$query.= "AND Rettifica_Flag!=\"si\"  AND Flag_Blocco_Coazione!=\"si\" ";
$query.= "ORDER BY Partita_ID";

$partite = $cls_db->getResults($cls_db->SelectQuery($query));
$cls_codici = new codice_tributo(null);
$cod_partita = 0;
for($i=0;$i<count($partite);$i++){
    if($partite[$i]['Partita_ID']!=$cod_partita){
        $cod_partita = $partite[$i]['Partita_ID'];

    $primo_atto = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM atto WHERE Partita_ID=".$partite[$i]['Partita_ID']." ORDER BY Data_Elaborazione ASC"));
    $tributi = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM tributo WHERE Partita_ID=".$partite[$i]['Partita_ID']));

    $a_importi = array("Importo"=>0,"Spese"=>0,"Sanzione"=>0,"Maggiorazione"=>0,"Interessi"=>0,"Addizionale"=>0,"Pagamenti"=>0);
    $a_codici_id = array("Importo"=>0,"Spese"=>0,"Sanzione"=>0,"Maggiorazione"=>0,"Interessi"=>0,"Addizionale"=>0,"Pagamenti"=>0);

    $totale_codici = 0;
    for($y=0;$y<count($tributi);$y++){
        $tipo_tributo = $cls_codici->tipo_tributo($tributi[$y]['Codice_Tributo']);

        switch($tipo_tributo){
            case "IMPORTO":
                $a_importi['Importo'] = $tributi[$y]['Imposta'];
                $a_codici_id['Importo'] = $tributi[$y]['ID'];

                $checkImporto=1;
                break;
            case "SPESE":
                $a_importi['Spese'] = $tributi[$y]['Imposta'];
                $a_codici_id['Spese'] = $tributi[$y]['ID'];
                break;
            case "SANZIONE":
                $a_importi['Sanzione'] = $tributi[$y]['Imposta'];
                $a_codici_id['Sanzione'] = $tributi[$y]['ID'];
                break;
            case "MAGGIORAZIONE":
                $a_importi['Maggiorazione'] = $tributi[$y]['Imposta'];
                $a_codici_id['Maggiorazione'] = $tributi[$y]['ID'];
                break;
            case "INTERESSI":
                $a_importi['Interessi'] = $tributi[$y]['Imposta'];
                $a_codici_id['Interessi'] = $tributi[$y]['ID'];
                break;
            case "PAGAMENTO":
                $a_importi['Pagamenti'] = $tributi[$y]['Imposta'];
                $a_codici_id['Pagamenti'] = $tributi[$y]['ID'];
                break;
            case "ADDIZIONALE":
                $a_importi['Addizionale'] = $tributi[$y]['Imposta'];
                $a_codici_id['Addizionale'] = $tributi[$y]['ID'];
                break;
        }

        if($tipo_tributo!="PAGAMENTO")
            $totale_codici+=$tributi[$y]['Imposta'];
    }

        if($totale_codici!=$partite[$i]['SOMMA_ATTO']) {

            $differenza = $partite[$i]['SOMMA_ATTO'] - $totale_codici;

            $risultato = $a_importi['Importo'] + $differenza;

            if($risultato>0){
                $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Importo'];
                $cls_db->ExecuteQuery($query);
            }
            else{
                if(abs($differenza)>=$a_importi['Maggiorazione'] && $a_codici_id['Maggiorazione']>0){
                    $differenza+= $a_importi['Maggiorazione'];
                    $query = "DELETE FROM tributo WHERE ID=".$a_codici_id['Maggiorazione'];
                    $cls_db->ExecuteQuery($query);
//                    alert($differenza);
                }
                else if($a_codici_id['Maggiorazione']>0){
                    $risultato = $a_importi['Maggiorazione']+$differenza;
                    $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Maggiorazione'];
                    $cls_db->ExecuteQuery($query);
                    continue;
                }

                if(abs($differenza)>=$a_importi['Interessi'] && $a_codici_id['Interessi']>0){
                    $differenza+= $a_importi['Interessi'];
                    $query = "DELETE FROM tributo WHERE ID=".$a_codici_id['Interessi'];
                    $cls_db->ExecuteQuery($query);
                }
                else if($a_codici_id['Interessi']>0){
                    $risultato = $a_importi['Interessi']+$differenza;
                    $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Interessi'];
                    $cls_db->ExecuteQuery($query);
                    continue;
                }

                if(abs($differenza)>=$a_importi['Sanzione'] && $a_codici_id['Sanzione']>0){
                    $differenza+= $a_importi['Sanzione'];
                    $query = "DELETE FROM tributo WHERE ID=".$a_codici_id['Sanzione'];
                    $cls_db->ExecuteQuery($query);
                }
                else if($a_codici_id['Sanzione']>0){
                    $risultato = $a_importi['Sanzione']+$differenza;
                    $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Sanzione'];
                    $cls_db->ExecuteQuery($query);
                    continue;
                }

                if(abs($differenza)>=$a_importi['Spese'] && $a_codici_id['Spese']>0){
                    $differenza+= $a_importi['Spese'];
                    $query = "DELETE FROM tributo WHERE ID=".$a_codici_id['Spese'];
                    $cls_db->ExecuteQuery($query);
                }
                else if($a_codici_id['Spese']>0){
                    $risultato = $a_importi['Spese']+$differenza;
                    $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Spese'];
                    $cls_db->ExecuteQuery($query);
                    continue;
                }

                if(abs($differenza)>=$a_importi['Addizionale'] && $a_codici_id['Addizionale']>0){
                    $differenza+= $a_importi['Addizionale'];
                    $query = "DELETE FROM tributo WHERE ID=".$a_codici_id['Addizionale'];
                    $cls_db->ExecuteQuery($query);
                }
                else if($a_codici_id['Addizionale']>0){
                    $risultato = $a_importi['Addizionale']+$differenza;
                    $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Addizionale'];
                    $cls_db->ExecuteQuery($query);
                    continue;
                }

                if(abs($differenza)>=$a_importi['Importo'] && $a_codici_id['Importo']>0){
                    $differenza+= $a_importi['Importo'];
                    $query = "DELETE FROM tributo WHERE ID=".$a_codici_id['Importo'];
                    $cls_db->ExecuteQuery($query);
                }
                else if($a_codici_id['Importo']>0){
                    $risultato = $a_importi['Importo']+$differenza;
                    $query = "UPDATE tributo SET Imposta = ".$risultato." WHERE ID=".$a_codici_id['Importo'];
                    $cls_db->ExecuteQuery($query);
                    continue;
                }
            }
        }

    }

}

//RACCOGLIMENTO CODICI TRIBUTO

//$query = "SELECT SUM(TRIB.Imposta) AS SOMMA, TRIB.* FROM partita_tributi AS PAR
//JOIN tributo AS TRIB ON PAR.ID = TRIB.Partita_ID
//GROUP BY TRIB.Partita_ID, TRIB.Codice_Tributo HAVING COUNT(TRIB.Codice_Tributo)>1 ORDER BY TRIB.Codice_Tributo";
//
//$tributi = $cls_db->getResults($cls_db->SelectQuery($query));
//for($i=0;$i<count($tributi);$i++){
//    $query = "SELECT ID FROM tributo WHERE Partita_ID = ".$tributi[$i]['Partita_ID']." AND Codice_Tributo = '".$tributi[$i]['Codice_Tributo']."'";
//    $idTributi = $cls_db->getResults($cls_db->SelectQuery($query));
//    for($y=0;$y<count($idTributi);$y++){
//        $queryUpdate = "UPDATE tributo SET Imposta = ".$tributi[$i]['SOMMA']." WHERE ID=".$idTributi[$y]['ID'];
//        $queryDelete = "DELETE FROM tributo WHERE ID=".$idTributi[$y]['ID'];
//        if($y==0)
//            $cls_db->ExecuteQuery($queryUpdate);
//        else
//            $cls_db->ExecuteQuery($queryDelete);
//    }
//}



//$partite = $cls_db->getResults($cls_db->SelectQuery("SELECT Partita_ID from v_tributi WHERE CODICI_TRIBUTO-SOMMA_ATTO=Interessi_Precedenti AND CODICI_TRIBUTO!=SOMMA_ATTO AND Rettifica_Flag!=\"si\" "));
//for($i=0;$i<count($partite);$i++){
//    $atti = $cls_db->getResults($cls_db->SelectQuery("SELECT * FROM atto WHERE Partita_ID=".$partite[$i]['ID']." ORDER BY Data_Elaborazione ASC"));
//
//    $interessi_precedenti = 0;
//    for($y=0;$y<count($atti);$y++){
//
//        $query_atto = "UPDATE atto SET Importo = Importo+Interessi_Precedenti";
//        $query_atto.= "Interessi_Precedenti = ".$interessi_precedenti." ";
//        $query_atto.= "WHERE ID=".$atti[$y]['ID'];
//
//        $interessi_precedenti+= $atti[$y]['Interessi'];
//        //        echo $query_atto;
//        $cls_db->ExecuteQuery($query_atto);
//
//    }
//}


die;