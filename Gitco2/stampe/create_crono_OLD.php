<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_print.php";
include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_Stampe.php";

$cls_stampe = new cls_Stampe();

$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$filter = array();
$filter['city'] = $c;

$filter['PrinterId'] = $cls_help->getVar('sort');
$filter['PrintTypeId'] = $cls_help->getVar('PrintTypeId');
$filter['officialType'] = $cls_help->getVar('officialType');
$filter['docType'] = $cls_help->getVar('docType');


$filter['printType'] = $cls_help->getVar('printType');
$filter['printStatus'] = $cls_help->getVar('printStatus');
$filter['finalDate'] = $cls_help->toDbDate($cls_help->getVar('finalDate'));
$filter['from_elaborationDate'] = $cls_help->getVar('from_elaborationDate');
$filter['to_elaborationDate'] = $cls_help->getVar('to_elaborationDate');
$filter['from_printDate'] = $cls_help->getVar('from_printDate');
$filter['to_printDate'] = $cls_help->getVar('to_printDate');
$filter['from_notificationDate'] = $cls_help->getVar('from_notificationDate');
$filter['to_notificationDate'] = $cls_help->getVar('to_notificationDate');
$filter['from_flowDate'] = $cls_help->getVar('from_flowDate');
$filter['to_flowDate'] = $cls_help->getVar('to_flowDate');

$filter['type'] = $cls_help->getVar('type');
$filter['from_surname'] = $cls_help->getVar('from_surname');
$filter['to_surname'] = $cls_help->getVar('to_surname');
$filter['from_name'] = $cls_help->getVar('from_name');
$filter['to_name'] = $cls_help->getVar('to_name');
$filter['from_taxRecord'] = $cls_help->getVar('from_taxRecord');
$filter['to_taxRecord'] = $cls_help->getVar('to_taxRecord');
$filter['from_taxYear'] = $cls_help->getVar('from_taxYear');
$filter['to_taxYear'] = $cls_help->getVar('to_taxYear');
$filter['taxType'] = $cls_help->getVar('taxType');
$filter['taxStopFlag'] = $cls_help->getVar('taxStopFlag');
$filter['sort'] = $cls_help->getVar('sort');


$Tipo = $cls_help->getVar('type');
//var_dump($Tipo);
$cls_print = new cls_print("html",$filter['type']);

$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($filter['docType'],$filter['PrintTypeId']);

$where = $cls_print->getWhereFromFilters($filter,null,$Tipo);
$order = $cls_print->getOrder($filter['sort'],$Tipo);
//var_dump($cls_ruolo->a_docDetails);
$query = "";
$Table = "";
switch($Tipo)
{
    case "pigno":
        $orderBy = " ORDER BY ".$order;

        $query = "SELECT * FROM v_pignoramento ";//JOIN v_partita ON v_partita.Partita_ID = v_pignoramento.Partita_ID JOIN document_type ON v_pignoramento.DocumentTypeId = document_type.Id
        $query.= "WHERE 1=1 ";
        if($filter['city']==$c)
            $query.= "AND v_pignoramento.CC='".$c."' ";
        $query.= "AND ".$where." AND v_pignoramento.DocumentTypeId=".$cls_ruolo->a_docDetails['DocumentTypeId']." ".$orderBy;

        //echo $query;
        $Table = "pignoramento_generale";
        break;
    case "atto":
        $query = "SELECT * FROM v_atti ";
        $query.= "WHERE 1=1 ";
        if($filter['city']==$c)
            $query.= "AND CC='".$c."' ";
        $query.= "AND ".$where." AND DocumentTypeId=".$cls_ruolo->a_docDetails['DocumentTypeId']." ORDER BY ".$order;

        $Table = "atto";
        break;
    default: die;
}



//echo $query;
$a_results = $cls_db->getResults($cls_db->SelectQuery($query));
//var_dump($a_results);

$a_ID = null;
for($i=0;$i<count($a_results);$i++) {
    switch($Tipo)
    {
        case "atto": $a_ID[] = $a_results[$i]['Atto_ID']; break;
        case "pigno": $a_ID[] = $a_results[$i]['ID']; break;
    }

}

$atto = array();
if($a_ID != null)
    for($i=0;$i<count($a_ID);$i++)
    {
        switch($Tipo)
        {
            case "atto":
                $query = "SELECT * FROM atto WHERE ID = ".$a_ID[$i]." AND CC = '".$c."'";
                $atto[$i] = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),$Table);
                break;
            case "pigno":
                $query = "SELECT * FROM pignoramento_generale as A JOIN v_partita as B ON B.Partita_ID = A.Partita_ID WHERE A.ID = ".$a_ID[$i]." AND A.CC = '".$c."'";
                $atto[$i] = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),$Table);
                break;
        }
    }
//$cls_help->alert(count($atto));
$crono = $cls_stampe->ultimo_id(date('Y'),$c);

$tipo_atto =$cls_ruolo->a_docDetails['type'];
$titolo_pag = "Cronologici atti";
if($tipo_atto=="Ingiunzione")
    $titolo_pag = "Cronologici ingiunzioni";
else if($tipo_atto=="avvisoIntimazione")
    $titolo_pag = "Cronologici avvisi di intimazione ad adempiere";
else if($tipo_atto=="SOLL_PRE")
    $titolo_pag = "Cronologici solleciti pre ingiunzione";
else if($tipo_atto=="AV_MORA")
    $titolo_pag = "Cronologici solleciti pre ingiunzione";
else if($tipo_atto=="veicolo")
    $titolo_pag = "Cronologici pignoramento veicolo";
else if($tipo_atto=="banca")
    $titolo_pag = "Cronologici pignoramento presso banca";

?>


<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
    <tr>
        <td><font class="titolo font16 under_decor"><?php echo $titolo_pag; ?></font></td>
    </tr>
</table>
<table class="table_interna text_center" border="0" cellspacing=0 >
    <tr>
        <td class="text_left width35"><span class="titolo font15">PROTOCOLLO</span></td>
        <td class="text_left width25">
            <select id="tipoParProtocollo" name="tipoParProtocollo" onchange="checkProto();control_crono();" disabled>
                <option value="">Assente</option>
                <option value="progr">Progressivo</option>
                <option value="fisso">Fisso</option>
            </select>
        </td>
        <td class="text_left width40">

        </td>
    </tr>
    <tr class="rowProto" style="display:none;">
        <td class="text_left">Data Protocollo</td>
        <td class="text_left">
            <input class="text_center picker" onchange="control_crono();" id="dataParProtocollo" name="dataParProtocollo" value="" size="6">
        </td>
        <td class="text_left"></td>
    </tr>
    <tr class="rowProto" style="display:none;">
        <td class="text_left"><span id="testoProtocollo"></span></td>
        <td class="text_left">
            <input class="text_right" onchange="control_crono();" id="numeroParProtocollo" name="numeroParProtocollo" value="" size="3">
        </td>
        <td class="text_left"></td>
    </tr>
</table>
<br>
<form id=form_cronologici name=form_cronologici action="cronologici_salva.php" method=post>
    <input name=invia_submit  id=invia_submit	type=hidden	value="" >

    <input type=hidden name=c value="<?php echo $c; ?>" >
    <input type=hidden name=a value="<?php echo $a; ?>" >
    <input type=hidden name=type value="<?php echo $Tipo; ?>" >
    <input type=hidden name=tipo_atto value="<?php echo $filter['docType']; ?>" >

    <?php
    //$cls_help->alert(count($atto));
    if(count($atto)!=0)
    {?>


        <table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">
            <tr class="text_left riga_dispari" style="height:30px;" >

                <td class="width1"><br></td>
                <td class="text_left width20"><b>Atto</b></td>
                <td class="width1"><br></td>
                <td class="text_center width10"><b>Totale (&euro;)</b></td>
                <td class="width1"><br></td>
                <td class="text_left width20"><b>Utente</b></td>
                <td class="width1"><br></td>
                <td class="width25 text_center"><b>Prot. / Data</b></td>
                <td class="width1"><br></td>
                <td class="width20 text_center"><b>Crono / Anno</b></td>
            </tr>

            <?php
            //$query = "SELECT * FROM forma_giuridica_societa WHERE 1=2";
            //$forma = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");//new forma_giuridica();
            $query = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****'";
            $array_forma = $cls_db->getResults($cls_db->ExecuteQuery($query));//$cls_stampe->array_completo($forma);

            for($i=0; $i<count($atto); $i++)
            {
                $query = "SELECT * FROM partita_tributi WHERE ID = '".$atto[$i]->Partita_ID."' AND CC = '".$c."'";
                $partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");//new partita($atto[$i]->Partita_ID, $c);

                $query = "SELECT * FROM utente WHERE ID = '".$partita->Utente_ID."' AND CC_Comune = '".$c."'";
                $utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");
                $forma_descr = "";

                if($utente->Forma_Giuridica!='')
                {
                    $index_value = $utente->Forma_Giuridica;
                    if(isset($array_forma[$index_value]['Sigla']))
                        $forma_descr = $array_forma[$index_value]['Sigla'];
                }

                switch($Tipo)
                {
                    case "atto":
                        if($atto[$i]->Atto=="Avviso di intimazione ad adempiere")
                            $nomeAtto = "Avviso";
                        else
                            $nomeAtto = $atto[$i]->Atto;
                        break;
                    case "pigno":
                        $query = "SELECT Description FROM document_type WHERE Id = ".$atto[$i]->DocumentTypeId;
                        $nomeAtto = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"document_type")["Description"];
                        break;
                }


                $nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;

                $y = $i;

                if ($y++ % 2)
                {$stile_riga = 'class="riga_dispari text_left pheight30"'	;	}
                else
                {$stile_riga = 'class="riga_pari text_left pheight30"'	;	}

                ?>
                <tr <?php echo $stile_riga; ?>>
                    <td class="width1"><input type=hidden id="id_<?php echo $i; ?>" name="id[]" value="<?php echo $atto[$i]->ID; ?>" ></td>
                    <td class="text_left width20"><?php echo $nomeAtto; ?></td>
                    <td class="width1"><br></td>
                    <td class="text_center width10"><?php echo number_format($atto[$i]->Totale_Dovuto,2,",",""); ?></td>
                    <td class="width1"><br></td>
                    <td class="text_left width20"><?php echo $nome_utente; ?></td>
                    <td class="width1"><br></td>
                    <td class="text_center width25" >
                        <input id="proto_<?php echo $i; ?>" name="proto[<?php echo $i; ?>]"  value="" size=3>
                        /
                        <input id="dataProto_<?php echo $i; ?>" name="dataProto[<?php echo $i; ?>]"  value="" size=6>
                    </td>
                    <td class="width1"><br></td>
                    <td class="text_center width20" >
                        <input class="text_right" id="crono_<?php echo $i; ?>" name="crono[<?php echo $i; ?>]"  value="<?php echo $crono; ?>" size=3>
                        /
                        <input class="text_right sfondo_readonly" id="anno_<?php echo $i; ?>" name="anno[<?php echo $i; ?>]" value="<?php echo date('Y'); ?>" size=3 readonly>
                    </td>
                </tr>
                <tr <?php echo $stile_riga; ?>>
                    <td class="width1"><br></td>
                    <td class="text_left" colspan=7><font class="font14 titolo"><?php echo $atto[$i]->Info_Cartella; ?></font></td>
                    <td class="width1"><br></td>
                    <td class="text_center width20">
                        <input type="checkbox" id="escludi_<?php echo $i; ?>" name="escludi[]" value=si onclick="control_crono();" > <font class="font14 titolo">ESCLUDI ATTO</font>
                    </td>
                </tr>

                <?php $crono++;}?>
        </table>

    <?php }?>

</form>

<script>

    switchMenuImg("F3");
    F3_button = function()
    {
        control = submit_buttons('Update');
        if(control)
            $('#form_cronologici').submit();
    }


    function control_crono()
    {
        ultimo_crono = parseInt('<?php echo $crono; ?>');
        ultimo_proto = $("#numeroParProtocollo").val();
        data_proto = $("#dataParProtocollo").val();
        tipoProto = $("#tipoParProtocollo").val();

        for(var j=0;j<<?php echo isset($a_ID)?count($a_ID):0; ?>;j++)
        {
            if($('#escludi_'+j).prop('checked')==true)
            {
                $('#proto_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
                $('#dataProto_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
                $('#crono_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
                continue;
            }

            $('#crono_'+j).prop('readonly', false).val(ultimo_crono).removeClass('sfondo_grigio');
            ultimo_crono++;

            if(tipoProto!="" && data_proto!="" && ultimo_proto!=""){

                $('#proto_'+j).val(ultimo_proto).prop('readonly', false).removeClass('sfondo_grigio');
                $('#dataProto_'+j).val(data_proto).prop('readonly', false).removeClass('sfondo_grigio');

                if(tipoProto=="progr")
                    ultimo_proto++;
            }
            else{
                $('#proto_'+j).val("").prop('readonly', false).removeClass('sfondo_grigio');
                $('#dataProto_'+j).val("").prop('readonly', false).removeClass('sfondo_grigio');
            }
        }
    }

    $( function() {

        $( ".picker" ).datepicker();

    } );

    function checkProto(){
        tipoProto = $("#tipoParProtocollo").val();
        switch(tipoProto){

            case "progr":
                $('.rowProto').show();
                $("#dataParProtocollo").val("");
                $("#numeroParProtocollo").val("");
                $("#testoProtocollo").text("A partire dal Protocollo numero");
                break;
            case "fisso":
                $('.rowProto').show();
                $("#dataParProtocollo").val("");
                $("#numeroParProtocollo").val("");
                $("#testoProtocollo").text("Numero di Protocollo fisso");
                break;
            default:
                $("#dataParProtocollo").val("");
                $("#numeroParProtocollo").val("");
                $("#testoProtocollo").text("");
                $('.rowProto').hide();
                break;
        }
    }

    $(document).ready(function(){

        //alert("La pagina e' stata caricata!");
        $("#tipoParProtocollo").prop("disabled",false);

    });
</script>

<?php include(INC."/footer.php"); ?>