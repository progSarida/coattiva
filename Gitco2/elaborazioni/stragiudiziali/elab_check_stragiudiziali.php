<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(ROOT . "/_parameter.php"); //dati database
include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_elaboration.php";
include_once CLS . "/cls_elaborationPignoramenti.php";
include_once ELAB_STRAGIUDIZIALI . "/cls/cls_elaborationStragiudiziali.php";
include_once ELAB_STRAGIUDIZIALI . "/cls/cls_GestionePivotPartitaProcedure.php";
include_once "bar.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();
$pivot = new GestionePivot($cls_db);

/** PHP PROGRESS BAR  START  */
flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

/** PHP PROGRESS BAR    END  */


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
?>
<script>
    var c = '<?= $c?>';
    var a = '<?= $a?>';
    var p = '<?= $p?>';
</script>    
<?php

$auth = $cls_help->getVar('auth');

$data_elab = $cls_help->toDbDate($cls_help->getVar('data_elab'));


if(intval($auth ) === 1){
    $cod_catastale = $cls_help->getVar('ente');
}else{
    $cod_catastale = $c;
}


$cur_day = new DateTime('now');
//prov
// $cur_day->modify("-1 year");
//
$cur_day->setTimezone(new DateTimeZone('UTC'));
$current_day  = $cur_day->format('Y-m-d');
$current_year = $cur_day->format('Y');
//$documentTypeId = $cls_help->getVar('DocumentTypeId'); 
$tipo = $cls_help->getVar('Tipo'); 

$tipo_partita = $cls_help->getVar('tipo_partita');
$da_n_elenco = $cls_help->getVar('da_n_elenco');
$a_n_elenco = $cls_help->getVar('a_n_elenco');
$da_data = $cls_help->getVar('da_data');
$a_data = $cls_help->getVar('a_data');
$da_anno = $cls_help->getVar('da_anno');
$a_anno = $cls_help->getVar('ad_anno');
$description = $cls_help->getVar('description');
$ruolo = $cls_help->getVar('ruolo');

$creation_username = $_SESSION['username'];

$documentTypeId = "40";


$cod_catastale = trim($cod_catastale);
$tipo_partita = trim($tipo_partita);
$data_elab =    trim($data_elab);




//if (!((!empty($documentTypeId) && is_numeric($documentTypeId) && !empty($cod_catastale) && !empty($description) && !empty($data_elab) && !empty($data_calc_int))
if (!((!empty($cod_catastale) && !empty($description) && !empty($data_elab))
    // TUTTI VALORIZZATI 
)) {
    include(INC . "/header.php");
    echo "<div class='alert alert-danger' role='alert'>
                È OBBLIGATORIO COMPILARE IL CAMPO DESCRIZIONE	<a class='alert alert-danger' role='alert' href='javascript:history.back();'> TORNA INDIETRO </a>
                </div>";
    include(INC . "/footer.php");
    return;
}



// Query parametri_annuali
$query_par_an = "SELECT * FROM parametri_annuali WHERE CC = '" . $cod_catastale . "' AND Anno = " . (int)$current_year;
$a_parAnnuali = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par_an));

$msg_error = "MANCANZA PARAMETRI ANNUALI";
$bool = is_null($a_parAnnuali);
include "error.php";

// Query controllo testo
$query_testo = "SELECT * FROM text_parameters WHERE CC = '" . $cod_catastale . "' AND Form_Type_Id = 40" ;
$a_testo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_testo));


$msg_error = "MANCANZA TESTO STRAGIUDIZIALE PER QUESTO COMUNE";
$bool = is_null($a_testo);
include "error.php";

//Query controllo testo Abilitazione del Gestore
$ControllaAbilitazione = function () : bool
{
    if(!empty($a_enteAdmin['Gestore_ID']))
    {
        if(!empty($a_enteAdmin['Gestore_Abilitazione']))
        {
            return true;
        }
        else
        {
            //TODO bloccare stampa !! 
            return false;
        }

    }
    return true;

};

$msg_error = "MANCANZA TESTO ABILITAZIONE GESTORE";
$bool = !$ControllaAbilitazione();

include "error.php";

// Query lockup_periods

$query_loc_per = "SELECT * FROM lockup_periods";
$a_lockupPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query_loc_per));

$queryDocTypes = "SELECT Id, ExpireDays FROM document_type WHERE ExpireDays is not null";
$a_docExpireDays = $cls_db->getResults($cls_db->ExecuteQuery($queryDocTypes),"array","Id");

// Query v_check_partite

$previdenziali = $tipo == "Previdenziali" ? "_previdenziali" : "";
$query_partita = " select * from v_check_stragiudiziali$previdenziali where 1=1 ";

if(!empty($ruolo))
    $query_partita .= " AND Ruolo_ID = ".$ruolo;
if (!empty(trim($da_n_elenco)))
    $query_partita .= " AND  Comune_ID >= " . (int)$da_n_elenco;
if (!empty(trim($a_n_elenco)))
    $query_partita .= " AND   Comune_ID <= " . (int)$a_n_elenco;
if (!empty(trim($tipo_partita)))
    $query_partita .= " AND   Tipo_Riscossione= '" . $tipo_partita . "'";
if (!empty(trim($cod_catastale)))
    $query_partita .= " AND CC = '" . $cod_catastale . "'";
if (!empty(trim($da_anno)))
    $query_partita .= " AND Anno_Riferimento >= " . (int)$da_anno;
if (!empty(trim($a_anno)))
    $query_partita .= " AND Anno_Riferimento <= " . (int)$a_anno;
if (!empty(trim($da_data)))
    $query_partita .= " AND Data_Notifica_Atto <= '" . $da_data . "'";
if (!empty(trim($a_data)))
    $query_partita .= " AND Data_Notifica_Atto <= '" . $a_data . "'";

  


$results = $cls_db->ExecuteQuery($query_partita);
$partite = $cls_db->getResults($results); 


if (count($partite) > 0) {

    $da_partita = (!empty(trim($da_n_elenco))) ? $da_n_elenco : 0;
    $a_partita = (!empty(trim($a_n_elenco))) ? $a_n_elenco :  0;
    

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

        $a_dbParams_procedure = array(
            'table' => 'procedures',
            'fields'=> array(
                array(  'name' => 'Procedure_Type_Id',      'type' => 'int', 'value' => 1),
                array(  'name' => 'Procedure_Status_Id',      'type' => 'int', 'value' => 1),
                array(  'name' => 'Document_Type_Id',     		'type' => 'int', 'value' => $documentTypeId),
                array(  'name' => 'Description',                'type' => 'string', 'value' => $description),
                array(  'name' => 'CC',                 		'type' => 'string', 'value' => $cod_catastale),
                array(  'name' => 'User_Id',  		'type' => 'string', 'value' => $get_user_id()),
                array(  'name' => 'Procedure_Date',              'type' => 'date', 'value' => $data_elab),
                array('name' => 'Datetime', 'type' => 'date', 'value' => date("Y-m-d H:i:s")),
                
            )
        );
        $last_procedure_id = $cls_db->DbInsert($a_dbParams_procedure);
        $a_params = array(
            'Parametri_Annuali' => $a_parAnnuali,
            'Lockup_Periods' => $a_lockupPeriods,
            'Doc_Expire_Days' => $a_docExpireDays,
            'Elaboration_DocumentTypeId' => $documentTypeId, 
            'Data_Elaborazione' => $data_elab
        );
       
        $cls_elaboration = new cls_elaborationStragiudiziali($a_params);
        $i = 0;
        foreach ($partite as $partita) {
            $i++;
            flush();	ob_flush();		flush();	ob_flush();
            echo "<script>updateBar(".ceil($i*100/count($partite)).");</script>";
            flush();	ob_flush();		flush();	ob_flush();
            
            $a_params = array(
                "Tipo_Riscossione" => $partita['Tipo_Riscossione']
            );
            $cls_elaboration->setParams($a_params);

            $a_getPositionStatus = $cls_elaboration->getPositionStatus($partita);
           
            if($a_getPositionStatus['check_error'] == 1){
             
                throw new Exception(
                    "Mancanza parametri annuali o errore dati v_check_partita"
                );
            }
          

            // Elaborabile      
            
            $pivot->Insert($partita['Partita_ID'],$last_procedure_id,$a_getPositionStatus['position_status'],$a_getPositionStatus['flag_elaboration']); 
            
        }

       
    } 
    catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
        $cls_help->alert("ERRORE!!!!!!!!");
            die;
        return;
    }

    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

    $tipo_ = "";
    if($tipo == 'Banca')
        $tipo_ = "su banche";
    else   
        $tipo_ = "su enti previdenziali";

    $storico->insRow('E', "Elaborazione '".$description."': Procedure stragiudiziali ".$tipo_." ".$ente['Denominazione']."[".$c."]. Stato 'Pre-Elaborazione'");

    $cls_db->End_Transaction();

    flush();	ob_flush();
    echo "<script>endBar('".$c."','".$a."',".$last_procedure_id.",'".$tipo."','".$tipo_partita."');
    </script>";
    flush();	ob_flush();		flush();	ob_flush();
    /** PHP PROGRESS BAR    END  */

   
} 
else {
    flush();	ob_flush();
    echo "<script>noResultsBar();</script>";
    flush();	ob_flush();		flush();	ob_flush();
    /** PHP PROGRESS BAR    END  */
}

?>


