<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$tipo_protocollo = get_var('prototipo');
$protocollo = get_var('proto');
$cronologico = get_var('crono');
$anno = get_var('anno');
$id = get_var('id');

mysql_query('BEGIN');

$cont = 0;
for($i=0;$i<count($id);$i++)
{
	if($cronologico[$i]!="")
	{
		$atto = new pignoramento($id[$i], $c);
		
		$atto->Anno_Cronologico = $anno[$i];
		$atto->ID_Cronologico = $cronologico[$i];
		$atto->Tipo_Protocollo = $tipo_protocollo[$i];
		$atto->Protocollo = $protocollo[$i];
				
		$control = $atto->Update($id[$i]);
		
		if($control === false)
		{
			echo "ERROR ".mysql_error();
			mysql_query('ROLLBACK');
			die;
		}
		
		$cont++;
	}
}

if($cont>0)
{
	echo "OK";
	mysql_query('COMMIT');
}
else 
	echo "NO";



?>