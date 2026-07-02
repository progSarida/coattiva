<?php

include("_path.php");
include("_parameter.php");

include(INC."/html_header.php");

$cls_db = new cls_db();
$cls_html = new cls_html();
$queryCities = "SELECT EG.* FROM enti_gestiti EG LEFT JOIN anni_gestiti A ON A.CC_Anno=EG.CC WHERE A.ID is not null GROUP BY EG.ID";

$selectCity = null;
$selectYear = $a;

if($_SESSION['CC_User']!="****" && $_SESSION['CC_User']!="***+"){
    $c = $_SESSION['CC_User'];
    $queryCities.= " WHERE CC='".$_SESSION['CC_User']."' ";
}


if($_SESSION['aut_tipo']>2 && $_SESSION['aut_tipo']<20){
    $queryCities.= " WHERE Autorizzazione=".$_SESSION['aut_tipo']." ";
}

if($c!=""){
    $queryYears = "SELECT * FROM anni_gestiti WHERE CC_Anno = '".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC";
    $a_years = $cls_db->getResults( $cls_db->SelectQuery($queryYears) );

    $optionsCityYears = $cls_html->optionsFromArray($a_years, "Anno", $selectYear );
    $selectCity = $c;
}
else
    $optionsCityYears = "";

$queryCities.= " ORDER BY Denominazione";
if($_SESSION['username']=="user4")
    echo $queryCities;
$a_enti = $cls_db->getResults( $cls_db->SelectQuery($queryCities) );
//print_r($a_enti);
$a_selection = array("value"=>"CC","firstOpt"=>1,"selected"=>$selectCity,"text"=>array("[Denominazione]"," - ","[CC]","  ","[Descrizione]"));
$optionsCities = $cls_html->getOptions($a_enti,$a_selection);

//$adminCity = $obj_ente->Denominazione." [".$obj_ente->CC."]";

$layout = "<script>";
$layout.= "$('select:first').focus();";

if($c=="")
    $layout.= "$('#select_years').hide();";
if($c!="" && $a!="")
    $layout.= "$('#confirmButton').prop('disabled',false).addClass('button_azzurro');";


$layout.= "</script>";
?>

    <table class="table_interna text_center">

        <tr class="text_center">
            <td class="width23 pheight75">

            </td>
            <td class="text_center pheight75">
                <font class="titolo font22 under_decor">Selezione Ente/Anno</font>
            </td>
            <td class="width23 pheight75">

            </td>
        </tr>

        <tr class="text_center">
            <td class="pheight100" colspan=3>
                <select id='select_cities' name='select_cities' onchange="changeAdminCity();">
                    <option></option>
                    <?php echo $optionsCities; ?>
                </select>
            </td>
        </tr>
        <tr class="text_center" >
            <td class="pheight100" colspan=3>
                <select id='select_years' name='select_years' onchange="changeAdminYear();">
                    <option></option>
                    <?php echo $optionsCityYears; ?>
                </select>
            </td>
        </tr>
        <tr class="text_center" >
            <td class="pheight100" colspan=3>
                <input type="button" id="confirmButton" value="CONFERMA" onclick="openLocation('home');" disabled>
            </td>
        </tr>
    </table>

<?php

if($_SESSION['aut_tipo']==1){
    $a_allUploadFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) AS TOT, flows.CityId, enti_gestiti.Denominazione AS Ente FROM `flows` JOIN enti_gestiti ON enti_gestiti.CC=flows.CityId WHERE flows.CreationDate is not null AND flows.UploadDate is null AND DATEDIFF(CURDATE(),flows.CreationDate)>3 GROUP BY flows.CityId, enti_gestiti.Denominazione"));
    $a_allProcessingFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) AS TOT, flows.CityId, enti_gestiti.Denominazione AS Ente FROM `flows` JOIN enti_gestiti ON enti_gestiti.CC=flows.CityId WHERE flows.UploadDate is not null AND flows.ProcessingDate is null AND DATEDIFF(CURDATE(),flows.UploadDate)>5 GROUP BY flows.CityId, enti_gestiti.Denominazione"));
    $a_allPaymentFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) AS TOT, flows.CityId, enti_gestiti.Denominazione AS Ente FROM `flows` JOIN enti_gestiti ON enti_gestiti.CC=flows.CityId WHERE flows.ProcessingDate is not null AND flows.PostagePaymentDate is null AND DATEDIFF(CURDATE(),flows.ProcessingDate)>3 GROUP BY flows.CityId, enti_gestiti.Denominazione"));
    $a_allSendFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) AS TOT, flows.CityId, enti_gestiti.Denominazione AS Ente FROM `flows` JOIN enti_gestiti ON enti_gestiti.CC=flows.CityId WHERE flows.PostagePaymentDate is not null AND flows.SendDate is null AND DATEDIFF(CURDATE(),flows.PostagePaymentDate)>3 GROUP BY flows.CityId, enti_gestiti.Denominazione"));

    ?>

    <table class="width95 text_center" border="0">
    <tr class="sfondo_new_gitco">
        <td class="text_center" colspan="5"><span style="color: white;"><b>ANALISI FLUSSI DATI IN USCITA - TUTTI I COMUNI</b></span></td>
    </tr
    <?php
    $countLines = 0;
    for($i=1;$i<5;$i++){
        $value = 0;
        $ect = "";
        $details = array();
        switch($i){
            case 1:
                $label = "Data Upload assente > 3gg";
                for($y=0;$y<count($a_allUploadFlow);$y++){
                    $value+= $a_allUploadFlow[$y]['TOT'];
                    if(isset($a_allUploadFlow[$y]['CityId']))
                        $details[$y] = "<span class=\"titolo\">".$a_allUploadFlow[$y]['Ente']."</span> ".$a_allUploadFlow[$y]['TOT'];

                }

                break;
            case 2:
                $label = "Data Fine Lavorazione assente > 5gg";
                for($y=0;$y<count($a_allProcessingFlow);$y++){
                    $value+= $a_allProcessingFlow[$y]['TOT'];

                    if(isset($a_allProcessingFlow[$y]['CityId']))
                        $details[$y] = "<span class=\"titolo\">".$a_allProcessingFlow[$y]['Ente']."</span> ".$a_allProcessingFlow[$y]['TOT'];

                }

                break;
            case 3:
                $label = "Data Pagamento assente > 3gg";
                for($y=0;$y<count($a_allPaymentFlow);$y++){
                    $value+= $a_allPaymentFlow[$y]['TOT'];

                    if(isset($a_allPaymentFlow[$y]['CityId']))
                        $details[$y] = "<span class=\"titolo\">".$a_allPaymentFlow[$y]['Ente']."</span> ".$a_allPaymentFlow[$y]['TOT'];
                }

                break;
            case 4:
                $label = "Data Consegna assente > 3gg";
                for($y=0;$y<count($a_allSendFlow);$y++){
                    $value+= $a_allSendFlow[$y]['TOT'];

                    if(isset($a_allSendFlow[$y]['CityId']))
                        $details[$y] = "<span class=\"titolo\">".$a_allSendFlow[$y]['Ente']."</span> ".$a_allSendFlow[$y]['TOT'];
                }

            break;

}

$check=true;
$cont = 0;
$class="";
if(isset($details[0])){
    $countLines++;
    while($check===true){

        if($cont!=0){
            $label="";
            $class="";
        }
        else
            $class="riga_dispari";

        ?>
        <tr>
            <td class="text_center width28 <?=$class;?>"><span class="titolo"><?= $label; ?></span></td>
            <?php
            for($j=0;$j<3;$j++){
                if(isset($details[$cont])){
                    $displayDetails = $details[$cont];
                }
                else{
                    $displayDetails = "";
                    $check=false;
                }
                ?>
                <td class="text_left width24"><?=$displayDetails;?></td>
                <?php
                $cont++;
            }
            ?>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td class="text_center" colspan="5"><hr></td>
    </tr>
    <?php
}
}
if($countLines==0){
    ?>
    <tr>
        <td class="text_center" colspan="5"><span class="titolo"><b>Nessun flusso pendente</b></span></td>
    </tr>
    <?php
}
?>
    </table>

<?php
}

echo $layout;
include(INC."/footer.php");
