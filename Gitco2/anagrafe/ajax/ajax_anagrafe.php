<?php
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	
	include CLASSI . "/comuni.php";
	include CLASSI . "/anagrafe.php";

if (!session_id()) session_start();

$blocco = get_var('blocco');

if ($blocco != null)
{
	$ID = $_POST['ID'];
	$locker = new locker_utente();
	
	switch ($blocco)
	{
		case 'on':
			
			$flag = $locker->lock($ID);
			
			if($flag===true)
			{
				$_SESSION['control_mode'] ='true';
				$_SESSION['id_sblocco'] = $ID;
				$_SESSION['uscita_utente'] = 1;
				echo "OK";
			}
			else
			{
				echo "Busy";
			}			
			
			break;
			
		case 'off':

			$flag = $locker->unlock($ID);

			if($flag===true)
			{
				unset($_SESSION['control_mode']);
				unset($_SESSION['id_sblocco']);
				unset($_SESSION['uscita_utente']);
				echo "OK";
			}
			else
			{
				echo "Failed";
			}
					
			break;			
	}
}

$genere = get_var('genere');

if ($genere != null)
{
	$cognome = $_POST['cognome'];
	$nome = $_POST['nome'];
	$data = to_mysql_date($_POST['data']);
	$CC = $_POST['cc'];
	$CF = $_POST['CF'];
	$ditta = $_POST['ditta'];
	$PI = $_POST['PI'];
	$cc_com = $_POST['cc_com'];
				
	$controllo = check_omonimi($genere, $PI, $ditta, $CF, $nome, $cognome, $CC , $data , $cc_com);
	
	echo $controllo;

}

$codice_catastale = get_var('CC_CF');

if($codice_catastale != null)
{
	$verifica_stato = substr($codice_catastale,0,1);
	if($verifica_stato=="Z")
	{
		$stato_control = new stato_estero($codice_catastale);
		$stato = $stato_control->Nome;
		$comune = "";
	}
	else
	{
		$comune_control = new comune($codice_catastale);
		$stato = "Italia";
		$comune = $comune_control->Nome;
	}
	
	echo $stato."**".$comune;
	
}








?>