<?php
if(empty($_GET['c']))
    $_GET['c']="D925";
if(empty($_GET['a']))  
    $_GET['a']= 2018;

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC . "/headerAjax.php");
include_once CLS . "/cls_db.php";
$cls_db = new cls_db();

$query='SELECT P.Comune_ID, P.CC, SUM(T.Imposta)-IFNULL(T2.Imposta,0) AS TOT_CODICI, A.Importo
FROM atto A
JOIN partita_tributi P ON A.Partita_ID=P.ID
JOIN tributo T ON T.Partita_ID=P.ID AND T.Codice_Tributo!="S_02"
LEFT JOIN tributo T2 ON T2.Partita_ID=P.ID AND T2.Codice_Tributo="S_02"
WHERE A.ID=(SELECT MIN(ID) FROM atto WHERE Partita_ID=P.ID) AND A.Importo>0
GROUP BY A.ID  
HAVING TOT_CODICI!=A.Importo  
ORDER BY `P`.`CC` ASC';

$a_totTributi = $cls_db->getResults($cls_db->ExecuteQuery($query));
foreach($a_totTributi as $key=>$a_totTributo){
    var_dump($a_totTributo);
    echo "<br><br>";
}

die;



include(INC . "/footer.php");