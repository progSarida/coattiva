<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_Utils.php";

$utils = new cls_Utils();

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

$query = "SELECT FL.*, LT.Name AS ListTypeName, FT.Name AS FileTypeName 
            FROM file_list AS FL 
            LEFT JOIN list_types AS LT ON LT.Id = FL.ListType 
            LEFT JOIN file_types AS FT ON FT.Id = FL.FileType
            WHERE 1=1 ";

if($cls_help->getVar("listFile")!=null)
    $query .= " AND FL.ListType = ".$cls_help->getVar("listFile")." ";
if($cls_help->getVar("typeFile")!=null)
    $query .= " AND FL.FileType = ".$cls_help->getVar("typeFile")." ";


$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

for($i=0;$i<count($result);$i++){
    $img="";
    if($result[$i]["FileType"] == 1) $img = IMG."/icon_excel.png";
    else if($result[$i]["FileType"] == 2) $img = IMG."/icon_pdf.png";

    $webPathFile = SUPER_WEB_ROOT."/".$utils->mostra_file_path($result[$i]["Path"]);
    $file = "<img src='".$img."' width=25 style='cursor: pointer;' title='".$result[$i]["Name"]."' onclick='window.open(\"".$webPathFile."\",\"File\",\"height=600,width=500,top=150,left=150\");'>";
    $result[$i]["File"] = $file;
}

$query = "SELECT * FROM list_types";
$resultLT = $cls_db->getResults($cls_db->ExecuteQuery($query));

$optionList = "<option value=''></option>";
for($i=0; $i<count($resultLT); $i++) {
    $selectedOption = "";
    if($resultLT[$i]["Id"] == $cls_help->getVar("listFile"))
        $selectedOption = "selected";
    $optionList .= "<option value='" . $resultLT[$i]["Id"] . "' ".$selectedOption.">" . $resultLT[$i]["Name"] . "</option>";
}

$query = "SELECT * FROM file_types";
$resultFT = $cls_db->getResults($cls_db->ExecuteQuery($query));

$optionFile = "<option value=''></option>";
for($i=0; $i<count($resultFT); $i++) {
    $selectedOption = "";
    if($resultFT[$i]["Id"] == $cls_help->getVar("typeFile"))
        $selectedOption = "selected";
    $optionFile .= "<option value='" . $resultFT[$i]["Id"] . "' ".$selectedOption.">" . $resultFT[$i]["Name"] . "</option>";
}

?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href='visualizza_file_sgravi_annull.php?&c=<?= $c; ?>&a=<?= $a; ?>';
    }
</script>

<form method=post action="visualizza_file_sgravi_annull.php">
    <input type="hidden" name="c" value="<?= $c; ?>">
    <input type="hidden" name="a" value="<?= $a; ?>">
    <input type="hidden" name="listFile" value="<?= $cls_help->getVar("listFile"); ?>">
    <input type="hidden" name="typeFile" value="<?= $cls_help->getVar("typeFile"); ?>">

    <div class="row" style="margin-top: 2%;">

        <div class="col col-lg-4 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo elenco</label>
                <div class="col-lg-8">
                    <select name="listFile" id="listFile" class="form-control" style="width: 150px;">
                        <?= $optionList; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo file</label>
                <div class="col-lg-8">
                    <select name="typeFile" id="typeFile" class="form-control" style="width: 150px;">
                        <?= $optionFile; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <button type="submit" class="btn btn-primary" name="filtro" >Filtra</button>
        </div>
    </div>
</form>



<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>

<script type="text/javascript">

    $(document).ready(function(){
        var toprint = [{originalName: "CC", replacedName: "CC"},{originalName: "Date", replacedName: "Data"},{originalName: "ListTypeName", replacedName: "Tipo"},{originalName: "Name", replacedName: "Nome file"},{originalName: "Description", replacedName: "Descrizione"},{originalName: "File", replacedName: "File"}];
        var widthCell = ["8%","10%","15%","25%","37%","5%"];
        var fontsize = "10px";
        var test = new TableGenerator(<?= json_encode($result)?>,toprint,widthCell,fontsize);


    });


</script>