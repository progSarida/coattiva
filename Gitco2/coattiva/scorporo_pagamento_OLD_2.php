<?php
if (!session_id()) session_start();

$submenuPageNo = 4;



include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");


include(CLS."/cls_GestionePartita.php");
include(CLS."/cls_split_payment.php");
include(INC."/header.php");
include(INC."/menu.php");
include(INC."/submenu_partita.php");


if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_gestione = new cls_GP();

//$partita = new partita($partita_ID, $c, $a);
$partita = $cls_gestione->getDataScorporo($partita_ID,$c,$a);

$readonly = "class='width70 corrige_numero'";
if($partita["Tipo"]!="RIFIUTI" && $partita["Sottotipo"]!="TSRSU")
    $readonly = "class='width70 readonly' readonly";

$cls_split = new cls_split_payment();

$a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersFromIdQuery($partita["Split_Parameters_ID"]) ) );
if(!$a_params['id']>0)
    $a_params = $cls_db->getArrayLine( $cls_db->SelectQuery( $cls_split->getParametersQuery($c) ) );


$a_order = $cls_split->getLineByPriority($a_params);

//print_r($a_order);

$a_prevPayments = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM pagamento WHERE Partita_ID = '".$partita_ID."' AND CC='".$c."' AND Tipo_Atto = 'Precedenti'"));

if(!isset($a_prevPayments)){
    $a_prevPayments['ID'] = "";
    $a_prevPayments['Importo'] = 0;
    for($i=1;$i<=count($a_order);$i++)
        $a_prevPayments['Split_Payment'.$i] = 0;
}

$count = 0;

$a_totalAmounts = array();
$a_totalAmounts['Total_Payments'] = $a_prevPayments['Importo'];
for($i=1;$i<=count($a_order);$i++)
    $a_totalAmounts['Total_Split'.$i] = $a_prevPayments['Split_Payment'.$i];

$headerHtml = "";
$countHeader = 0;
for($y=0;$y<3;$y++){
    if($countHeader<count($a_order)){
        $headerHtml.= '<tr class="riga_dispari text_left"><td class="width1"></td><td class="text_center width11">';
        if($y==0)
            $headerHtml.= '<b>Pagato</b>';
        $headerHtml.= '</td><td class="width1"><br></td>';

        for($i=0;$i<6;$i++){
            $headerHtml.='<td class="text_center width13"><span class="font12"><b>';

            if(isset($a_order[$countHeader])) {
                $headerHtml.= $a_order[$countHeader]['header'];
                $countHeader++;
            }

            $headerHtml.= '</b></span></td>';
            $headerHtml.= '<td class="width1"><br></td>';
        }
        $headerHtml.= '</tr>';

    }
}

$count = 0;
$prevPaymentHtml = "";
$prevPaymentHtml.= '<tr class="riga_pari text_left" style="height:30px;"><td><br></td>';
$prevPaymentHtml.= '<td class="text_left titolo" colspan=13><b>PAGAMENTI PRECEDENTI</b></td><td><br></td></tr>';
$prevPaymentHtml.= $headerHtml;
$countHeader = 0;


for($y=0;$y<3;$y++){
    if($countHeader<count($a_order)){
        $prevPaymentHtml.= '<tr class="text_left"><td></td><td class="text_center">';
        if($y==0){
            $prevPaymentHtml.= '<input class="form-control resize" type=hidden id="id_pagamento_'.$count.'" name="id_pagamento['.$count.']" value="'.$a_prevPayments['ID'].'">';
            $prevPaymentHtml.= '<div class="form-group">';
            $prevPaymentHtml.= '<div style="float: left; width: 70%;">';
            $prevPaymentHtml.= '<input class="form-control resize validateCustom vld_Custom_d vld_Custom_r" style="" id="importo_pagato_'.$count.'" name="importo_pagato['.$count.']" value="'.number_format($a_prevPayments['Importo'],2,",",".").'"> ';
            $prevPaymentHtml.= '</div>';
            $prevPaymentHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
            $prevPaymentHtml.= '</div>';
        }

        $prevPaymentHtml.= '</td><td></td>';

        for($i=0;$i<6;$i++){
            $prevPaymentHtml.='<td class="text_center">';

            if(isset($a_order[$countHeader])) {
                $prevPaymentHtml.= '<div class="form-group">';
                $prevPaymentHtml.= '<div style="float: left; width: 70%;">';
                $prevPaymentHtml.= '<input class="form-control resize validateCustom vld_Custom_d vld_Custom_r" id="split'.$a_order[$countHeader]['split_number'].'_'.$count.'" name="split'.$a_order[$countHeader]['split_number'].'['.$count.']" ';
                $prevPaymentHtml.= 'value="'.number_format($a_prevPayments['Split_Payment'.$a_order[$countHeader]['split_number']],2,",",".").'">';
                $prevPaymentHtml.= '</div>';
                $prevPaymentHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
                $prevPaymentHtml.= '</div>';
                $countHeader++;
            }

            $prevPaymentHtml.= '</td><td></td>';
        }
        $prevPaymentHtml.= '</tr>';
    }
}
$prevPaymentHtml.= '<tr><td class="text_left" colspan=15><hr></td></tr>';

$count++;
$attiHtml = "";
$tot_dovuto = 0.00;
$countAtto = isset($partita["Atto"])?count($partita["Atto"]):0;
for($i=0; $i<$countAtto; $i++) {

    $a_tot = $cls_gestione->getTotalAmountDue($partita["Atto"][$i]);
    $tot_dovuto = $a_tot['tot'];
    if (!isset($partita["Atto"][$i]["Pagamento"]))
        continue;

    $titoloAtto = strtoupper($partita["Atto"][$i]["Atto"]." n.".$partita["Atto"][$i]["ID_Cronologico"]." del ".$partita["Atto"][$i]["Anno_Cronologico"]);

    $attiHtml.= '<tr class="riga_pari text_left" style="height:30px;"><td><br></td>';
    $attiHtml.= '<td class="text_left titolo" colspan=13><b>'.$titoloAtto.'</b></td><td><br></td></tr>';
    $attiHtml.= $headerHtml;

    if(count($partita["Atto"][$i]["Pagamento"])>0) {

        for ($y = 0; $y < count($partita["Atto"][$i]["Pagamento"]); $y++) {
            $pagamento = $partita["Atto"][$i]["Pagamento"][$y];

            $a_totalAmounts['Total_Payments'] += $pagamento["Importo"];
            for($k=1;$k<=count($a_order);$k++){
                $a_totalAmounts['Total_Split'.$k] += $pagamento["Split_Payment".$k];
            }

            if ($pagamento["Totale_Rate"] > 0)
                $tot_rate = "/" . $pagamento["Totale_Rate"];
            else
                $tot_rate = "";

            $countHeader = 0;
            for($z=0;$z<3;$z++){
                if($countHeader<count($a_order)){
                    $attiHtml.= '<tr class="text_left"><td></td><td class="text_center">';
                    if($z==0){
                        $attiHtml.= '<input type=hidden id="id_pagamento_'.$count.'" name="id_pagamento['.$count.']" value="'.$pagamento["ID"].'">';

                        $attiHtml.= '<div class="form-group">';
                        $attiHtml.= '<div style="float: left; width: 70%;">';
                        $attiHtml.= '<input readonly id="importo_pagato_'.$count.'" name="importo_pagato['.$count.']" style="background-color: #FFC7C7;border: 1px solid black;" class="form-control resize validateCustom vld_Custom_d vld_Custom_r" value="'.number_format($pagamento["Importo"],2,",",".").'"> ';
                        $attiHtml.= '</div>';
                        $attiHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
                        $attiHtml.= '</div>';

                        //$attiHtml.= '<input readonly id="importo_pagato_'.$count.'" name="importo_pagato['.$count.']" class="form-control resize validateCustom vld_Custom_d vld_Custom_r sfondo_red" value="'.number_format($pagamento["Importo"],2,",",".").'"> &euro;';
                    }
                    else if($z==1){
                        $attiHtml.= '<a href="#"><img onclick="pulisci_scorpori('.$count.')" title="Azzera scorpori" src="'.IMG.'/elimina_icon.png" width="10px" height="10px"/></a> <b><span class="color_titolo font14">Rata '.$pagamento["Rata"].$tot_rate.'</span></b>';
                    }

                    $attiHtml.= '</td><td></td>';

                    for($k=0;$k<6;$k++){
                        $attiHtml.='<td class="text_center">';

                        if(isset($a_order[$countHeader])) {
                            $keyPag = "Split_Payment".$a_order[$countHeader]['split_number'];
                            if($pagamento[$keyPag]=="")$pagamento[$keyPag] = 0;
                            $attiHtml.= '<div class="form-group">';
                            $attiHtml.= '<div style="float: left; width: 70%;">';
                            $attiHtml.= '<input id="split'.$a_order[$countHeader]['split_number'].'_'.$count.'" name="split'.$a_order[$countHeader]['split_number'].'['.$count.']" ';
                            $attiHtml.= 'class="form-control resize validateCustom vld_Custom_d vld_Custom_r" value="'.number_format($pagamento[$keyPag],2,",",".").'">';
                            $attiHtml.= '</div>';
                            $attiHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
                            $attiHtml.= '</div>';
                            $countHeader++;
                        }

                        $attiHtml.= '</td><td></td>';
                    }
                    $attiHtml.= '</tr>';

                }
            }
            $attiHtml.= '<tr><td class="text_left" colspan=15><hr></td></tr>';
            $count++;
        }
    }
}

$pignoHtml = "";
$countFor = isset($partita["Pignoramento"])?count($partita["Pignoramento"]):0;
for($i=0; $i<$countFor; $i++) {

    $a_tot = $cls_gestione->getTotalAmountDuePigno($partita["Pignoramento"][$i]);
    $tot_dovuto = $a_tot['tot'];
    $countPag = isset($partita["Pignoramento"][$i]["Pagamento"])?count($partita["Pignoramento"][$i]["Pagamento"]):0;
    if ($countPag == 0)
        continue;

    $titoloPigno = strtoupper("Pignoramento ".$partita["Pignoramento"][$i]["Tipo"]." n.".$partita["Pignoramento"][$i]["ID_Cronologico"]." del ".$partita["Pignoramento"][$i]["Anno_Cronologico"]);

    $pignoHtml.= '<tr class="riga_pari text_left" style="height:30px;"><td><br></td>';
    $pignoHtml.= '<td class="text_left titolo" colspan=13><b>'.$titoloPigno.'</b></td><td><br></td></tr>';
    $pignoHtml.= $headerHtml;

    if(count($partita["Pignoramento"][$i]["Pagamento"])>0){
        for($y=0;$y<count($partita["Pignoramento"][$i]["Pagamento"]);$y++){
            $pagamento = $partita["Pignoramento"][$i]["Pagamento"][$y];

//            $atto = new atto($partita->Pignoramento[$i]->Atto_ID,$pagamento->CC);
//            $pagamento->splitPayment($partita,$atto,$partita->Pignoramento[$i],$a_order);

            $a_totalAmounts['Total_Payments'] += $pagamento["Importo"];
            for($k=1;$k<=count($a_order);$k++){
                $key = "Split_Payment".$k;
                $a_totalAmounts['Total_Split'.$k] += $pagamento[$key];
            }

            if ($pagamento["Totale_Rate"] > 0)
                $tot_rate = "/" . $pagamento["Totale_Rate"];
            else
                $tot_rate = "";

            $countHeader = 0;
            for($z=0;$z<3;$z++){
                if($countHeader<count($a_order)){
                    $pignoHtml.= '<tr class="text_left"><td></td><td class="text_center">';
                    if($z==0){
                        $pignoHtml.= '<input type=hidden id="id_pagamento_'.$count.'" name="id_pagamento['.$count.']" value="'.$pagamento["ID"].'">';
                        $pignoHtml.= '<div class="form-group">';
                        $pignoHtml.= '<div style="float: left; width: 70%;">';
                        $pignoHtml.= '<input readonly id="importo_pagato_'.$count.'" name="importo_pagato['.$count.']" style="background-color: #FFC7C7;border: 1px solid black;" class="form-control resize validateCustom vld_Custom_d vld_Custom_r" value="'.number_format($pagamento["Importo"],2,",",".").'">';
                        $pignoHtml.= '</div>';
                        $pignoHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
                        $pignoHtml.= '</div>';
                    }
                    else if($z==1){
                        $pignoHtml.= '<a href="#"><img onclick="pulisci_scorpori('.$count.')" title="Azzera scorpori" src="'.IMG.'/elimina_icon.png" width="10px" height="10px"/></a> <b><span class="color_titolo font14">Rata '.$pagamento->Rata.$tot_rate.'</span></b>';
                    }

                    $pignoHtml.= '</td><td></td>';

                    for($k=0;$k<6;$k++){
                        $pignoHtml.='<td class="text_center">';

                        if(isset($a_order[$countHeader])) {
                            $keyPag = "Split_Payment".$a_order[$countHeader]['split_number'];
                            if($pagamento[$keyPag]=="")$pagamento[$keyPag] = 0;
                            $pignoHtml.= '<div class="form-group">';
                            $pignoHtml.= '<div style="float: left; width: 70%;">';
                            $pignoHtml.= '<input id="split'.$a_order[$countHeader]['split_number'].'_'.$count.'" name="split'.$a_order[$countHeader]['split_number'].'['.$count.']" ';
                            $pignoHtml.= 'class="form-control resize validateCustom vld_Custom_d vld_Custom_r" value="'.number_format($pagamento[$keyPag],2,",",".").'">';
                            $pignoHtml.= '</div>';
                            $pignoHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
                            $pignoHtml.= '</div>';
                            $countHeader++;
                        }

                        $pignoHtml.= '</td><td></td>';
                    }
                    $pignoHtml.= '</tr>';

                }
            }
            $pignoHtml.= '<tr><td class="text_left" colspan=15><hr></td></tr>';
            $count++;
        }
    }


}

$totaliHtml = "";
$totaliHtml.= '<tr class="riga_pari text_left" style="height:30px;"><td><br></td>';
$totaliHtml.= '<td class="text_left color_red" colspan=13><b>TOTALI</b></td><td><br></td></tr>';
$totaliHtml.= $headerHtml;
$countHeader = 0;
for($z=0;$z<3;$z++){
    if($countHeader<count($a_order)){
        $totaliHtml.= '<tr class="text_left"><td></td><td class="text_center">';
        if($z==0){
            $totaliHtml.= '<div class="form-group">';
            $totaliHtml.= '<div style="float: left; width: 70%;">';
            $totaliHtml.= '<input style="background-color: #FFC7C7;border: 1px solid black;" class="form-control resize validateCustom vld_Custom_d vld_Custom_r" name="importo_pagato_totali" id="importo_pagato_totali" value="'.number_format($a_totalAmounts['Total_Payments'],2,",",".").'" />';
            $totaliHtml.= '</div>';
            $totaliHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
            $totaliHtml.= '</div>';
        }

        $totaliHtml.= '</td><td></td>';

        for($k=0;$k<6;$k++){
            $totaliHtml.='<td class="text_center">';

            if(isset($a_order[$countHeader])) {
                $totaliHtml.= '<div class="form-group">';
                $totaliHtml.= '<div style="float: left; width: 70%;">';
                $totaliHtml.= '<input id="split'.$a_order[$countHeader]['split_number'].'_totali" readonly ';
                $totaliHtml.= 'style="background-color: #FFC7C7;border: 1px solid black;" class="form-control resize validateCustom vld_Custom_d vld_Custom_r" value="'.number_format($a_totalAmounts['Total_Split'.$a_order[$countHeader]['split_number']],2,",",".").'">';
                $totaliHtml.= '</div>';
                $totaliHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
                $totaliHtml.= '</div>';
                $countHeader++;
            }

            $totaliHtml.= '</td><td></td>';
        }
        $totaliHtml.= '</tr>';

    }
}
$totaliHtml.= '<tr><td class="text_left" colspan=15><hr></td></tr>';
$totaleDovutoHtml = "";
$totaleDovutoHtml.= '<tr class="riga_pari text_left" style="height:30px;"><td><br></td>';
$totaleDovutoHtml.= '<td class="text_left color_red" colspan=13><b>TOTALE DOVUTO</b></td><td><br></td></tr>';
$totaleDovutoHtml.= '<tr><td></td><td class="text_center">';
$totaleDovutoHtml.= '<div class="form-group">';
$totaleDovutoHtml.= '<div style="float: left; width: 70%;">';
$totaleDovutoHtml.= '<input style="background-color: #FFC7C7;border: 1px solid black;" class="form-control resize validateCustom vld_Custom_d vld_Custom_r" readonly value="'.number_format($tot_dovuto,2,",",".").'">';
$totaleDovutoHtml.= '</div>';
$totaleDovutoHtml.= '<label class="resize control-label" style="text-align: left; float: right; width: 30%;">&nbsp;&euro;</label>';
$totaleDovutoHtml.= '</div>';
$totaleDovutoHtml.= '</td></tr>';
$totaleDovutoHtml.= '<tr><td class="text_left" colspan=15><hr></td></tr>';
?>

<script>
    var num_pagamenti = "";
    //F3
    switchMenuImg("F3");
    F3_button = function(){
        verifica = control_scorpori();
        if(verifica===false){
            return false;
        }

        control = submit_buttons('Update');
        if(control && validateForm())
            $("#btnSub").trigger("click");
    }

    // //F4
    // switchMenuImg("F4");
    // F4_button = function(){
    //     control = submit_buttons('Delete');
    //     if(control)
    //         $("#form_scorpori").submit();
    // }

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
    var a_scorpori = <?php echo json_encode($a_order); ?>;
    console.log(a_scorpori);

function control_scorpori(){
	for(var i = 0; i<num_pagamenti;i++){
		verifica = control_somma_scorpori(i);
		if(verifica===false){
			return false;
		}
	}
}

function pulisci_scorpori(value){
    for(var i=0;i<a_scorpori.length;i++){
        $('#split'+a_scorpori[i]['split_number']+'_'+value).val('0,00');
    }
}

function control_somma_scorpori(value){
    somma = 0;
    for(var i=0;i<a_scorpori.length;i++){
        somma+=parseNumber($('#split'+a_scorpori[i]['split_number']+'_'+value).val());
    }

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

/*$('#form_scorpori').ajaxForm(

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

});*/

$("#submit_click").click( F3_button );

$("#delete_click").click( F4_button );


});

</script>

<form id=form_scorpori class="form-horizontal validate" name=form_scorpori action="scorporo_pagamento_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=p value=<?php echo $p; ?> >
<input type=hidden name=partita value=<?php echo $partita_ID; ?> >
<input name=params_id  type=hidden	value="<?php echo $a_params['id']; ?>">


<br>
<table class="text_center table_interna" cellspacing=0 border=0 >
    <?php echo $prevPaymentHtml; ?>
    <?php echo $attiHtml; ?>
    <?php echo $pignoHtml; ?>
    <?php echo $totaliHtml; ?>
    <?php echo $totaleDovutoHtml; ?>
</table>
    <script>var num_pagamenti = <?php echo $count; ?>;</script>
<table>

  <div class="form-group">
  	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
  </div>

</form>

<?php include(INC."/footer.php"); ?>
