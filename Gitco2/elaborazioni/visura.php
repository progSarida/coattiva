<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once(INC."/headerAjax.php");
include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_DateTimeInLine.php");

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_date = new cls_DateTimeI("IT",false);

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$utente_id = $cls_help->getVar('utente_id');


function alert($message)
{
    echo "<script>alert('".$message."');</script>";
}
?>

<script>
function inizio()
{
	$('#progressbar').progressbar({
		value: false
	});
	$( "#barlabel" ).text("Inizio elaborazione...");
}

function update(value)
{
	$('#progressbar').progressbar({
		value: 100
	});
	if(value=="VPN")
		value="VPN disconnessa. E' necessario connettersi per effettuare la visura.";
	$( "#barlabel" ).text(value);
}
</script>

<div class="row justify-content-md-center " style="margin-bottom: 2%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Elaborazione Visura</span>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <div id=vedi_file></div>
    </div>
</div>

<?php 

$pathZOC = "C:/progra~2/ZOC5/";
$pathfileZOC = "C:/Users/Mirko/Documents/";

$query = "SELECT * FROM utente WHERE ID = '".$utente_id."' AND CC_Comune = '".$c."'";
$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");// new utente($utente_id, $c);

$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'res'";
$utente->Residenza = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");

if($utente->Genere=="D")
{
	$runArg = "DITTACOMUN*";
	
	$ditta = $utente->Ditta;
	$indirizzo = $utente->Residenza;
	
	if(strtoupper($indirizzo->Paese)=="ITALIA" && $indirizzo->Comune!="")
	{
		$runArg.= $ditta."*".$indirizzo->Provincia."*".$indirizzo->Comune;
	}
	else 
	{
		alert('Ditta con sede in stato estero! Visura al momento non gestita.');
		window.close();
	}
	
	$filename = "C:/TEMP/" . $ditta . ".txt";
}
else 
{
	if($utente->Codice_Fiscale!="")
	{
		$runArg = "CODICEFISC*".$utente->Codice_Fiscale;
		
		$filename = "C:/TEMP/".$utente->Codice_Fiscale.".txt";
	}
	else if($utente->Data_Nascita!="0000-00-00" && $utente->Data_Nascita!=null && $utente->Paese_Nascita!="")
	{
		$runArg = "COGNOMNOME*".$utente->Cognome."*".$utente->Nome."*";
		
		$data_nascita = explode("-",$utente->Data_Nascita);
		
		$runArg.= $data_nascita[2]."*".$data_nascita[1]."*".$data_nascita[0]."*";
		if(strtoupper($utente->Paese_Nascita)!="ITALIA")
			$runArg.= "*";
		else
			$runArg.= $utente->Provincia_Nascita."*".$utente->Comune_Nascita;
		
		$filename = "C:/TEMP/".$utente->Cognome.$utente->Nome.".txt";
	}
	else 
	{
		alert('Impossibile effettuare la visura! Dati utente incompleti.');
		window.close();
	}
}

flush();
ob_flush();

echo "<script>inizio();</script>";

set_time_limit(60);

$cmdZOC = $pathZOC."zoc /RUN:".$pathZOC."motor.zrx \"/RUNARG:".$runArg."\"";


flush();
ob_flush();flush();
ob_flush();
exec($cmdZOC);

return;
echo "qui 4";

$fopentxt = fopen($filename , "r");
$testotxt = fread($fopentxt, filesize($filename));
fclose($fopentxt);

if(strpos($testotxt, "VPN DISCONNESSA")!==false)
{
	flush();
	ob_flush();
	
	echo "<script>update('VPN');</script>";
	
	flush();
	ob_flush();flush();
	ob_flush();
	
	unlink($filename);
	
	die;
}


$array_targhe = Array();
$array_info = Array();

$control_riga = 0;
$riga = 0;
$primo_step = 0;
$cont = 0;

$blocchi = explode("\n",$testotxt);
for($i=0;$i<count($blocchi);$i++)
{
	$blocco = $blocchi[$i];
	
	if($primo_step == 0)
		if(substr($blocco, 0,5)=="-----")
			$primo_step = 1;
	
	if($primo_step==1)
	{
		if(substr($blocco, 0,5)!="-----")
		{
			$control_riga = 0;
			$riga++;
		
			$elementi = explode("+++",$blocco);
			for($y=0;$y<count($elementi);$y++)
			{
				$elemento = $elementi[$y];
					
				if($riga>1)
				{
					$targhe = explode("   ",$elemento);
			
					for($k=0;$k<count($targhe);$k++)
					{
						$targa = rtrim($targhe[$k]);
						if($targa!="")
							$array_targhe[$cont][] = $targa;
					}
				}
				else
				{
					if(rtrim($elemento)!="")
						$array_info[$cont][] = rtrim($elemento);
				}
			}
		}
		else
		{
			$riga = 0;
			$cont++;
			// 		if($control_riga == 0)
			// 			$control_riga = 1;
			// 		else
			// 			echo "<br>!!!FINE!!!";
		}
	}
	
}

print_r($array_info);
print_r($array_targhe);


unlink($filename);
	
?>

</html>