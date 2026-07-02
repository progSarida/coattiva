<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

//require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";

include(INC."/header.php");
include(INC."/menu.php");

include CLS . "/cls_split_payment.php";

$cls_split = new cls_split_payment();
$a_types = $cls_db->getResults( $cls_db->SelectQuery( $cls_split->getTypesQuery() ) );
$a_params = $cls_db->getArrayLineNull( $cls_db->SelectQuery( $cls_split->getParametersQuery($c) ),"split_payment_parameters");
$options = $cls_html->optionsFromArray($a_types,"id",null,"category");

for($i=1;$i<=14;$i++){

    $a_categories[$i] = unserialize($a_params['split'.$i.'_categories']);
    $a_type[$i] = "".$a_params['split'.$i.'_type']."";
}

?>

<script>
    var a_categories = <?php echo json_encode($a_categories); ?>;
    var a_type = <?php echo json_encode($a_type); ?>;

//F3
switchMenuImg("F3");
F3_button = function(){

    control = submit_buttons('Salva');
    if(control)
        $("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="par_scorpori.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


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

<style>
.row.no-gutter {
     margin-top: 0;
     margin-bottom: 0;
}
.row.no-gutter [class*='col-']:not(:first-child),
.row.no-gutter [class*='col-']:not(:last-child) {
     padding-top: 0;
     padding-bottom: 0;
}

.padding-0{
       padding-bottom:0;
       margin-bottom:0;
}
</style>



<div class="row justify-content-md-center ">
  <div class="col col-md-auto text_center">
      <p class="titolo font16 under_decor"> Parametri di scorporo </p>
  </div>
</div>

<form class="form-horizontal validate" id=form_scorpori method=post action="par_scorpori_salva.php">

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >

<?php for($i=1;$i<=16;$i++) : ?>

  <div class="row" style="margin-top: 2%;">
    <div class="col col-lg-1 col-lg-offset-1">
      <p class="color_titolo resize" style="text-align: center;"><b ><?=$i?></b></p>
    </div>
    <div class="col col-lg-3">
  		<div class="form-group padding-0">
          <input class="form-control resize" type="text" id="split<?=$i?>" name="split[<?=$i?>]" value="<?php echo $a_params['split'.$i]; ?>">
  		</div>
  	</div>
    <div class="col col-lg-4">
      <div class="form-group">
  			<label  class="col-lg-4 control-label resize">Priorita'</label>
  			<div class="col-lg-8">
            <select class="form-control resize" name="split_type[<?=$i?>]" id="split<?=$i?>_type">
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
  			</div>
  		</div>
  	</div>
  </div>

  <div class="row">
    <div class="col col-lg-1 col-lg-offset-1 resize">
      <span style="text-align: center;"><b>Categorie</b></span>
    </div>
    <div class="col col-lg-3">
      <div class="form-group padding-0">
        <select id="split<?=$i?>_category1" name="split_category1[<?=$i?>]" class="form-control resize">
            <option></option>
            <?php echo $options; ?>
        </select>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group padding-0">
        <select id="split<?=$i?>_category2" name="split_category2[<?=$i?>]" class="form-control resize">
            <option></option>
            <?php echo $options; ?>
        </select>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group padding-0">
        <select id="split<?=$i?>_category3" name="split_category3[<?=$i?>]" class="form-control resize">
            <option></option>
            <?php echo $options; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col col-lg-1 col-lg-offset-1 resize">

    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <select id="split<?=$i?>_category4" name="split_category4[<?=$i?>]" class="form-control resize">
            <option></option>
            <?php echo $options; ?>
        </select>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <select id="split<?=$i?>_category5" name="split_category5[<?=$i?>]" class="form-control resize">
            <option></option>
            <?php echo $options; ?>
        </select>
      </div>
    </div>
    <div class="col col-lg-3">
      <div class="form-group">
        <select id="split<?=$i?>_category6" name="split_category6[<?=$i?>]" class="form-control resize">
            <option></option>
            <?php echo $options; ?>
        </select>
      </div>
    </div>
  </div>

  <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

<?php endfor; ?>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>
</form>

<script>updateInputs();</script>

<?php include(INC."/footer.php"); ?>
