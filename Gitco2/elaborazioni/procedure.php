<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_DateTimeInLine.php");
include_once CLS."/cls_file.php";
include_once CLS."/cls_html.php";



if (strtolower($_SESSION['username']) == "mirkop" || strtolower($_SESSION['username']) == "robertop" || strtolower($_SESSION['username']) == "fabrizio")
    $authFlag = 1;
else
    $authFlag = 0;// per tutti


$cls_date = new cls_DateTimeI("IT",false);
$cls_file = new cls_file();

$ProcedureTypeId = $cls_help->getVar("procedureTypeId");

$query = "SELECT * FROM procedure_types";
$a_procedureTypes = $cls_db->getResults($cls_db->ExecuteQuery($query));
$cls_html = new cls_html();
$a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $ProcedureTypeId, "text" => array("[Name]"));
$opt_procedureTypes = $cls_html->getOptions($a_procedureTypes,$a_selection);

$query = "SELECT P.*,P.Id as Id, PT.Name as Procedure_Type, A.User as Username FROM procedures P JOIN autenticazione A ON A.ID=P.User_Id JOIN procedure_types PT ON PT.Id=P.Procedure_Type_Id WHERE P.CC = '".$c."' ";
if(!empty($ProcedureTypeId))
    $query .= " AND Procedure_Type_ID = ".$ProcedureTypeId." ";
$query .= "ORDER BY Datetime DESC";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$count = count($result);

$object_table = array();
for($i=0; $i < $count; $i++){
    $object_table[$i]["Procedure_Type"] = $result[$i]["Procedure_Type"];
    $object_table[$i]["Procedure_Date"] = $cls_date->Get_DateNewFormat($result[$i]["Procedure_Date"],"DB");
    $object_table[$i]["Descrizione"] = $result[$i]["Description"];
    $object_table[$i]["Id"] = $result[$i]["Id"];
    $object_table[$i]["Username"] = $result[$i]["Username"];

    if(is_dir(PROCEDURE.$result[$i]['Id'])) {
        $a_files = $cls_file->getFilesFromPath(PROCEDURE . $result[$i]['Id'], PROCEDURE_WEB . $result[$i]['Id']);
        //var_dump($a_files[0]['fileWeb']);die;
        $htmlFile = "";
        foreach ($a_files as $a_file) {
            $htmlFile .= "<img src='" . $a_file['icon'] . "' width=25 style='cursor: pointer; margin-right:5px;' title='" . $a_file['fileName'] . "' onclick='showF(\"" . $a_file['fileWeb'] . "\");/*window.open(\"" . $a_file['fileWeb'] . "\",\"File\");*/'>";
        }
        $object_table[$i]["Files"] = $htmlFile;
    }
    else{
        $object_table[$i]["Files"] = "<img src='" . IMG . "/icon_unknown.png' width=25 style='cursor: pointer; margin-right:5px;' title='File non ancora caricato' >";
    }
    if ($result[$i]["Procedure_Type_Id"]==5) //delete al momento solo per Art17.
        $object_table[$i]["Elimina"] = "<button onclick='Elimina(\"".$result[$i]["Id"]."\",\"".$result[$i]["CC"]."\",\"".$result[$i]["Anno_Riferimento"]."\")'>Elimina</button>";
    else
        $object_table[$i]["Elimina"] = "";
    
}

?>
<!--//* LOADER -->
<div class="back_spiners" id="caricamento_spiners">
    <div class="d-flex align-items-center text-info" style="width: 220px;position: fixed;top:45%;left:45%;text-align: center;">
        <div style="display: inline;"><img style="width: 100%; border-radius: 100px;opacity: 0.5;" src="<?= GIFWEB ?>/loading_3.gif"></div>
        <div id="text_spiners" style="display: inline;font-size: 18px;width:100%;text-align: center;font-weight: bold;">Loading...</div>
    </div>
</div>
<style>
        .back_spiners {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.80);
            z-index: 10000;
        }
    </style>
<script>
    function showF(path){
        showFileOnModal(path,"File PDF",path.split('.').pop());
    }
    function startSpiners(){
        
        $("#caricamento_spiners").show();
    }

    function closeSpiner(){
        //alert("close");
        $("#caricamento_spiners").hide();
    }
</script>
<form method=post action="procedure.php">
    <input type="hidden" name="c" value="<?= $c; ?>">
    <input type="hidden" name="a" value="<?= $a; ?>">
    <input type="hidden" name="procedureTypeId" value="<?= $ProcedureTypeId; ?>">

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">PROCEDURE</span>
        </div>
    </div>

    <div class="row" style="margin-top: 2%;">

        <div class="col col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo</label>
                <div class="col-lg-8">
                    <select name="procedureTypeId" id="procedureTypeId" class="form-control" onchange="setHidden(this);">
                        <?= $opt_procedureTypes; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">

        </div>
        <div class="col col-lg-2">
            <button type="submit" class="btn btn-primary" name="filtro" >Filtra</button>
        </div>
    </div>
</form>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

<div class="row">
    <div class="col-lg-offset-1 col-lg-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="checkManual" onchange="showHideDiv(this,'divManual');">
            <label class="form-check-label" for="checkManual">
                Inserimento manuale
            </label>
        </div>
    </div>
</div>
<div style="display: none;" id="divManual">
    <form action="save_procedure_manualy.php" method="post" enctype="multipart/form-data">

        <input type="hidden" name="c" value="<?=$c?>">
        <input type="hidden" name="file_name" id="file_name" value="">
        <div class="row">
            <div class="col-lg-offset-1 col-lg-5">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo</label>
                    <div class="col-lg-8">
                        <select name="procedureTypeId" id="procedureTypeId" class="form-control">
                            <?= $opt_procedureTypes; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="sovr" name="sovrascrivi">
                    <label class="form-check-label" for="sovr">
                        Sovrascrivi
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-4 control-label">File</label>
                    <div class="col-lg-8">
                        <input type="file" class="form-control validateCustom vld_Custom_r" id="file_choice" name="file_choice" />
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label class="col-lg-4 control-label resize" style="text-align: left;">Anno Rif.</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control resize validateCustom vld_Custom_n" required id=anno name=anno value="" maxlength="4" >
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <button type="button" class="btn btn-primary" onclick="submitForm();">Carica</button>
                <button type="submit" style="display: none;" id="btnSub"></button>
            </div>
        </div>

    </form>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>

<script type="text/javascript">
    
    // Cattura nome file
    $('#file_choice').change(function(){
        var value = $(this).val();
        $('#file_name').val(value);
    })
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="procedure.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Report_Sgravi_Automatici.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Report Discarichi Automatici</b>");
        $("#helpModal").modal('show');
    }

    function submitForm(){
        if(validateForm())
            $("#btnSub").trigger("click");
    }

    function showHideDiv(el,id){
        //alert($("#"+el.id).is(":visible"));
        if($("#"+id).is(":visible")) {
            $("#divManual").hide();
          //  alert("chiudi");
        }
        else {
            //alert("apri");
            $("#divManual").show();
        }

    }

    $(document).ready(function(){
        var toprint = [
            {originalName: "Procedure_Type", replacedName: "Tipo procedura"},
            {originalName: "Procedure_Date", replacedName: "Data", type: "date"},
            {originalName: "Descrizione", replacedName: "Descrizione"},
            {originalName: "Username", replacedName: "Operatore"},
            {originalName: "Files", replacedName: "Files"}
            <?php if ($authFlag==1) {?>
            ,{originalName: "Elimina", replacedName: "Elimina"}
            <?php }?>
            // {originalName: "File_PDF", replacedName: "PDF"},
            // {originalName: "File_EXCEL", replacedName: "EXCEL"}
        ];
        <?php if ($authFlag==1) {?>
            var widthCell = ["20%","10%","40%","15%","8%","7%"];
        <?php } else {?>
            var widthCell = ["20%","10%","40%","15%","15%"];
        <?php }?>
        var fontsize = "10px";
        //var idTable = "jhabdscfjbcdas";
        var test = new TableGenerator(<?= json_encode($object_table)?>,toprint,widthCell,fontsize);
    });

    function Elimina(id,comune,anno_riferimento)
    {
        var  authFlag = <?= $authFlag ?>;
        if (authFlag==1) // controllo rindondante
        {
            swal({
                title: "SEI SICURO?",
                text: "Una volta eliminata la procedura non può più essere recuperata!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
            if (willDelete) {
                startSpiners();
                $.ajax({
                    type: "POST",
                    url: "<?= WEB_ROOT ?>/controlli/ajax/ajax_delete_procedure_art17.php",
                    data: { "proc_id" : id,"comune" : comune,"anno_riferimento":anno_riferimento},
                    cache: false,
                    success: function(response){        
                    var response = JSON.parse(response);
                    closeSpiner();    
                    if(response.esito == "OK")
                    {
                        swal({
                                title: "SUCCESS!",
                                text:  response.message,
                                icon: "success",
                                timer: 25000,
                                buttons: false
                            });
                            window.location.href ="<?= WEB_ROOT ?>/elaborazioni/procedure.php?&p=&c=<?= $c?>&a=<?= $a?>";
                    }
                    else{
                            
                        swal({
                                title: "ERROR!",
                                text:  response.message,
                                icon: "danger",
                                timer: 5000,
                                buttons: false
                            });
                        
                    }
        
                    },
                    error: function(error){
                        console.log(error);
                            closeSpiner();
                    }        
                });
            } else {
                swal("La tua elaborazione è salva!");
            }
             });
        }
            
    }


</script>
