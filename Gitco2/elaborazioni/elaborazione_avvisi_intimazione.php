<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";*/

include_once INC . "/headerAjax.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_html.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_math.php";

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();
$cls_math = new cls_math();

//AGGIUNGERE SANZIONE DA INGIUNZIONE


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($c);


if( $comune->Gestore_ID != 0 )
{
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Gestore_ID']);
}
else
{
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Info_ID']);
}

$gestore = $comune->Gestore;

//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir(  ATTI ."/". $c . "/Intimazione_ad_adempiere/Elenco_elaborazioni" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_avv_intimaz_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($download);

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
        <span class="titolo font18 under_decor">Elaborazione Avvisi di intimazione ad adempiere</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div id=vedi_file></div>
    </div>
</div>


<?php
$modalitaStampa = $cls_help->getVar('modalita_stampa');

$a_PrintType = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM print_type WHERE Modalita_Stampa='".$modalitaStampa."'"));
$PrintTypeId = $a_PrintType['Id'];

$data_calcolo = $cls_help->getVar('data_calcolo');
if($data_calcolo=="")
    $data_calcolo = date("d-m-Y");

$da_n_elenco  = $cls_help->getVar('da_n_elenco');
$a_n_elenco  = $cls_help->getVar('a_n_elenco');

$daco  = strtoupper($cls_help->getVar('daco'));
$dano  = strtoupper($cls_help->getVar('dano'));

$acog  = strtoupper($cls_help->getVar('acog'));
$anom  = strtoupper($cls_help->getVar('anom'));

$PrinterId = $cls_help->getVar("PrinterId");

$da_anno = $cls_help->getVar('da_anno');
$ad_anno = $cls_help->getVar('ad_anno');

$tipo_partita = $cls_help->getVar('tipo_partita');

$danotif = $cls_help->getVar('da_data');
$anotif = $cls_help->getVar('a_data');

$data_elaborazione = $cls_help->getVar('data_elab');
$anno_elab = explode("/", $data_elaborazione);
$anno_elab = $anno_elab[2];

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

$y1_vert = $pdf->setRow($array_intestaz, "up_down" , $styleRetta, null, 0 ,$array_width );
//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down" , $styleRetta );

$dateTemp = new cls_DateTime($data_calcolo,"IT",false);

$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$dateTemp->GetYear()."' AND Tipo_Riscossione = '*****'";
$parametri = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");

if($parametri->ID == null) {
    $cls_help->alert("l'anno " . $dateTemp->GetYear() . " non è presente nei parametri annuali!");
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

/**		SELEZIONE UTENTI 			*/
$query_utente = $cls_elab->da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = $cls_db->getResults($cls_db->ExecuteQuery($query_utente));//mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
//SELEZIONE ANNI
$where = "(Flag_Blocco_Coazione != 'si' || Flag_Blocco_Coazione is null) ";
if($da_anno>0)
    $where.= "AND Anno_Riferimento >= '".$da_anno."' ";
if($ad_anno>0)
    $where.= "AND Anno_Riferimento <= '".$ad_anno."' ";

$query_partita = $cls_elab->da_a_partita( $c , $da_n_elenco , $a_n_elenco , $where );
$array_partite = $cls_db->getResults($cls_db->ExecuteQuery($query_partita));

/** 	SELEZIONE Date Notifica		*/
$query_notifica = $cls_elab->da_a_data( $c , "Avviso di intimazione ad adempiere" , "Data_Notifica" , $cls_date->GetDateDB($danotif,"IT") , $cls_date->GetDateDB($anotif,"IT") );
$array_notif = $cls_db->getResults($cls_db->ExecuteQuery($query_notifica));//mysql_array( $query_notifica );

$num_atti = count($array_notif);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite); 
$cont_result = 0;


	for( $l=0; $l < $num_atti; $l++ )
	{	
		echo "<script>update(".ceil($l*100/$num_atti).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		for( $k=0; $k < $num_partite; $k++ )
		{
			if( $array_notif[$l]['Partita_ID'] == $array_partite[$k]['ID'])
			{		
				if($tipo_partita != "")
					if($array_partite[$k]['Tipo']!=$tipo_partita)
						continue;
			
			for( $j=0; $j<$num_utenti; $j++ )
			{
				if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )
				{
					set_time_limit(30);
                    $partita = $cls_elab->getDataPartita($array_partite[$k]['ID'],$c,$array_partite[$k]['Anno_Riferimento']);//new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
                    if($partita->ultimo_atto!=$array_notif[$l]['ID'])
                        break;

                    $tributo = $partita->Tributo;
                    $info_cart = $tributo[0]->Info_Cartella;

                    $flag_blocco_diritto_riscossione = $partita->Flag_Blocco_Diritto_Riscossione;

                    $query = "SELECT * FROM utente WHERE ID = '".$array_partite[$k]['Utente_ID']."' AND CC_Comune = '".$c."'";
                    $utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");

                    //$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
                    $nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;

                    if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00" && $utente->Data_Morte!="")
                        break;

                    //$parametri = new parametri_annuali($c, $data_calcolo , $partita->Tipo );
                    $importo_min = $parametri->Importo_Minimo;
                    $diritto_min = $parametri->Diritto_Riscossione_Minimo;
                    $diritto_max = $parametri->Diritto_Riscossione_Massimo;

                    $spese_not = $parametri->Spese_Notifica;

                    $query = "SELECT * FROM atto WHERE ID = ".$partita->ultimo_atto." AND CC = '".$c."'";
                    $ultimo_atto_valido = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");
                    //$ultimo_atto_valido = new atto($partita->ultimo_atto, $c);
                    $ultimo_atto_valido->Scadenze_Rate = explode("*",$ultimo_atto_valido->Scadenze_Rate);

                    //var_dump($ultimo_atto_valido);
                    if($cls_elab->checkProcessAtto("avviso",Array("importo_minimo"=>$importo_min),$ultimo_atto_valido)===false)
                        break;
					
					$ultimo_atto = $partita->Atto[count($partita->Atto)-1];
                    if($cls_elab->checkPagamenti(Array("importo_minimo"=>$importo_min),$ultimo_atto)===false)
                        break;

                    $pagamenti_precedenti = $cls_elab->pagamenti_completi($ultimo_atto);
					$giacenza = $ultimo_atto->Stato_Notifica;
					$ind_validato = $ultimo_atto->Indirizzo_Validato;
					$anomalia = $ultimo_atto->Motivo_Notifica;
					$rielabora_flag = $ultimo_atto->Rielabora_Flag;
					$data_interessi = $ultimo_atto->Data_Calcolo_Interessi;
                    if($data_interessi==null)
                        $data_interessi = $cls_date->Get_DateNewFormat($ultimo_atto->Data_Notifica,"DB");

					$interessi_prec = $ultimo_atto->Interessi_Precedenti+$ultimo_atto->Interessi;
                    $spese_not_precedenti = $ultimo_atto->Spese_Notifica_Precedenti + $ultimo_atto->Spese_Notifica + $ultimo_atto->CAN + $ultimo_atto->CAD;

					$interessi = 0.00;
                    $diritto_risc_min = 0.00;
                    $diritto_risc_max = 0.00;

                    $a_codici = $cls_elab->totaleCodici($partita);

                    $totaleCheck = $a_codici["TOTALE"]+$ultimo_atto->Spese_Notifica_Precedenti+$ultimo_atto->Interessi;
                    $totaleCheck+= $ultimo_atto->Interessi_Precedenti+$ultimo_atto->Spese_Notifica+$ultimo_atto->CAN+$ultimo_atto->CAD;
                    if( number_format($totaleCheck,2)!=number_format($ultimo_atto->Totale_Dovuto,2)){
                        $cls_help->alert("L'Avviso di Intimazione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborato a causa di incoerenza dei dati!");
                        break;
                    }

                    $totale_dovuto = $a_codici["TOTALE"] + $spese_not + $spese_not_precedenti + $interessi + $interessi_prec;

                    if($flag_blocco_diritto_riscossione!="si" && $gestore->Tipo == "Concessionario")
                    {
                        $importo_calcolo_diritto = $totale_dovuto - $pagamenti_precedenti;
                        $diritto_risc_min = $importo_calcolo_diritto * $diritto_min / 100;
                        $diritto_risc_max = $importo_calcolo_diritto * $diritto_max / 100;
                    }

                    $cls_db->Start_Transaction();
                    $cls_db->Begin_Transaction();
						
					$query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
					$result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");
                    $comune_id = isset($result["Com"])?$result["Com"]:0;
					//$comune_id = single_query($query);
					
					$query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Avviso di intimazione ad adempiere'";
                    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");
                    $crono_id = isset($result["Com"])?$result["Com"]:0;
					//$crono_id = single_query($query);

					$salva = new stdClass();

                    $salva->Riferimento = $ultimo_atto->Riferimento;
                    $salva->Note = $ultimo_atto->Comune_ID." (ID Atto di riferimento)";
                    $salva->DocumentTypeId = 4;
                    $salva->PrintTypeId = $PrintTypeId;
					$salva->CC = $c;
					$salva->Comune_ID = $comune_id + 1;
					$salva->Partita_ID = $partita->ID;
					$salva->Anno_Cronologico = "0";
					$salva->ID_Cronologico = "0";
					$salva->Atto = "Avviso di intimazione ad adempiere";
					$salva->Info_Cartella = $ultimo_atto->Info_Cartella;
					$salva->Stato_Stampa = "Da stampare";
					$salva->Modalita_Stampa = $modalitaStampa;
					$salva->Tipo_Ufficiale = "diretta";
                    $salva->PrinterId = $PrinterId;
					$salva->Data_Elaborazione = $cls_date->GetDateDB($data_elaborazione,"IT");
					$salva->Data_Calcolo_Interessi = $ultimo_atto->Data_Calcolo_Interessi;
                    $salva->Data_Decorrenza_Interessi = $ultimo_atto->Data_Decorrenza_Interessi;

					$salva->Spese_Notifica = $spese_not;
                    $salva->Spese_Notifica_Precedenti = $spese_not_precedenti;
					$salva->Interessi_Precedenti = $interessi_prec;

					$salva->Totale_Dovuto = $totale_dovuto;

					$salva->Diritto_Riscossione_Minimo = $diritto_risc_min;
					$salva->Diritto_Riscossione_Massimo = $diritto_risc_max;

					//$control_salva = $salva->Insert(true);
                    $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$salva,"atto"));
					
					if($control_salva)
					{
						$id_avviso = $control_salva;
                        $pignoramento = $ultimo_atto_valido->Check_Pignoramento;
						if($pignoramento!=null && $id_avviso!=null)
						{
                            $query = "SELECT * FROM coefficiente_coazione WHERE CC = '*****' AND ( ( Credito_Minimo <= ".$pignoramento->Importo_Dovuto." AND Credito_Massimo >= ".$pignoramento->Importo_Dovuto." ) OR ( Credito_Massimo = 0 AND Credito_Minimo <= ".$pignoramento->Importo_Dovuto." ))";
                            $coeff_coazione_pre = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"coefficiente_coazione");// new coefficiente_coazione("*****",$pignoramento->Importo_Dovuto);
                            if($coeff_coazione_pre->Percentuale==null)	$coeff_coazione_pre->Percentuale=0;

                            $query = "SELECT * FROM coefficiente_coazione WHERE CC = '*****' AND ( ( Credito_Minimo <= ".$salva->Totale_Dovuto." AND Credito_Massimo >= ".$salva->Totale_Dovuto." ) OR ( Credito_Massimo = 0 AND Credito_Minimo <= ".$salva->Totale_Dovuto." ))";
                            $coeff_coazione_post = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"coefficiente_coazione");
							//$coeff_coazione_post = new coefficiente_coazione("*****",$salva->Totale_Dovuto);
							if($coeff_coazione_post->Percentuale==null)	$coeff_coazione_post=0;
							
							$pignoramento->Atto_ID = $id_avviso;
							$pignoramento->Importo_Dovuto = $salva->Totale_Dovuto;
							
							if($coeff_coazione_pre->Percentuale!=$coeff_coazione_post->Percentuale)
							{
                                $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$pignoramento->ID." AND CC = '".$c."'";
								$pignoramento_spese = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_spese");//new spese_pignoramento($pignoramento->ID, $c);
								$pignoramento_spese->Incremento_Percentuale = $coeff_coazione_post->Percentuale;
								$array_spese = $cls_elab->spese_array($pignoramento_spese);
								
								for($x_spesa=1;$x_spesa<11;$x_spesa++)
								{
									if($array_spese[$x_spesa]['ID']>0)
										if($array_spese[$x_spesa]['tipo_spesa']!="A KM")
										{
											$rimborso_pulito = $array_spese[$x_spesa]['rimborso'] / ( 1 + $coeff_coazione_pre->Percentuale/100 );
											
											$array_spese[$x_spesa]['rimborso'] = $rimborso_pulito * ( 1 + $coeff_coazione_post->Percentuale/100 );
										}
								}
								
								$cls_elab->inserisci_spese_array($array_spese,$pignoramento_spese);
								//$pignoramento_spese->Update($pignoramento_spese->ID);

                                $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$pignoramento_spese,"pignoramento_spese",array("ID" => $pignoramento_spese->ID)));
                                if(!$control_salva){
                                    $cls_db->Rollback();
                                }
								
								$pignoramento->Totale_Spese_Accessorie = $pignoramento_spese->Totale_Rimborso;
							}

							$pignoramento->Totale_Dovuto = ($pignoramento->Importo_Dovuto + $pignoramento->Totale_Spese_Accessorie + $pignoramento->Totale_Spese_Notifica);

							$control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$pignoramento,"pignoramento_generale",array("ID" => $pignoramento->ID)));
							//$pignoramento->Update($pignoramento->ID);
                            if(!$control_salva){
                                $cls_db->Rollback();
                            }
							
						}

						//mysql_query('COMMIT');

						$ID_avv = $salva->Comune_ID;
						$ID_partita = $partita->Comune_ID;

                        $nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;
						
						$pdf->SetFont('Arial', '', 10);

						$array_value = array();
						
						$array_value[] = $ID_avv;
						$array_value[] = $ID_partita;						
						$array_value[] = $nome_utente;				
						$array_value[] = $salva->Note;
						$array_value[] = $cls_math->conv_num($salva->Totale_Dovuto)." Euro";

						$y = $pdf->setRow($array_value, "down", $styleDash ,null, 0, $array_width);
						//$y = crea_riga ( $pdf , $array_width, $array_value, "down", $styleDash );
						
						if( $y > 266 )
						{
							$y2_vert = $pdf->getY();

							$pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
							//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
							
							$pdf->AddPage();
							$pdf->Ln(10);
							
							$pdf->SetFont('Arial', 'B', 11);

                            $y1_vert = $pdf->setRow($array_intestaz, "up_down", $styleRetta, null, 0, $array_width);
							//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down", $styleRetta );
							
						}
						
						$cont_result++;
					
					}
					else
					{
					    $cls_db->Rollback();
						//mysql_query('ROLLBACK');
					}
                    $cls_db->End_Transaction();
					break;
					
				}//CHIUSURA IF			
			
			}//CHIUSURA UTENTI
	
			break;
			
			}//CHIUSURA IF
			
		}//CHIUSURA PARTITE
	
	}//CHIUSURA ATTI
	
	$y2_vert = $pdf->getY();

	$pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
	//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
	
	$pdf->Output( $file_elenco , 'F');
	
	if($num_atti == 0 || $cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

<?php include(INC."/footer.php"); ?>