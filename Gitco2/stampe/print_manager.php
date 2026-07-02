<?php
include("../_path.php");
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_print.php");
include_once(CLS."/cls_ruolo.php");

//print or list
$docType = $cls_help->getVar('docType');
$printType = $cls_help->getVar('printType');

$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($docType);

$cls_print = new cls_print($printType,$docType);


$a_years = $cls_db->getResults($cls_db->SelectQuery("SELECT Anno FROM anni_gestiti WHERE CC_Anno ='".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC"));
$opt_years = "";
for($i=0;$i<count($a_years);$i++)
    $opt_years.= "<option value='".$a_years[$i]['Anno']."'>".$a_years[$i]['Anno']."</option>";

$a_partite = $cls_db->getResults($cls_db->SelectQuery("SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC"));
$serieOption = "";
for($i=0;$i<count($a_partite);$i++)
    $serieOption .= "<option value='" . $a_partite[$i]['Comune_ID'] . "'>" . $a_partite[$i]['Comune_ID'] . "</option>";

if(($_SESSION['CC_User']=="****" || $_SESSION['CC_User']=="***+") && $_SESSION['aut_tipo']==1)
    $citySelect = "<select name='city' class='width95'><option value='".$c."'>".$adminCity."</option><option>Tutti</option></select>";
else
    $citySelect = "<select name='city' class='width95' disabled><option value='".$c."'>".$adminCity."</option></select>";

?>

<script>

    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="gestione_stampe.php?printType=<?php echo $printType; ?>&docType=<?php echo $docType; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F5
    switchMenuImg("F10");
    F10_button = function(){
        $('#print_form').submit();
    }

    function callParent(valorediritorno){
        if(valorediritorno!=null)
        {
            $.post("ajax/ajax_stampe.php?c=<?php echo $c; ?>" ,

                { 'ajax': 'nome' ,
                    'ID': valorediritorno },

                function (value) {

                    var array_ritorno = value.split('*');

                    if(selectRif==1){
                        $('#from_surname').val(array_ritorno[0]);
                        $('#to_surname').val(array_ritorno[0]);
                    }
                    else if(selectRif==2){
                        $('#to_surname').val(array_ritorno[0]);
                    }

                    if(array_ritorno.length == 2){
                        if(selectRif==1){
                            $('#from_name').val(array_ritorno[1]);
                            $('#to_name').val(array_ritorno[1]);
                        }
                        else if(selectRif==2){
                            $('#to_name').val(array_ritorno[1]);
                        }
                    }
                    else{
                        if(selectRif==1){
                            $('#from_name').val("");
                            $('#to_name').val("");
                        }
                        else if(selectRif==2){
                            $('#to_name').val("");
                        }
                    }
                }
            );
        }
    }

    var selectRif = "";
    function getUtente(rif){
        selectRif = rif;
        var stringa = "/gitco2/coattiva/modali/ricerca_alert_modale.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        window.open(stringa, "", "width=600,height=300,top=70,left=70,scrollbars=yes,menubar=no");
    }

</script>


<div><span class="titolo font16 under_decor"><?php echo $cls_print->a_type['title']; ?></span></div>
<br>
	
<form id="print_form" name="print_form" action="<?php echo $cls_print->a_type['action']; ?>" method="post" target="stampa" onSubmit="window.open('', 'stampa', 'width=900,height=500,top=70,left=70,scrollbars=yes,menubar=no')">

	<input type=hidden name="c" value="<?php echo $c ?>">
	<input type=hidden name="a" value="<?php echo $a ?>">
    <input type=hidden name="docType" value="<?php echo $docType ?>">

    <div class="pwidth750 text_center pheight10" style="clear:both;"><hr></div>
    <div class="pwidth750 text_center pheight20" style="clear:both;">
        <div style="float:left;" class="width25 text_left">
            <input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="getUtente(1);">
        </div>
        <div style="float:left;" class="width40 text_center">
            <input type="text" id="from_surname" name="from_surname" class="width55" >
            <input type="text" id="from_name" name="from_name" class="width35">
        </div>
        <div style="float:left;" class="width15 text_left">Da partita</div>
        <div style="float:left;" class="width20 text_left">
            <select name="from_taxRecord">
                <option value=""></option>
                <?php echo $serieOption ?>
            </select>
        </div>
    </div>
    <div class="pwidth750 text_center pheight25" style="clear:both;">
        <div style="float:left;" class="width25 text_left">
            <input class="button_azzurro pwidth150" type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="getUtente(2);">
        </div>
        <div style="float:left;" class="width40 text_center">
            <input type="text" id="to_surname" name="to_surname" class="width55" >
            <input type="text" id="to_name" name="to_name" class="width35">
        </div>
        <div style="float:left;" class="width15 text_left">A partita</div>
        <div style="float:left;" class="width20 text_left">
            <select name="to_taxRecord">
                <option value=""></option>
                <?php echo $serieOption ?>
            </select>
        </div>
    </div>
    <div class="pwidth750 text_center pheight20" style="clear:both;">
        <div style="float:left;" class="width25 text_left"><span class="color_titolo font_bold">Anni di riferimento</span></div>
        <div style="float:left;" class="width10 text_center">Da anno</div>
        <div style="float:left;" class="width10 text_center"><input name="from_taxYear" type="text" value="<?php echo $a; ?>" class="width75 text_right"></div>
        <div style="float:left;" class="width10 text_center">Ad anno</div>
        <div style="float:left;" class="width10 text_center"><input name="to_taxYear" type="text" value="<?php echo $a; ?>" class="width75 text_right"></div>
        <div style="float:left;" class="width15 text_left">Tipo Entrata</div>
        <div style="float:left;" class="width20 text_left">
            <select name=taxType class="width90">
                <option>CDS</option>
                <option>IMMOBILI</option>
                <option>IRPEF</option>
                <option>OSAP</option>
                <option>PATRIMONIALE</option>
                <option value="PUBBLICITA">PUBBLICITA'</option>
                <option>RIFIUTI</option>
            </select>
        </div>
    </div>
    <div class="pwidth750 text_center pheight5" style="clear:both;"><br></div>
    <div class="pwidth750 text_center pheight8" style="clear:both;"><hr></div>
    <div class="pwidth750 text_center pheight5" style="clear:both;"><br></div>

    <?php echo $cls_print->getFilters(); ?>

    <div class="pwidth750 text_center pheight5" style="clear:both;"><br></div>
    <div class="pwidth750 text_center pheight8" style="clear:both;"><hr></div>

</form>

<?php include(INC."/footer.php"); ?>