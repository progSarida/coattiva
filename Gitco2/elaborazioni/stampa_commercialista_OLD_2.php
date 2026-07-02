<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_Utils.php");

$cls_date = new cls_DateTimeI("DB",false);
$cls_utils = new cls_Utils();

$chkNO = " CHECKED ";
$chkSI = "";
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');
$p = $cls_help->getVar('p');
$dadata = $cls_help->getVar('dadata');
$adata = $cls_help->getVar('adata');
$comuni = $cls_help->getVar("comuni");
$tipoPagamento = $cls_help->getVar('tipo_pagamento');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

$sottotipo_riscossione = $cls_help->getVar('sottotipo_riscossione');
$sottotipo_layout = null;
switch($tipo_riscossione)
{
	case "CDS":
    case "RIFIUTI":
    case "IMMOBILI":
    case "PATRIMONIALE":
        $sottotipo_riscossione = "";
        $sottotipo_layout = "$('#sottotipo_riscossione').hide();";
        break;
    case "PUBBLICITA":
        break;
    case "OSAP":
        $sottotipo_layout = "$('#affissioni').hide();";
		break;
    default:
        $tipo_riscossione = "CDS";
        $sottotipo_riscossione = "";
        $sottotipo_layout = "$('#sottotipo_riscossione').hide();";
        break;
}


if($comuni=="SI"){
	$chkSI = " CHECKED ";
	$chkNO = "";
}
//$comune = new ente_gestito($c);
//$nome_comune = $comune->Nome;
$nome_user = "Operatore: " . $_SESSION['username'];

/**************************************************************************************/
/********funzione di appoggio per contare la lunghezza dei campi del record*************/
function conta_bit($stringa,$numero,$fh_bit)
{
	if($numero==0)
	{
		fputs($fh_bit,$stringa);
	}
	else
	{
		$stringa_temp=substr($stringa,0,$numero);
		for($s=0; $s<=($numero-strlen($stringa_temp)-1); $s++)
		{
			fputs($fh_bit,"0");
		}
		fputs($fh_bit,"$stringa_temp");
	}
}

?>


	<!-- ********** GESTIONE LINK MENU ********** -->
	<script>

		function inizio()
		{
			$('#progressbar').progressbar({
				value: false
			});
			$( "#barlabel" ).text("Inizio elaborazione...");
		}

		function update(valore)
		{
			$( "#progressbar" ).progressbar({value: parseInt(valore) });
			$( "#barlabel" ).text( valore + "%" );
		}

		function nessun_risultato()
		{
			$( "#progressbar" ).progressbar({value: 100 });
			$( "#barlabel" ).text("Nessun risultato trovato");
			//sleep(1000);
		}

		function fine(value)
		{
			$( "#progressbar" ).progressbar({value: 100 });
			$( "#barlabel" ).text( value );

		}

		function changeRiscossione(value){
		    switch(value){
                case "CDS":
                case "RIFIUTI":
                case "IMMOBILI":
                case "PATRIMONIALE":
                    $('#sottotipo_riscossione').hide();
                    break;
                case "OSAP":
                    $('#sottotipo_riscossione').show();
                    $('#affissioni').hide();
                    break;
                case "PUBBLICITA":
                    $('#sottotipo_riscossione').show();
                    $('#affissioni').show();
                    break;
            }
        }
	</script>

    <div class="row justify-content-md-center " style="margin-bottom: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font18 under_decor">File TRAFAT</span>
        </div>
    </div>


    <table class="table_interna text_center" border="0">
        <tr class="pheight40">
            <td valign=top>
                <form action="stampa_commercialista.php">
                    <input type=hidden name=c value=<?= $c ?>>
                    <input type=hidden name=a value=<?= $a ?>>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-lg-4 control-label resize" style="text-align: left;">Da data</label>
                                <div class="col-lg-8">
                                    <input type="text" name="dadata" class="form-control resize" value=<?= $dadata ?>>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-lg-4 control-label resize" style="text-align: left;">A data</label>
                                <div class="col-lg-8">
                                    <input type="text" name="adata" class="form-control resize" value=<?= $adata ?>>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo riscossione</label>
                                <div class="col-lg-8">
                                    <select id=tipo_riscossione name=tipo_riscossione class="form-control resize" onchange="changeRiscossione(this.value);">
                                        <option value="CDS">CDS/AMMINISTRATIVA</option>
                                        <option value="IMMOBILI">ICI/IMU</option>
                                        <option>OSAP</option>
                                        <option value="PATRIMONIALE">PATRIMONIALE</option>
                                        <option value="PUBBLICITA">PUBBLICITA'</option>
                                        <option value="RIFIUTI">TSRSU/TARI/TARES</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4" >
                            <div class="form-group" id=sottotipo_riscossione>
                                <label class="col-lg-4 control-label resize" style="text-align: left;">Sottotipo riscossione</label>
                                <div class="col-lg-8">
                                    <select  name=sottotipo_riscossione class="form-control resize">
                                        <option>PERMANENTE</option>
                                        <option>TEMPORANEO</option>
                                        <option id="affissioni">AFFISSIONI</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-lg-5 control-label">Tutti i comuni:</label>
                                <div class="col-lg-7">
                                    <div class="row">
                                        <div class=" col-lg-6">
                                            <label>
                                                <input type="radio" name="comuni" value="NO" <?= $chkNO ?>> NO
                                            </label>
                                        </div>
                                        <div class=" col-lg-6">
                                            <label>
                                                <input type="radio" name="comuni" value="SI" <?= $chkSI ?>> SI
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo pagamento</label>
                                <div class="col-lg-8">
                                    <select id=tipo_pagamento name=tipo_pagamento class="form-control resize" tabindex=6>
                                        <option></option>
                                        <option>Bancomat</option>
                                        <option>Bolletta</option>
                                        <option>C/C</option>
                                        <option>Contanti</option>
                                        <option>Assegno</option>
                                        <option>POS</option>
                                        <option>Vaglia</option>
                                        <option>BPL</option>
                                        <option>BGSG</option>
                                        <option>PAYPAL</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>$('#tipo_pagamento').val('<?php echo $tipoPagamento; ?>');</script>
                    <div class="row">
                        <div class="col-lg-2">
                            <input type="submit" class="btn btn-primary" value="Crea file">
                        </div>
                    </div>
                </form>
                    <br><br>

<?php
if($sottotipo_layout!="")
echo "<script>".$sottotipo_layout."</script>";
if($tipo_riscossione!="")
	echo "<script>$('#tipo_riscossione').val('".$tipo_riscossione."');</script>";
if($sottotipo_riscossione!="")
    echo "<script>$('#sottotipo_riscossione').val('".$sottotipo_riscossione."')</script>";
set_time_limit(-1);

$dadata = $cls_date->GetDateDB($dadata,"IT");// to_mysql_date($dadata);
$adata = $cls_date->GetDateDB($adata,"IT");// to_mysql_date($adata);


if ($dadata=="" || $adata=="") die;

$query = "SELECT SUM(PAG.Importo) Somma_Importi, PAG.Data_Pagamento, PAG.Modalita, CL.Com_Nome, ";
$query.= "PARTITA.CC, PARTITA.Tipo AS Tipo_Riscossione, PARTITA.Sottotipo AS Sottotipo_Riscossione ";
$query.= "FROM pagamento PAG JOIN partita_tributi PARTITA ON PAG.Partita_ID = PARTITA.ID AND PAG.Conto_Terzi!='Y' ";
$query.= "LEFT JOIN comuni_lista CL ON CL.Com_Codice_Catastale = PARTITA.CC ";
$query.= "WHERE PARTITA.Tipo = '".$tipo_riscossione."' ";
if($sottotipo_riscossione!=""){
    $query.= "AND (PARTITA.Sottotipo= '".$sottotipo_riscossione."' ";
    if($sottotipo_riscossione=="PERMANENTE")
        $query.= "OR PARTITA.Sottotipo= '' OR PARTITA.Sottotipo is null ";
    $query.=") ";
}


if($tipoPagamento!="")
    $query.="AND PAG.Modalita = '".$tipoPagamento."' ";
$query.= "AND PAG.Data_Pagamento >='".$dadata."' AND PAG.Data_Pagamento<='".$adata."' ";

if ($comuni=="NO") 	$query .= "AND PARTITA.CC  = '".$c."' ";
else				$query .= "AND PARTITA.CC  != 'ZZZZ' ";

$query .= "GROUP BY PAG.Data_Pagamento, PAG.Modalita, PARTITA.Tipo, PARTITA.CC, CL.Com_Nome ";
$query .= "ORDER BY PARTITA.CC, PARTITA.Tipo, PAG.Modalita, PAG.Data_Pagamento ";

$res = $cls_db->ExecuteQuery($query);// safe_query($query);

if($nome_user=="Operatore: mirkop")
    echo $query."<br><br>";

if (mysqli_num_rows($res)==0){
	echo "Nessun risultato trovato";die;
}else{
	echo "RIEPILOGO:<br><br>";
}

$nome_file = "trafat_".$c."_".$dadata."_".$adata.".txt";
$cls_utils->crea_dir(SUPER_ROOT."/archivio/Commercialista/");
//********************** Apertura file stampa e intestazione
$fh=fopen(SUPER_ROOT."/archivio/Commercialista/".$nome_file, "w");
$con_pag=1;
$con_righe=3;
$co_app=1;
$contr_app[0]=0;

$contoTotale = 0;
$contoComune = "";
$ccTemp = "";
$totale_completo = 0;
while($trovato = mysqli_fetch_array($res)){
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

	if($ccTemp!=""){

		if(($ccTemp==$trovato["CC"])){
			$contoTotale += number_format($trovato["Somma_Importi"],2,".","");
		}
		else{
			echo $contoComune." ".number_format($contoTotale,2,",",".")."<br>";

            $ccTemp = $trovato["CC"];
			$contoTotale = number_format($trovato["Somma_Importi"],2,".","");
		}
	}else{
        $ccTemp = $trovato["CC"];
		$contoTotale = number_format($trovato["Somma_Importi"],2,".","");
	}

    if($trovato["Com_Nome"]!=null)
        $com_nome = $trovato["Com_Nome"];
    else if($trovato["CC"]=="U003")
        $com_nome = "Provincia Savona";
    $com_nome = strtoupper($com_nome);

    $contoComune = $com_nome;
	
	$totale_completo += $trovato["Somma_Importi"];
	
	//Inizio prima parte del record
	$ditta="80";   //Codice della ditta che deve essere lungo 5 bit
	conta_bit($ditta,5,$fh);
	$versione="1";    //Versione del programma che deve essere lungo 1 bit
	conta_bit($versione,0,$fh);
	$tarc="0";        //Tipo di archivio che deve essere lungo 1 bit
	conta_bit($tarc,0,$fh);
	for($i=0;$i<=61;$i++){
		// 32 spazi vuoti per la ragione sociale
		// 30 spazi vuoti per l'indirizzo
		fputs($fh," ");
	}
	// Cap del contribuente, lo metto a zero perch� non serve
	$cap="00000";
	conta_bit($cap,5,$fh);
	for($i=0;$i<=42;$i++){
		// 25 spazi per la citta
		// 2 spazi per la provincia
		// 16 spazi per il codice fiscale
		fputs($fh," ");
	}
	//Partita Iva del contribuente, la metto a zero perch� non serve
	$piva="00000000000";
	conta_bit($piva,11,$fh);
	//Persona Fisica, lo metto vuoto perch� non serve
	fputs($fh," ");
	//Posizione spazio fra Cognome e Nome, la metto a zero perch� non serve
	$divide="00";
	conta_bit($divide,2,$fh);
	//Causale
	$causale="42";
	conta_bit($causale,3,$fh);
	//Descrizione della causale
	$caus_desc = strtoupper($tipo_riscossione." ".$sottotipo_riscossione);
	$descr_caus=substr($caus_desc,0,15);
	fputs($fh,$descr_caus);
	for($i=0;$i<=(14-strlen($descr_caus));$i++){
		fputs($fh," ");
	}
	//Fine prima parte record e aggiunta del nome del comune e della data del pagamento

	$comune=substr($com_nome,0,18);
	fputs($fh,$comune);
	for($i=0;$i<=(17-strlen($comune));$i++){
		fputs($fh," ");
	}
	$cls_date->changeFormat("IT",false);
	$data_pagamento = $cls_date->Get_DateNewFormat($trovato["Data_Pagamento"],"DB");// from_mysql_date($trovato["Data_Pagamento"]);
	$anno_doc = substr($data_pagamento,8);//mi servono solo le ultime due cifre dell'anno
	$giorno_doc = substr($data_pagamento,0,2);
	$mese_doc = substr($data_pagamento,3,2);
	$doc_data = $giorno_doc.$mese_doc.$anno_doc;
	conta_bit($doc_data,6,$fh);
		
	
	//Inizio seconda parte del record
	//Scrivo 5 zeri per numero documento, 2 per il sezionale,6 per l'estratto conto numero della partita
	//2 per l'anno della partita
	conta_bit("",15,$fh);
	//Ciclo di 8 volte dei campi imponibile (11),prima metto un + aliquota (3), iva11 (1),imposta (10) finisco con un +
	for($i=0;$i<=7;$i++){
		conta_bit("",11,$fh);
		fputs($fh,"+");
		conta_bit("",14,$fh);
		fputs($fh,"+");
	}
	//Totale fattura (11) seguito da +
	conta_bit("",11,$fh);
	fputs($fh,"+");

	//Ciclo di 8 volte  sul conto di ricavo (7) e l'importo di ricavo (11) seguito da un +
	for($i=0;$i<=7;$i++){
		conta_bit("",18,$fh);
		fputs($fh,"+");
	}
	//Fine seconda parte del record.
	//Selezione del tipo di conto dare a seconda del tipo di pagamento selezionato
	/**
	 * Bancomat
	 * Bolletta - Contanti - Assegno			2415005
	 * C/C										2405065
	 * POS
	 * Vaglia
	 * BPL										2405502
	 * BGSG										2405505
	 */
	//Selezione del tipo di conto dare a seconda del tipo di pagamento selezionato
	if($trovato["Modalita"]=="C/C"){
		$conto = "2405065";//Conto Posta
	}
	else if($trovato["Modalita"]=="Bolletta" || $trovato["Modalita"]=="Contanti" || $trovato["Modalita"]=="Assegno"){
		$conto = "2415005";//Conto Cassa
	}
	else if($trovato["Modalita"]=="BPL"){
		$conto = "2405502";//Conto Banca Lodi
	}
	else if($trovato["Modalita"]=="BGSG"){
		$conto = "2405505";//Conto Banca S. Giorgio
	}
    else if($trovato["Modalita"]=="PAYPAL"){
        $conto = "2405521";//Paypal
    }
	else 
		$conto = "";

	//Scrittura del conto che occupa 7 bit seguito da D che sta per Dare
	conta_bit($conto,7,$fh);
	fputs($fh,"D");
	//Totale dei versamenti per data e comune seguito da un +
	$appoggio=number_format($trovato["Somma_Importi"],2,".","");
	conta_bit($appoggio,11,$fh);
	fputs($fh,"+");
	//Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
	//della partita del pagamento, 2 zeri per l'anno della partita
	for($i=0;$i<=17;$i++){
		fputs($fh," ");
	}
	conta_bit("",8,$fh);

	$conto_avere = "";

	//Selezione del tipo di conto avere a seconda del tributo selezionato
	switch($trovato['Tipo_Riscossione'])
	{
		case "CDS":
			$conto_avere="5205512";
			break;
		case "RIFIUTI":
		    $conto_avere="5205510";
			break;
		case "OSAP":
            switch($trovato['Sottotipo_Riscossione']){
                case "PERMANENTE": $conto_avere="5810505";
                case "TEMPORANEO": $conto_avere="5810506";
            }
			break;
		case "IMMOBILI":
		    $conto_avere="5205513";
			break;
        case "PATRIMONIALE":
            $conto_avere="5205517";//5810517
            break;
        case "PUBBLICITA":
            switch($trovato['Sottotipo_Riscossione']){
                case "PERMANENTE": $conto_avere="5810502";
                case "TEMPORANEO": $conto_avere="5810503";
                case "PERMANENTE": $conto_avere="5810504";
            }
            break;
        case "VOTIVA":
            $conto_avere="1845090";
            break;

	}

	//Numero del conto avere lungo 7 bit seguito da A che sta per avere
	conta_bit($conto_avere,7,$fh);
	fputs($fh,"A");
	//Totale dei pagamenti per comune e data che deve occupare 11 bit seguito da +
	$appoggio=number_format($trovato["Somma_Importi"],2,".","");
	conta_bit($appoggio,11,$fh);
	fputs($fh,"+");
	//Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
	//della partita del pagamento, 2 zeri per l'anno della partita
	for($i=0;$i<=17;$i++){
		fputs($fh," ");
	}
	conta_bit("",8,$fh);
	//Ciclo di 78 volte sul pagamento vuoto [costituito da 7 zeri per il conto, seguiti da uno spazio vuoto per D (dare)
	//o A (Avere), e da 11 zeri per l'importo e da un +] e sul pagamento parziale.
	for($i=0;$i<=77;$i++){
		//Pagamento vuoto
		conta_bit("",7,$fh);
		fputs($fh," ");
		conta_bit("",11,$fh);
		fputs($fh,"+");
		//Pagamento prziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
		//della partita del pagamento, 2 zeri per l'anno della partita
		for($t=0;$t<=17;$t++){
			fputs($fh," ");
		}
		conta_bit("",8,$fh);
	}

}

fclose($fh);
?>

			<?php echo $contoComune." ".number_format($contoTotale,2,",","."); ?>
			<br><br><br><br>
			<?php echo "TOTALE ".number_format($totale_completo,2,",","."); ?>
			<br><br><br><br>
			file creato:
			<br><br>
			<a href="../../../archivio/Commercialista/<?php echo $nome_file; ?>" target="_new">Apri file</a>
			<br><br>
					</td>
				</tr>
			</table>
		
		</td>
	</tr>
</table>

<?php include(INC."/footer.php"); ?>