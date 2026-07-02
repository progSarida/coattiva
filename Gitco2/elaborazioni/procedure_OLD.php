<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_DateTimeInLine.php");
include_once CLS."/cls_file.php";
include_once CLS."/cls_html.php";

$cls_date = new cls_DateTimeI("IT",false);
$cls_file = new cls_file();

$ProcedureTypeId = $cls_help->getVar("procedureTypeId");

$query = "SELECT * FROM procedure_types";
$a_procedureTypes = $cls_db->getResults($cls_db->ExecuteQuery($query));
$cls_html = new cls_html();
$a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $ProcedureTypeId, "text" => array("[Name]"));
$opt_procedureTypes = $cls_html->getOptions($a_procedureTypes,$a_selection);

$query = "SELECT P.*, PT.Name as Procedure_Type, A.User as Username FROM procedures P JOIN autenticazione A ON A.ID=P.User_Id JOIN procedure_types PT ON PT.Id=P.Procedure_Type_Id WHERE P.CC = '".$c."' ";
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
    $object_table[$i]["Username"] = $result[$i]["Username"];

    $a_files = $cls_file->getFilesFromPath(PROCEDURE.$result[$i]['Id'],PROCEDURE_WEB.$result[$i]['Id']);
    $htmlFile = "";
    foreach ($a_files as $a_file){
        $htmlFile.= "<img src='".$a_file['icon']."' width=25 style='cursor: pointer; margin-right:5px;' title='".$a_file['fileName']."' onclick='window.open(\"".$a_file['fileWeb']."\",\"File\");'>";
    }
    $object_table[$i]["Files"] = $htmlFile;
}

?>
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
                    <select name="procedureTypeId" id="procedureTypeId" class="form-control">
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

<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>

<script type="text/javascript">

    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="visualizza_file_report_sgravi_annull.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Report_Sgravi_Automatici.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Report Sgravi Automatici</b>");
        $("#helpModal").modal('show');
    }

    $(document).ready(function(){
        var toprint = [
            {originalName: "Procedure_Type", replacedName: "Tipo procedura"},
            {originalName: "Procedure_Date", replacedName: "Data"},
            {originalName: "Descrizione", replacedName: "Descrizione"},
            {originalName: "Username", replacedName: "Operatore"},
            {originalName: "Files", replacedName: "Files"}
            // {originalName: "File_PDF", replacedName: "PDF"},
            // {originalName: "File_EXCEL", replacedName: "EXCEL"}
        ];
        var widthCell = ["15%","15%","40%","15%","15%"];
        var fontsize = "10px";
        var test = new TableGenerator(<?= json_encode($object_table)?>,toprint,widthCell,fontsize);
    });


</script>
