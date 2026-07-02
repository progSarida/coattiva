<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

//include_once($_SESSION['_path']);
//include_once(ROOT."/_parameter.php");//dati database

//include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_elaborazioniUtils.php";
include_once CLS . "/cls_CoazioneUtils.php";

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);
$cls_stampe = new cls_Stampe();
$cls_elab = new cls_elaborazioniUtils();
$cls_coaz = new cls_Coazione();

/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";*/

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

/*class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		$this->Cell(0, 5, "Elenco Ingiunzioni" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage() + 1) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}*/

function array_completo( $c = "*****" )
{
    $cls_db = new cls_db();

    $query = "SELECT * FROM forma_giuridica_societa WHERE CC = '".$c."'";
    $result = $cls_db->ExecuteQuery($query);// mysql_query();
    $results = array();

    while($line = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
        $results[$line['ID']] = $line;
    }

    return $results;

}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$_SESSION['progress'] = "0.00";
session_write_close();

//$forma = new forma_giuridica();
$array_forma = array_completo();

//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir( ATTI ."/". $c . "/Ingiunzioni/Elenchi" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_ing_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = SUPER_WEB_ROOT.$cls_utils->mostra_file_path($download);

$flag_ultimo_atto = $cls_help->getVar('ultimo_atto');

//COGNOME NOME
$daco  = strtoupper($cls_help->getVar('daco'));
$acog  = strtoupper($cls_help->getVar('acog'));
$dano  = strtoupper($cls_help->getVar('dano'));
$anom  = strtoupper($cls_help->getVar('anom'));

//PARTITA
$da_partita  = $cls_help->getVar('da_n_elenco');
$a_partita  = $cls_help->getVar('a_n_elenco');

//ANNI RIFERIMENTO
$da_anno = $cls_help->getVar('da_anno');
$ad_anno = $cls_help->getVar('ad_anno');

$tipo_partita = $cls_help->getVar('tipo_partita');

//DATA ELABORAZIONE
$data_elab = $cls_help->getVar('data_elab');
$da_elab = $cls_help->getVar('da_elab');
$a_elab = $cls_help->getVar('a_elab');

//UFFICIALE
$tipo_ufficiale = $cls_help->getVar('tipo_ufficiale');

//DATA NOTIFICA
$data_notif = $cls_help->getVar('data_notif');
$da_notif = $cls_help->getVar('da_notif');
$a_notif = $cls_help->getVar('a_notif');

//STATI NOTIFICA ( modalita' - giacenza[ind validato] - anomalie )
$modalita_notif = $cls_help->getVar('modalita');
$stato_giacenza = $cls_help->getVar('giacenza');
$indirizzo_validato = $cls_help->getVar('indirizzo_validato');
$anomalie = $cls_help->getVar('anomalie');

//RATEIZZAZIONE
$rateizzazione = $cls_help->getVar('rateizzazione');

//RIELABORA FLAG
$rielabora = $cls_help->getVar('rielaborazione');

//DATA STAMPA
$data_stampa = $cls_help->getVar('data_stampa');
$da_stampa = $cls_help->getVar('da_stampa');
$a_stampa = $cls_help->getVar('a_stampa');

//STATO STAMPA
$stato_stampa = $cls_help->getVar('stato_stampa');

//STATO PAGAMENTO
$pagamento = $cls_help->getVar('pagamento');

//BLOCCO COAZIONE
$blocco = $cls_help->getVar('blocco');

//TRIBUNALE
$filtro_tribunale = $cls_help->getVar('tribunale');

//SALTA PAGINA
$filtro_salta = $cls_help->getVar('salta');

//ORDINAMENTO
$ordinamento = $cls_help->getVar('ordinamento');

/**		SELEZIONE UTENTI 			*/

$query = "(SELECT ID, Nome, Cognome AS utente_cognome FROM utente ";
$query.= "WHERE Cognome != '' AND CC_Comune = '".$c."' ";
if($daco != null)
{
    $query.= "AND ( ( Cognome > '".addslashes($daco)."' ) ";
    $query.= "AND ( Cognome < '".addslashes($acog)."' ) ";
    $query.= "OR ( Cognome = '".addslashes($daco)."' ";
    if($dano != null)
    {
        $query.= "AND Nome >= '".addslashes($dano)."' ";
    }

    $query.= ") OR ( Cognome = '".addslashes($acog)."' ";
    if($anom != null)
    {
        $query.= "AND Nome <= '".addslashes($anom)."' ";
    }
    $query.= ") ) ";
}

$query.= " ) ";

$query.= "UNION ";
$query.= "(SELECT ID, Nome, Ditta AS utente_cognome FROM utente ";
$query.= "WHERE Ditta != '' AND CC_Comune = '".$c."' ";

if($daco != null)
{
    $query.= "AND ( Ditta >= '".addslashes($daco)."' AND Ditta <= '".addslashes($acog)."' ) ";
}
$query.= ") ";
$query.= "ORDER BY utente_cognome ASC, Nome ASC";

//$query_utente = da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = $cls_db->getResults($cls_db->ExecuteQuery($query));// mysql_array( $query_utente );


/** 	SELEZIONE PARTITE			*/
$where_anno = null;
if( $da_anno != null && $ad_anno != null )
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."'";


$query = "SELECT * FROM partita_tributi ";
$query.= "WHERE CC = '".$c."' ";
if($da_partita != null)
{
    $query.= "AND ( Comune_ID >= '".$da_partita."' AND Comune_ID <= '".$a_partita."' ) ";
}
if($where_anno != null)
{
    $query.= "AND ".$where_anno." ";
}

$query.= "ORDER BY Comune_ID ASC";
//$query_partita = da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = $cls_db->getResults($cls_db->ExecuteQuery($query));// mysql_array( $query_partita );


/** 	SELEZIONE ATTI	*/
$campi_stati = array("atto.Stato_Stampa" , "atto.Motivo_Notifica", "atto.Modalita_Notifica", "atto.Tipo_Ufficiale");
$valori_stati = array ( $stato_stampa , $anomalie, $modalita_notif, $tipo_ufficiale );

$query_stati = $cls_stampe->where_stati_notifica($campi_stati, $valori_stati);

$campi_array = array();
$array_da_data = array();
$array_a_data = array();
$date_vuote = array();
if($data_elab!="assente")
{
	$campi_array[] = "Data_Elaborazione";
	$array_da_data[] = $cls_date->GetDateDB($da_elab,"IT");
	$array_a_data[] = $cls_date->GetDateDB($a_elab,"IT");
}
else 
	$date_vuote[] = "atto.Data_Elaborazione";

if($data_notif!="assente")
{
	$campi_array[] = "Data_Notifica";
	$array_da_data[] = $cls_date->GetDateDB($da_notif,"IT");
	$array_a_data[] = $cls_date->GetDateDB($a_notif,"IT");
}
else
	$date_vuote[] = "atto.Data_Notifica";

if($data_stampa!="assente")
{
	$campi_array[] = "Data_Stampa";
	$array_da_data[] = $cls_date->GetDateDB($da_stampa,"IT");
	$array_a_data[] = $cls_date->GetDateDB($a_stampa,"IT");
}
else
	$date_vuote[] = "atto.Data_Stampa";

$query_date_vuote = $cls_stampe->where_date_vuote($date_vuote);
$query_giacenza = $cls_stampe->where_giacenza($stato_giacenza, $indirizzo_validato);


$bloccoSingolo = $cls_help->getVar("blockSingleAct");
if($bloccoSingolo == "si") $where4 = "atto.archived IS NOT NULL";
else if($bloccoSingolo == "no") $where4 = "atto.archived IS NULL";
else $where4 = null;


$query_date = $cls_stampe->da_a_data_array_order( $c , "Ingiunzione" , $campi_array , $array_da_data , $array_a_data , $query_stati, $ordinamento, $query_date_vuote, $query_giacenza, $where4 );


$array_atti = $cls_db->getResults($cls_db->ExecuteQuery($query_date));

$num_atti = count($array_atti);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite);

/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
	$pdf->SetMargins(10, 10, 10);
	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$pdf->AddPage('L');
	$pdf->SetFont('Arial', 'B', 10);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();
	$altezza_pag = $pdf->getPageHeight();	
	
	
	$pdf->SetAutoPageBreak(false);
	$pdf->Ln(5);
	
	$array_width = array();
	$array_intestaz_1 = array();
	$array_intestaz_2 = array();
	
	$array_width[] = 25;						$array_intestaz_1[] = "Cronologico";				$array_intestaz_2[] = "Partita";
	$array_width[] = 50;						$array_intestaz_1[] = "COD-Utente";					$array_intestaz_2[] = "CF / PI";
	$array_width[] = 120;						$array_intestaz_1[] = "Indirizzo";					$array_intestaz_2[] = "Informazioni Cartella";
	$array_width[] = $larghezza_pag - 225 - 20;	$array_intestaz_1[] = "Data Elaborazione - Calcolo";$array_intestaz_2[] = "Data Stampa - Notifica";
	$array_width[] = 30;						$array_intestaz_1[] = "Dovuto";						$array_intestaz_2[] = "Pagato";
	
	$pdf->setCellPaddings(2,1,2,0);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
    $y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);// crea_riga($pdf , $a_width, $a_header_1, "up" , $styleRetta);
	
	$pdf->setCellPaddings(2,0,2,1);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
    $y1_vert = $pdf->setRow($array_intestaz_2,"down",$styleRetta,null,0,$array_width);
	
/**
	//////////////////////////////////////////////////////////////////////////////
*/
		
	$cont_result = 0;
	
	$parz_dovuto = 0.00;
	$parz_pagato = 0.00;
	
	$tot_gen_dovuto = 0.00;
	$tot_gen_pagato = 0.00;
	
	$a_sanzioniCDS = array();
	
	$ctrl_linea = "no";
	if($num_atti == 0){
	
		if(session_status() == PHP_SESSION_NONE)session_start();
		$_SESSION['progress'] = "100";
		session_write_close();
		
		echo json_encode([
			"error" => 2,
			"msg" => "Nessun risultato trovato!"
		]);
		
		die;
	}
	for( $l=0; $l < $num_atti; $l++ )//FOR ATTI
	{	
		set_time_limit(100);
		
		if(session_status() == PHP_SESSION_NONE)session_start();
		$_SESSION['progress'] = number_format(($l*100)/$num_atti ,2);
		session_write_close();
		
		for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
		{			
			if($tipo_partita != "")
				if($array_partite[$k]['Tipo']!=$tipo_partita)
					continue;
				
			if( $array_atti[$l]['Partita_ID'] == $array_partite[$k]['ID'] )//IF ATTO/PARTITA
			{		
				if($blocco=="Si")
				{
					if($array_partite[$k]['Flag_Blocco_Coazione']!="si")
						break;
				}
				
				if($blocco=="No")
				{
					if($array_partite[$k]['Flag_Blocco_Coazione']=="si")
						break;
				}
				
				for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
				{
					if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
					{
						set_time_limit(30);
						
						//$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
                        //$query = "SELECT * FROM partita_tributi WHERE ID = '".$array_partite[$k]['ID']."' AND CC = '".$c."'";
                        //if($a!=null)	$query.=" AND Anno_Riferimento = '".$array_partite[$k]['Anno_Riferimento']."'";
                        $partita = $cls_elab->getDataPartita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);

						if($flag_ultimo_atto=="si")
						{
							$id_ultimo_atto = $partita->Atto[count($partita->Atto)-1]->ID;
						
							if($id_ultimo_atto!=$array_atti[$l]['ID'])
								break;
						}
						    $query = "SELECT * FROM atto WHERE ID = ".$array_atti[$l]['ID']." AND CC = '".$c."'";
                            $ing = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");
							//$ing = new atto( $array_atti[$l]['ID'], $c );
							
							if($rielabora == "Si" && $ing->Rielabora_Flag != "si")
								break;
								
							if($rielabora == "No" && $ing->Rielabora_Flag == "si")
								break;
								
							if( $rateizzazione == "Si" && $ing->Rate_Previste < 1 )
								break;
								
							if( $rateizzazione == "No" && $ing->Rate_Previste > 0 )
								break;
							
							$ID_ing = $ing->Comune_ID;
							$ID_partita = $partita->Comune_ID;

                            $query = "SELECT * FROM utente WHERE ID = '".$array_partite[$k]['Utente_ID']."' AND CC_Comune = '".$c."'";
                            $utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");
                            $utente = $cls_elab->GetDataUtente($utente);

                            //var_dump($utente);
                            //die;

							//$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
                            $query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$utente->Residenza->CC_Indirizzo."' AND Tipo = 'tribunale' LIMIT 1";
                            $tribunale = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_giudiziario");
							//$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
							$query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$tribunale->CC_Ufficio."' AND Tipo = 'istituto' LIMIT 1";
                            $ufficio_vendite = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_giudiziario");
							//$ufficio_vendite = new ufficio_giudiziario($tribunale->CC_Ufficio, "istituto");
								
							//CONTROLLO TRIBUNALE
							if($filtro_tribunale!=null)
							{
								if($tribunale->CC_Ufficio != $filtro_tribunale)
									break;
							}
								
							$indirizzo_utente = $cls_coaz->righe_indirizzo((array)$utente);// $utente->righe_indirizzo();
							$forma_descr = "";
							if($utente->Forma_Giuridica!='')
							{
								$index_value = $utente->Forma_Giuridica;
								if($index_value>0)
									$forma_descr = $array_forma[$index_value]['Sigla'];
							}
								
							$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
							
							if( strlen($nome_utente) > 22 )
								$nome_utente = substr($nome_utente,0,21)."...";
							
							if( $utente->Genere=="D" )
								$CF_PI = $utente->Partita_Iva;
							else 
								$CF_PI = $utente->Codice_Fiscale;
														
							$info_cart = $ing->Info_Cartella;
							$totale_dovuto = $ing->Totale_Dovuto;
							$tot_pagamenti = $cls_stampe->totale_pagamenti($ing);
							
							if($tot_pagamenti == 0 )
							{
								if($pagamento!="Nessuno" && $pagamento!="Nessuno_Parziale" && $pagamento!="")
									break;
							}
							else if($tot_pagamenti<$totale_dovuto)
							{
								if($pagamento!="Parziale" && $pagamento!="Nessuno_Parziale" && $pagamento!="")
									break;
							}
							else if($tot_pagamenti>=$totale_dovuto)
							{
								if($pagamento!="Totale" && $pagamento!="")
									break;
							}
							
							$parz_dovuto += $totale_dovuto;
							$parz_pagato += $tot_pagamenti;
							
							$stati_atto = $ing->Stato_Esecuzione." / ".$ing->Stato_Stampa;
							if($ing->Stato != "")
								$stati_atto.=" / ".$ing->Stato;
							
							$pdf->SetFont('Arial', '', 8);
	
							$array_value_1 = array();
							$array_value_2 = array();
							
							$array_value_1[] = $ing->ID_Cronologico."/".$ing->Anno_Cronologico;
							$array_value_1[] = "(".$utente->Comune_ID.") ".$nome_utente;
							$array_value_1[] = $indirizzo_utente['Completo'];						
							$array_value_1[] = $cls_date->Get_DateNewFormat($ing->Data_Elaborazione,"DB")." - ".$cls_date->Get_DateNewFormat($ing->Data_Calcolo_Interessi,"DB");
							$array_value_1[] = number_format($totale_dovuto,2,",",".")." Euro";
							
							$array_value_2[] = $ID_partita."/".$partita->Anno_Riferimento;
							$array_value_2[] = strtoupper($CF_PI);
							$array_value_2[] = $info_cart;
							$array_value_2[] = ($ing->Data_Stampa != null ? $cls_date->Get_DateNewFormat($ing->Data_Stampa,"DB") : 'Assente')." - ".($ing->Data_Notifica != null ? $cls_date->Get_DateNewFormat($ing->Data_Notifica,"DB") : 'Assente');
							$array_value_2[] = number_format($tot_pagamenti,2,",",".")." Euro";
													
							$array_align = array("L","L","L","L","R");
							
							$pdf->setCellPaddings(2,2,2,0);
							//$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align );
                            $y = $pdf->setRow($array_value_1,$ctrl_linea,$styleDash,$array_align,0,$array_width);
							
							if($ctrl_linea == "no")	$ctrl_linea = "up";
							
							$pdf->setCellPaddings(2,0,2,2);
							
							if( $y > $altezza_pag - 40)
							{
								//$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
                                $y = $pdf->setRow($array_value_2,"no",$styleDash,$array_align,0,$array_width);
								$y2_vert = $pdf->getY();

                                $margine = $pdf->getMargins();
                                $x = $margine['left'];
                                for($kk=0 ; $kk < count($array_width)-1 ; $kk++ )
                                {
                                    $x += $array_width[$kk];
                                    $pdf->Line( $x , $y1_vert , $x , $y2_vert , $styleDash );
                                }
								//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
								
								$tot_gen_dovuto += $parz_dovuto;
								$tot_gen_pagato += $parz_pagato;
								
								$array_width_fine = array();
								$array_fine_1 = array();
								$array_fine_2 = array();
								
								$array_width_fine[] = 195;						
								$array_width_fine[] = $larghezza_pag - 225 - 20;
								$array_width_fine[] = 30;						
								
								$array_fine_1[] = "PARZIALI DI PAGINA";			
								$array_fine_2[] = "";
								$array_fine_1[] = "Totali di pagina dovuti";	
								$array_fine_2[] = "Totali di pagina pagati";
								$array_fine_1[] = number_format($parz_dovuto,2,",",".")." Euro";
								$array_fine_2[] = number_format($parz_pagato,2,",",".")." Euro";
								
								$parz_dovuto = 0.00;
								$parz_pagato = 0.00;
								
								$array_align_fine = array("L","L","R");
									
								$pdf->SetFont('Arial', 'B', 8);
								
								$pdf->setCellPaddings(2,1,2,0);
                                $y = $pdf->setRow($array_fine_1,"up",$styleRetta,$array_align_fine,0,$array_width_fine);
								//$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
								$pdf->setCellPaddings(2,0,2,1);
                                $y = $pdf->setRow($array_fine_2,"down",$styleRetta,$array_align_fine,0,$array_width_fine);
								//$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
								
								$control_pagine = 0;
								if($l<$num_atti-1)
								{
								
								$pdf->AddPage();
								$pdf->Ln(5);
								
								$pdf->SetFont('Arial', 'B', 10);
								
								$pdf->setCellPaddings(2,1,2,0);
                                $y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);
								//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
								
								$pdf->setCellPaddings(2,0,2,1);
                                $y1_vert = $pdf->setRow($array_intestaz_2,"down",$styleRetta,null,0,$array_width);
								//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);

								$ctrl_linea = "no";
								
								}
								else 
									$control_pagine = 1;
							}
							else
                                $y = $pdf->setRow($array_value_2,"no",$styleDash,$array_align,0,$array_width);
								//$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
							
							if($partita->Tipo=="CDS" && $c=="B838"){
								for($trib = 0;$trib<count($partita->Tributo);$trib++){
								
									if($partita->Tributo[$trib]->Codice_Tributo == 5242){
										$a_sanzioniCDS[] = $partita->Tributo[$trib]->Imposta;
									}	
								
								}
							}													
							
							
							$cont_result++;
							
							break;		//Una partita pu� avere un solo intestatario per cui una volta trovato si pu� uscire dal ciclo degli utenti		
									
					}//CHIUSURA IF PARTITA/UTENTE
	
				}//CHIUSURA FOR UTENTI
			
				break;		//Un atto pu� corrispondere ad una sola partita per cui una volta trovato si pu� uscire dal ciclo delle partite
				
			}//CHIUSURA IF ATTO/PARTITA
				
		}//CHIUSURA PARTITE
			
	}//CHIUSURA ATTII
	
		$y2_vert = $pdf->getY();
		
		//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
        $margine = $pdf->getMargins();
        $x = $margine['left'];
        for($k=0 ; $k < count($array_width)-1 ; $k++ )
        {
            $x += $array_width[$k];
            $pdf->Line( $x , $y1_vert , $x , $y2_vert , $styleDash );
        }
		
		$tot_gen_dovuto += $parz_dovuto;
		$tot_gen_pagato += $parz_pagato;
		
		$array_width_fine = array();
		$array_fine_1 = array();
		$array_fine_2 = array();
		
		$array_width_fine[] = 195;
		$array_width_fine[] = $larghezza_pag - 225 - 20;
		$array_width_fine[] = 30;
		
		$array_fine_1[] = "PARZIALI DI PAGINA";
		$array_fine_2[] = "";
		$array_fine_1[] = "Totali di pagina dovuti";
		$array_fine_2[] = "Totali di pagina pagati";
		$array_fine_1[] = number_format($parz_dovuto,2,",",".")." Euro";
		$array_fine_2[] = number_format($parz_pagato,2,",",".")." Euro";
		
		$parz_dovuto = 0.00;
		$parz_pagato = 0.00;
		
		$array_align_fine = array("L","L","R");
		
		$pdf->SetFont('Arial', 'B', 9);
		
		$pdf->setCellPaddings(2,2,2,0);
        $y = $pdf->setRow($array_fine_1,"up",$styleRetta,$array_align_fine,0,$array_width_fine);
		//$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
		$pdf->setCellPaddings(2,0,2,2);
        $y = $pdf->setRow($array_fine_2,"down",$styleRetta,$array_align_fine,0,$array_width_fine);
		//$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );

	
	$pdf->setPrintHeader(false);
	$pdf->addPage();	
	$pdf->setPrintFooter(false);
	
	if($daco != "")
		$sel_utente = "Da ".$daco." ".$dano." a ".$acog." ".$anom;
	else
		$sel_utente = "Nessun filtro";
	
	if($da_partita != "")
		$sel_partita = "Dalla partita contabile numero ".$da_partita." alla partita contabile numero ".$a_partita;
	else
		$sel_partita = "Nessun filtro";
	
	if($da_elab != "")
		$sel_elab = "Dal ".$da_elab." al ".$a_elab;
	else if($data_elab == "assente")
		$sel_elab = "Assente";
	else
		$sel_elab = "Nessun filtro";
	
	if($da_notif != "")
		$sel_notif = "Dal ".$da_notif." al ".$a_notif;
	else if($data_notif == "assente")
		$sel_notif = "Assente";
	else
		$sel_notif = "Nessun filtro";
	
	if ($stato_giacenza != "" && $stato_giacenza != "Nessuno" && $stato_giacenza != "Tutti")
	{
	    $query = "SELECT Descrizione FROM Parametri_Notifica WHERE ID = ".$stato_giacenza;
		$res_giacenza = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"Parametri_Notifica")["Descrizione"];// single_answer_query($query);
		if(strlen($res_giacenza)>106)	$res_giacenza = substr($res_giacenza, 0,102)."...";
		$sel_stato_giacenza = $res_giacenza;
	}
	else if ($stato_giacenza == "Nessuno" || $stato_giacenza == "Tutti")
		$sel_stato_giacenza = $stato_giacenza;
	else
		$sel_stato_giacenza = "Nessun filtro";
	
	if($indirizzo_validato == "validato")
		$sel_ind_validato = "Validato";
	else if($indirizzo_validato == "attesa")
		$sel_ind_validato = "In attesa di validazione";
	else 
		$sel_ind_validato = "Nessun filtro";
	
	if ($modalita_notif != "" && $modalita_notif != "Nessuna" && $modalita_notif != "Tutte")
	{
	    $query = "SELECT Descrizione FROM Parametri_Notifica WHERE ID = ".$modalita_notif;
		$res_modalita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"Parametri_Notifica")["Descrizione"];//single_answer_query($query);
		if(strlen($res_modalita)>106)	$res_modalita = substr($res_modalita, 0,102)."...";
		$sel_modalita_notif = $res_modalita;
	}
	else if ($modalita_notif == "Nessuna" || $modalita_notif == "Tutte")
		$sel_modalita_notif = $modalita_notif;
	else
		$sel_modalita_notif = "Nessun filtro";
	
	if ($anomalie != "" && $anomalie != "Nessuna" && $anomalie != "Tutte") {
        $query = "SELECT Descrizione FROM Parametri_Notifica WHERE ID = " . $anomalie;
        $sel_anomalie = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"Parametri_Notifica")["Descrizione"];//single_answer_query($query);
    }
	else if ($anomalie == "Nessuna" || $anomalie == "Tutte")
		$sel_anomalie = $anomalie;
	else
		$sel_anomalie = "Nessun filtro";
	
	if($rielabora == "Si")
		$sel_rielabora = "Si";
	else if($rielabora == "No")
		$sel_rielabora = "No";
	else
		$sel_rielabora = "Nessun filtro";
	
	if($da_stampa != "")
		$sel_stampa = "Dal ".$da_stampa." al ".$a_stampa;
	else if($data_stampa == "assente")
		$sel_stampa = "Assente";
	else
		$sel_stampa = "Nessun filtro";
	
	
	if ($stato_stampa != "" )
		$sel_stato_stampa = $stato_stampa;
	else
		$sel_stato_stampa = "Nessun filtro";
	
	
	$sel_blocco = $blocco;
	$sel_pagamento = $pagamento;
	if($sel_pagamento == "") $sel_pagamento = "Nessun filtro";
	else if($sel_pagamento == "Nessuno_Parziale")	$sel_pagamento = "Nessuno e parziale";

    $query = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'";
    $comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");// new ente_gestito($c);
    $nome_com = $comune["Denominazione"];
	
	$pdf->setCellPaddings(2,0,2,1);
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 18);
	$pdf->Cell(0, 0, "COMUNE DI ".strtoupper($nome_com) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->SetFont('Arial', '', 16);
	$pdf->Cell(0, 0, "ELENCO INGIUNZIONI" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->ln(10);
	
	$y = $pdf->getY();
	
	function tablePdf($label, $text){
		$stringa = "<tr>";
		$stringa.= "<td class='text_left'>".strtoupper($label).":</td>";
		$stringa.= "<td class='text_left'><i>".$text."</i></td>";
		$stringa.= "</tr>";
		
		return $stringa;
	}
	
	$pdf->SetFont('Arial', '', 12);
	$left_column = "<h3><b>SELEZIONI</b></h3><br><table>";
	$left_column.= tablePdf("UTENTE",$sel_utente);
	$left_column.= tablePdf("PARTITA",$sel_partita);
	$left_column.= tablePdf("DATA DI ELABORAZIONE",$sel_elab);
	$left_column.= tablePdf("DATA DI STAMPA",$sel_stampa);
	$left_column.= tablePdf("DATA DI NOTIFICA",$sel_notif);
	$left_column.= tablePdf("MODALITA' DI NOTIFICA",$sel_modalita_notif);
	$left_column.= tablePdf("STATO DI GIACENZA",$sel_stato_giacenza);
	$left_column.= tablePdf("INDIRIZZO",$sel_ind_validato);
	$left_column.= tablePdf("ANOMALIE",$sel_anomalie);
	$left_column.= tablePdf("FLAG RIELABORA",$sel_rielabora);
	$left_column.= tablePdf("STATO DI STAMPA",$sel_stato_stampa);
	$left_column.= tablePdf("BLOCCO COAZIONE",$sel_blocco);
	$left_column.= tablePdf("STATO PAGAMENTI",$sel_pagamento);
	$left_column.= "</table>";
	$left_column.= "<br><h3><b>RIEPILOGO</b></h3><br><table>";
	$left_column.= tablePdf("NUMERO PAGINE",$pdf->PageNo());
	$left_column.= tablePdf("NUMERO ATTI",$cont_result);
	$left_column.= tablePdf("TOTALE DOVUTO",number_format($tot_gen_dovuto,2,",",".")." Euro");
	$left_column.= tablePdf("TOTALE PAGATO",number_format($tot_gen_pagato,2,",",".")." Euro");
	$left_column.= "</table>";
	
	
	if(count($a_sanzioniCDS)>0){
		sort($a_sanzioniCDS);

		$ctrlImporto = 0;
		$contaSanzioni = 0;
		$a_sanzioni = array();
		$a_sanzioni['Count'][$contaSanzioni] = 0;

		for($i=0;$i<count($a_sanzioniCDS);$i++){
			if($ctrlImporto>0 && $a_sanzioniCDS[$i]>$ctrlImporto){
				$contaSanzioni++;
				$a_sanzioni['Count'][$contaSanzioni] = 0;
			}
			
			$a_sanzioni['Importo'][$contaSanzioni] = $a_sanzioniCDS[$i];
			$a_sanzioni['Count'][$contaSanzioni]++;
			$ctrlImporto = $a_sanzioniCDS[$i];
		}
		
		$right_column = '<h3><b>NUMERO INGIUNZIONI ACCORPATE PER IMPORTO</b></h3><br><table>';
		for($i=0;$i<count($a_sanzioni['Importo']);$i++){
			$right_column.= tablePdf("IMPORTO [".number_format($a_sanzioni['Importo'][$i],2,",",".")." Euro]",$a_sanzioni['Count'][$i]);
		}
		$right_column.= "</table>";
	}
	else
		$right_column = "";	
	
	$pdf->writeHTMLCell(150, '', '', $y, $left_column, 0, 0, 0, true, 'J', true);
	$pdf->writeHTMLCell(130, '', '', '', $right_column, 0, 1, 0, true, 'J', true);
	
	$pdf->movePage($pdf->PageNo(), 1);
	
	$pdf->Output( $file_elenco , 'F');
	
	
	$file = $vedi_file;
	
	if(session_status() == PHP_SESSION_NONE)session_start();
	
	echo json_encode([
		"path" => $file,
		"error" => 0,
		"msg" => "File stampato correttamente!"
	]);
	