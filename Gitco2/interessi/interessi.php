<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/BuildFilter.php";


$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");


$title["value"] = "";

$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();
$cls_html = new cls_html();
$cls_utils = new cls_Utils();
/*$queryCities = "SELECT EG.* FROM enti_gestiti EG LEFT JOIN anni_gestiti A ON A.CC_Anno=EG.CC WHERE A.ID is not null";

$selectCity = null;

if($_SESSION['CC_User']!="****" && $_SESSION['CC_User']!="***+"){
    $c = $_SESSION['CC_User'];
    $queryCities.= " AND CC='".$_SESSION['CC_User']."' ";
}


if($_SESSION['aut_tipo']>2 && $_SESSION['aut_tipo']<20){
    $queryCities.= " AND Autorizzazione=".$_SESSION['aut_tipo']." ";
}
$queryCities.= " GROUP BY EG.ID";

$a_enti = $cls_db->getResults( $cls_db->SelectQuery($queryCities) );
//print_r($a_enti);
$a_selection = array("value"=>"CC","firstOpt"=>1,"selected"=>$selectCity,"text"=>array("[Denominazione]"," - ","[CC]","  ","[Descrizione]"));
$optionsCities = $cls_html->getOptions($a_enti,$a_selection);*/

$query = "SELECT LP.*, LT.Name AS NameType, IF(LP.CC = '*****','TUTTE',EG.Denominazione) AS Denominazione FROM lockup_periods AS LP LEFT JOIN lockup_types AS LT ON LT.Id = LP.Lockup_Type_Id LEFT JOIN enti_gestiti AS EG ON EG.CC = LP.CC";
$allInterestBlocks = $cls_db->getResults($cls_db->ExecuteQuery($query));
$count = count($allInterestBlocks);
for($i=0; $i < $count; $i++){
    $allInterestBlocks[$i]["Links"] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i title="Modifica blocco interessi" onclick="editInterest('.$allInterestBlocks[$i]["Id"].');" style="font-size: 20px !important;color: #0016b0;cursor: pointer;" class="fa fa-address-book" aria-hidden="true"></i>
                                        &nbsp;&nbsp;
                                        <i title="Elimina blocco interessi" onclick="deleteInterest('.$allInterestBlocks[$i]["Id"].');" style="font-size: 20px !important;color: #CC1616;cursor: pointer;" class="fa fa-trash fa-2xl" aria-hidden="true"></i>';

    $allInterestBlocks[$i]["Start_Date"] = $cls_date->Get_DateNewFormat($allInterestBlocks[$i]["Start_Date"],"DB");
    $allInterestBlocks[$i]["End_Date"] = $cls_date->Get_DateNewFormat($allInterestBlocks[$i]["End_Date"],"DB");
}

//var_dump($allInterestBlocks);
?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="interessi.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    /*switchMenuImg("F10");
    F10_button = function(){
        if($("#query_type").val() == "")
        {
            alert("Selezionare il tipo di query");
            return false;
        }
        $("#print").val("yes");
        $("#form_stampe_guidate").submit();
    }*/

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Blocco_Interessi.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Blocco interessi</b>");
        $("#helpModal").modal('show');
    }


    $( document ).ready(function() {
        var toprint = [
            {originalName: "CC", replacedName: "CC"},
            {originalName: "Denominazione", replacedName: "Città"},
            {originalName: "NameType", replacedName: "Tipo blocco"},
            {originalName: "Name", replacedName: "Nome blocco"},
            {originalName: "Start_Date", replacedName: "Data inizio"},
            {originalName: "End_Date", replacedName: "Data fine"},
            {originalName: "Links", replacedName: " "}
        ];
        var widthCell = ["5%","15%","20%","25%","10%","12.5%","12.5%"];
        var fontsize = "10px";
        var test = new TableGenerator(<?= json_encode($allInterestBlocks)?>,toprint,widthCell,fontsize);
    });

    function editInterest(id){
        location.href = "<?= WEB_ROOT; ?>/interessi/edit.php?c=<?= $c; ?>&a=<?= $a; ?>&id="+id;
    }
    function deleteInterest(id){
        if(confirm("Si è certi di voler eliminare questo blocco?"))
            location.href = "<?= WEB_ROOT; ?>/interessi/delete.php?c=<?= $c; ?>&a=<?= $a; ?>&id="+id;
    }
    function addInterest(){
        location.href = "<?= WEB_ROOT; ?>/interessi/create.php?c=<?= $c; ?>&a=<?= $a; ?>";
    }

</script>

<form action="" method="post" name="form_stampe_guidate" id="form_stampe_guidate">
    <input type=hidden name="c" value="<?php echo $c; ?>" />
    <input type=hidden name="a" value="<?php echo $a; ?>" />

    <div class="row">
        <div class="col-lg-offset-10 col-lg-1">
            <i title="Aggiungi blocco interessi" style="cursor: pointer;color: green;" class="fa fa-plus-circle fa-2x" aria-hidden="true" onclick="addInterest();"></i>
        </div>
    </div>
    <div id="appendTable"></div>

</form>

<?php include(INC."/footer.php");?>

