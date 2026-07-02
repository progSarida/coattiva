<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_paramUtils.php");

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

if($tipo_riscossione=="CDS")
    $titolo_riscossione = $tipo_riscossione."/AMMINISTRATIVA";
else
    $titolo_riscossione = $tipo_riscossione;

$nome_com = $a_enteAdmin["Denominazione"];

if(substr($c,0,1)=="U")
    $enteSpese = "della ".$a_enteAdmin["Denominazione"];
else
    $enteSpese = "del Comune di ".$a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$layout="";


$cls_param = new cls_param();
$a_param = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_Gen($c , $tipo_riscossione)));

$par_id = $a_param['ID'];
if($par_id==null) $par_id = 0;

$layout.="<script>updateInputs('".$a_param['Spese_Anticipate']."','".$a_param['Testo_Spese_Anticipate']."','".$a_param['SMA']."','".$a_param['Intestatario_SMA']."','".$a_param['Numero_SMA']."')</script>";
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

    function updateInputs(spese,testo_spese,SMA,intestatario_SMA,numero_SMA){

        $('#spese_anticipate').val(spese);

        if(spese=="y"){
            $('#testo_spese').prop('readonly',false).toggleClass("readonly").val(testo_spese);
        }

        $('#SMA').val(SMA);

        if(SMA=="y"){
            $('#intestatario_SMA').prop('readonly',false).toggleClass("readonly").val(intestatario_SMA);
            $('#numero_SMA').prop('readonly',false).toggleClass("readonly").val(numero_SMA);
        }
    }

    function changeSMA(){
        SMA = $('#SMA').val();
        if(SMA=="y"){
            $('#intestatario_SMA').prop('readonly',false).removeClass("readonly");
            $('#numero_SMA').prop('readonly',false).removeClass("readonly");
        }
        else{
            $('#intestatario_SMA').prop('readonly',true).addClass("readonly").val("");
            $('#numero_SMA').prop('readonly',true).addClass("readonly").val("");
        }
    }

    function changeSpese(){
        spese_anticipate = $('#spese_anticipate').val();
        if(spese_anticipate=="y"){
            $('#testo_spese').prop('readonly',false).removeClass("readonly");
        }
        else{
            $('#testo_spese').prop('readonly',true).addClass("readonly").val("");
        }
    }

</script>

	<?php

	include(INC."/menu.php");

	?>

<script type="text/javascript">


//F3
switchMenuImg("F3");
F3_button = function()
{
	control = submit_buttons('Salva');
	if(control)
	    $("#form_par_generali").submit();
}



//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="par_generali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_responsabili.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "par_email.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Parametri generali (<?php echo $titolo_riscossione; ?>)</font></td>
	</tr>
</table>
<br>

<form name=form_par_generali id=form_par_generali method=post action="par_generali_salva.php" enctype="multipart/form-data">

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=invia_submit 	value=""	id=invia_submit  	>
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> >
<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">

    <tr>
        <td class="text_left width25" colspan="2"><span class="titolo">DISTINTA SMA</span></td>
        <td class="text_left" colspan="3">
            <select id="SMA" name="SMA" onchange="changeSMA();">
                <option value="n">No</option>
                <option value="y">Si</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
    <tr>
        <td class="width10"></td>
        <td class="text_left">Intestatario</td>
        <td class="text_left">
            <input class="readonly width80" name="intestatario_SMA" id="intestatario_SMA" readonly>
        </td>
        <td class="text_left">Numero</td>
        <td class="text_left">
            <input class="readonly width80" name="numero_SMA" id="numero_SMA" readonly>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
    <tr>
        <td class="text_left" colspan="2"><span class="titolo">RIFATTURAZIONE SPESE</span></td>
        <td colspan="3" class="text_left">
            <select id="spese_anticipate" name="spese_anticipate" onchange="changeSpese();">
                <option value="n">No</option>
                <option value="y">Si</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
    <tr>
        <td class="width10"></td>
        <td class="text_left">Testo</td>
        <td colspan="3" class="text_left">
            <textarea class="readonly width91" name="testo_spese" id="testo_spese" readonly></textarea>
        </td>
    </tr>
    <tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
    <tr>
        <td class="text_left width25" colspan="5"><span class="titolo">RESTITUZIONE MOD.23L - RACCOMANDATA A.G.</span></td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Soggetto mittente</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'NOME GESTORE - GESTIONE:'" class="width91" name="restituzione[1]" id="restituzione1"><?php echo $a_param['Restituzione1']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Ente gestito</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'COMUNE DI NOME ENTE'" class="width91" name="restituzione[2]" id="restituzione2"><?php echo $a_param['Restituzione2']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Recapito - Soggetto</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'C/O MERCURIO SERVICES S.R.L.'" class="width91" name="restituzione[3]" id="restituzione3"><?php echo $a_param['Restituzione3']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Recapito - Indirizzo</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'VIA DELLA CASA BUIA 4-4/G'" class="width91" name="restituzione[4]" id="restituzione4"><?php echo $a_param['Restituzione4']; ?></textarea>
        </td>
    </tr>
    <tr>

        <td class="text_center" colspan="2">Recapito - CAP Comune Provincia</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. '40129 BOLOGNA BO'" class="width91" name="restituzione[5]" id="restituzione5"><?php echo $a_param['Restituzione5']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
    <tr>
        <td class="text_left width25" colspan="5"><span class="titolo">RESTITUZIONE MOD.23O - RACCOMANDATA</span></td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Soggetto mittente</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'NOME GESTORE - GESTIONE:'" class="width91" name="restituzione_Mod23O[1]" id="restituzione_Mod23O_1"><?php echo $a_param['Restituzione1_Mod23O']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Ente gestito</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'COMUNE DI NOME ENTE'" class="width91" name="restituzione_Mod23O[2]" id="restituzione_Mod23O_2"><?php echo $a_param['Restituzione2_Mod23O']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Recapito - Soggetto</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'C/O MERCURIO SERVICES S.R.L.'" class="width91" name="restituzione_Mod23O[3]" id="restituzione_Mod23O_3"><?php echo $a_param['Restituzione3_Mod23O']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan="2">Recapito - Indirizzo</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. 'VIA DELLA CASA BUIA 4-4/G'" class="width91" name="restituzione_Mod23O[4]" id="restituzione_Mod23O_4"><?php echo $a_param['Restituzione4_Mod23O']; ?></textarea>
        </td>
    </tr>
    <tr>

        <td class="text_center" colspan="2">Recapito - CAP Comune Provincia</td>
        <td colspan="3" class="text_left">
            <textarea placeholder="es. '40129 BOLOGNA BO'" class="width91" name="restituzione_Mod23O[5]" id="restituzione_Mod23O_5"><?php echo $a_param['Restituzione5_Mod23O']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="text_center" colspan=5><hr></td>
    </tr>
</table>

<br>

</form>
<?php
	echo $layout;
?>


<?php include(INC."/footer.php"); ?>
