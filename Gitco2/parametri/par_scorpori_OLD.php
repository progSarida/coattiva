<?php

include("../_path.php");
include(ROOT."/_parameter.php");

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include(INC."/header.php");
include(INC."/menu.php");

include CLS . "/cls_split_payment.php";

$cls_split = new cls_split_payment();
$a_types = $cls_db->getResults( $cls_db->SelectQuery( $cls_split->getTypesQuery() ) );
$a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersQuery($c) ) );
$options = $cls_html->optionsFromArray($a_types,"id",null,"category");

for($i=1;$i<=14;$i++){

    $a_categories[$i] = unserialize($a_params['split'.$i.'_categories']);
    $a_type[$i] = "".$a_params['split'.$i.'_type']."";
}

?>

<script>
    var a_categories = <?php echo json_encode($a_categories); ?>;
    var a_type = <?php echo json_encode($a_type); ?>;

    // console.log( a_categories );
    // console.log( a_type );

function processJsonForm(a_backForm) {
    // 'data' is the json object returned from the server
    if (a_backForm['response'] === true) {
        alert(a_backForm['message']);
        F5_button();
    }
    else{
        alert('Errore nel salvataggio!');
    }
}

//F3
switchMenuImg("F3");
F3_button = function(){

    control = submit_buttons('Salva');
    if(control)
        $("#form_scorpori").submit();
}

//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="par_scorpori.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

$(document).ready(function(){
    var optionsForm = {
        dataType:  'json',
        success:   processJsonForm
    };
    $('#form_scorpori').ajaxForm(optionsForm);
    
});

function updateInputs(){

    for(var i=1;i<=Object.keys(a_type).length;i++){
        if(a_type[i]>0){
            $('#split'+i+'_type').val(a_type[i]);
        }
        for(var y=1;y<=Object.keys(a_categories[i]).length;y++){
            if(a_categories[i][y]>0){
                $('#split'+i+'_category'+y).val(a_categories[i][y]);
            }
        }

    }
}



</script>



<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><span class="titolo font16 under_decor">Parametri di scorporo</span></td>
	</tr>
</table>

<form id=form_scorpori method=post action="par_scorpori_salva.php">

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
    <?php for($i=1;$i<=16;$i++){?>
	<tr>
        <td class="text_center width10"><span class="color_titolo"><b><?=$i?></b></span></td>
        <td class="text_left width30">
            <input class="width95" type="text" id="split<?=$i?>" name="split[<?=$i?>]" value="<?php echo $a_params['split'.$i]; ?>">
        </td>
		<td class="text_center width30">
            Priorita'
            &nbsp;<select name="split_type[<?=$i?>]" id="split<?=$i?>_type">
                <option value="0"></option>
                <option value="100">Percentuale</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
                <option>7</option>
                <option>8</option>
                <option>9</option>
                <option>10</option>
                <option>11</option>
                <option>12</option>
                <option>13</option>
                <option>14</option>
                <option>15</option>
                <option>16</option>
            </select>
        </td>
        <td class="text_center width30"></td>
	</tr>
    <tr>
        <td>Categorie</td>
        <td class="text_left">
            <select id="split<?=$i?>_category1" name="split_category1[<?=$i?>]" class="width100">
                <option></option>
                <?php echo $options; ?>
            </select>
        </td>
        <td class="text_left">
            <select id="split<?=$i?>_category2" name="split_category2[<?=$i?>]" class="width100">
                <option></option>
                <?php echo $options; ?>
            </select>
        </td>
        <td class="text_left">
            <select id="split<?=$i?>_category3" name="split_category3[<?=$i?>]" class="width100">
                <option></option>
                <?php echo $options; ?>
            </select>
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="text_left">
            <select id="split<?=$i?>_category4" name="split_category4[<?=$i?>]" class="width100">
                <option></option>
                <?php echo $options; ?>
            </select>
        </td>
        <td class="text_left">
            <select id="split<?=$i?>_category5" name="split_category5[<?=$i?>]" class="width100">
                <option></option>
                <?php echo $options; ?>
            </select>
        </td>
        <td class="text_left">
            <select id="split<?=$i?>_category6" name="split_category6[<?=$i?>]" class="width100">
                <option></option>
                <?php echo $options; ?>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="4"><hr></td>
    </tr>
<?php } ?>
</table>

</form>

<script>updateInputs();</script>

<?php include(INC."/footer.php"); ?>

