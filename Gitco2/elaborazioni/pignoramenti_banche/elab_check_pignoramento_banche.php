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
include_once ELAB_PIGNORAMENTI_LAVORO_CLS . "/cls_DefaultTipoUfficiale.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

?>

<!-- JS SWEETALERT  START -->

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<!-- JS sweetalert    END -->
<!-- JS PROGRESS BAR  START -->

<script>
    
    function startBar(){
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function updateBar(valore){
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function noResultsBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(c,a,el){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Elaborazione terminata!");

        if(el !== null){
            swal({
                    title: 'ATTENZIONE',
                    text: "PROCESSO TERMINATO. STAI PER ESSERE REINDIRIZZATO ALLA PAGINA DEI RISULTATI OTTENUTI",
                    icon: 'success',
                    timer: 3000,
                    buttons: false
                })
                .then((result) => {
                    location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
                })
        }
        else{
                
            swal({
                    title: 'ATTENZIONE',
                    text: "PROCESSO TERMINATO. NON SONO STATI TROVATI DATI.",
                    icon: 'warning',
                    timer: 3000,
                    buttons: false
                }).then((result) => {
                
                    location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
                })
        }
    }
</script>
<!-- HTML PROGRESS BAR  START -->
<body class="sfondo_new_gitco">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="table_interna text_center" id="progressbar" style="height:55px;width:100%;"><div class="text_center" id="barlabel"></div></div>
        </div>
    </div>
    <br/><br/>
    <div class="row">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor" style="color:red;">Non chiudere la finestra prima del termine della procedura</span>
        </div> 
    </div>
</body>
<!-- HTML PROGRESS BAR    END -->  
<!-- JS PROGRESS BAR    END -->   
<?php
/** PHP PROGRESS BAR  START  */
flush();	ob_flush();
echo "<script>startBar();</script>";
flush();	ob_flush();		flush();	ob_flush();

/** PHP PROGRESS BAR    END  */


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$auth = $cls_help->getVar('auth');

$data_elab = $cls_help->toDbDate($cls_help->getVar('data_elab'));
$data_int = $cls_help->toDbDate($cls_help->getVar('data_int'));

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
$documentTypeId = $cls_help->getVar('DocumentTypeId'); //Tipo Pignoramento al momento non discriminiamo

$tipo_partita = $cls_help->getVar('tipo_partita');
$da_n_elenco = $cls_help->getVar('da_n_elenco');
$a_n_elenco = $cls_help->getVar('a_n_elenco');
$da_data = $cls_help->getVar('da_data');
$a_data = $cls_help->getVar('a_data');
$da_anno = $cls_help->getVar('da_anno');
$a_anno = $cls_help->getVar('ad_anno');
$description = $cls_help->getVar('description');
$ruolo = $cls_help->getVar('ruolo');
$flag_massivo = $cls_help->getVar("massivo_banche");

$creation_username = $_SESSION['username'];

$documentTypeId = trim($documentTypeId);
// $documentTypeId = 42; //Aggiunto Pignoramento "generico" a Document_Type

$cod_catastale = trim($cod_catastale);
$tipo_partita = trim($tipo_partita);
$data_elab =    trim($data_elab);
$data_calc_int = trim($data_int);



//if (!((!empty($documentTypeId) && is_numeric($documentTypeId) && !empty($cod_catastale) && !empty($description) && !empty($data_elab) && !empty($data_calc_int))
if (!((!empty($cod_catastale) && !empty($description) && !empty($data_elab) && !empty($data_calc_int))
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

if(is_null($a_parAnnuali))
{
?>
    <script>
        swal({
            title: 'ERRORE',
            text: "MANCANZA PARAMETRI ANNUALI ",
            icon: 'danger',
            timer: 5000,
            buttons: false
        }).then((result) => {
            location.href ="<?= ELAB_PIGNORAMENTI_BANCA_WEB ?>/mgmt_pignoramenti_banche.php?c="+c+"&a="+a+"&el="+el;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}

// Query lockup_periods

$query_loc_per = "SELECT * FROM lockup_periods";
$a_lockupPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query_loc_per));

$queryDocTypes = "SELECT Id, ExpireDays FROM document_type WHERE ExpireDays is not null";
$a_docExpireDays = $cls_db->getResults($cls_db->ExecuteQuery($queryDocTypes),"array","Id");

// Query v_check_partite

$query_partita = " SELECT * FROM v_check_pignoramenti WHERE Elaboration_Id is null AND Genere_Utente!='D'";

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
    $query_partita .= " AND Data_Notifica <= '" . $da_data . "'";
if (!empty(trim($a_data)))
    $query_partita .= " AND Data_Notifica <= '" . $a_data . "'";

  
  

$results = $cls_db->ExecuteQuery($query_partita);
$partite = $cls_db->getResults($results); 


if (count($partite) > 0) {

    $da_partita = (!empty(trim($da_n_elenco))) ? $da_n_elenco : 0;
    $a_partita = (!empty(trim($a_n_elenco))) ? $a_n_elenco :  0;
    

    /** INIZIO TRANSAZIONE **/
    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();
    try {
      

        $a_dbParams_pre_elab = array(
            'table' => 'elaborations',
            'fields'=> array(
                array(  'name' => 'Elaboration_Status_Id',      'type' => 'int', 'value' => 1),
                array(  'name' => 'Description',                'type' => 'string', 'value' => $description),
                array(  'name' => 'CC',                 		'type' => 'string', 'value' => $cod_catastale),
                array(  'name' => 'From_Year',                  'type' => 'int', 'value' => $da_anno),
                array(  'name' => 'To_Year',              		'type' => 'int', 'value' => $a_anno),
                array(  'name' => 'From_Partita',               'type' => 'int', 'value' => $da_partita),
                array(  'name' => 'To_Partita',          		'type' => 'int', 'value' => $a_partita),
                array(  'name' => 'Document_Type_Id',     		'type' => 'int', 'value' => $documentTypeId),
                array(  'name' => 'Creation_Username',  		'type' => 'string', 'value' => $creation_username),
                array(  'name' => 'Creation_Date',              'type' => 'date', 'value' => $current_day),
                array(  'name' => 'Data_Elaborazione',          'type' => 'date', 'value' => $data_elab),
                array(  'name' => 'Data_Calcolo_Interessi',     'type' => 'date', 'value' => $data_calc_int),
                array(  'name' => 'Flag_Pigno_Banca_Massivo',   'type' => 'string', 'value' => $flag_massivo)
                
            )
        );

        $last_el_id = $cls_db->DbInsert($a_dbParams_pre_elab);
        //Inserisco il default dentro elaborations
        $updateQuery = DefaultTipoUfficiale::UpdateQuery($_POST,$last_el_id);
        $cls_db->ExecuteQuery($updateQuery);
        
        $a_params = array(
            'Parametri_Annuali' => $a_parAnnuali,
            'Lockup_Periods' => $a_lockupPeriods,
            'Doc_Expire_Days' => $a_docExpireDays,
            'Elaboration_DocumentTypeId' => $documentTypeId, 
            'Data_Elaborazione' => $data_elab,            
            'Data_Calcolo_Interessi' => $data_calc_int 
        );
       
        $cls_elaboration = new cls_elaborationPignoramenti($a_params);
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

            $query_up_par = "UPDATE partita_tributi SET Elaboration_Id = " . $last_el_id;
            $query_up_par.= ", Position_Status_Id = ".$a_getPositionStatus['position_status'].", flag_elaboration = ".$a_getPositionStatus['flag_elaboration'];
            if($a_getPositionStatus['position_status']==8)
                $query_up_par.= ", Is_Expired=1";
            else
                $query_up_par.= ", Is_Expired=0";
            $query_up_par.= " WHERE ID = " . $partita['Partita_ID'];
            
            mysqli_query($cls_db->conn, $query_up_par);
        }

       
    } 
    catch (mysqli_sql_exception $e) {
        $cls_db->Rollback();
        $log->error("Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage());
        $cls_help->alert("ERRORE!!!!!!!!");
        /*flush();	ob_flush();
        echo "<script>endBar('".$c."','".$a."',".$last_el_id.",".$documentTypeId.");
        </script>";
        flush();	ob_flush();		flush();	ob_flush();*/
            die;
        return;
    }

    $ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$cod_catastale."'") );
    $nome_ente = $ente['Denominazione'];	

    $storico->insRow('E', "Elaborazione '".$description."': Pignoramenti presso banca ".$nome_ente."[".$cod_catastale."]. Stato 'Pre-Elaborato'");

    $cls_db->End_Transaction();

    flush();	ob_flush();
    echo "<script>endBar('".$c."','".$a."',".$last_el_id.");
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


