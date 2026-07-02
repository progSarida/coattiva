<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

ini_set('display_errors',1);

include_once(CLS."/cls_290.php");
include_once(CLS."/cls_registry.php");
include_once(INC."/header.php");
include_once(INC."/menu.php");

$username = $_SESSION['username'];


$Import_Id = $cls_help->getVar('Import_Id');

$query = "SELECT * FROM imports WHERE Id=".$Import_Id;
$a_import = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
$query = "SELECT * FROM import_status";
$a_importStatus = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");
$query = "SELECT * FROM import_types";
$a_importTypes = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");

$a_ente = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$a_import['CC']."'") );

$imp_date = "";
$imp_operator = "";
if(!is_null($a_import['Import_Datetime'])){
    $imp_date = date('d/m/Y H:i',strtotime($a_import['Import_Datetime']));
    if($a_import['Import_User_Id']>0)
        $imp_operator = $a_usersAdmin[$a_import['Import_User_Id']]['User'];
}
$a_params = array(
    "CC" => $a_ente['CC'],
    "290Code" => $a_ente['Codice_290'],
    "Ruolo_ID" => $a_import['Ruolo_ID']
);


$html = "";
$checkDataDecorrenza = 0;
$cls_290 = new cls_290($a_params);
$cls_290->setClass("cls_db", $cls_db);
$cls_290->setClass("cls_registry", new cls_registry());
if($a_import['Import_Type_Id']==1){

    $cls_290->getFile(DUENOVANTA."/toImport/".$a_import['Filename']);
    $cls_290->read290();
    $cls_290->check290();

    $checkDataDecorrenza = $cls_290->checkDataDecorrenza;
}
else if($a_import['Import_Type_Id']==2){
    $cls_290->readXlsxModel(DUENOVANTA."/toImport/".$a_import['Filename']);
}
switch($a_import['Import_Status_Id']){
    case 1:
        if($_SESSION['username']=="mirkop" || $_SESSION['username']=="robertop"){
            $html = "<input type='button' disabled id='import' class='btn btn-success' value='Importa'> ";
            $html.= '<input type="button" id="deleteRecord" class="btn btn-danger" onClick="deleteFile()" value="Cancella">';
        }
        break;
}

?>

    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <span class="titolo font22 under_decor">Gestione Importazione Ruolo</span>
        </div>
    </div>

    <div class="row-fluid gitco-container">
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Ente/comune
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_ente['Denominazione'];?>
        </div>
        <div class="col-lg-2 RowLabel">
            Codice catastale
        </div>
        <div class="col-lg-1 RowInput">
            <?= $a_import['CC'];?>
        </div>
        <div class="col-lg-1 RowLabel">
            Codice 290
        </div>
        <div class="col-lg-1 RowInput">
            <?=$a_ente['Codice_290'];?>
        </div>
        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Denominazione
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_import['Name'];?>
        </div>
        <div class="col-lg-2 RowLabel">
            Status
        </div>
        <div class="col-lg-3 RowInput">
            <?= $a_importStatus[$a_import['Import_Status_Id']]['Name'];?>
        </div>
        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Tipo
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_importTypes[$a_import['Import_Type_Id']]['Name'];?>
        </div>
        <div class="col-lg-2 RowLabel">
            File registrato
        </div>
        <div class="col-lg-3 RowInput">
            <a title="<?=$a_import['Filename'];?>" href="<?=DUENOVANTA_WEB.'/toImport/'.$a_import['Filename']?>"><?=$a_import['Filename'];?></a>
        </div>
        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Data Upload
        </div>
        <div class="col-lg-3 RowInput">
            <?= date('d/m/Y H:i',strtotime($a_import['Upload_Datetime']));?>
        </div>
        <div class="col-lg-2 RowLabel">
            Operatore upload
        </div>
        <div class="col-lg-3 RowInput">
            <?=$a_usersAdmin[$a_import['Upload_User_Id']]['User'];?>
        </div>

        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-2 col-lg-offset-1 RowLabel">
            Data Importazione
        </div>
        <div class="col-lg-3 RowInput">
            <?= $imp_date; ?>
        </div>
        <div class="col-lg-2 RowLabel">
            Operatore importazione
        </div>
        <div class="col-lg-3 RowInput">
            <?=$imp_operator;?>
        </div>

        <div class="HSpace1 clean_row"></div>
        <div class="col-lg-offset-1 col-lg-2 RowLabel">
            Posizioni importate
        </div>
        <div class="col-lg-3 RowInput">
            <?= (int)$a_import['Imported_Positions']; ?>
            /
            <?php
            if($a_import['Total_Positions']>0)
                echo (int)$a_import['Total_Positions'];
            else
                echo (int)$cls_290->a_count["N2"];
            ?>
        </div>
        <div class="col-lg-2 RowLabel">
            Scarti
        </div>
        <div class="col-lg-3 RowInput">
            <?php
            if($a_import['Total_Positions']>0)
                echo (int)($a_import['Total_Positions']-$a_import['Imported_Positions']);
            else
                echo 0; ?>
            /
            <?php
            if($a_import['Total_Positions']>0)
                echo (int)$a_import['Total_Positions'];
            else
                echo (int)$cls_290->a_count["N2"];
            ?>
        </div>

        <div class="HSpace4 clean_row"></div>
        <div class="col-lg-offset-1 col-lg-10 RowInput RowInputBtnHeight5 text-center">
            <input type="button" id=backPage class="btn btn-gitco" value="Elenco File">
            <?php echo $html; ?>
        </div>

        <div class="HSpace4 clean_row"></div>
        <br>
        <div class="clean_row HSpace4"></div>
        <div class="col-lg-12 RowLabel RowLabelHeight4 text-center">
            CONTROLLI FILE
        </div>
        <div class="clean_row HSpace1"></div>
        <?php

            if($a_import['Import_Type_Id']==1){
                echo $cls_290->getHtmlFileChecks();
//                if($_SESSION['username']=="mirkop"){
//                    foreach($cls_290->a_290['N1'] as $n1=>$a_n1){
//                        foreach ($cls_290->a_290['N2'][$n1] as $n2=>$a_n2){
//                            var_dump($a_n2['Check']);
//                            echo "<br><br>";
//                        }
//
//                    }
//                }
            }

            else if($a_import['Import_Type_Id']==2){
                echo $cls_290->getHtmlModel();
            }
        ?>
    </div>


<!-- GV - 09/04/2022 - START -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<!-- GV - 09/04/2022 - END   -->
<script>
    var checkDataDecorrenza = <?= $checkDataDecorrenza; ?>;
    var posizioni = <?= $cls_290->a_count['N2']; ?>;
    var errori = <?= $cls_290->a_countCheck['errori']; ?>;
    var scarti = <?= $cls_290->a_countCheck['scarti']+$cls_290->a_countCheck['importato']; ?>;
    if(errori==0 && scarti<posizioni){
        $('#import').prop("disabled", false);
    }
    $('#backPage').click(
        function(){
            location.href = "<?=WEB_ROOT?>/290/upload_290.php?c=<?=$c?>&a=<?=$a?>";
        }
    );

    $('#import').click(
        function(){
            link = "<?=WEB_ROOT?>/290/import_290.php?c=<?=$c?>&a=<?=$a?>&Import_Id=<?=$Import_Id;?>";
            if(checkDataDecorrenza==1){
                if(confirm("Stai per importare i dati del tracciato 290.\nLa data di decorrenza interessi risulta assente e verrà sostituita con la data di fornitura del ruolo. Confermi l'importazione?"))
                    location.href = link;
                else
                    alert("No");
            }
            else
                location.href = link;
        }
    );

    /** GV - 08/04/2022 - START */
    function deleteFile(){
        swal({
				title: "Sei Sicuro?",
				text: "Una volta cancellato, non sarai più in grado di recuperare questo file!",
				icon: "warning",
				buttons: true,
				dangerMode: true,
				})
				.then((willDelete) => {
				if (willDelete) {
					ajaxDeleteFile();
				} 
			});
    }


    function ajaxDeleteFile(){
                            
        $.ajax({
                    url:"ajax/ajax_cancelFile.php",    
                    type: "POST",    
                    dataType: 'json',
                    data: 	{ 
                                'idImport': <?php echo $Import_Id;  ?>,
                                'path' : '<?php echo DUENOVANTA."/toImport/".$a_import['Filename'];  ?>',
                            },
                    success:function(data) {
                        
                        console.log(data);
                        console.log(data.message);
                        if(data.message === "OK"){
                            swal("Il file è stato cancellato correttamente!", {
                                    icon: "success",
                            });
                            $('#backPage').click();
                            
                        }
                        else{
                            console.log("SONO in KO");
                            
                            swal("Impossibile eliminare il File!", {
                                icon: "error",
                                });
                        }
                            
                    } ,
                    error:function(data){
                        console.log("SONO in ERROR");
                        swal("Impossibile eliminare il File!", {
                                icon: "error",
                                });
                    }
                });
        }
/** GV - 08/04/2022 - END */

</script>

<?php


include_once(INC."/footer.php");