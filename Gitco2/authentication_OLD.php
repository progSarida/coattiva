<?php

require "_path.php";

include_once ROOT."/_parameter.php";

include_once CLS."/cls_db.php";
include_once CLS."/cls_help.php";

include_once INC."/html_header.php";

$cls_help = new cls_help();
$user = $cls_help->getVar("user");
$pass = $cls_help->getVar("pass");

$cls_db = new cls_db();
$query = "SELECT * FROM autenticazione WHERE User='".$user."' and Password='".md5($pass)."'";
$results = $cls_db->SelectQuery($query);
$a_auth = $cls_db->getArrayLine($results);

if($results->num_rows==1){
    if(isset($_SESSION['username']))
	    session_unset();

	$_SESSION['username'] = $user;
	$_SESSION['password'] = md5($pass);
    $_SESSION['_path'] = ROOT."/_path.php";

    $_SESSION['aut_progr'] = $a_auth['ID'];
    $_SESSION['aut_tipo'] = $a_auth['Tipo'];
    $_SESSION['CC_User'] = $a_auth['CC_User'];

    if($_SESSION['aut_tipo']==2)
    	$c = $a_auth['CC_User'];
    else
        $c = null;

    $data = $a_auth['Data'];
    $anno = date("Y");

    $regDate = new DateTime($data);
    $today = new DateTime(date("Y-m-d"));
    $difference = $today->diff($regDate);

    if($difference->days>90){
        ?>
          <script>alert("Password scaduta. Registrarsi nuovamente. La data di inizio della Password scaduta e' <?=$data?>");</script>
          <script>location.href='<?=SUPER_WEB_ROOT?>/index.php';</script>
    <?php
    }
    else{
        ?>
        <script>location.href="<?=WEB_ROOT?>/selectCityYear.php?c=<?=$c?>";</script>
    <?php
    }
}
else{
	if(isset($_SESSION['count_theip']))
	    $_SESSION['count_theip']++;
	else
	    $_SESSION['count_theip'] = 1;

	if ($_SESSION['count_theip'] > 3)
        echo "<script>alert('Nome utente o Password errati. Numero massimo di tentativi esaurito'); self.close();</script>";
	else{
        echo "<script>alert('Nome utente o Password errati.');</script>";
        echo "<script>location.href='".SUPER_WEB_ROOT."/index.php';</script>";
    }

}

include INC."/footer.php";

