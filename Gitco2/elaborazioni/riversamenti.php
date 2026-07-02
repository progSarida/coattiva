<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

$query = "SELECT importo, data_versamento, canale FROM riversamenti WHERE CC = '".$c."' AND year = '".$a."' ORDER BY data_versamento";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

?>

<form action="riversamenti_salva.php" method="post">

    <input type="hidden" name="c" value="<?=$c?>">
    <input type="hidden" name="a" value="<?=$a?>">
    <input type="hidden" id="invia_submit" name="invia_submit" value="">

    <table class="table table-sm table-dark table-hover" style="width: 98%;margin-left: 1%;">
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 65%;">
            <col style="width: 5%;">
        </colgroup>
        <thead style="background-color: #4A4A4A;color: white;">
        <tr>
            <th scope="col">Data versamento</th>
            <th scope="col">Importo €</th>
            <th scope="col">Canale pagamento</th>
            <th scope="col" style="text-align: center;"><i onclick="addRow();" class="fa fa-plus add" aria-hidden="true"></i></th>
        </tr>
        </thead>
        <tbody id="appendRow">
        <?php if(count($result) > 0){
            $count = count($result);
            for($i=0; $i < $count; $i++){

                ?>
                <tr class="info import_row" id="row_<?=$i?>" >
                    <th>
                        <div class="form-group">
                            <input style="width: 100%;" type="date" class="form-control validateCustom vld_CheckPrintDate_4" required id=date_<?=$i?> name=date[] value="<?= $result[$i]["data_versamento"]; ?>" >
                        </div>
                    </th>
                    <td>
                        <div class="form-group">
                            <input style="width: 100%;" onblur="this.value = this.value.replaceAll('.',',')" type="text" class="form-control validateCustom vld_Custom_d" id=import_<?=$i?> name=import[] required value="<?= number_format($result[$i]["importo"],2,",",""); ?>" >
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <input style="width: 100%;" type="text" class="form-control" id=canal_<?=$i?> name=canal[] value="<?= $result[$i]["canale"]; ?>" >
                        </div>
                    </td>
                    <td style="text-align: center;"><i onclick="removeRow('<?=$i?>');" class="fa fa-minus remove" aria-hidden="true"></i></td>
                </tr>
                <?php
            }
        } else { ?>
            <tr class="info import_row" id="row_0" >
                <th>
                    <div class="form-group">
                        <input style="width: 100%;" type="date" class="form-control validateCustom vld_CheckPrintDate_4" required id=date_0 name=date[] value="" >
                    </div>
                </th>
                <td>
                    <div class="form-group">
                        <input style="width: 100%;" type="text" onblur="this.value = this.value.replaceAll('.',',')" class="form-control validateCustom vld_Custom_d" id=import_0 name=import[] required value="" >
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <input style="width: 100%;" type="text" class="form-control" id=canal_0 name=canal[] value="" >
                    </div>
                </td>
                <td style="text-align: center;"><i onclick="removeRow('0');" class="fa fa-minus remove" aria-hidden="true"></i></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <button type="submit" id="btnSub" style="display: none;"></button>

</form>

<?php include(INC."/footer.php"); ?>


<script>

    switchMenuImg("F3");
    F3_button = function()
    {
        control_salva = submit_buttons('Salva');
        //alert($("#invia_submit").val());
        if(control_salva && validateForm())
            $("#btnSub").trigger("click");
    }

    switchMenuImg("F4");
    F4_button = function()
    {
        control_salva = submit_buttons('Delete');
        //alert($("#invia_submit").val());
        if(control_salva)
            $("#btnSub").trigger("click");
    }

    switchMenuImg("F5");
    F5_button = function()
    {
        top.location.href = "riversamenti.php?c=<?=$c?>&a=<?=$a?>";
    }

    function removeRow(id){
        if($('.import_row').length > 1)
            $("#row_"+id).remove();
        else{
            $("#date_"+id).val("");
            $("#import_"+id).val("");
            $("#canal_"+id).val("");
        }
    }

    function addRow(){
        //appendRow

        var i = 0;
        while($("#row_"+i).length > 0)
        {
            i++;
        }

        var str = `
        <tr class="info import_row" id="row_`+i+`" >
            <th>
                <div class="form-group">
                    <input style="width: 100%;" type="date" class="form-control validateCustom vld_CheckPrintDate_4" required id=date_`+i+` name=date[] value="" >
                </div>
            </th>
            <td>
                <div class="form-group">
                    <input style="width: 100%;" onblur="this.value = this.value.replaceAll('.',',')" type="text" class="form-control validateCustom vld_Custom_d" id=import_`+i+` required name=import[] value="" >
                </div>
            </td>
            <td>
                <div class="form-group">
                    <input style="width: 100%;" type="text" class="form-control" id=canal_`+i+` name=canal[] value="" >
                </div>
            </td>
            <td style="text-align: center;"><i onclick="removeRow('`+i+`');" class="fa fa-minus remove" aria-hidden="true"></i></td>
        </tr>`;

        $("#appendRow").append(str);
    }
</script>

<style>
    .add {
        color: green;
        cursor: pointer;
    }
    .add:hover{
        color: darkgreen;
    }

    .remove {
        color: red;
        cursor: pointer;
    }
    .remove:hover {
        color: darkred;
    }
</style>