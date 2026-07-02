<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_DateTimeInLine.php";



$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

$cls_db = new cls_db();

$query = "SELECT CC, Denominazione FROM enti_gestiti ORDER BY Denominazione";
$city = $cls_db->getResults($cls_db->ExecuteQuery($query));

$count = count($city);
$dropCity = "<option value='*****'>TUTTI</option>";
for($i=0; $i < $count; $i++){
    $dropCity .= "<option value='".$city[$i]["CC"]."'>".$city[$i]["Denominazione"]."</option>";
}

$query = "SELECT Id,Name FROM lockup_types";
$interestType = $cls_db->getResults($cls_db->ExecuteQuery($query));

$count = count($interestType);
$dropInterestType = "";
for($i=0; $i < $count; $i++){
    $dropInterestType .= "<option value='".$interestType[$i]["Id"]."'>".$interestType[$i]["Name"]."</option>";
}


$query = "SELECT * FROM lockup_periods";
$allInterestBlocks = json_encode($cls_db->getResults($cls_db->ExecuteQuery($query)));

?>

<script>

    function saveData(){
        if(validateForm())
            $("#form_block_interests").submit();
    }

    $(document).ready(function(){
        //var arrayAllInterest = <?php echo $allInterestBlocks; ?>;
        //console.log(arrayAllInterest);
    });

</script>

<div class="row" style="margin-top: 3%;">
    <div class="col-lg-1 col-lg-offset-10"><a href="interessi.php?c=<?= $c; ?>&a=<?= $a; ?>" title="Indietro" style="color: blue;cursor: pointer;" class="fa fa-arrow-circle-left fa-2x" aria-hidden="true"></a></div>
</div>

<div class="row justify-content-md-center" style="margin-bottom: 4%;margin-top: 3%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font22 under_decor">Crea blocco interessi</span>
    </div>
</div>

<form action="save_block_interests.php" method="post" name="form_block_interests" id="form_block_interests">
    <input type=hidden name="c" value="<?php echo $c; ?>" />
    <input type=hidden name="a" value="<?php echo $a; ?>" />

    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-5 control-label resize" style="text-align: left;">Seleziona ente</label>
                <div class="col-lg-7">
                    <select id=cc name=cc class="resize form-control validateCustom vld_Custom_r" style="width: 100%;">
                        <?=$dropCity;?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-5 control-label resize" style="text-align: left;">Tipo di blocco</label>
                <div class="col-lg-7">
                    <select id=blockType name=blockType class="resize form-control validateCustom vld_Custom_r" style="width: 100%;">
                        <?=$dropInterestType;?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-5 control-label resize" style="text-align: left;">Data Inizio</label>
                <div class="col-lg-7">
                    <input class="text_left form-control resize picker validateCustom vld_Custom_r vld_CheckInterestDate"  id=start_date name=start_date value="<?php echo ""; ?>">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-5 control-label resize" style="text-align: left;">Data fine</label>
                <div class="col-lg-7">
                    <input class="text_left form-control resize picker validateCustom vld_CheckInterestDate"  id=end_date name=end_date value="<?php echo ""; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Nome</label>
                <div class="col-lg-10">
                    <input class="text_left form-control resize validateCustom vld_Custom_r"  id=name name=name value="<?php echo ""; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 1%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Descrizione</label>
                <div class="col-lg-10">
                    <textarea style="max-width: 100%;" class="form-control resize validateCustom vld_Custom_r" name="description" id="description" ><?= isset($partita["Note_Blocco"])?$partita["Note_Blocco"]:""; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 3%;">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <button style="width: 100%;" type="button" id="btnSub" class="btn btn-primary" onclick="saveData();">Salva</button>
            </div>
        </div>
    </div>
</form>

<?php include(INC."/footer.php");?>


