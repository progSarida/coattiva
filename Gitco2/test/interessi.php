<?php
if(empty($_GET['c']))
    $_GET['c']="D925";
if(empty($_GET['a']))  
    $_GET['a']= 2018;
if(empty($_GET['Partita_ID']))  
    $_GET['Partita_ID']= null;
if(empty($_GET['Comune_ID']))  
    $_GET['Comune_ID']= 1;

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC . "/headerAjax.php");
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_elaboration.php";
$cls_db = new cls_db();

echo "<pre>TEST INTERESSI PIGNORAMENTI<hr></pre>";


//* QUERY LOCKUP_PERIODS
$query_loc_per = "SELECT * FROM lockup_periods";
$a_lockupPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query_loc_per));

//* QUERY PERIODI INTERESSI
$query_periods =    "SELECT * FROM interessi_tributi WHERE CC = '". $_GET['c'] ."' ORDER BY Data_Inizio";
$a_interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($query_periods));

$a_params = array(
    'Interessi_Tributi' => $a_interessiTributi,
    'Lockup_Periods' => $a_lockupPeriods
);
$cls_elab = new cls_elaboration($a_params);

$query = "SELECT 
P.ID as Partita_ID,
P.CC,
P.Tipo AS Tipo_Riscossione,
A.Totale_Dovuto + A.Diritto_Riscossione_Massimo AS Totale_Dovuto_ATTO,
SUM(PA.Importo) AS TOTALE_PAGAMENTI,
A.Info_Cartella AS Info_Cartella,
A.ID AS Atto_ID,

IFNULL(A.Interessi_Precedenti,0) + IFNULL(A.Interessi,0) AS Atto_Interessi,
IFNULL(A.Spese_Notifica_Precedenti,0) + IFNULL(A.Spese_Notifica,0) + IFNULL(A.CAN,0) + IFNULL(A.CAD,0) AS Atto_Spese_Notifica,
IFNULL(A.Diritto_Riscossione_Massimo, 0) AS Atto_Diritto_Riscossione,
A.Data_Calcolo_Interessi AS Atto_Data_Calcolo_Interessi, 
A.Data_Decorrenza_Interessi AS Atto_Data_Decorrenza_Interessi,

V.ID AS Veicolo_ID,
V.Data_Visura,
V.SerieTarga AS Tipo_Veicolo, V.Targa AS Targa_Veicolo, V.Data_Visura, TRIM(V.Telaio) AS Telaio_Veicolo, 
TRIM(V.Fabbrica) AS Fabbrica_Veicolo, TRIM(V.Tipo) AS Modello_Veicolo, TRIM(V.Serie) AS Serie_Veicolo,
V.DataPrimaImmatricolazione AS Data_Immatricolazione,

T.Codici_Tributo, 
T.Importi_Codici_Tributo,
T.Tipo_Codice

FROM partita_tributi AS P
JOIN (
    SELECT TR.Partita_ID,
    GROUP_CONCAT(TR.Codice_Tributo SEPARATOR '*') AS Codici_Tributo, 
	GROUP_CONCAT(TR.Imposta SEPARATOR '*') AS Importi_Codici_Tributo,
	GROUP_CONCAT(CT.Tipo_Codice SEPARATOR '*') AS Tipo_Codice
    FROM tributo AS TR
	JOIN codice_tributo AS CT ON CT.Codice_Tributo = TR.Codice_Tributo
    GROUP BY TR.Partita_ID
) 
AS T ON P.ID=T.Partita_ID
JOIN utente as U ON P.Utente_ID = U.ID
JOIN veicoli V ON V.ID = (SELECT ID FROM veicoli WHERE Utente_ID=P.Utente_ID AND (StatoVeicolo is null OR StatoVeicolo='Targa Attuale') AND Telaio is not null GROUP BY Utente_ID HAVING MAX(DataPrimaImmatricolazione))
JOIN atto AS A ON A.ID=(SELECT MAX(ID) FROM atto AS A2 WHERE A2.Partita_ID = P.ID AND A2.DocumentTypeId!=3 AND A2.DocumentTypeId!=11 AND A2.Data_Notifica IS NOT NULL)
LEFT JOIN pagamento AS PA on P.ID = PA.Partita_ID AND PA.DocumentTypeId is not null
WHERE P.CC = '" . $_GET['c']."' AND P.Comune_ID = " . $_GET['Comune_ID']."
GROUP BY P.ID";

$a_data = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
echo "<br><br>";
var_dump($a_data);

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