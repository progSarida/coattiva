<?php

$submenuPageNo = 4;

include("../_path.php");
include(ROOT."/_parameter.php");

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/classe_anni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/parametri.php";
include CLASSI . "/notifiche_importate.php";

include(INC."/header.php");
include(INC."/menu.php");
include(INC."/submenu_partita.php");

$partita = new partita($partita_ID, $c, $a);

$readonly = "class='width70 corrige_numero'";
if($partita->Tipo!="RIFIUTI" && $partita->Sottotipo!="TSRSU")
    $readonly = "class='width70 readonly' readonly";

$pag_prec = $cls_db->getResults( $cls_db->SelectQuery("SELECT * FROM pagamento WHERE Partita_ID = '".$partita_ID."' AND CC='".$c."' AND Tipo_Atto = 'Precedenti'"));

if(!isset($pag_prec[0])){
    $pag_prec[0]['ID'] = "";
    $pag_prec[0]['Importo'] = 0;
    $pag_prec[0]['Scorporo_Tributo'] = 0;
    $pag_prec[0]['Scorporo_Spese_Ricerca'] = 0;
    $pag_prec[0]['Scorporo_Spese_Notifica'] = 0;
    $pag_prec[0]['Scorporo_Interessi'] = 0;
    $pag_prec[0]['Scorporo_Eca'] = 0;
    $pag_prec[0]['Scorporo_Tributo_Provinciale'] = 0;
    $pag_prec[0]['Scorporo_Spese_Precedenti'] = 0;
    $pag_prec[0]['Scorporo_Spese_Accessorie'] = 0;
    $pag_prec[0]['Scorporo_Notifica_Pignoramento'] = 0;
    $pag_prec[0]['Scorporo_Diritto_Riscossione'] = 0;
}

$count = 0;

$Totali_Importo = $pag_prec[0]['Importo'];
$Totali_Scorporo_Tributo = $pag_prec[0]['Scorporo_Tributo'];
$Totali_Scorporo_Spese_Ricerca = $pag_prec[0]['Scorporo_Spese_Ricerca'];
$Totali_Scorporo_Spese_Notifica = $pag_prec[0]['Scorporo_Spese_Notifica'];
$Totali_Scorporo_Interessi = $pag_prec[0]['Scorporo_Interessi'];
$Totali_Scorporo_Eca = $pag_prec[0]['Scorporo_Eca'];
$Totali_Scorporo_Tributo_Provinciale = $pag_prec[0]['Scorporo_Tributo_Provinciale'];
$Totali_Scorporo_Spese_Precedenti = $pag_prec[0]['Scorporo_Spese_Precedenti'];
$Totali_Scorporo_Spese_Accessorie = $pag_prec[0]['Scorporo_Spese_Accessorie'];
$Totali_Scorporo_Notifica_Pignoramento = $pag_prec[0]['Scorporo_Notifica_Pignoramento'];
$Totali_Scorporo_Diritto_Riscossione = $pag_prec[0]['Scorporo_Diritto_Riscossione'];

?>

<script>

    //F3
    switchMenuImg("F3");
    F3_button = function(){
        verifica = control_scorpori();
        if(verifica===false){
            return false;
        }

        control = submit_buttons($('#invia_submit').val());
        if(control)
            $("#form_scorpori").submit();
    }

    //F4
    switchMenuImg("F4");
    F4_button = function(){
        control = submit_buttons('Delete');
        if(control)
            $("#form_scorpori").submit();
    }

    //F5
    switchMenuImg("F5");
    F5_button = function(){
	    location.href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F6
    switchMenuImg("F6");
    F6_button = function(){
        if( modifica == 0 )
        {
            location.href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&nuovo_pag=true";
        }
        else
            alert("salvare i dati o annullare prima di procedere");
    }

</script>

<!-- ********** CALENDARIO ********** -->
<script>

$(document).ready(function(){

	$('#data_pag').datepicker();

	});

</script>

<!-- ********** AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>

function control_scorpori(){
	for(var i = 0; i<num_pagamenti;i++){
		verifica = control_somma_scorpori(i);
		if(verifica===false){
			return false;
		}
	}
}

function pulisci_scorpori(value){
	$('#tributo_'+value).val('0,00');
	$('#spese_ricerca_'+value).val('0,00');
	$('#spese_precedenti_'+value).val('0,00');
	$('#spese_notifica_'+value).val('0,00');
	$('#eca_'+value).val('0,00');
	$('#tributo_provinciale_'+value).val('0,00');
	$('#spese_accessorie_'+value).val('0,00');
	$('#spese_not_pigno_'+value).val('0,00');
	$('#interessi_'+value).val('0,00');
	$('#diritto_riscossione_'+value).val('0,00');
}

function control_somma_scorpori(value){
	somma = parseNumber($('#tributo_'+value).val())+parseNumber($('#spese_ricerca_'+value).val())+parseNumber($('#spese_precedenti_'+value).val())+parseNumber($('#spese_notifica_'+value).val());
	somma+= parseNumber($('#eca_'+value).val())+parseNumber($('#tributo_provinciale_'+value).val())+parseNumber($('#spese_accessorie_'+value).val())+parseNumber($('#spese_not_pigno_'+value).val());
	somma+= parseNumber($('#interessi_'+value).val())+parseNumber($('#diritto_riscossione_'+value).val());

	somma = number_format(somma,2);
	pagamento = parseNumber($('#importo_pagato_'+value).val());
	
	if(pagamento!=somma && somma>0){
		alert("ATTENZIONE! Il pagamento di "+pagamento+" Euro non coincide con la somma degli scorpori equivalente a "+somma+" Euro!");
		return false;
	}
	else
		return true;
}

function parseNumber(string){
	return parseFloat(string.replace(',','.').replace(' ',''));
}

function focus_index()
{
	$('[tabindex=1]').focus();
}

</script>


<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
	{
		setupPagina = "dialogWidth:" + sWidth + "px";
		setupPagina += "; dialogHeight:" + sHeight + "px";
		setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
	}

function callParent(valorediritorno) {
    if(valorediritorno!=null){
        switch(selectParent){
            case "utente":

                if(typeof valorediritorno !== 'string')
                    reopen('obj',valorediritorno);
                else
                    reopen('str',valorediritorno);

                break;
        }
    }
}

function reopen(type, value){
    if(type == 'obj')
        top.location.href="../scorporo_pagamento.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
    else if(type == 'str')
        top.location.href="../gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

var selectParent = "";
var selectRif = "";

function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
    var valorediritorno = 0;
    var strDim = Dim_Alert(600, 300);

    switch(value)
    {
        case "utente":

            strDim = Dim_Alert(800, 400);
            var stringa = "modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            valorediritorno = window.showModalDialog(stringa,"", strDim);

            break;
    }
}

</script>


<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

$('#form_scorpori').ajaxForm(
		
    function(value) {
        var array_ritorno = value.split(' ');
        
	if(array_ritorno[0]=='OK')
	{	
		alert('Salvataggio effettuato correttamente!');
		top.location.href = "scorporo_pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else if(array_ritorno[0]=='ERROR')
	{
		alert("Errore nel salvataggio degli scorpori dei pagamenti.");
		top.location.href = "scorporo_pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else if(array_ritorno[0]=='DELETE')
	{
		alert("Pagamento eliminato correttamente.");
		top.location.href = "scorporo_pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert(value);
	
});

$("#submit_click").click( salva_form );

$("#delete_click").click( cancella_form );


});

</script>

<form id=form_scorpori name=form_scorpori action="scorporo_pagamento_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=p value=<?php echo $p; ?> >
<input type=hidden name=partita value=<?php echo $partita_ID; ?> >
<input name=invia_submit  id=invia_submit	type=hidden	value="Update">

    <table class="text_center table_interna" cellspacing=0 border=0 >
        <tr>
            <td class="text_left" colspan=4><hr></td>
        </tr>
        <tr class="riga_pari text_left" style="height:30px;">
            <td><br></td>
            <td class="text_left titolo" colspan=2><b>PAGAMENTI PROVA</b></td>
            <td><br></td>
        </tr>
        <tr class="riga_pari text_left" style="height:30px;">
            <td><br></td>
            <td class="text_left titolo" colspan=2><b>PAGAMENTI PROVA</b></td>
            <td><br></td>
        </tr>
        <?php for($i=0;$i<5;$i++){
            ?>
            <tr class="riga_dispari text_left">
                <td class="width2"><br></td>
                <td class="text_center"></td>
                <td class="text_center"></td>
                <td class="text_center"><?php echo ($i+1);?></td>
                <td class="text_center"></td>
                <td class="text_center"><?php echo ($i+1);?></td>
                <td class="text_center"></td>
                <td class="text_center"><?php echo ($i+1);?></td>
                <td class="text_center"></td>
                <td class="width2"><br></td>
            </tr>
        <?php
        }
        ?>

    </table>

<table class="text_center table_interna" cellspacing=0 border=0 >
<tr>
	<td class="text_left" colspan=15><hr></td>
</tr>
<tr class="riga_pari text_left" style="height:30px;">
	<td><br></td>
	<td class="text_left titolo" colspan=13><b>PAGAMENTI PRECEDENTI</b></td>
	<td><br></td>	
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Pagato</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Tributo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Ricerca</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Spese Acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Diritto risc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Eca</b></td>
	<td class="width2"><br></td>
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"></td>
	<td class="width2"><br></td>
	<td class="text_center width14"><b>Sp. Notifica</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Prec.</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Interessi</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Trib. prov.</b></td>
	<td class="width1"><br></td>
</tr>
<tr>
	<td><input type=hidden id="id_pagamento_<?php echo $count; ?>" name="id_pagamento[<?php echo $count; ?>]" value="<?php echo $pag_prec[0]['ID']; ?>"><br></td>
	<td class="text_center"></td>
	<td><br></td>
	<td class="text_center"><input id="importo_pagato_<?php echo $count; ?>" name="importo_pagato[<?php echo $count; ?>]" class="width70 sfondo_red" value="<?php echo number_format($pag_prec[0]['Importo'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="tributo_<?php echo $count; ?>" name="tributo[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Tributo'],2,",","."); ?>"> &euro;</td>
	<td><br></td>	
	<td class="text_center"><input id="spese_ricerca_<?php echo $count; ?>" name="spese_ricerca[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Ricerca'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="spese_accessorie_<?php echo $count; ?>" name="spese_accessorie[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Accessorie'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="diritto_riscossione_<?php echo $count; ?>" name="diritto_riscossione[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Diritto_Riscossione'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input <?php echo $readonly; ?> id="eca_<?php echo $count; ?>" name="eca[<?php echo $count; ?>]" value="<?php echo number_format($pag_prec[0]['Scorporo_Eca'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
</tr>
<tr>
	<td><br></td>
	<td class="text_center"><br></td>
	<td><br></td>
	<td class="text_center"><br></td>
	<td><br></td>
	<td class="text_center"><input id="spese_notifica_<?php echo $count; ?>" name="spese_notifica[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Notifica'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="spese_precedenti_<?php echo $count; ?>" name="spese_precedenti[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Precedenti'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="spese_not_pigno_<?php echo $count; ?>" name="spese_not_pigno[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Notifica_Pignoramento'],2,",","."); ?>"> &euro;</td>
	<td><br></td>	
	<td class="text_center"><input id="interessi_<?php echo $count; ?>" name="interessi[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Interessi'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input <?php echo $readonly; ?> id="tributo_provinciale_<?php echo $count; ?>" name="tributo_provinciale[<?php echo $count; ?>]" value="<?php echo number_format($pag_prec[0]['Scorporo_Tributo_Provinciale'],2,",","."); ?>"> &euro;</td>
	<td><br></td>		
	<td class="text_center"></td>
	<td><br></td>
</tr>
<tr>
	<td class="text_left" colspan=15><hr></td>
</tr>
<?php
$count++;
for($i=0; $i<count($partita->Atto); $i++)
{		
	if(count($partita->Atto[$i]->Pagamento)==0)
		continue;
?>	

<tr class="riga_pari text_left" style="height:30px;">
	<td><br></td>
	<td class="text_left titolo" colspan=13><b><?php echo strtoupper($partita->Atto[$i]->Atto." n.".$partita->Atto[$i]->ID_Cronologico." del ".$partita->Atto[$i]->Anno_Cronologico); ?></b></td>
	<td><br></td>	
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"><b>Rata</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Pagato</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Tributo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Ricerca</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Spese Acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Diritto risc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Eca</b></td>
	<td class="width2"><br></td>
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"></td>
	<td class="width2"><br></td>
	<td class="text_center width14"><b>Sp. Notifica</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Prec.</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Interessi</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Trib. prov.</b></td>
	<td class="width1"><br></td>
</tr>

	<?php 

if(count($partita->Atto[$i]->Pagamento)>0){
	for($y=0;$y<count($partita->Atto[$i]->Pagamento);$y++){
		$pagamento = $partita->Atto[$i]->Pagamento[$y];
		
		$Totali_Importo+= $pagamento->Importo;
		$Totali_Scorporo_Tributo+= $pagamento->Scorporo_Tributo;
		$Totali_Scorporo_Spese_Ricerca+= $pagamento->Scorporo_Spese_Ricerca;
		$Totali_Scorporo_Spese_Notifica+= $pagamento->Scorporo_Spese_Notifica;
		$Totali_Scorporo_Interessi+= $pagamento->Scorporo_Interessi;
		$Totali_Scorporo_Eca+= $pagamento->Scorporo_Eca;
		$Totali_Scorporo_Tributo_Provinciale+= $pagamento->Scorporo_Tributo_Provinciale;
		$Totali_Scorporo_Spese_Precedenti+= $pagamento->Scorporo_Spese_Precedenti;
		$Totali_Scorporo_Spese_Accessorie+= $pagamento->Scorporo_Spese_Accessorie;
		$Totali_Scorporo_Notifica_Pignoramento+= $pagamento->Scorporo_Notifica_Pignoramento;
		$Totali_Scorporo_Diritto_Riscossione+= $pagamento->Scorporo_Diritto_Riscossione;
		
		if($pagamento->Totale_Rate>0)
			$tot_rate = "/".$pagamento->Totale_Rate;
		else 
			$tot_rate = "";
			
		?>
		<tr>
			<td><input type=hidden id="id_pagamento_<?php echo $count; ?>" name="id_pagamento[<?php echo $count; ?>]" value="<?php echo $pagamento->ID; ?>"><br></td>
			<td class="text_center"><b><span class="color_titolo"><?php echo $pagamento->Rata.$tot_rate; ?></span></b></td>
			<td><br></td>
			<td class="text_center"><input readonly id="importo_pagato_<?php echo $count; ?>" name="importo_pagato[<?php echo $count; ?>]" class="width70 sfondo_azzurro" value="<?php echo number_format($pagamento->Importo,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="tributo_<?php echo $count; ?>" name="tributo[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Tributo,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="spese_ricerca_<?php echo $count; ?>" name="spese_ricerca[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Ricerca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_accessorie_<?php echo $count; ?>" name="spese_accessorie[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Accessorie,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="diritto_riscossione_<?php echo $count; ?>" name="diritto_riscossione[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Diritto_Riscossione,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="eca_<?php echo $count; ?>" name="eca[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Eca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center" colspan=3>
				<input class="width90 sfondo_red" type=button name="azzera" value="Azzera scorpori" onclick="pulisci_scorpori('<?php echo $count; ?>');">
			</td>
			<td><br></td>
			<td class="text_center"><input id="spese_notifica_<?php echo $count; ?>" name="spese_notifica[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Notifica,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_precedenti_<?php echo $count; ?>" name="spese_precedenti[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Precedenti,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_not_pigno_<?php echo $count; ?>" name="spese_not_pigno[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Notifica_Pignoramento,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="interessi_<?php echo $count; ?>" name="interessi[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Interessi,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="tributo_provinciale_<?php echo $count; ?>" name="tributo_provinciale[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Tributo_Provinciale,2,",","."); ?>"> &euro;</td>
			<td><br></td>		
			<td class="text_center"></td>
			<td><br></td>
		</tr>
		<tr>
			<td class="text_left" colspan=15><hr></td>
		</tr>

	<?php $count++; }
}
}
for($i=0; $i<count($partita->Pignoramento); $i++)
{		
	if(count($partita->Pignoramento[$i]->Pagamento)==0)
		continue;		
?>	

<tr class="riga_pari text_left" style="height:30px;">
	<td class="width1"><br></td>
	<td class="text_left titolo" colspan=13><b><?php echo strtoupper("Pignoramento ".$partita->Pignoramento[$i]->Tipo." n.".$partita->Pignoramento[$i]->ID_Cronologico." del ".$partita->Pignoramento[$i]->Anno_Cronologico); ?></b></td>
	<td><br></td>	
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"><b>Rata</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Pagato</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Tributo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Ricerca</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Spese Acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Diritto risc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Eca</b></td>
	<td class="width2"><br></td>
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"></td>
	<td class="width2"><br></td>
	<td class="text_center width14"><b>Sp. Notifica</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Prec.</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Interessi</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Trib. prov.</b></td>
	<td class="width1"><br></td>
</tr>
	<?php 

if(count($partita->Pignoramento[$i]->Pagamento)>0){
	for($y=0;$y<count($partita->Pignoramento[$i]->Pagamento);$y++){
		$pagamento = $partita->Pignoramento[$i]->Pagamento[$y];
		
		$Totali_Importo+= $pagamento->Importo;
		$Totali_Scorporo_Tributo+= $pagamento->Scorporo_Tributo;
		$Totali_Scorporo_Spese_Ricerca+= $pagamento->Scorporo_Spese_Ricerca;
		$Totali_Scorporo_Spese_Notifica+= $pagamento->Scorporo_Spese_Notifica;
		$Totali_Scorporo_Interessi+= $pagamento->Scorporo_Interessi;
		$Totali_Scorporo_Eca+= $pagamento->Scorporo_Eca;
		$Totali_Scorporo_Tributo_Provinciale+= $pagamento->Scorporo_Tributo_Provinciale;
		$Totali_Scorporo_Spese_Precedenti+= $pagamento->Scorporo_Spese_Precedenti;
		$Totali_Scorporo_Spese_Accessorie+= $pagamento->Scorporo_Spese_Accessorie;
		$Totali_Scorporo_Notifica_Pignoramento+= $pagamento->Scorporo_Notifica_Pignoramento;
		$Totali_Scorporo_Diritto_Riscossione+= $pagamento->Scorporo_Diritto_Riscossione;
		
		if($pagamento->Totale_Rate>0)
			$tot_rate = "/".$pagamento->Totale_Rate;
		else 
			$tot_rate = "";
			
		?>
		<tr>
			<td><input type=hidden id="id_pagamento_<?php echo $count; ?>" name="id_pagamento[<?php echo $count; ?>]" value="<?php echo $pagamento->ID; ?>"><br></td>
			<td class="text_center"><b><span class="color_titolo"><?php echo $pagamento->Rata.$tot_rate; ?></span></b></td>
			<td><br></td>
			<td class="text_center"><input readonly id="importo_pagato_<?php echo $count; ?>" name="importo_pagato[<?php echo $count; ?>]" class="width70 sfondo_azzurro" value="<?php echo number_format($pagamento->Importo,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="tributo_<?php echo $count; ?>" name="tributo[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Tributo,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="spese_ricerca_<?php echo $count; ?>" name="spese_ricerca[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Ricerca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_accessorie_<?php echo $count; ?>" name="spese_accessorie[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Accessorie,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="diritto_riscossione_<?php echo $count; ?>" name="diritto_riscossione[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Diritto_Riscossione,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="eca_<?php echo $count; ?>" name="eca[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Eca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center" colspan=3>
				<input class="width90 sfondo_red" type=button name="azzera" value="Azzera scorpori" onclick="pulisci_scorpori('<?php echo $count; ?>');">
			</td>
			<td><br></td>
			<td class="text_center"><input id="spese_notifica_<?php echo $count; ?>" name="spese_notifica[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Notifica,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_precedenti_<?php echo $count; ?>" name="spese_precedenti[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Precedenti,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_not_pigno_<?php echo $count; ?>" name="spese_not_pigno[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Notifica_Pignoramento,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="interessi_<?php echo $count; ?>" name="interessi[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Interessi,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="tributo_provinciale_<?php echo $count; ?>" name="tributo_provinciale[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Tributo_Provinciale,2,",","."); ?>"> &euro;</td>
			<td><br></td>		
			<td class="text_center"></td>
			<td><br></td>
		</tr>
		<tr>
			<td class="text_left" colspan=15><hr></td>
		</tr>
	<?php $count++; }
}
}?>

		<tr class="riga_pari text_left" style="height:30px;">
			<td class="width1"><br></td>
			<td class="text_left color_red" colspan=13><b>TOTALI</b></td>
			<td><br></td>	
		</tr>
		<tr class="riga_dispari text_left">
			<td class="width2"><br></td>
			<td class="text_center width6"></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Pagato</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Tributo</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Sp. Ricerca</b></td>
			<td class="width1"><br></td>	
			<td class="text_center width14"><b>Spese Acc.</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Diritto risc.</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Eca</b></td>
			<td class="width2"><br></td>
		</tr>
		<tr class="riga_dispari text_left">
			<td class="width2"><br></td>
			<td class="text_center width6"></td>
			<td class="width1"><br></td>
			<td class="text_center width14"></td>
			<td class="width2"><br></td>
			<td class="text_center width14"><b>Sp. Notifica</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Sp. Prec.</b></td>
			<td class="width1"><br></td>	
			<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Interessi</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Trib. prov.</b></td>
			<td class="width1"><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center"></td>
			<td><br></td>
			<td class="text_center"><input readonly id="importo_pagato_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Importo,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="tributo_totali" name="tributo[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Tributo,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input readonly id="spese_ricerca_totali" name="spese_ricerca[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Ricerca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_accessorie_totali" name="spese_accessorie[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Accessorie,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="diritto_riscossione_totali" name="diritto_riscossione[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Diritto_Riscossione,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="eca_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Eca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center" colspan=3></td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_notifica_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Notifica,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_precedenti_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Precedenti,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_not_pigno_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Notifica_Pignoramento,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input readonly id="interessi_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Interessi,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="tributo_provinciale_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Tributo_Provinciale,2,",","."); ?>"> &euro;</td>
			<td><br></td>		
			<td class="text_center"></td>
			<td><br></td>
		</tr>
		<tr>
			<td class="text_left" colspan=15><br></td>
		</tr>
	</table>
	
</form>

<?php include(INC."/footer.php"); ?>