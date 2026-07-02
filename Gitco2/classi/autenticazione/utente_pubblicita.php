<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

register_globals();

?>
<html>
<head>
<title>Verifica Nome Utente e Password - </title>
<meta http-equiv="Content-Type" content="text/html; charset = utf-8">
<LINK rel=StyleSheet href="/gitco2/CSS/stili.css" type="text/css" media=screen>
</head>
<body>

<?php
$oggi = date("Y-m-d");				
$ora = date('H:i:s');

$query = "select * from autenticazione where User='".$user."' and Password='".md5($pass)."'";
$risposta = mysql_query($query);
$autenticazione = mysql_fetch_assoc($risposta);

if(mysql_num_rows($risposta)==1)
{
	session_unset('count_theip');

	$_SESSION['username'] = $user;
	$_SESSION['password'] = md5($pass);
    $_SESSION['aut_tipo'] = $autenticazione['Tipo'];
    $_SESSION['aut_progr'] = $autenticazione['ID'];
    $_SESSION['CC_User'] = $autenticazione['CC_User'];
    
    if($autenticazione['CC_User']!='****' && $autenticazione['CC_User']!='***+')
    {
    	$c = $autenticazione['CC_User'];
    }
    
    //controllo sulla scadenza della password
    $query = "select Data from autenticazione where ID = '".$_SESSION['aut_progr']."'";
    $data = single_answer_query($query);
    $data = from_mysql_date($data);
    $oggi = date("d/m/Y");
    $anno = substr($oggi,6);
    $giorni = calcola_giorni($data,$oggi);
    $mesi = $giorni/30;
    
    if($mesi>3)
    {
           echo"<script>alert('Password scaduta. Registrarsi nuovamente. La data di inizio della Password scaduta è $data');</script>";
           echo"<script>top.location.href='../../index_pubblicita.php';</script>";
    }
    else
    {

           echo <<< END
           <script>
           window.location.href="/gitco2/menu/scelta_CC_e_anno_pubblicita.php?c=$c";
           </script>
END;

    }
}
/*elseif($conta>1)
{
    echo "<script>alert('Impossibile entrare due volte per uno stesso Utente, se questi è ancora nel programma.');history.back();</script>";
} */
else
{
	if (isset($_SESSION['count_theip']))	{	$_SESSION['count_theip']++;		}
	else									{	$_SESSION['count_theip'] = 1;	}
	
	if ($_SESSION['count_theip'] > 3)
        echo "<script>alert('Nome utente o Password errati. Numero massimo di tentativi esaurito'); self.close();</script>";
	else
		echo "<script>alert('Nome utente o Password errati.'); history.back();</script>";

}
?>
</body>
</html>