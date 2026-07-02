<?php
$a_tipo_ufficiale = array(["Tipo"=> "diretta"], ["Tipo"=> "riscossione"], ["Tipo"=> "giudiziario"], ["Tipo"=> "procedimento"]);

$a_selection = array("value" => "Tipo", "firstOpt" => 0, "selected" => "riscossione", "text" => array("[Tipo]"));
$optTipoUfficialePEC = $cls_html->getOptions($a_tipo_ufficiale, $a_selection);
$a_selection = array("value" => "Tipo", "firstOpt" => 0, "selected" => "diretta", "text" => array("[Tipo]"));
$optTipoUfficialeRaccomandata = $cls_html->getOptions($a_tipo_ufficiale, $a_selection);

$queryPrintType = "SELECT Id, Description FROM print_type";
$resultPrintType = $cls_db->getResults($cls_db->ExecuteQuery($queryPrintType));

$optPrintType = "";
foreach($resultPrintType as $key => $value){
    $optPrintType .= "<option value='".$value["Id"]."'>".$value["Description"]."</option>";
}
?>

<div class="row" style="margin-top: 1%;">
    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>
        <div class="col col-lg-5 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
            <div class="form-group">
                <div class="col-lg-4">
                <select class="form-select validateCustom vld_Custom_r"  id="DefaultTipoUfficialePEC" name="DefaultTipoUfficialePEC" style="height:35px; width:100%;">
                    <?= $optTipoUfficialePEC; ?>
                </select>
                </div>
                <div class="col-lg-4">
                <select class="form-select validateCustom vld_Custom_r"  id="DefaultTipoStampaPEC" name="DefaultTipoStampaPEC" style="height:35px; width:100%;">
                    <?= $optPrintType; ?>
                </select>
                </div>
            </div>
        </div>
        
        <div class="col col-lg-5">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Raccomandata</label>
            <div class="form-group">
                <div class="col-lg-4">
                    <select id="DefaultTipoUfficialeRaccomandata" name="DefaultTipoUfficialeRaccomandata"  style="height:35px; width:100%;">
                        <?= $optTipoUfficialeRaccomandata; ?>
                    </select>
                </div>
                <div class="col-lg-4">
                <select class="form-select validateCustom vld_Custom_r"  id="DefaultTipoStampaRaccomandata" name="DefaultTipoStampaRaccomandata" style="height:35px; width:100%;">
                    <?= $optPrintType; ?>
                </select>
                </div>
            </div>
        </div>
    </div>