<?php
if(empty($_GET['c']))
    $_GET['c']="D925";
if(empty($_GET['a']))  
    $_GET['a']= 2018;
if(empty($_GET['Partita_ID']))  
    $_GET['Partita_ID']= null;
if(empty($_GET['Comune_ID']))  
    $_GET['Comune_ID']= 1;

if(empty($_GET['Pignoramento_ID']))  
    $_GET['Pignoramento_ID']= null;

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC . "/headerAjax.php");
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_elaboration.php";
$cls_db = new cls_db();

echo "<pre>UPDATE SPESE ACCESSORIE PIGNORAMENTI<hr></pre>";



$query = "SELECT * FROM pignoramento_spese";
if(!empty($_GET['Pignoramento_ID']))
    $query.=" WHERE Pignoramento_ID=".$_GET['Pignoramento_ID'];

$a_speseAccessorie = $cls_db->getResults($cls_db->ExecuteQuery($query));
foreach($a_speseAccessorie as $key => $a_row){
    $a_tot = array(1=>0,2=>0,3=>0);
    for($i=1;$i<=10;$i++){
        if(!empty($a_row['Tipo_Totale_'.$i]))
            $a_tot[$a_row['Tipo_Totale_'.$i]]+=$a_row['Rimborso_'.$i];
    }
    echo "<br><br>";
    var_dump($a_row);
    if($a_tot[2]>0)
        $a_tot[2]+=$a_tot[1];
    if($a_tot[3]>0)
        $a_tot[3]+=$a_tot[2];

    $query = "UPDATE pignoramento_generale SET Spese_Accessorie_1 = ".$a_tot[1].", Spese_Accessorie_2 = ".$a_tot[2].", ".
    "Spese_Accessorie_3 = ".$a_tot[3]." WHERE ID=".$a_row['Pignoramento_ID'];
    $cls_db->ExecuteQuery($query);
    var_dump($a_tot);
}

die;


$a_params = array("Tipo_Riscossione" => $a_data['Tipo_Riscossione']);
$cls_elab->setParams($a_params);
$a_totaliCodiciTributo = $cls_elab->totaliCodiciTributo(explode("*", $a_data['Tipo_Codice']), explode("*", $a_data['Importi_Codici_Tributo']));
echo "<br><br>";
var_dump($a_totaliCodiciTributo);

$BasePagamento = 0;
$sum_imp_tributo = $a_totaliCodiciTributo['BASE_INTERESSI'];
if ($a_data['Tipo_Riscossione'] == 'CDS'){
    $sum_imp_tributo += $a_data['Atto_Spese_Notifica'];
    $BasePagamento = $a_data['TOTALE_PAGAMENTI']-$a_data['Atto_Interessi']-$a_data['Atto_Diritto_Riscossione'];
    if($BasePagamento>0)
        $sum_imp_tributo-= $BasePagamento;
}
else {
    $totaleCheck = $a_totaliCodiciTributo['TOTALE'] + $a_data['Atto_Spese_Notifica'] + $a_data['Atto_Interessi'];
    if ($totaleCheck - $a_data['TOTALE_PAGAMENTI'] < $sum_imp_tributo)
        $sum_imp_tributo = $totaleCheck - $a_data['TOTALE_PAGAMENTI'];
}

$a_params = array(
    "DocumentTypeId" => 22,
    "StartDate" => $a_data['Atto_Data_Calcolo_Interessi'],
    "EndDate" => date('Y-m-d'),

    "PagamentoOriginale" => $a_data['TOTALE_PAGAMENTI'],
    "InteresseOriginale" => $a_data['Atto_Interessi'],
    "DirRiscossioneOriginale" => $a_data['Atto_Diritto_Riscossione'],
    
    "BaseCodiciTributo" => $a_totaliCodiciTributo['BASE_INTERESSI'],
    "BaseSpese" => $a_data['Atto_Spese_Notifica'],
    "BasePagamento" => $BasePagamento,
    "BaseAmount" => $sum_imp_tributo,
);

$a_interessi = array(
    "Interessi" => $cls_elab->calcInterests($a_params),
    "Importo_Atto" => $a_data["Totale_Dovuto_ATTO"]-$a_data['TOTALE_PAGAMENTI']
);
$a_interessi['Debito'] = $a_interessi['Interessi']+$a_interessi['Importo_Atto'];
echo "<br><br>";
var_dump($a_params);
echo "<br><br>";
var_dump($a_interessi);



include(INC . "/footer.php");