<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include(INC . "/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_LOG.php"; 
include_once "bar.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$creation_username =  $_SESSION['username'];

/** PHP PROGRESS BAR  START  */
flush();
ob_flush();
echo "<script>startBar();</script>";
flush();
ob_flush();
flush();
ob_flush();

/** PHP PROGRESS BAR    END  */

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_partita = $cls_help->getVar('tipo_partita');
$tipo = $cls_help->getVar('tipo');
$last_proc_id = $cls_help->getVar('proc');


                        
$query_procedures = "select * from procedures where Id = $last_proc_id";
$a_proceudre = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_procedures));

$data_elab = $a_proceudre['Procedure_Date'];


if (is_null($a_proceudre)) {
?>

    <script>
        swal({
                title: 'ERRORE',
                text: "MANCANZA DI ELABORAZIONI",
                icon: 'danger',
                timer: 5000,
                buttons: false
        }).then((result) => {
            location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/mgmt_stragiudiziali.php?c="+c+"&a="+a+"&proc="+<?php echo $last_proc_id ?>"&tipo="+<?php echo $tipo ?>"&tipo_partita="+<?php echo $tipo_partita ?>;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}

// QUERY istituti
if ($tipo=="Previdenziali")
    $query_istituti =   " select ID from enti_esterni where Tipo = \"previdenza\" and PEC is not null and PEC<>\"\"";
else
    $query_istituti =   " select ID from banca where Tipo_Banca = \"sede\" and PEC is not null and PEC<>\"\"";

    
$results = $cls_db->ExecuteQuery($query_istituti);
$istituti = $cls_db->getResults($results);



// QUERY PARAMETRI ANNUALI

$query_par_y =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $a_proceudre['CC'] . "' AND Anno=" . date('Y');

$log->info( $query_par_y);

$params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par_y));

if (is_null($params_arr)) {
   
?>
    <script>
        swal({
            title: 'ERRORE',
            text: "MANCANZA PARAMETRI ANNUALI ",
            icon: 'danger',
            timer: 5000,
            buttons: false
        }).then((result) => {
            location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/mgmt_stragiudiziali.php?c="+c+"&a="+a+"&proc="+<?php echo $last_proc_id ?>"&tipo="+<?php echo $tipo ?>"&tipo_partita="+<?php echo $tipo_partita ?>;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}


if (count($istituti) > 0) {

        $countAllResult = count($istituti);

        /** INIZIO TRANSAZIONE **/

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        

        try {
            $get_user_id = function() use ($creation_username,$cls_db)
            {
                $q = "select Id from autenticazione where User = '$creation_username'";;
                $result = $cls_db->getArrayLine($cls_db->ExecuteQuery($q));
                if(is_null($result)) throw new Exception("Utente non presente nella tabella autenticazione");
                
                return $result["Id"];
            };
            //UPDATE PROCEDURES
            $a_dbParams = array(
                'table' => 'procedures',
                'updateField' => array(
                    array('name' => 'Id',  'type' => 'int', 'value' => $a_proceudre['Id']),
                ),
                'fields'=> array(
                    array(  'name' => 'Procedure_Status_Id',  'type' => 'int', 'value' => 10),
                    array(  'name' => 'User_Id',        'type' => 'string', 'value' => $get_user_id()),
                    array(  'name' => 'Procedure_Date',            'type' => 'date', 'value' => date('Y-m-d')),
                   
                )
            );

            $cls_db->DbSave( $a_dbParams);

            flush();
            ob_flush();
            flush();
            ob_flush();
            echo "<script>updateBar(" . ceil(50) . ");</script>";
            flush();
            ob_flush();
            flush();
            ob_flush();

            //SGANCIARE LE NON ELABORABILI
            $queryNonElab = "delete  from partita_procedure_pvt
            where Procedure_Id = $last_proc_id and Flag_Elaboration = 0";
            $cls_db->ExecuteQuery($queryNonElab);

            //ciclo

            foreach ($istituti as $key => $istituto) {
                flush();
                ob_flush();
                flush();
                ob_flush();
                echo "<script>updateBar(" . ceil($key * 100 / $countAllResult) . ");</script>";
                flush();
                ob_flush();
                flush();
                ob_flush();
        
                
        
                /** */
        
        
                /** INIZIO TRANSAZIONE **/
        
                $cls_db->Start_Transaction();
                $cls_db->Begin_Transaction();
        
                try {
                    if($tipo=="Previdenziali")
                        $colonna_id = 'Previdenza_Id';
                    else
                        $colonna_id = 'Banca_Id';
                    
                    $a_stragiudiziali = array(
                        'table' => 'stragiudiziali',
                        'fields' => array(
                            array('name' => 'Procedure_Id',      'type' => 'int', 'value' => $last_proc_id),
                            array('name' => $colonna_id,          'type' => 'int', 'value' => $istituto["ID"]),
                            array('name' => 'CC',                'type' => 'string', 'value' => $c),
                            array('name' => 'Tipo_Riscossione',  'type' => 'string', 'value' => $tipo_partita),
                            array('name' => 'Print_Type_ID',     'type' => 'int', 'value' => 4),
    
                        )
                    );
    
                    $lastId_stragiudiziali = $cls_db->DbInsert($a_stragiudiziali);
        
                } catch (mysqli_sql_exception $e) {
                    $cls_db->Rollback();
                    $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
                    $cls_help->alert("ERRORE!!!!!!!!");
                    flush();
                    ob_flush();
                    echo "<script>endBar('".$c."','".$a."',".$last_proc_id.",'".$tipo."'),'".$tipo_partita."');</script>";
                    flush();
                    ob_flush();
                    flush();
                    ob_flush();
                    die;
                    return;
                }
                $cls_db->End_Transaction();
        
            }
             //END FOREACH 

            flush();
            ob_flush();
            flush();
            ob_flush();
            echo "<script>updateBar(" . ceil(50) . ");</script>";
            flush();
            ob_flush();
            flush();
            ob_flush();


        } catch (mysqli_sql_exception $e) {
            $cls_db->Rollback();
            $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
            $cls_help->alert("ERRORE!!!!!!!!");
            flush();
            ob_flush();
            echo "<script>endBar('".$c."','".$a."',".$last_proc_id.",'".$tipo."','".$tipo_partita."');</script>";
            flush();
            ob_flush();
            flush();
            ob_flush();
            die;
            return;
        }

        $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$a_proceudre['CC']."'") );

        $tipo_ = "";
        if($tipo == 'Banca')
            $tipo_ = "su banche";
        else   
            $tipo_ = "su enti previdenziali";

        $storico->insRow('E', "Elaborazione '".$a_proceudre['Description']."': Procedure stragiudiziali ".$tipo_." ".$ente['Denominazione']."[".$a_proceudre['CC']."]. Stato 'Tragiudiziali create'");


        $cls_db->End_Transaction();


     flush();
     ob_flush();
     echo "<script>endBar('".$c."','".$a."',".$last_proc_id.",'".$tipo."','".$tipo_partita."');</script>";
     flush();
     ob_flush();
     flush();
     ob_flush(); 
} else {
    flush();
    ob_flush();
    echo "<script>endBar('".$c."','".$a."',".$last_proc_id.",'".$tipo."''".$tipo_partita."');</script>";
    flush();
    ob_flush();
    flush();
    ob_flush();
    /** PHP PROGRESS BAR    END  */
}

?>