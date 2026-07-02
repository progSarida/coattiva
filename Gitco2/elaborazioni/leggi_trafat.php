<?php
if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS . "/cls_zip.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');	
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$control_submit = $cls_help->getVar('submit_file');

$query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'";
//$comune = new ente_gestito($c);
$nome_comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti")["Denominazione"];// $comune->Nome;

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

function estrai_stringa($stringa, &$array_stringa, $num_car)
{
	$array_stringa['substring'] = substr($stringa,$array_stringa['caratteri'],$num_car);
	$array_stringa['caratteri'] += $num_car;
	return $array_stringa['substring'];
}

function alert($message)
{
    echo "<script>alert('".$message."');</script>";
}

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

var modifica = 0;
var operatore = "<?php echo $_SESSION['username']; ?>";

//F5
switchMenuImg("F5");
F5_button = function()
{
    location.href = "preimportazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}



//F11-F12 sono nel menu'

</script>	

<script>
function importa()
{
	location.href = "controlli_importazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0="+id_n0+"&solo_rate=0";
}

function rate()
{
	location.href = "controlli_importazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0="+id_n0+"&solo_rate=1";
}

function bonifica()
{
	location.href = "bonifica_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function abilitaconferma()
{
	if ($("#tastosfoglia").val() != "")
		$("#tastoconferma").attr("disabled", false);

	else if ($("#tastosfoglia").val() == "")
		$("#tastoconferma").attr("disabled", "disabled");
}

function fine()
{
	$( "#progress_bar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Preimportazione completata!");
	$( "div#importazione" ).append("<input type=button name=avanti class=button_azzurro value='Controlli Importazione' onclick='importa();'>");

	if(operatore=="mirkop")
		$( "div#importazione" ).append("<input title='Importa solo rateizzazioni per partite presenti in archivio' type=button name=avanti class=sfondo_red value='Rateizzazione' onclick='rate();'>");
}

function ennezero()
{
	$('#progress_bar').progressbar();
	$( "#barlabel" ).text("Controllo N0 N9");
}

function enneuno()
{
	$('#progress_bar').progressbar();
	$( "#barlabel" ).text("Controllo N1 N5");
}

function control_ente()
{
	ritorno = confirm("!!!ATTENZIONE!!!\n\nIl file che si sta per importare non ha informazioni riguardanti il Codice Catastale del comune.\nEffettuare ugualmente l'importazione per il Comune di <?php echo $nome_comune; ?>?");
	if(ritorno)
	{
	}
	else
	{
		alert("Importazione annullata!");
		annulla();
	}
}

</script>

<div class="row justify-content-md-center " style="margin-bottom: 2%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font22 under_decor">Lettura file TRAFAT</span>
    </div>
</div>
<div class="row justify-content-md-center " style="margin-bottom: 2%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font16 under_decor">Carica File</span>
    </div>
</div>

    <form id=form_290 name=form_290 method="post" action="leggi_trafat.php" enctype="multipart/form-data">
        <input type="hidden" name="c" value="<?php echo $c?>">
        <input type="hidden" name="a" value="<?php echo $a?>">
        <input type="hidden" name="submit_file" value="1">
        <div class="row">
            <div class="col col-lg-5 col-lg-offset-1">
                <div class="form-group">
                    <div class="col-lg-8">
                        <input type="file" class="form-control resize" size="50" name="fileTRAFAT" id="tastosfoglia" onchange="abilitaconferma()">
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-lg-offset-3">
                <input type="submit" class="btn btn-primary" disabled size="10" id="tastoconferma" value="Conferma">
            </div>
        </div>

    </form>

    <br>
<?php 
/**	DESCRIZIONE DEI CAMPI UTILIZZATI  OGNI CAMPO HA UNA LUNGHEZZA PREDEFINITA VISUALIZZATA A LATO E TRA PARENTESI UN VALORE PREDEFINITO:
 *	DITTA 5 (00080)
 *	VERSIONE PROGRAMMA 1 (1)
 *	TIPO ARCHIVIO 1 (0)
 *	RAGIONE SOCIALE CLIENTE 32
 *	INDIRIZZO 30
 *	CAP 5 (00000)
 *	CITTA 25
 *	PROVINCIA 2
 *	CODICE FISCALE 16
 *	PARTITA IVA 11 (00000000000)
 *	PERSONA FISICA 1
 *	POSIZIONE SPAZIO FRA COGNOME E NOME 2 (00)
 *	CAUSALE 3 (042)
 *	DESCRIZIONE CAUSALE 15 (RISCOSSIONE)
 *	DESCRIZIONE AGGIUNTIVA 18 (NOME DEL COMUNE IN OGGETTO)
 *	DATA DOCUMENTO 6 (GGMMAA)
 *	NUMERO  7
 *	NUMERO DOCUMENTO 5
 *	SEZIONALE 2
 *	ESTRATTO CONTO NUMERO PARTITA 6
 *	ESTRATTO CONTO ANNO PARTITA 2
 *	IMPONIBILE 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
 *	ALIQUOTA 3
 *	IVA11 1 (CODICE DI MEMORIZZAZIONE PER IVA11)
 *	IMPOSTA 11 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 10 E SARA' SEGUITO DA UN + CHE OCCUPA L'11� BIT)
 *	TOTALE FATTURA 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
 *	CONTO DI RICAVO/COSTO 7
 *	IMPORTO RICAVO/COSTO 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
 *	CONTO 7
 *	DARE AVERE 1 (D=DARE A=AVERE)
 *	IMPORTO 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
 *	DESCRIZIONE AGGIUNTIVA 18
 *	ESTRATTO CONTO NUMERO PARTITA PAGAMENTO 6
 *	ESTRATTO CONTO ANNO PARTITA PAGAMENTI 2

 *	PRIMA PARTE DEL RECORD= DITTA+VERSIONE+TIPO ARCHIVIO+RAGIONE SOCIALE+INDIRIZZO+CAP+CITTA
 *	+PROVINCIA+CODICE FISCALE+PARTITA IVA+PERSONA FISICA+DIVISIONE NOME COGNOME+CAUSALE+DESCRIZIONE CAUSALE
 *	+CAUSALE AGGIUNTIVA (NOME DEL COMUNE DI RIFERIMENTO)
 *	INOLTRE E' STATA AGGIUNTA LA DATA DEL VERSAMENTO

 *	SECONDA PARTE DEL RECORD=N_DOC (NUMERO DOCUMENTO E SEZIONALE)+ESTRATTO CONTO NUMERO PARTITA+ANNO PARTITA+
 *	DATI IVA SINGOLO (IMPONIBILE, ALIQUOTA,IVA11,IMPOSTA) RIPETUTI 8 VOLTE+TOTALE FATTURA+RICAVI(CONTO DI RICAVO E IMPORTI DI RICAVO)
 *	RIPETUTO 8 VOLTE DOPODICHE' CI SONO I CODICI DEL CONTO DARE SEGUITI DA UNA D E DAL TOTALE DEI PAGAMENTI E DAL PAGAMENTO PARZIALE
 *	(DESCRIZIONE AGGIUNTIVA, ESTRATTO CONTO NUMERO PARTITA PAGAMENTO,ANNO PARTITA PAGAMENTO)

 *	INFINE CI SONO I CODICI DEL CONTO AVERE SEGUITI DA UNA A E DAL TOTALE DEI PAGAMENTI E DAL PAGAMENTO PARZIALE (DESCRIZIONE
 *	AGGIUNTIVA, ESTRATTO CONTO NUMERO PARTITA PAGAMENTO,ANNO PARTITA PAGAMENTO)
 *	INFINE C'E' UNA SEQUENZA DI 78 PAGAMENTO VUOTO (CONTO, DARE AVERE, IMPORTO)+PAGAMENTO PARZIALE
 */

if($control_submit==1)
{
$percorso_file = $_FILES['fileTRAFAT']['tmp_name'];
$nome_file = $_FILES['fileTRAFAT']['name'];

    $extention = pathinfo($nome_file)['extension'];

    $cls_zip = new cls_zip();
    if($nome_file!="" && $extention == "zip"){
        $checkExtraction = $cls_zip->extractZip($percorso_file,SUPER_ROOT."/archivio");
        if($checkExtraction){
            alert( "FILE ESTRATTO");
            $a_zipFiles = $cls_zip->readZip($percorso_file);
            $fileTRAFAT = SUPER_ROOT."/archivio"."/".trim($a_zipFiles[0]);
        }
        else
            alert("ERRORE ESTRAZIONE");
    }
    else
    {
        if($nome_file!="")
        {
            $uploads_dir = SUPER_ROOT."/archivio";
            move_uploaded_file($percorso_file, "$uploads_dir/$nome_file");
            alert("FILE CARICATO");
            $fileTRAFAT = SUPER_ROOT."/archivio"."/".trim($nome_file);
        }else alert("FILE NON CARICATO");

    }


$fopenTRAFAT = fopen($fileTRAFAT , "r");
$testoTRAFAT = fread($fopenTRAFAT, filesize($fileTRAFAT));
fclose($fopenTRAFAT);
unlink($fileTRAFAT);
    $somma_totale = 0;
$somma = 0;
$array_trafat = str_split($testoTRAFAT,4248);
$num = count($array_trafat);
$array_dati = array();
for($y=0; $y<$num ; $y++)
{ 
	$array_stringa = array();
	$array_stringa['substring'] = "";
	$array_stringa['caratteri'] = 0;
	
	$array_dati[$y]['DITTA'] = estrai_stringa($array_trafat[$y], $array_stringa, 5);						//	DITTA 5 (00080)
	$array_dati[$y]['VERSIONE_PROGRAMMA'] = estrai_stringa($array_trafat[$y], $array_stringa, 1);			//	VERSIONE PROGRAMMA 1 (1)
	$array_dati[$y]['TIPO_ARCHIVIO'] = estrai_stringa($array_trafat[$y], $array_stringa, 1);				//	TIPO ARCHIVIO 1 (0)
	$array_dati[$y]['RAGIONE_SOCIALE'] = estrai_stringa($array_trafat[$y], $array_stringa, 32);				//	RAGIONE SOCIALE CLIENTE 32
	$array_dati[$y]['INDIRIZZO'] = estrai_stringa($array_trafat[$y], $array_stringa, 30);					//	INDIRIZZO 30
	$array_dati[$y]['CAP'] = estrai_stringa($array_trafat[$y], $array_stringa, 5);							//	CAP 5 (00000)
	$array_dati[$y]['CITTA'] = estrai_stringa($array_trafat[$y], $array_stringa, 25);						//	CITTA 25
	$array_dati[$y]['PROVINCIA'] = estrai_stringa($array_trafat[$y], $array_stringa, 2);					//	PROVINCIA 2
	$array_dati[$y]['CODICE_FISCALE'] = estrai_stringa($array_trafat[$y], $array_stringa, 16);				//	CODICE FISCALE 16
	$array_dati[$y]['PARTITA_IVA'] = estrai_stringa($array_trafat[$y], $array_stringa, 11);					//	PARTITA IVA 11 (00000000000)
	$array_dati[$y]['PERSONA_FISICA'] = estrai_stringa($array_trafat[$y], $array_stringa, 1);				//	PERSONA FISICA 1
	$array_dati[$y]['SPAZIO'] = estrai_stringa($array_trafat[$y], $array_stringa, 2);						//	POSIZIONE SPAZIO FRA COGNOME E NOME 2 (00)
	$array_dati[$y]['CAUSALE'] = estrai_stringa($array_trafat[$y], $array_stringa, 3);						//	CAUSALE 3 (042)
	$array_dati[$y]['DESCRIZIONE_CAUSALE'] = estrai_stringa($array_trafat[$y], $array_stringa, 15);			//	DESCRIZIONE CAUSALE 15 (RISCOSSIONE)
	$array_dati[$y]['DESCRIZIONE_AGGIUNTIVA'] = estrai_stringa($array_trafat[$y], $array_stringa, 18);		//	DESCRIZIONE AGGIUNTIVA 18 (NOME DEL COMUNE IN OGGETTO)
	$array_dati[$y]['DATA_DOCUMENTO'] = estrai_stringa($array_trafat[$y], $array_stringa, 6);				//	DATA DOCUMENTO 6 (GGMMAA)
	$array_dati[$y]['NUMERO_DOCUMENTO'] = estrai_stringa($array_trafat[$y], $array_stringa, 5);				//	NUMERO DOCUMENTO 5
	$array_dati[$y]['SEZIONALE'] = estrai_stringa($array_trafat[$y], $array_stringa, 2);					//	SEZIONALE 2
	$array_dati[$y]['ESTRATTO_CONTO_NUMERO'] = estrai_stringa($array_trafat[$y], $array_stringa, 6);		//	ESTRATTO CONTO NUMERO PARTITA 6
	$array_dati[$y]['ESTRATTO_CONTO_ANNO'] = estrai_stringa($array_trafat[$y], $array_stringa, 2);			//	ESTRATTO CONTO ANNO PARTITA 2

	for($i=0;$i<1;$i++){
		$array_dati[$y]['IMPONIBILE'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 11);			//	IMPONIBILE 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
		$array_stringa['caratteri']+= 1;
		$array_dati[$y]['ALIQUOTA'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 3);				//	ALIQUOTA 3
		$array_dati[$y]['IVA11'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 1);					//	IVA11 1 (CODICE DI MEMORIZZAZIONE PER IVA11)
		$array_dati[$y]['IMPOSTA'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 10);				//	IMPOSTA 11 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 10 E SARA' SEGUITO DA UN + CHE OCCUPA L'11� BIT)
		$array_stringa['caratteri']+= 1;
	}
	$array_stringa['caratteri']+= (27*7);
	
	$array_dati[$y]['TOTALE FATTURA'] = estrai_stringa($array_trafat[$y], $array_stringa, 11);				//	TOTALE FATTURA 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
	$array_stringa['caratteri']+= 1;
	
	for($i=0;$i<1;$i++){
		$array_dati[$y]['CONTO RICAVO COSTO'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 7);		//	CONTO DI RICAVO/COSTO 7
		$array_dati[$y]['IMPORTO RICAVO COSTO'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 11);	//	IMPORTO RICAVO/COSTO 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
		$array_stringa['caratteri']+= 1;
	}
	$array_stringa['caratteri']+= (19*7);
	
	for($i=0;$i<2;$i++){
		$array_dati[$y]['CONTO'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 7);							//	CONTO 7
		$array_dati[$y]['DARE AVERE'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 1);						//	DARE AVERE 1 (D=DARE A=AVERE)
        $importo = estrai_stringa($array_trafat[$y], $array_stringa, 11);
        if($i==0)
            $somma+=$importo;
		$array_dati[$y]['IMPORTO'][$i] = number_format($importo,2,",","");//	IMPORTO 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
		$array_stringa['caratteri']+= 1;					
		$array_dati[$y]['DESCRIZIONE AGGIUNTIVA 2'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 18);		//	DESCRIZIONE AGGIUNTIVA 18
		$array_dati[$y]['ESTRATTO CONTO NUMERO PAGAMENTO'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 6);	//	ESTRATTO CONTO NUMERO PARTITA PAGAMENTO 6
		$array_dati[$y]['ESTRATTO CONTO ANNO PAGAMENTO'][$i] = estrai_stringa($array_trafat[$y], $array_stringa, 2);	//	ESTRATTO CONTO ANNO PARTITA PAGAMENTI 2
	}

    $a_completo[$array_dati[$y]['DESCRIZIONE_AGGIUNTIVA']][$array_dati[$y]['CONTO'][0]][$array_dati[$y]['CONTO'][1]][] = $importo;

}//CHIUSURA CICLO

//    print_r($a_completo);

foreach ($a_completo as $desc => $a_desc){
    foreach ($a_desc as $modalita => $a_modalita){
        foreach ($a_modalita as $riscossione => $a_riscossione){
            $totale_importi = array_sum($a_riscossione);
            $modalitaTxt = $modalita;
            $riscossioneTxt = $riscossione;
            switch($modalita){

                case "2405065":     $modalitaTxt.= " C/C"; break;
                case "2415005":     $modalitaTxt.= " Bolletta, Contanti o Assegno"; break;
                case "2405502":     $modalitaTxt.= " BPL"; break;
                case "2405505":     $modalitaTxt.= " BGSG"; break;
                case "2405521":     $modalitaTxt.= " PAYPAL"; break;
                default:            $modalitaTxt.= " SCONOSCIUTO"; break;
            }

            switch($riscossione){
                case "5205512":     $riscossioneTxt.= " CDS/AMMINISTRATIVA"; break;
                case "5205510":     $riscossioneTxt.= " TSRSU/TARI/TARES"; break;
                case "5810505":     $riscossioneTxt.= " OSAP PERMANENTE"; break;
                case "5810506":     $riscossioneTxt.= " OSAP TEMPORANEO"; break;
                case "5205513":     $riscossioneTxt.= " ICI/IMU"; break;
                case "5810502":     $riscossioneTxt.= " PUBBLICITA' PERMANENTE"; break;
                case "5810503":     $riscossioneTxt.= " PUBBLICITA' AFFISSIONI"; break;
                case "5810504":     $riscossioneTxt.= " PUBBLICITA' TEMPORANEO"; break;
                case "5205517":     $riscossioneTxt.= " PATRIMONIALE"; break;
                case "1845090":     $riscossioneTxt.= " VOTIVA"; break;
                default:            $riscossioneTxt.= " SCONOSCIUTO"; break;
            }
            echo "DESCRIZIONE: ".$desc." - RISCOSSIONE: ".$riscossioneTxt." - MODALITA' ".$modalitaTxt." - TOTALE: ".number_format($totale_importi,2,",",".")." &euro;<br><br>";
        }
    }
}


    for($y=0;$y<count($array_dati);$y++){
        $a_echo = $array_dati[$y];
        foreach ($a_echo as $key => $value) {
            if(is_array($value)){
                for($i=0;$i<count($value);$i++){
                    echo $key." - ".($i+1)." : ".$value[$i]." ";
                }
                echo "<br>";
            }
            else{
                echo $key." : ".$value."<br>";
            }
        }
    }



}//CHIUSURA IF (control_submit)

$storico->insRow('P', "Letto file TRAFAT '".$nome_file."'");

?>
		</td>
	</tr>
</table>


<?php include(INC."/footer.php"); ?>