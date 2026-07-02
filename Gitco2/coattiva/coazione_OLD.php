<?php

    if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");

	//include(INC."/header.php");
	//include(INC."/menu.php");

	include(CLS."/cls_CoazioneUtils.php");
	include_once(CLS."/cls_math.php");
	include(INC."/header.php");
	include(INC."/menu.php");
    $submenuPageNo = "6";
    include(INC."/submenu_partita.php");



	if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	/*$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');*/

	$cls_coazione = new cls_Coazione();
	$cls_math = new cls_math();

	$partita_ID = $cls_help->getVar('partita');

	//$comune = new ente_gestito($c);
	//$nome_comune = $comune->Nome;

	$layout = "<script>";

	//$anni_gestiti = new anni_gestiti($c, null);

	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $cls_coazione->Options_Anni_Veloci($c, "COATTIVA", "coazione");

		if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";
	}

	$layout.= "</script>";

	/*$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];*/

	//$partita = new partita($partita_ID, $c, $a);

	//$query = "SELECT * FROM partita_tributi WHERE ID = '".$partita_ID."' AND CC = '".$c."' AND Anno_Riferimento = '".$a."'";
	//$partita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi");

	$partita = $cls_coazione->GetDataPartita($partita_ID,$c,$a);

	$query = "SELECT ID FROM pignoramento_generale WHERE Partita_ID = '".$partita["ID"]."'";
	$pignoramento_id = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "pignoramento_generale", "Partita_ID = '".$this->ID."'");

	for( $i=0; $i<count($pignoramento_id); $i++)
	{
		$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id[$i]['ID']." AND CC = '".$c."'";
		$partita["Pignoramento"][$i] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));//new pignoramento( $pignoramento_id[$i]['ID'] , $c );
	}

	if(isset($partita["Pignoramento"]))
	    if(count($partita["Pignoramento"])>0)
	        $ultimoAtto = $partita["Pignoramento"][count($partita["Pignoramento"])-1]["ID"];


	$flag_blocco = $partita["Flag_Blocco_Coazione"];
	$ID_Partita = $partita["Comune_ID"];

	$anno_riferimento = $partita["Anno_Riferimento"];

	//$parametri_annuali = new parametri_annuali($c, date("Y-m-d"), $partita["Tipo"]);
	$date = date("Y");

	$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$date."' AND Tipo_Riscossione = '*****'";
	$parametri_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

	$array_presenza_parametri = $cls_coazione->verificaPresenzaParametri($parametri_annuali);
    $importo_min = $parametri_annuali["Importo_Minimo"];


	$utente_ID = $partita["Utente_ID"];
	$query = "SELECT * FROM utente WHERE ID = '".$utente_ID."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
	$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");

	//$utente = new utente($utente_ID,$c);

	$id_utente 				= 	$utente["ID"];
	$genere_utente 			= 	$utente["Genere"];
	$comune_id 				=	$utente["Comune_ID"];
	$cognome_utente 		=	$utente["Cognome"];
	$nome_utente 			=	$utente["Nome"];
	$ditta					=	$utente["Ditta"];

	$tipo = $partita["Tipo"];

	$control_atti = "si";
	$atti = isset($partita["Atto"])?$partita["Atto"]:null;
	if($partita_ID==null)	$control_atti = "";
	else if($atti==null)	$control_atti = "no";



	$ultimo_atto_id = isset($partita["ultimo_atto"])?$partita["ultimo_atto"]:null;
	$ultimo_atto = null;
	//echo "<h1>aa ".$ultimo_atto_id."</h1>";
	if($ultimo_atto_id!=null)
	{
		$query = "SELECT * FROM atto WHERE ID = ".$ultimo_atto_id." AND CC = '".$c."'";
		$ultimo_atto = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");

		$query = "SELECT ID FROM pagamento WHERE Atto_ID = '".$ultimo_atto["ID"]."' AND Partita_ID = '".$ultimo_atto["Partita_ID"]."' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
		$pagamento_id = $cls_db->getResults($cls_db->ExecuteQuery($query));

		for( $i=0; $i<count($pagamento_id); $i++)
		{
			$query = "SELECT * FROM pagamento WHERE ID = '".$pagamento_id[$i]['ID']."' AND CC = '".$c."'";
			$ultimo_atto["Pagamento"][$i] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"pagamento");//new pagamento( $pagamento_id[$i]['ID'] , $c );
		}
	}
	else $ultimo_atto["Atto"] = null;

//var_dump($ultimo_atto_id);


	$control_pignoramento = $cls_coazione->checkProcess("pignoramento", Array("importo_minimo"=>$importo_min), $ultimo_atto);
	//var_dump($control_pignoramento);

	//$ultimo_atto = new atto($ultimo_atto_id, $c);
    $processType = explode(" ",strtolower($ultimo_atto["Atto"]));

	//$control_pignoramento = $ultimo_atto->checkProcess("pignoramento", Array("importo_minimo"=>$importo_min), $ultimo_atto);
	if($control_pignoramento===true)
	    $control_pignoramento = 1;
	else
        $control_pignoramento = 2;
//	alert($control_pignoramento);
	$pignoramento = isset($partita["Pignoramento"])?$partita["Pignoramento"]:null;
	if($partita_ID!=null&&$pignoramento!=null)
		$num_pignoramenti = count($pignoramento);
	else
		$num_pignoramenti = 0;

	$nav =$cls_coazione->GetNavigation($partita["ID"],$c,$a);

	$prev = $nav["prev"];
	$next = $nav["next"];


?>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>
var flag_blocco = "<?php echo $flag_blocco; ?>";
var control_pignoramento = "<?php echo $control_pignoramento; ?>";
var spese_pignoramento = "<?php echo $array_presenza_parametri['Spese_Notifica_Pignoramento']; ?>";
var CAD = "<?php echo $array_presenza_parametri['CAD']; ?>";
var stringa_parametri = "<?php echo $array_presenza_parametri['Stringa']; ?>"


//F5
switchMenuImg("F5");
F5_button = function(){
	location.href="coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
switchMenuImg("F6");
F6_button = function(){
	if("<?php echo $partita_ID; ?>"!= "")
	{
		if("<?php echo $control_atti; ?>"=="si")
		{
			if( modifica == 0 )
			{
				if(flag_blocco=="si")
				{
					alert("Coazione bloccata! Impossibile creare un nuovo pignoramento!");
					return false;
				}
				else if(control_pignoramento != "1")
				{
					alert("Impossibile creare un nuovo pignoramento!");
					return false;
				}
				else if(spese_pignoramento!="ok" || CAD!="ok")
				{
					alert(stringa_parametri);
					return false;
				}
				else if(control_pignoramento == "1")
				{
					crea_pignoramento();
				}
			}
			else
				alert("salvare i dati o annullare prima di procedere");
		}
		else
			alert("non esistono atti per la creazione del pignoramento");
	}
	else
		alert("selezionare una partita per creare un nuovo pignoramento");

    //crea_pignoramento();
}


//F7
//switchMenuImg("F7");
F7_button = function(){
	if( modifica == 0 )
	{
		value = "<?php echo $prev; ?>";
		location.href="coazione.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F8
//switchMenuImg("F8");
F8_button = function(){
	if( modifica == 0 )
	{
		value = "<?php echo $next; ?>";
		location.href="coazione.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}



//PAG GIU
//switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
	{
		location.href="appeal_list.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
//switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href="pagamento_pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F9
function ricerca_F9()
{
	if( modifica == 0 )
	{
		RicercheDaId('utente',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function ruolo (value)
{
	location.href="gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function crea_pignoramento()
{
	top.location.href = "pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&flagInsert=1";
}

function dettagli_pigno(value)
{
	if(flag_blocco=="si")
		alert('ATTENZIONE! Coazione bloccata!');

	top.location.href = "pignoramento.php?partita=<?php echo $partita_ID; ?>&pignoramento="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

<!-- ********** CALENDARIO ********** -->
<script>
$( function() {

	 $( ".picker" ).datepicker();

	 } );

</script>

<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px; ";
	setupPagina += "dialogHeight:" + sHeight + "px; ";
	setupPagina += "dialogLeft:80px; dialogTop:80px;";

	return setupPagina;
}

function callParent(valorediritorno) {
    if(valorediritorno!=null){
        switch(selectParent){
            case "utente":

                if(typeof valorediritorno !== 'string')
                    reopen('obj',valorediritorno);
                else
                    reopen('str',valorediritorno);

                break;
        }
    }
}

function reopen(type, value){
    if(type == 'obj')
        top.location.href="../coazione.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
    else if(type == 'str')
        top.location.href="../gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

var selectParent = "";
var selectRif = "";

function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
	var valorediritorno = 0;
	var strDim = Dim_Alert(600, 300);

	switch(value)
	{
		case "utente":

			strDim = Dim_Alert(800, 400);
			var stringa = "modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			valorediritorno = window.showModalDialog(stringa,"", strDim);

			break;
	}
}

</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

$(document).ready(function(){

$('#cerca_id').ajaxForm(

	        function(value) {
	            var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='NO')
		{
			alert('Codice partita non trovato!');
			annulla();
		}
		else
		{
			top.location.href = "coazione.php?partita="+array_ritorno[0]+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
		}
	});

});

</script>



<?php if($num_pignoramenti!=0)
{?>

<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">
    <input type=hidden name=ultimoAtto id=ultimoAtto value="<?php echo $ultimoAtto; ?>" >
    <input type=hidden name=nomePagina id=nomePagina value="coazione" >

<tr class="text_left riga_dispari" style="height:30px;" >
	<td class="width4"><br></td>
	<td class="width1"><br></td>
	<td class="text_center width8"><b>Crono.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width8"><b>Anno</b></td>
	<td class="width1"><br></td>
	<td class="text_center width25"><b>Tipologia</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Importo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Spese not.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Spese acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width12"><b>Totale</b></td>
	<td class="width1"><br></td>
</tr>

<?php

for($i=0; $i<$num_pignoramenti; $i++)
{
	$y = $i;

	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left"'	;	}

	switch($pignoramento[$i]["Tipo"])
	{
		case "terzi":

			switch($pignoramento[$i]["Tipo_Terzi"])
			{
				case "lavoro":	$tipo_terzi = "Presso datore di lavoro"; 			break;
				case "banca":	$tipo_terzi = "Presso banca"; 						break;
				case "inps":	$tipo_terzi = "Presso ist. previdenziali"; 			break;
				case "altro":	$tipo_terzi = "Presso altri terzi"; 				break;
				default:		$tipo_terzi = "Presso terzi";						break;
			}

			break;

		case "immobiliare":		$tipo_terzi = "Immobiliare";						break;
		case "mobiliare":		$tipo_terzi = "Mobiliare";							break;
		case "fermo":			$tipo_terzi = "Fermo amministrativo";				break;
		case "preav_fermo":		$tipo_terzi = "Preavviso fermo amministrativo";		break;
		case "veicolo":			$tipo_terzi = "Beni mobili registrati";				break;
		default:				$tipo_terzi = "ASSENTE";							break;

	}

?>

		<tr <?php echo $stile_riga; ?>>
			<td class="text_center width4">
			<input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Dettagli Pignoramento" onClick="dettagli_pigno('<?php echo $pignoramento[$i]["ID"]; ?>');return false;"></td>
			<td class="width1"><br></td>
			<td class="text_center width8"><?php echo $pignoramento[$i]["ID_Cronologico"]; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width8"><?php echo $pignoramento[$i]["Anno_Cronologico"]; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width25"><?php echo $tipo_terzi; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($pignoramento[$i]["Importo_Dovuto"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($pignoramento[$i]["Totale_Spese_Notifica"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($pignoramento[$i]["Totale_Spese_Accessorie"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
			<td class="text_center width12"><?php echo number_format($pignoramento[$i]["Totale_Dovuto"],2,",","."); ?> &euro;</td>
			<td class="width1"><br></td>
		</tr>

	<?php }?>
	</table>

<?php }
else if($control_atti=="no")
{?>
	<br>
	<div><b>Nessuna Ingiunzione presente in archivio</b></div>
	<br>
	<div><b>NON E' POSSIBILE CREARE UN NUOVO PIGNORAMENTO</b></div>
<?php }
else if($control_atti=="si")
{?>
	<br>
	<div><b>Nessun Pignoramento presente in archivio</b></div>
	<br>
	<div><b>Per crearne uno nuovo selezionare F6</b></div>
	<br>
	<div>L'inserimento di un Pignoramento Presso Terzi necessita che le anagrafiche dei terzi siano presenti nella relativa pagina.</div>
	<br>
	<div>Se si deve effettuare un pignoramento presso il datore di lavoro ma non si conosce ancora l'esatta denominazione e/o sede dello stesso ma si conosce solo la matricola INPS dell'azienda presso la quale eseguire il pignoramento è possibile registrare il pignoramento presso terzi, presso il datore di lavoro, inserendo provvisoriamente solo questo dato.	</div>
	<br>
	<div>Per integrare ad una registrazione dei dati parziale e' presente una apposita stampa relativa ai pignoramenti presso il datore di lavoro inseriti senza anagrafica del terzo associata.</div>

<?php }
?>


<?php echo $layout; ?>

<?php include(INC."/footer.php"); ?>
