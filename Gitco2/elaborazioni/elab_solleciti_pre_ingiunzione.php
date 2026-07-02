<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
//include(INC."/menu.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_pdf.php";

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();

class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 12);
		$this->ln(8);
		$this->Cell(0, 5, "Elenco ingiunzioni elaborate" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-15);
		$this->SetFont('helvetica', 'N', 6);
		$this->Cell(0, 5, "Pag. ".$this->getPage()." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir( $_SERVER['DOCUMENT_ROOT'] ."archivio/atti/". $c . "/Ingiunzioni/Elenco_elaborazioni" );

//echo "<h1>".$_SESSION['path'] ."/archivio/atti/". $c . "/Ingiunzioni/Elenco_elaborazioni"."</h1><br><h1>new --> ".$_SERVER['DOCUMENT_ROOT']."</h1>";

$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_ing_elab_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = $cls_utils->mostra_file_path($download);

?>

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
}

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	$( "div#vedi_file" ).append("<div class='row' style='margin-top: 4%;'><div class='col-lg-2 col-lg-offset-1'><input type=button name=avanti class='btn btn-primary resize' value='Elenco elaborazioni' onclick='mostra_file();'></div></div>");
}

function mostra_file()
{
	window.open('<?php echo $vedi_file; ?>');
	self.close();
}

</script>

    <div class="row justify-content-md-center " style="margin-top: 1%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font18 under_decor">Elaborazione Solleciti pre Ingiunzione</span>
        </div>
    </div>
    <div class="row" style="margin-top: 3%;">
        <div class="col-lg-10 col-lg-offset-1" >
            <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12" >
            <div id=vedi_file></div>
        </div>
    </div>

<?php

$primo_sollecito = $cls_help->getVar('primo_sollecito');
$data_elaborazione = $cls_date->Get_DateNewFormat($cls_help->getVar('data_elab'),"IT");//from_mysql_date($cls_help->getVar('data_elab'));
$data_calcolo = $cls_date->Get_DateNewFormat($cls_help->getVar('data_calcolo'),"IT");//from_mysql_date($cls_help->getVar('data_calcolo'));
$modalitaStampa = $cls_help->getVar('modalita_stampa');

//$cls_db = new cls_db();
$a_PrintType = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT * FROM print_type WHERE Modalita_Stampa='".$modalitaStampa."'"),"print_type");
$PrintTypeId = $a_PrintType['Id'];

$da_n_elenco  = $cls_help->getVar('da_n_elenco');
$a_n_elenco  = $cls_help->getVar('a_n_elenco');

$daco  = strtoupper($cls_help->getVar('daco'));
$dano  = strtoupper($cls_help->getVar('dano'));

$acog  = strtoupper($cls_help->getVar('acog'));
$anom  = strtoupper($cls_help->getVar('anom'));

$da_anno = $cls_help->getVar('da_anno');
$ad_anno = $cls_help->getVar('ad_anno');

$PrinterId = $cls_help->getVar("PrinterId");

$anno_elab = explode("/", $data_elaborazione);
$anno_elab = $anno_elab[2];

$tipo_partita = $cls_help->getVar('tipo_partita');

/**		SELEZIONE UTENTI 			*/
$query_utente = $cls_elab->da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = $cls_db->getResultsNull($cls_db->ExecuteQuery($query_utente),"utente");// mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
//SELEZIONE ANNI
$where = "( Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' AND Flag_Blocco_Coazione != 'si' )";

$query_partita = $cls_elab->da_a_partita( $c , $da_n_elenco , $a_n_elenco , $where );
$array_partite = $cls_db->getResultsNull($cls_db->ExecuteQuery($query_partita),"partita_tributi");// mysql_array( $query_partita );

$num_partite = count($array_partite);
$num_utenti = count($array_utenti);

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
//$pdf->setPrintHeader(false);
$pdf->SetMargins(10, 10, 10);
$pdf->setCellPaddings(2,1,2,1);

$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 11);

$pdf->SetAutoPageBreak(false);
$pdf->Ln(10);

$array_width = array();
$array_intestaz = array();
						
$array_width[] = 10;	$array_intestaz[] = "ID";
$array_width[] = 18;	$array_intestaz[] = "Partita";
$array_width[] = 60;	$array_intestaz[] = "Utente";
$array_width[] = 80;	$array_intestaz[] = "Informazioni";
$array_width[] = 25;	$array_intestaz[] = "Dovuto";

$y1_vert = $pdf->setRow(  $array_intestaz, "up_down" , $styleRetta, null,0,$array_width);
//$cls_pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);

$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".date('Y')."' AND Tipo_Riscossione = '*****'";
$parametri = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");// new parametri_annuali($c, date('Y-m-d') , $partita->Tipo );

if($parametri->ID == null) {
    $cls_help->alert("l'anno " . date('Y') . " non è presente nei parametri annuali!");
    die;
}

flush();
ob_flush();

echo "<script>inizio();</script>";

flush();
ob_flush();
flush();
ob_flush();
sleep(2);
		
	$id_ingiunzione = array();
	$partita_ok = array();
	$partita_ko = array();

	$cont_result = 0;
	for( $k=0; $k < $num_partite; $k++ )
	{	
		echo "<script>update(".ceil($k*100/$num_partite).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if($tipo_partita != "")
			if($array_partite[$k]['Tipo']!=$tipo_partita)
				continue;
		
		for( $j=0; $j < $num_utenti; $j++ )
		{			
			if($array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'])
			{				
				set_time_limit(30);

                $stato_stampa = "Da stampare";

                $query = "SELECT * FROM partita_tributi WHERE ID = '".$array_partite[$k]['ID']."' AND CC = '".$c."' AND Anno_Riferimento = '".$array_partite[$k]['Anno_Riferimento']."'";
                $partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");

                $query = "SELECT ID FROM tributo WHERE Partita_ID = '".$partita->ID."' ORDER BY Codice_Tributo";
                $tributo_id = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"tributo");// select_mysql_array("ID", "tributo","Partita_ID = '".$this->ID."'","Codice_Tributo");
                for( $i=0; $i<count($tributo_id); $i++) {
                    $query = "SELECT * FROM tributo WHERE ID = '".$tributo_id[$i]['ID']."' AND CC = '".$c."'";
                    $partita->Tributo[$i] = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"tributo");

                    $query = "SELECT Tipo_Codice FROM codice_tributo WHERE Codice_Tributo = '".$partita->Tributo[$i]->Codice_Tributo."'";
                    $partita->Tributo[$i]->Tipo_Codice = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"codice_tributo")["Tipo_Codice"];
                }

                $query = "SELECT ID FROM atto WHERE Partita_ID = '".$partita->ID."'";
                $atto_id = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"atto");// select_mysql_array("ID", "atto","Partita_ID = '".$this->ID."'");

                for( $i=0; $i<count($atto_id); $i++) {
                    $query = "SELECT * FROM atto WHERE ID = '".$atto_id[$i]['ID']."' AND CC = '".$c."'";
                    $partita->Atto[$i] = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");// new atto($atto_id[$i]['ID'], $c);

                    if($partita->Atto[$i]->Atto == "Ingiunzione" || $partita->Atto[$i]->Atto == "Avviso di intimazione ad adempiere" || $partita->Atto[$i]->Atto == "Avviso di messa in mora"){
                        $partita->ultimo_atto = $atto_id[$i]['ID'];
                    }
                    else $partita->ultimo_atto = null;
                }

				//$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
				$tributo = $partita->Tributo;
                $info_cart = $tributo[0]->Info_Cartella;

				$flag_blocco_diritto_riscossione = $partita->Flag_Blocco_Diritto_Riscossione;

                $query = "SELECT * FROM utente WHERE ID = '".$array_partite[$k]['Utente_ID']."' AND CC_Comune = '".$c."'";
				$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");// new utente( $array_partite[$k]['Utente_ID'] , $c );
				$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;

                if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
                    break;

                $importo_min = $parametri->Importo_Minimo;
                $diritto_min = $parametri->Diritto_Riscossione_Minimo;
                $diritto_max = $parametri->Diritto_Riscossione_Massimo;

                $spese_not = $parametri->Spese_Postali;

                $interessi_prec = 0.00;
                $spese_not_precedenti = 0.00;
                $pagamenti_precedenti = 0.00;

                $importoInteressi = 0;
                $a_codici = $cls_elab->totaleCodici($partita);

                if($partita->ultimo_atto > 0){
                    $query = "SELECT * FROM atto WHERE ID = ".$partita->ultimo_atto." AND CC = '".$c."'";
                    $ultimo_atto_valido = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");// new atto($partita->ultimo_atto, $c);
                    $a_params = Array("importo_minimo"=>$importo_min);

                    if($cls_elab->checkProcessAtto("sollecito",$a_params,$ultimo_atto_valido)===false)
                        continue;

                    if($partita->Tipo=="IMMOBILI" || $partita->Tipo=="PUBBLICITA" || $partita->Tipo=="OSAP")
                        break;

                    if($cls_elab->checkProcess($ultimo_atto_valido)===false)
                        break;

                    if($primo_sollecito == "si")
                        break;

                    $ultimo_atto = $partita->Atto[count($partita->Atto)-1];
                    $pagamenti_precedenti = $cls_elab->pagamenti_completi($ultimo_atto);


                    $riferimento = $ultimo_atto->Riferimento + 1;

                    $interessi_prec = $ultimo_atto->Interessi_Precedenti+$ultimo_atto->Interessi;
                    $spese_not_precedenti = $ultimo_atto->Spese_Notifica_Precedenti + $ultimo_atto->Spese_Notifica + $ultimo_atto->CAN + $ultimo_atto->CAD;

                    $totaleCheck = $a_codici["TOTALE"]+$spese_not_precedenti+$interessi_prec;

                    if( number_format($totaleCheck,2)!=number_format($ultimo_atto->Totale_Dovuto,2)){
                        alert("Il sollecito pre ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborata a causa di incoerenza dei dati!");
                            break;
                    }

                    $importoInteressi = 0.00;

                }
                else{
                    $importoInteressi = 0.00;
                    $riferimento = 1;
                }

                $interessi = 0.00;
                $diritto_risc_min = 0.00;
                $diritto_risc_max = 0.00;

                $totale_dovuto = $a_codici["TOTALE"] + $spese_not + $spese_not_precedenti + $interessi + $interessi_prec;

                $note = "";

                //mysql_query('BEGIN');

                $query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
                $comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto")["Com"];// single_query($query);

                /*$salva = new atto(null,$c);
                $salva->DocumentTypeId = 11;
                $salva->PrintTypeId = $PrintTypeId;
                $salva->CC = $c;
                $salva->Comune_ID = $comune_id + 1;
                $salva->Partita_ID = $partita->ID;*/

                $a_param["DocumentTypeId"] = 11;
                $a_param["PrintTypeId"] = $PrintTypeId;
                $a_param["CC"] = $c;
                $a_param["Comune_ID"] = $comune_id + 1;
                $a_param["Partita_ID"] = $partita->ID;

                $ID_cronologico = 0;
                $anno_cronologico = 0;

                /*->ID_Cronologico = $ID_cronologico;
                $salva->Anno_Cronologico = $anno_cronologico;
                $salva->Data_Calcolo_Interessi = null;
                $salva->Data_Decorrenza_Interessi = null;
                $salva->Stato_Stampa = $stato_stampa;*/

                $a_param["ID_Cronologico"] = $ID_cronologico;
                $a_param["Anno_Cronologico"] = $anno_cronologico;
                $a_param["Data_Calcolo_Interessi"] = null;
                $a_param["Data_Decorrenza_Interessi"] = null;
                $a_param["Stato_Stampa"] = $stato_stampa;

                //$salva->Atto = "Sollecito pre ingiunzione";
                //$salva->Info_Cartella = $info_cart;

                $a_param["Atto"] = "Sollecito pre ingiunzione";
                $a_param["Info_Cartella"] = $info_cart;

                /*$salva->Data_Elaborazione = to_mysql_date($data_elaborazione);
                $salva->Modalita_Stampa = $modalitaStampa;
                $salva->Tipo_Ufficiale = "diretta";
                $salva->PrinterId = $PrinterId;*/

                $cls_date->changeFormat("DB",false);

                $a_param["Data_Elaborazione"] = $cls_date->GetDateDB($data_elaborazione,"IT");
                $a_param["Modalita_Stampa"] = $modalitaStampa;
                $a_param["Tipo_Ufficiale"] = "diretta";
                $a_param["PrinterId"] = $PrinterId;

                /*$salva->Riferimento = $riferimento;
                $salva->Note = $note;*/

                $a_param["Riferimento"] = $riferimento;
                $a_param["Note"] = $note;

                /*$salva->Spese_Notifica_Precedenti = $spese_not_precedenti;
                $salva->Spese_Notifica = $spese_not;

                $salva->Interessi = $interessi;
                $salva->Interessi_Precedenti = $interessi_prec;

                $salva->Diritto_Riscossione_Minimo = $diritto_risc_min;
                $salva->Diritto_Riscossione_Massimo = $diritto_risc_max;

                $salva->Totale_Dovuto = $totale_dovuto;*/

                $a_param["Spese_Notifica_Precedenti"] = $spese_not_precedenti;
                $a_param["Spese_Notifica"] = $spese_not;
                $a_param["Interessi"] = $interessi;
                $a_param["Interessi_Precedenti"] = $interessi_prec;
                $a_param["Diritto_Riscossione_Minimo"] = $diritto_risc_min;
                $a_param["Diritto_Riscossione_Massimo"] = $diritto_risc_max;
                $a_param["Totale_Dovuto"] = $totale_dovuto;

                $cls_db->Start_Transaction();
                $cls_db->Begin_Transaction();

                $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery($a_param,"atto"));
                //$control_salva = $salva->Insert(true);

                if($control_salva)
                {
                    $cls_db->End_Transaction();
                    $id_ingiunzione = $control_salva;//mysql_insert_id();

                    //mysql_query('COMMIT');
                    $query = "SELECT * FROM atto WHERE ID = ".$id_ingiunzione." AND CC = '".$c."'";
                    $ing = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($id_ingiunzione, $c);
                    $ID_ing = $ing->Comune_ID;
                    $ID_partita = $partita->Comune_ID;


                    $pdf->SetFont('Arial', '', 10);

                    $array_value = array();

                    $array_value[] = $ID_ing;
                    $array_value[] = $ID_partita;
                    $array_value[] = $nome_utente;
                    $array_value[] = $info_cart;
                    $array_value[] = number_format($totale_dovuto,2,",",".");

                    $y = $pdf->setRow( $array_value,  "down" , $styleDash, null,0,$array_width);

                    //$cls_pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);

                    if( $y > 266 )
                    {

                        $y2_vert = $pdf->getY();

                        $pdf->addLines(); //crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);

                        $pdf->AddPage();
                        $pdf->Ln(10);

                        $pdf->SetFont('Arial', 'B', 11);

                        $y1_vert = $pdf->setRow( $array_intestaz,  "up_down" , $styleRetta, null,0,$array_width); //crea_riga($pdf , $array_width, $array_intestaz , "up_down" , $styleRetta);

                    }

                    $cont_result++;

                }
                else
                {
                    $cls_db->Rollback();
                    //mysql_query('ROLLBACK');
                    $cls_db->End_Transaction();
                }

                break;

            }

        }

	}
	
	$y2_vert = $pdf->getY();
	
	//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
    //$pdf->setRow( $y1_vert,  $y2_vert , $styleDash, null,0,$array_width);
    $pdf->addLines();
	
	$pdf->Output( $file_elenco , 'F');

	if($cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

<?php include(INC."/footer.php"); ?>