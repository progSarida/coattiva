<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include CLASSI . "/targhe_estere.php";
include CLASSI . "/targhe_estere_utenti.php";
include CLASSI . "/targhe_estere_pagamenti.php";

include CLASSI . "/comuni.php";

$c = get_var('c');
$a = get_var('a');
   
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

//alertAllGlobalVariables();

$linkFisso = "?c=$c&a=$a";

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Keep the http-equiv meta tag for IE8 -->
<meta http-equiv="X-UA-Compatible" content="IE=8" />	
<title>GITCO - Menu Principale -</title>
<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<script type="text/javascript" src="/gitco2/librerie/js/JQuery.js"></script>
<script>

function privacy()
{
    window.open('doc_privacy.html','new_win1','width=700, height=500,top=130 left=50, scrollbars=yes,menubar=yes');
}

function chiusura()
{
	location.href="../../index_estere.php";
}

</script>

<script>

//F11
var fn = function (e)
{
	if (!e)
	{
		e = window.event;
	}

	var keycode = e.keyCode;
	if (e.which)
	{
		keycode = e.which;
	}
    
	if (122 == keycode)
	{
		controlScreen();
	}
};

document.onkeyup = fn;

$(document).ready(function(){
	
	controlScreen();
	
});

function controlScreen()
{
	if (!window.screenTop && !window.screenY) {
	    //full web browser
	    $('#verifica_full').text('');
	}
	else
	{
		$('#verifica_full').text("Se si desidera la visualizzazione a schermo intero è necessario selezionarla prima di accedere alle pagine del programma cliccando F11. Successivamente questa funzione sara' disabilitata a favore dello strumento di aiuto per l'utente (HELP)");
	}
}


//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{     
	
}

//F4
function cancella_form() 
{     
	
}

//F5

function annulla ()
{
	var stringaLink = "home_targhe_estere.php?";
	stringaLink += "c=" + "<?php echo $c?>";
	stringaLink += "&a=" + "<?php echo $a?>";
	location.href = stringaLink;
}


//F6
function nuovo_F6()
{

}

//F7-F8
function cambia_pag(value)
{

}

//PAG GIU
function pag_prec()
{

}

//PAG SU
function pag_suc()
{

}

//F9
function ricerca_F9()
{
	
}

//F10
function stampa_F10()
{
return true;
}

function cambiocomune()
{
	var strLink = "home_targhe_estere.php?";
	strLink += "c=" + $("#sceglicomune").val();
	strLink += "&a=" + $("#sceglianno").val();

	location.href = strLink;
}

//F11-F12 sono nel menu'
</script>

</head>
<body class="sfondo_new_gitco">
<?php

$where = $_SESSION['aut_progr'];
$autorizzazione = $_SESSION['aut_tipo'];

$comune = new ente_gestito($c);
$nome_comune = $comune->Nome;
$comune->dim_stemma_1;

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."] - ".$a);
$nome_user = "Operatore: ".$_SESSION['username'];

// $stemma_1 = $comune->Stemma_1;
// $dim_1 = $comune->dim_stemma_1;
// $stemma_2 = $comune->Stemma_2;
// $dim_2 = $comune->dim_stemma_2;

// if($dim_1[0]>=$dim_1[1])
// {
// 	$rapporto = $dim_1[1]/100;
// 	$largh_1 = 100;
// 	$altez_1 = $dim_1[1]/$rapporto;
// }
// else
// {
// 	$rapporto = $dim_1[1]/100;
// 	$altez_1 = 100;
// 	$largh_1 = $dim_1[0]/$rapporto;
// }

// if($dim_2[0]>=$dim_2[1])
// {
// 	$rapporto = $dim_2[0]/100;
// 	$largh_2 = 100;
// 	$altez_2 = $dim_2[1]/$rapporto;
// }
// else
// {
// 	$rapporto = $dim_2[1]/100;
// 	$altez_2 = 100;
// 	$largh_2 = $dim_2[0]/$rapporto;
// }


?>
<!--   <table class="table_azzurra text_center" style="height:8%;"> -->
<!-- 	<tr> -->
<!-- 		<td width="23%" align="center"> -->
<!-- 		<img src="/gitco2/immagini/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0"> -->
<!-- 		</td> -->
<!-- 		<td align="center"><font class="titolo font24" >Gestione Integrata Tributi Comunali</font></td> -->
<!-- 		<td width=23% align="center"> -->
<!-- 		<img src="/gitco2/immagini/sarida_logo_medium.png" alt="Logo dell'Azienda" border="0"> -->
<!-- 		</td> -->
<!-- 	</tr> -->
<!-- </table> -->

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<!-- <td class="text_left"><font class="titolo font22" ><?php echo $nome_comune ?></font></td> -->
		<td class="text_left"><font class="comune" ><?php echo ElencoEsteriComuni($c, $a, $autorizzazione); ?> Anno <?php $annoTrovato = ElencoAnni("TARGHEESTERE", $c, $a); ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
<tr style="height:15%;" valign="top">
	<td>
	
	<?php include TARGHEESTERE . '/menu/menu_targheestere.php'; ?>
	
		<table class="table_interna text_center">
			<tr>
				<td align=center width=7%>
					<a onMouseover="title='Modifica'" href="#" onClick="" >
					<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
					</a>
				</td>
				<td align=center width=7% >
					<input type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
				</td>
				<td align=center width=7% >
					<input type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
				</td>
				<td align=center width=7% >
					<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
					<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
					</a>
				</td>
				<td align=center width=7% >	
					<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
					<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
					</a>
				</td>
				<td align=center width=7% >
					<a onMouseover="title='Pagina precedente'" href="#" onclick = "" style="text-decoration: none;">
					<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
					</a>
				</td>
				<td align=center width=7% >
					<a onMouseover="title='Pagina successiva'" href="#" onclick = "" style="text-decoration: none;">
					<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
					</a>
				</td>
				<td width=7% align="center">
		          	<a href="#" onMouseover=" title='Record precedente F7' " onclick="">
		          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
		          	</a>
				</td>
				<td width=7% align="center">
		          	<a href="#" onMouseover=" title='Record successivo F8' " onclick="">
		          	<img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
		          	</a>
		        </td>
		         <td width=11%></td>
		        <td width=7% align="center">
		          	<a href="#" onMouseover="title='Stampa'" onclick="">
		          	<img src="/gitco2/immagini/printF10grey.png" width=50 height=50 border="0" ></a>
		    	</td>
		        <td width=3%></td>
				<td align=center width=7% >
					<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
					<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
					</a>
				</td>
				<td width=2%></td>
				<td width=7%>
					<a onMouseover="title='Home'" href="#" onClick="" style="text-decoration: none;">
					<img src="/gitco2/immagini/homegrey.png" width=60 height=50 border=0>
					</a>
				</td>
			</tr>
		</table>
	
	</td>
</tr>


<?php if ($annoTrovato != "") { ?>

<tr style="height:60%;" valign="middle">
	<td>
		
		
			<table class="width100">
			<tr class="sfondo_new_gitco pheight30">
				<td class="width60">
					<b>Infrazioni</b>
				</td>
				<td class="width40">
					<b>Numero</b>
				</td>
			</tr>
			
			<?php 
			
			$stileriga = "sfondo_grigio";
			$trovatoQualcosa = false;
			$myNotifica = new targhe_estere_notifiche(null);
			$myVerbale = new registro_cronologico_cds(null);
			
			
			$queryTotale = "SELECT Reg_Progr FROM registro_cronologico_cds ";
			$queryTotale .= " WHERE Reg_Comune_Violazione = '$c' ";
			$queryTotale .= " AND Reg_Anno = $a ";
			$resTotale = esegui_query($queryTotale);
			$numeroTotale = numero_risposte_query($resTotale);
				
			if ($numeroTotale != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
			
				?>
							
						<tr class="<?=$stileriga?> pheight30">
							<td>
								<i><b>Infrazioni inserite nel programma per l'anno <?php echo $a ?></b></i>
							</td>
							<td>
								<i><b><?php echo $numeroTotale ?></b></i>
							</td>
						</tr>
							
				<?php 
			}
			
			$queryIdTotale = "SELECT Reg_Progr FROM registro_cronologico_cds ";
			$queryIdTotale .= " WHERE Reg_Comune_Violazione = '$c' ";
			$queryIdTotale .= " AND Reg_Anno = $a ";
			$queryIdTotale .= " AND Reg_Progr_Registro != 0 ";
			$resIdTotale = esegui_query($queryIdTotale);
			$numeroIdTotale = numero_risposte_query($resIdTotale);
			if ($numeroIdTotale != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
						<tr class="<?=$stileriga?> pheight30">
							<td>
								<i><b>Verbali inseriti nel programma per l'anno <?php echo $a ?></b></i>
							</td>
							<td>
								<i><b><?php echo $numeroIdTotale ?></b></i>
							</td>
						</tr>
							
				<?php 
			}
			
			$queryPagTotale = "SELECT DISTINCT Pag_Registro FROM targhe_estere_pagamenti ";
			$queryPagTotale .= " WHERE Pag_Comune_CC = '$c' ";
			$queryPagTotale .= " AND Pag_Anno = $a ";
			//echo $queryPagTotale;
			//$queryPagTotale .= " AND Reg_Progr_Registro != 0 ";
			$resPagTotale = esegui_query($queryPagTotale);
			$numeroPagTotale = numero_risposte_query($resPagTotale);
			if ($numeroPagTotale != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
						<tr class="<?=$stileriga?> pheight30">
							<td>
								<i><b>Verbali pagati (anche parzialmente) per l'anno <?php echo $a ?></b></i>
							</td>
							<td>
								<i><b><?php echo $numeroPagTotale ?></b></i>
							</td>
						</tr>
							
				<?php 
			}
			
			?>
			
			</table>
			
		<?php if ($autorizzazione == 1) { ?>
		
			<table class="width100">
			<tr class="sfondo_new_gitco pheight30">
				<td class="width60">
					<b>Operazioni da svolgere</b>
				</td>
				<td class="width20">
					<b>Numero operazioni</b>
				</td>
				<td class="width20">
					<b>Link</b>
				</td>
			</tr>
			
			<?php 
			
			$stileriga = "sfondo_grigio";
			$trovatoQualcosa = false;
			$myNotifica = new targhe_estere_notifiche(null);
			$myVerbale = new registro_cronologico_cds(null);
			
			$numeroFileDaControllare = 0;
			$cartelladovecercarefile = $_SERVER['DOCUMENT_ROOT'] . $PathFotoEstereTarghe . "/";
			
			// controllo se ci sono file DatiRegistro da controllare
			if (is_dir($cartelladovecercarefile))
			{
				$handle = opendir($cartelladovecercarefile);
				while (($file = readdir($handle)) != false)
				{
					//alert ($file);
					if ($file != '.' && $file != '..' && $file != "Thumbs.db")
					{
						//alert ("analizzo file: " . $file);
						if (substr($file, 0, 13) == "datiRegistro_")
						{
							$numeroFileDaControllare++;
						}
					}
				}
				closedir($handle);
			}
				
			if ($numeroFileDaControllare != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Ci sono infrazioni estere da importare da Gitco
								</td>
								<td>
									<?php echo $numeroFileDaControllare ?>
								</td>
								<td>
									
								</td>
							</tr>
							
				<?php 
			}
			
			
			
			
			
			
			
			$controlloCartella = true;
			$linkFileImport = "";
			switch ($c)
			{
				case "G213":  //  padenghe
					$cartellaComune = "/Importazioni/DatiPadengheTargheEstere/";
					$cartelladovecercarefile = $_SERVER['DOCUMENT_ROOT'] . $cartellaComune;
					$linkFileImport = "/gitco2/targheestere/importazioni/modello_padenghe_targhe_estere.php";
					$linkFileImport .= $linkFisso;
					break;
				case "B509":  //  campiglia
					$cartellaComune = "/Importazioni/DatiCampigliaTargheEstere/";
					$cartelladovecercarefile = $_SERVER['DOCUMENT_ROOT'] . $cartellaComune;
					$linkFileImport = "/gitco2/targheestere/importazioni/modello_campiglia_targhe_estere.php";
					$linkFileImport .= $linkFisso;
					break;
				case "H416":  //  rocca
					$cartellaComune = "/Importazioni/DatiRoccaTargheEstere/";
					$cartelladovecercarefile = $_SERVER['DOCUMENT_ROOT'] . $cartellaComune;
					$linkFileImport = "/gitco2/targheestere/importazioni/modello_rocca_targhe_estere.php";
					$linkFileImport .= $linkFisso;
					break;
				case "C933":  //  como
					$cartellaComune = "/Importazioni/DatiComoTargheEstere/";
					$cartelladovecercarefile = $_SERVER['DOCUMENT_ROOT'] . $cartellaComune;
					$linkFileImport = "/gitco2/targheestere/importazioni/modello_como_targhe_estere.php";
					$linkFileImport .= $linkFisso;
					break;
				default:
					$controlloCartella = false;
					break;
			}
				
			$numeroFoto = 0;
			if ($controlloCartella == true)
			{
				if (is_dir($cartelladovecercarefile))
				{
					$handle = opendir($cartelladovecercarefile);
					while (($file = readdir($handle)) != false)
					{
						if ($file != '.' && $file != '..' && $file != "Thumbs.db")
						{
							$numeroFoto++;
						}
					}
					closedir($handle);
				}
				else
				{
					alert ("Problema cartella importazione errata su questo comune: " . $cartelladovecercarefile);
				}
			}
			else if ($controlloCartella == true)
			{
				alert ("problema cartella importazione errata su questo comune: " . $cartelladovecercarefile);
			}
				
			if ($numeroFoto != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Probabilmente ci sono infrazioni da importare
								</td>
								<td>
									<?php echo $numeroFoto ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkFileImport ?>'; return false;">
								</td>
							</tr>
							
				<?php 
			}
			
			
			
			
			
			
			
			
			
			
			
			$velocePercorso = $PathFotoEstereTarghe . "/" . $c . "/DaAssociare/";
			$percorsoimmagini = $_SERVER['DOCUMENT_ROOT'] . $velocePercorso;
			
			$linkFoto = "/gitco2/targheestere/richiesta_manuale_targhe_estere.php";
			$linkFoto .= $linkFisso;
			
			$numeroFoto = 0;
			if (is_dir($percorsoimmagini))
			{
				$handle = opendir($percorsoimmagini);
				while (($file = readdir($handle)) != false)
				{
					if (!is_dir($percorsoimmagini.$file) && $file != '.' && $file != '..' && $file != "Thumbs.db")
					{
						$numeroFoto++;
					}
				}
				closedir($handle);
			}
			
			if ($numeroFoto != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
			
				?>
							
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Foto da associare
								</td>
								<td>
									<?php echo $numeroFoto ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkFoto ?>'; return false;">
								</td>
							</tr>
							
				<?php 
			}
			
			$veloceDocsPercorso = $PathDocsEstereTarghe . "/" . $c . "/DaAssociare/";
			$percorsodocumenti = $_SERVER['DOCUMENT_ROOT'] . $veloceDocsPercorso;
				
			$linkDocs = "/gitco2/targheestere/richiesta_manuale_targhe_estere.php";
			$linkDocs .= $linkFisso;
				
			$numeroDocs = 0;
			if (is_dir($percorsodocumenti))
			{
				$handle = opendir($percorsodocumenti);
				while (($file = readdir($handle)) != false)
				{
					if (!is_dir($percorsodocumenti.$file) && $file != '.' && $file != '..' && $file != "Thumbs.db")
					{
						$numeroDocs++;
					}
				}
				closedir($handle);
			}
				
			if ($numeroDocs != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Documenti da associare
								</td>
								<td>
									<?php echo $numeroDocs ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkDocs ?>'; return false;">
								</td>
							</tr>
							
				<?php 
			}
			
			$veloceDocsNoleggioPercorso = $PathDocsEstereTarghe . "/" . $c . "/DaAssociareNoleggi/";
			$percorsodocumenti = $_SERVER['DOCUMENT_ROOT'] . $veloceDocsNoleggioPercorso;
				
			$linkDocs = "/gitco2/targheestere/noleggio_targhe_estere.php";
			$linkDocs .= $linkFisso;
				
			$numeroDocs = 0;
			if (is_dir($percorsodocumenti))
			{
				$handle = opendir($percorsodocumenti);
				while (($file = readdir($handle)) != false)
				{
					if ($file != '.' && $file != '..' && $file != "Thumbs.db")
					{
						$numeroDocs++;
					}
				}
				closedir($handle);
			}
				
			if ($numeroDocs != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Documenti da associare a noleggi
								</td>
								<td>
									<?php echo $numeroDocs ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkDocs ?>'; return false;">
								</td>
							</tr>
							
				<?php 
			}
			
			
			
			
			
			
			$queryImportazioni = "SELECT Reg_Progr FROM registro_cronologico_cds ";
			$queryImportazioni .= " WHERE Reg_Stato_Verbale = 'AUTOMATICO' ";
			$queryImportazioni .= " AND Reg_Comune_Violazione = '$c' ";
			$queryImportazioni .= " AND Reg_Ente_Per_Richiesta = 1 ";
			$queryImportazioni .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
			$queryImportazioni .= " AND Reg_Data_Annullamento = '0000-00-00' ";
			$resImportazioni = esegui_query($queryImportazioni);
			$numeroImportazioni = numero_risposte_query($resImportazioni);
			$linkImportazioni = "/gitco2/targheestere/analisi_importati_targhe_estere.php";
			$linkImportazioni .= $linkFisso;
			
			if ($numeroImportazioni != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
				
				?>
				
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Richieste importate da lavorare
								</td>
								<td>
									<?php echo $numeroImportazioni ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkImportazioni ?>'; return false;">
								</td>
							</tr>
							
				<?php 
			}
			
			
			$queryRichiesteDaStampare = $myVerbale->QueryRichiesteDaStampare($c);
			$resDaStampare = esegui_query($queryRichiesteDaStampare);
			$numeroRichiesteDaStampare = numero_risposte_query($resDaStampare);
			$linkRichiesteDaStampare = "/gitco2/targheestere/stampe/pagina_stampa_richieste_estere.php";
			$linkRichiesteDaStampare .= $linkFisso . "&tipo=STAMPA";
			
			if ($numeroRichiesteDaStampare != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Richieste dati da stampare
								</td>
								<td>
									<?php echo $numeroRichiesteDaStampare ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkRichiesteDaStampare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			$queryTrasgressori = "SELECT Reg_Progr FROM registro_cronologico_cds ";
			$queryTrasgressori .= " WHERE (Reg_Stato_Verbale = 'MANUALE' OR Reg_Stato_Verbale = 'AUTOMATICO') ";
			$queryTrasgressori .= " AND Reg_Progr_Registro != 0 ";
			$queryTrasgressori .= " AND Reg_Comune_Violazione = '$c' ";
			$queryTrasgressori .= " AND Reg_Ente_Per_Richiesta != 1 ";
			$queryTrasgressori .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
			$queryTrasgressori .= " AND Reg_Data_Annullamento = '0000-00-00' ";
			$resTrasgressori = esegui_query($queryTrasgressori);
			$numeroTrasgressori = numero_risposte_query($resTrasgressori);
			$linkTrasgressori = "/gitco2/targheestere/ritorno_dati_targhe_estere.php";
			$linkTrasgressori .= $linkFisso;
				
			if ($numeroTrasgressori != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
				
				?>
				
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Richieste in attesa di trasgressore
								</td>
								<td>
									<?php echo $numeroTrasgressori ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkTrasgressori ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			
			$queryNotificheSenzaRegione = $myNotifica->QuerySenzaRegione($c, $a);
			$resNotificheSenzaRegione = esegui_query($queryNotificheSenzaRegione);
			$numeroNotificheSenzaRegione = numero_risposte_query($resNotificheSenzaRegione);
			$linkNotificheSenzaRegione = "/gitco2/targheestere/visiona_preinserimenti_targhe_estere.php";
			$linkNotificheSenzaRegione .= $linkFisso;
			
			if ($numeroNotificheSenzaRegione != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
			
				?>
							
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Notifiche a cui inserire lo Stato di destinazione nell'anno <?php echo $a ?>
								</td>
								<td>
									<?php echo $numeroNotificheSenzaRegione ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkNotificheSenzaRegione ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			$queryDaCreare = $myNotifica->QueryDaCreareVerbali($c, $a);
			$resDaCreare = esegui_query($queryDaCreare);
			$numeroVerbaliDaCreare = numero_risposte_query($resDaCreare);
			$linkVerbaliDaCreare = "/gitco2/targheestere/elaborazioni/crea_verbali_esteri.php";
			$linkVerbaliDaCreare .= $linkFisso;
			
			if ($numeroVerbaliDaCreare != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Verbali da elaborare nell'anno <?php echo $a ?>
								</td>
								<td>
									<?php echo $numeroVerbaliDaCreare ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkVerbaliDaCreare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			
			$queryVerbDaStampare = $myNotifica->QueryDaStampareVerbali($c, $a);
			$resVerbDaStampare = esegui_query($queryVerbDaStampare);
			$numeroVerbaliDaStampare = numero_risposte_query($resVerbDaStampare);
			$linkVerbaliDaStampare = "/gitco2/targheestere/stampe/pagina_stampa_verbali_esteri.php";
			$linkVerbaliDaStampare .= $linkFisso . "&tipo=STAMPA";
			
			if ($numeroVerbaliDaStampare != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Verbali da stampare nell'anno <?php echo $a ?>
								</td>
								<td>
									<?php echo $numeroVerbaliDaStampare ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkVerbaliDaStampare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			
			$queryVerbDaFlussare = $myNotifica->QueryDaFareFlussoVerbali($c, $a);
			$resVerbDaFlussare = esegui_query($queryVerbDaFlussare);
			$numeroVerbaliDaFlussare = numero_risposte_query($resVerbDaFlussare);
			$linkVerbaliDaFlussare = "/gitco2/targheestere/stampe/pagina_stampa_verbali_esteri.php";
			$linkVerbaliDaFlussare .= $linkFisso . "&tipo=STAMPA";
			
			if ($numeroVerbaliDaFlussare != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Verbali di cui creare il flusso nell'anno <?php echo $a ?>
								</td>
								<td>
									<?php echo $numeroVerbaliDaFlussare ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkVerbaliDaFlussare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			$diff = strtotime(date("Y-m-d")) - 60 * 60 * 24 * 365 * 3;  //  3 anni prima
			$dataInfr = date("Y-m-d", $diff);
			$diff = strtotime(date("Y-m-d")) - 60 * 60 * 24 * 182;  //  182 giorni prima
			$dataStam = date("Y-m-d", $diff);
			$diff = strtotime(date("Y-m-d")) - 60 * 60 * 24 * 182;  //  182 giorni prima
			$dataNot = date("Y-m-d", $diff);
			
			$querySolleciti = $myNotifica->QueryDaCreareSolleciti($c, $dataInfr, $dataStam, $dataNot, "");
			$resultSoll = esegui_query($querySolleciti);
			$numeroRigheSolleciti = numero_risposte_query($resultSoll);
			$linkVerbaliDaSollecitare = "/gitco2/targheestere/elaborazioni/crea_solleciti_esteri.php";
			$linkVerbaliDaSollecitare .= $linkFisso . "&tipo=STAMPA";
			
			if ($numeroRigheSolleciti != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Verbali di cui creare il sollecito degli ultimi 3 anni
								</td>
								<td>
									<?php echo $numeroRigheSolleciti ?> <font class="font9">(numero non indicativo)</font>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkVerbaliDaSollecitare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			$queryStampeSolleciti = "SELECT * FROM registro_cronologico_cds, targhe_estere_solleciti ";
			$queryStampeSolleciti .= " WHERE Registro_Provenienza = Reg_Progr AND Reg_Anno = " . $a;
			$queryStampeSolleciti .= " AND Data_Stampa_Sollecito = '0000-00-00' ";
			$resultStampeSoll = esegui_query($queryStampeSolleciti);
			$numeroRigheStampe = numero_risposte_query($resultStampeSoll);
			$linkSollecitiDaStampare = "/gitco2/targheestere/stampe/pagina_stampa_solleciti_esteri.php";
			$linkSollecitiDaStampare .= $linkFisso . "&tipo=STAMPA";
			
			if ($numeroRigheStampe != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Solleciti dell'anno <?php echo $a ?> da stampare
								</td>
								<td>
									<?php echo $numeroRigheStampe ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkSollecitiDaStampare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			$queryFlussiSolleciti = "SELECT * FROM registro_cronologico_cds, targhe_estere_solleciti ";
			$queryFlussiSolleciti .= " WHERE Registro_Provenienza = Reg_Progr AND Reg_Anno = " . $a;
			$queryFlussiSolleciti .= " AND Data_Stampa_Sollecito <> '0000-00-00' ";
			$queryFlussiSolleciti .= " AND Data_Flusso_Sollecito = '0000-00-00' ";
			$resultFlussiSoll = esegui_query($queryFlussiSolleciti);
			$numeroRigheFlussi = numero_risposte_query($resultFlussiSoll);
			$linkSollecitiDaFlussare = "/gitco2/targheestere/stampe/pagina_stampa_solleciti_esteri.php";
			$linkSollecitiDaFlussare .= $linkFisso . "&tipo=STAMPA";
			
			if ($numeroRigheFlussi != 0)
			{
				$trovatoQualcosa = true;
				if ($stileriga == "sfondo_grigio") $stileriga = "riga_dispari";
				else $stileriga = "sfondo_grigio";
					
				?>
										
							<tr class="<?=$stileriga?> pheight30">
								<td>
									Solleciti dell'anno <?php echo $a ?> di cui creare il flusso
								</td>
								<td>
									<?php echo $numeroRigheFlussi ?>
								</td>
								<td>
									<input type="image" class="pwidth20 pheight20" src="/gitco2/immagini/enter.png" onclick="location.href='<?php echo $linkSollecitiDaFlussare ?>'; return false;">
								</td>
							</tr>
										
				<?php 
			}
			
			/*if ($myNotifica->ID != null)
			{
				if ($myVerbale->Reg_Progr_Registro != 0)
				{
					$link = "visiona_verbali_targhe_estere.php?";
					$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
					$link .= "&a=" . $myVerbale->Reg_Anno;
					$link .= "&verbaledamodificare=" . $myVerbale->Reg_Progr_Registro;
					$tipoPosto = "VERBALE";
				}
				else
				{
					$link = "visiona_preinserimenti_targhe_estere.php?";
					$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
					$link .= "&a=" . $myVerbale->Reg_Anno;
					$link .= "&verbaledamodificare=" . $myVerbale->Reg_Progr;
					//alert($myNotifica->ID . " e " . $numNotifica . " e $myVerbale->Reg_Progr_Registro, $myVerbale->Reg_Anno, $myVerbale->Reg_Comune_Violazione");
					$tipoPosto = "PREINS";
				}
			}
			else
			{
				if ($myVerbale->Reg_Stato_Verbale == "MANUALE")
				{
					if ($myVerbale->Reg_Data_Stampa_Richiesta == '0000-00-00')
					{
						$link = "richiesta_manuale_targhe_estere.php?";
						$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
						$link .= "&a=" . $myVerbale->Reg_Anno;
						$link .= "&richiestadamodificare=" . $myVerbale->Reg_Progr;
						$tipoPosto = "INSERIMENTO";
					}
					else
					{
						$link = "ritorno_dati_targhe_estere.php?";
						$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
						$link .= "&a=" . $myVerbale->Reg_Anno;
						$link .= "&richiestadamodificare=" . $myVerbale->Reg_Progr;
						$tipoPosto = "RICHIESTA";
					}
				}
				else if ($myVerbale->Reg_Stato_Verbale == "AUTOMATICO")
				{
					if ($myVerbale->Reg_Ente_Per_Richiesta == 1)
					{
						$link = "analisi_importati_targhe_estere.php?";
						$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
						$link .= "&a=" . $myVerbale->Reg_Anno;
						$link .= "&richiestadamodificare=" . $myVerbale->Reg_Progr;
					}
					else
					{
						$link = "ritorno_dati_targhe_estere.php?";
						$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
						$link .= "&a=" . $myVerbale->Reg_Anno;
						$link .= "&richiestadamodificare=" . $myVerbale->Reg_Progr;
					}
					$tipoPosto = "IMPORT";
				}
				else if ($myVerbale->Reg_Stato_Verbale == "WEBIMPORTATO")
				{
					if ($myVerbale->Reg_Anno == "0000") $myVerbale->Reg_Anno = $a;
					$numNotifica = $myNotifica->NotificaInAttesaDaProgrVerbale($myVerbale->Reg_Progr, $myVerbale->Reg_Comune_Violazione);
					$myNotifica = new targhe_estere_notifiche($numNotifica);
					$link = "setta_web_targhe_estere_parte1.php?";
					$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
					$link .= "&a=" . $myVerbale->Reg_Anno;
					$link .= "&richiestadamodificare=" . $myNotifica->ID;
					$tipoPosto = "WEB";
				}
				//else if ($myVerbale->Reg_Stato_Verbale == "INSERITO_M" ||
				 $myVerbale->Reg_Stato_Verbale == "INSERITO_A" ||
						$myVerbale->Reg_Stato_Verbale == "INSERITO_W")
				{
				$link = "ritorno_dati_targhe_estere.php?";
				$link .= "c=" . $myVerbale->Reg_Comune_Violazione;
				$link .= "&a=" . $myVerbale->Reg_Anno;
				$link .= "&richiestadamodificare=" . $myVerbale->Reg_Progr;
				}//
			}*/
			
			if ($trovatoQualcosa == false)
			{
				?>
				
					<tr class="<?=$stileriga?> pheight30">
						<td class="width50">
							Non ci sono operazioni da svolgere
						</td>
						<td class="width30">
							
						</td>
						<td class="width20">
							
						</td>
					</tr>
				
				<?php 
			}
			
			
			?>
			
			</table>
			
		<?php } ?>
			
			
			
	</td>
</tr>

<?php } else { ?>

<tr>
	<td>
		Anno non selezionato
	</td>
</tr>

<?php } ?>

<tr style="height:10%;">
	<td class="valign_bottom">
		<p id=verifica_full></p>
	</td>
</tr>
	
<tr>
	<td style="height:15%;">
	
		<table class="width100 text_center">
			<tr>
				<td colspan=3><hr></td>
			</tr>
			<tr>
		        <td class="width33">
		        	<a href="mailto:webmaster@gitco.it" title="Scrivi a webmaster@sarida.it">
		        		<img src="/gitco2/immagini/email.gif" width="65" height="65" border=0>
		        	</a>
		        </td>
		        <td class="width34">
		        	<a href="javascript:privacy();" title="Informativa sulla Privacy">
		        		<img src="/gitco2/immagini/privacy.jpg" width="60" height="60" border=0>
		        	</a>
		        </td>
		        <td class="width33">
		        	<a href="#" onClick="chiusura();" title="Esci dal programma">
		            	<img src="/gitco2/immagini/exit.jpg" width="60" height="60" border=0 alt="Clicca qui per disconnetterti">
		           	</a>
		        </td>
			</tr>
		</table>
	
	</td>
</tr>
</table>

</body>
</html>