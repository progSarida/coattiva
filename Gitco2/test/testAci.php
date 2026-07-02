<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
$db = new cls_db();
$query="SELECT * FROM semaforo WHERE Procedure_Type_Id = 6";
$a_semaforo = $db->getArrayLine($db->ExecuteQuery($query));
if(!empty($a_semaforo['Datetime'])){
    $datetime1 = new DateTime($a_semaforo['Datetime']); // 11 October 2013
    $datetime2 = new DateTime(date('Y-m-d H:i:s')); // 13 October 2013

    $interval = $datetime1->diff($datetime2);
    if($interval->i<2){
        $cls_help->alert("Attendere qualche minuto per eseguire la visura. La procedura è occupata da ".$interval->i." minuti!");
        die;
    }
}
$query = "DELETE FROM semaforo WHERE Procedure_Type_Id = 6";
$db->ExecuteQuery($query);

$query = "INSERT INTO semaforo (Procedure_Type_Id, Datetime, User_Id) ".
"VALUES (6, '". date('Y-m-d H:i:s')."', ".$_SESSION['aut_progr'].")";
$db->ExecuteQuery($query);


require CONFIG_ROOT."/_aciServer.php";

echo "<br>".ACICHECK_CMD."<br>";
$check = shell_exec(ACICHECK_CMD);
var_dump($check);
if(strpos($check,ACISERVERIP)){
    shell_exec(ACIVPN_KILL);
    echo "<br>".ACIVPN_KILL."<br>";
    sleep(2);
}

$check = shell_exec(ACICHECK_CMD);
if(strpos($check,ACISERVERIP)===false){
    $check = shell_exec(ACIVPN_CMD);
    echo "<br>".ACIVPN_CMD."<br>";
    var_dump($check);
    sleep(3);

    echo "<br>".ACICHECK_CMD."<br>";
    $check = shell_exec(ACICHECK_CMD);
    var_dump($check);
    if(strpos($check,ACISERVERIP)===false){
        echo "<br>CONNESSIONE FALLITA<br>";
        die;
    }
   
}
shell_exec(ACIVPN_KILL);
echo "<br>".ACIVPN_KILL."<br>";

$query = "DELETE FROM semaforo WHERE Procedure_Type_Id = 6";
$db->ExecuteQuery($query);

include(INC."/footer.php"); 
