<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_DateTimeInLine.php");

$cls_date = new cls_DateTimeI("IT",false);

$query = "SELECT * FROM report_elab_sgravi WHERE CC = '".$c."'";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$count = count($result);

$object_table = array();
for($i=0; $i < $count; $i++){
    $object_table[$i]["Data_Elaborazione"] = $cls_date->Get_DateNewFormat($result[$i]["Data_Elaborazione"],"DB");
    $object_table[$i]["Descrizione"] = $result[$i]["Descrizione"];

    //$pathPDFFinal = $cls_Utils->crea_dir(ARCHIVIO."/pdf_sgravi/".$c);
    //$pathEXCELLFinal = $cls_Utils->crea_dir(ARCHIVIO."/pdf_sgravi/".$c);

    $webPathPHPFile = SUPER_WEB_ROOT."/archivio/pdf_sgravi/".$c."/".$result[$i]["File_PDF"];
    $file = "<img src='".IMMAGINIWEB."/icon_pdf.png' width=25 style='cursor: pointer;' title='report PDF' onclick='window.open(\"".$webPathPHPFile."\",\"File\");'>";

    $object_table[$i]["File_PDF"] = $file;

    $webPathEXCELFile = SUPER_WEB_ROOT."/archivio/pdf_sgravi/".$c."/".$result[$i]["File_EXCEL"];
    $file = "<img src='".IMMAGINIWEB."/icon_excel.png' width=25 style='cursor: pointer;' title='report EXCEL' onclick='window.open(\"".$webPathEXCELFile."\",\"File\",\"height=600,width=500,top=150,left=150\");'>";

    $object_table[$i]["File_EXCEL"] = $file;
}

?>


<div id="appendTable"></div>

<?php include(INC."/footer.php"); ?>

<script type="text/javascript">

    $(document).ready(function(){
        var toprint = [{originalName: "Data_Elaborazione", replacedName: "Data di elaborazione"},{originalName: "Descrizione", replacedName: "Descrizione"},{originalName: "File_PDF", replacedName: "PDF"},{originalName: "File_EXCEL", replacedName: "EXCEL"}];
        var widthCell = ["15%","75%","5%","5%"];
        var fontsize = "10px";
        var test = new TableGenerator(<?= json_encode($object_table)?>,toprint,widthCell,fontsize);
    });


</script>
