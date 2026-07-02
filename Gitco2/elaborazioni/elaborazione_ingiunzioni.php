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
include_once CLS . "/cls_storico.php";
include_once CLS . "/cls_db.php";
include_once(CLS . "/cls_GestionePartita.php");


if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$cls_partita = new cls_GP();
$storico = new storico('storicoRuolo','3');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("IT",false);
$cls_elab = new cls_elaborazioniUtils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$partita_ID= $cls_help->getVar('partita');


$partita_query = "SELECT PT.Comune_ID AS Rif_P, PT.CC, T.Info_Cartella AS Info, EG.Denominazione AS Ente, ";
$partita_query.= "IF(Genere='D',COALESCE(Ditta,''),CONCAT(COALESCE(Cognome,''),' ',COALESCE(Nome,''))) as Utente, U.Comune_ID AS Rif_U FROM partita_tributi AS PT ";
$partita_query.= "LEFT JOIN tributo AS T ON PT.ID = T.Partita_ID ";
$partita_query.= "LEFT JOIN utente AS U ON PT.Utente_ID = U.ID ";
$partita_query.= "LEFT JOIN enti_gestiti AS EG ON PT.CC = EG.CC ";
$partita_query.= "WHERE PT.ID = ".$partita_ID;

$info = $cls_db->getResults($cls_db->ExecuteQuery($partita_query));


$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($c);

$query = "SELECT * FROM lockup_periods WHERE (CC='*****' OR CC='".$c."') AND Lockup_Type_Id<=3 ORDER BY Start_Date ASC";
$a_blockPeriods = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");//new ente_gestito($c);

$query = "SELECT * FROM interessi_tributi WHERE CC = '".$c."' ORDER BY Data_Inizio ASC";
$a_interessiTributi = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");//new interessi_tributi($c);

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
$elenco_dir = $cls_utils->crea_dir( ATTI ."/". $c . "/Ingiunzioni/Elenco_elaborazioni" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_ing_elab_".$data_file.".pdf";
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
        <span class="titolo font18 under_decor">Elaborazione Ingiunzioni</span>
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
$storico = new storico('storicoRuolo','3');

$ingiunzione290 = $cls_help->getVar('ingiunzione290');
$modalitaStampa = $cls_help->getVar('modalita_stampa');


$a_PrintType = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM print_type WHERE Modalita_Stampa='".$modalitaStampa."'"));
$PrintTypeId = $a_PrintType['Id'];
$prima_ingiunzione = $cls_help->getVar('prima_ingiunzione');
$data_elaborazione = $cls_help->getVar('data_elab');
$data_calcolo = $cls_help->getVar('data_calcolo');

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
$array_utenti = $cls_db->getResults($cls_db->ExecuteQuery($query_utente)) ;// mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
//SELEZIONE ANNI

$where = "( Flag_Blocco_Coazione != 'si' OR Flag_Blocco_Coazione IS NULL )";
if($da_anno>0)
    $where.= " AND Anno_Riferimento >= ".$da_anno;
if($ad_anno>0)
    $where.= " AND Anno_Riferimento <= ".$ad_anno;
$query_partita = $cls_elab->da_a_partita( $c , $da_n_elenco , $a_n_elenco , $where );
$array_partite = $cls_db->getResults($cls_db->ExecuteQuery($query_partita));//mysql_array( $query_partita );

if($_SESSION['username']=="mirkop"){
    echo $query_partita."<br><br>";
    echo $query_utente."<br>";
}

$num_partite = count($array_partite);
$num_utenti = count($array_utenti);

//$par_pagamento = new parametri_pagamento( $c, $tipo_partita );
//if(!$par_pagamento->ID>0 || !$par_pagamento->Scadenza_Sanzione>0){
//    alert("ATTENZIONE! La scadenza per la sanzione originaria non e' stata inserita nei Parametri pagamento per il tipo di riscossione ".$tipo_partita."!");
//    die;
//}

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

$y1_vert = $pdf->setRow($array_intestaz,"up_down",$styleRetta,null,0,$array_width);

//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down" , $styleRetta);

$dataTemp = new cls_DateTime($data_elaborazione,"IT",false);

$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$dataTemp->GetYear()."' AND Tipo_Riscossione = '*****'";
$parametri = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");

if($parametri->ID == null) {
    $cls_help->alert("l'anno " . $dataTemp->GetYear() . " non è presente nei parametri annuali!");
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



                $partita = $cls_elab->getDataPartita($array_partite[$k]['ID'],$c,$array_partite[$k]['Anno_Riferimento']);

				$tributo = $partita->Tributo;
                $info_cart = isset($tributo[0]->Info_Cartella)?$tributo[0]->Info_Cartella:null;

				$flag_blocco_diritto_riscossione = $partita->Flag_Blocco_Diritto_Riscossione;

                $query = "SELECT * FROM utente WHERE ID = '".$array_partite[$k]['Utente_ID']."' AND CC_Comune = '".$c."'";
                $utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");
                //$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
				$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;

                if(!is_null($utente->Data_Morte)){
                    if($_SESSION['username']=="mirkop"){
                        echo $utente->Data_Morte." DATA MORTE<br><br>";
                    }

//                    if($tipo_partita=="CDS")
                        break;
//                    else{
//
//                    }
                }



                //$parametri = new parametri_annuali($c, to_mysql_date($data_elaborazione) , $partita->Tipo );

                $importo_min = $parametri->Importo_Minimo;
                $diritto_min = $parametri->Diritto_Riscossione_Minimo;
                $diritto_max = $parametri->Diritto_Riscossione_Massimo;

                $spese_not = $parametri->Spese_Notifica;
                if($ingiunzione290=="y"){
                    $spese_not = 0;
                    $diritto_min = 0;
                    $diritto_max = 0;
                }

                $interessi_prec = 0.00;
                $spese_not_precedenti = 0.00;
                $pagamenti_precedenti = 0.00;

                $importoInteressi = 0;
                $a_codici = $cls_elab->totaleCodici($partita);

                $rettifica_flag=null;
                if($partita->ultimo_atto > 0){

                    $query = "SELECT * FROM atto WHERE ID = ".$partita->ultimo_atto." AND CC = '".$c."'";
                    $ultimo_atto_valido = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($partita->ultimo_atto, $c);

                    $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$ultimo_atto_valido->ID."' AND Partita_ID = '".$ultimo_atto_valido->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%' AND CC = '".$c."' ORDER BY Rata ASC";
                    $ultimo_atto_valido->Pagamento = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"pagamento","object");

                    $ultimo_atto_valido->Scadenze_Rate = explode("*",$ultimo_atto_valido->Scadenze_Rate);

                    if($cls_elab->checkProcessAtto("ingiunzione",Array("importo_minimo"=>$importo_min),$ultimo_atto_valido)===false)
                    {
                        break;
                    }

                    if($prima_ingiunzione == "si")
                        break;

                    $ultimo_atto = $partita->Atto[count($partita->Atto)-1];
                    if($cls_elab->checkPagamenti(Array("importo_minimo"=>$importo_min),$ultimo_atto)===false)
                        break;

                    $pagamenti_precedenti = $cls_elab->pagamenti_completi($ultimo_atto);
                    $rettifica_flag = $ultimo_atto->Rettifica_Flag;

                    if($rettifica_flag=="si"){

                        if(count($partita->Atto)==1 && $partita->Atto[0]->Atto=="Ingiunzione"){
                            $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                            $riferimento = 2;
                            $data_interessi = $cls_date->Get_DateNewFormat($tributo[0]->Data_Decorrenza_Interessi,"DB");
                        }
                        else{
                            $riferimento = "";
                            for($i=count($partita->Atto)-1;$i>=0;$i--){
                                if($partita->Atto[$i]->Rettifica_Flag=="si" && $partita->Atto[$i-1]->Atto!="Sollecito di pagamento"
                                    && $partita->Atto[$i-1]->Atto!="Sollecito pre ingiunzione"){

                                    $atto_pre_rettifica = $partita->Atto[$i-1];
                                    break;

                                }
                            }

                            $data_interessi = $cls_date->Get_DateNewFormat($atto_pre_rettifica->Data_Calcolo_Interessi,"DB");

                            $interessi_prec = $atto_pre_rettifica->Interessi_Precedenti+$atto_pre_rettifica->Interessi;
                            $spese_not_precedenti = $atto_pre_rettifica->Spese_Notifica_Precedenti+$atto_pre_rettifica->Spese_Notifica + $atto_pre_rettifica->CAN + $atto_pre_rettifica->CAD;

                            $totaleCheck = $a_codici["TOTALE"] + $interessi_prec + $spese_not_precedenti;
//                            if( number_format($totaleCheck,2)!=number_format($atto_pre_rettifica->Totale_Dovuto,2)){
//                                alert("L'ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborata a causa di incoerenza dei dati!");
//                                break;
//                            }

                            if($partita->Tipo=="CDS"){
                                $importoInteressi = $a_codici["IMPORTO_INTERESSI"] + $spese_not_precedenti - $pagamenti_precedenti;
                            }
                            else{
                                if($totaleCheck-$pagamenti_precedenti<$a_codici["IMPORTO_INTERESSI"])
                                    $importoInteressi = $totaleCheck-$pagamenti_precedenti;
                                else
                                    $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                            }
                        }
                    }
                    else{
                        $riferimento = $ultimo_atto->Riferimento + 1;
                        $data_interessi = $cls_date->Get_DateNewFormat($ultimo_atto->Data_Calcolo_Interessi,"DB");
                        if($ultimo_atto->Atto=="Avviso di messa in mora"){
                            if($data_interessi==null){

                                $obj_date = new cls_DateTime($ultimo_atto->Data_Notifica,"DB",false); // new DateTime($cls_date->Get_DateNewFormat($ultimo_atto->Data_Notifica,"DB"));
                                $obj_date->AddDay(15);
                                $data_interessi = $obj_date->GetDateDB();

                                /*$obj_date = new DateTime($cls_date->Get_DateNewFormat($ultimo_atto->Data_Notifica,"DB"));
                                $obj_date->modify("+15 days");
                                $data_interessi = $obj_date->format("Y-m-d");*/
                            }
                        }

                        $interessi_prec = $ultimo_atto->Interessi_Precedenti+$ultimo_atto->Interessi;
                        $spese_not_precedenti = $ultimo_atto->Spese_Notifica_Precedenti + $ultimo_atto->Spese_Notifica + $ultimo_atto->CAN + $ultimo_atto->CAD;

                        $totaleCheck = $a_codici["TOTALE"]+$spese_not_precedenti+$interessi_prec;

                        if( number_format($totaleCheck,2)!=number_format($ultimo_atto->Totale_Dovuto,2)){
                            $cls_help->alert("L'ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborata a causa di incoerenza dei dati!");
                                break;
                        }

                        if($partita->Tipo=="CDS"){
                            $importoInteressi = $a_codici["IMPORTO_INTERESSI"] + $spese_not_precedenti - $pagamenti_precedenti;
                        }
                        else{
                            if($totaleCheck-$pagamenti_precedenti<$a_codici["IMPORTO_INTERESSI"])
                                $importoInteressi = $totaleCheck-$pagamenti_precedenti;
                            else
                                $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                        }
                    }
                }
                else{
                    $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                    $riferimento = 1;

                    if(isset($tributo[0]->Data_Decorrenza_Interessi)) $data_interessi = $cls_date->Get_DateNewFormat($tributo[0]->Data_Decorrenza_Interessi,"DB");
                    else $data_interessi = null;
                }

                $interessi = 0;
                if($partita->Flag_Blocco_Maggiorazioni!="si" && $parametri->Maggiorazione_Ingiunzione!="no")
                {
                    if($importoInteressi>0 && $ingiunzione290!="y"){
                        $a_params = array(
                            "CalcType" => $partita->Tipo,
                            "StartDate" => $cls_help->toDbDate($data_interessi),
                            "EndDate" => $cls_help->toDbDate($data_calcolo),
                            "BaseAmount" => $importoInteressi,
                            "a_blocks" => $a_blockPeriods,
                            "a_interessiTributi" => $a_interessiTributi
                        );

                        $interessi = $cls_elab->calcInterests($a_params);
                    }
                    else
                        $interessi = 0.00;

                }
                else{
                    $interessi = 0.00;
                }

//                echo "<br><br>".$interessi."<br><br>";
//                die;

                $totale_dovuto = $a_codici["TOTALE"] + $spese_not + $spese_not_precedenti + $interessi + $interessi_prec;

                $diritto_risc_min = 0.00;
                $diritto_risc_max = 0.00;

                if($flag_blocco_diritto_riscossione!="si" && $gestore->Tipo == "Concessionario")
                {
                    $importo_calcolo_diritto = $totale_dovuto - $pagamenti_precedenti;
                    $diritto_risc_min = $importo_calcolo_diritto * $diritto_min / 100;
                    $diritto_risc_max = $importo_calcolo_diritto * $diritto_max / 100;
                }

                $note = "Notifica num. ".$riferimento;

                $cls_db->Start_Transaction();
                $cls_db->Begin_Transaction();

                $query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
                $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");
                $comune_id = isset($result["Com"])?$result["Com"]:0;//single_query($query);

                $query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Ingiunzione' AND Cronologico_Vecchio!='si'";
                $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");
                $crono_id = isset($result["Com"])?$result["Com"]:0;//single_query($query);

                $salva = new stdClass();//new atto(null,$c);
                $salva->DocumentTypeId = 2;
                $salva->PrintTypeId = $PrintTypeId;
                $salva->CC = $c;
                $salva->Comune_ID = $comune_id + 1;
                $salva->Partita_ID = $partita->ID;

                $ID_cronologico = 0;
                $anno_cronologico = 0;
                if($ingiunzione290=="y"){
                    if($c=="A318"){
                        $anno_cronologico = 2018;
                        $infoExp = explode(" | ",$info_cart);
                        $ingExp = explode(" ",$infoExp[1]);
                        $cronoExp = explode("/",$ingExp[1]);
                        $ID_cronologico = $cronoExp[0];
                        $protocollo = $cronoExp[1];
                        $data_notifica = $ingExp[4];//to_mysql_date($ingExp[4]);

                        $data_interessi = $data_notifica;
                        $data_calcolo = $data_notifica;
                        $stato_stampa = "Stampato";
                        $salva->Data_Stampa = $data_notifica;
                        $salva->Data_Notifica = $data_notifica;
                        $salva->Cronologico_Vecchio = "si";
                        $salva->Protocollo = $protocollo;

                        $info_cart = $infoExp[0];
                        $query = "UPDATE tributo JOIN partita_tributi ON partita_tributi.ID = tributo.Partita_ID ";
                        $query.= "SET Informazioni_Cartella = \"".$info_cart."\" WHERE partita_tributi.ID=".$partita->ID;

                    }
                    else if($c=="B256"){
                        $anno_cronologico = 2018;
                        $infoExp = explode(" ",$info_cart);
                        $cronoExp = explode("/",$infoExp[3]);
                        $ID_cronologico = $cronoExp[0];
                        if(isset($cronoExp[1]))
                            $protocollo = $cronoExp[1];
                        $data_notifica = $infoExp[count($infoExp)-1];//to_mysql_date

                        $data_interessi = $cls_date->GetDateDB($tributo[0]->Data_Decorrenza_Interessi,"IT");//to_mysql_date
                        $data_calcolo = $cls_date->GetDateDB($tributo[0]->Data_Decorrenza_Interessi,"IT");//to_mysql_date
                        $stato_stampa = "Stampato";
                        $salva->Data_Stampa = $data_notifica;
                        $salva->Data_Notifica = $data_notifica;
                        $salva->Cronologico_Vecchio = "si";
                        $salva->Protocollo = $protocollo;
                    }
                    else if($c=="L958"){
                        $anno_cronologico = 2018;
                        $infoExp = explode(" ",$info_cart);
                        $cronoExp = explode("/",$infoExp[2]);
                        $cronoExp[0] = str_replace("-","",$cronoExp[0]);
                        $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$cronoExp[0]);
                        $ID_cronologico = $arr[0];
                        if(isset($arr[1]))
                            $protocollo = $arr[1];
                        if(isset($arr[2]))
                            $protocollo.= $arr[2];
                        $data_notifica = $infoExp[count($infoExp)-1];//to_mysql_date

                        $data_interessi = $cls_date->GetDateDB($tributo[0]->Data_Decorrenza_Interessi,"IT");//to_mysql_date
                        $data_calcolo = $cls_date->GetDateDB($tributo[0]->Data_Decorrenza_Interessi,"IT");//to_mysql_date
                        $stato_stampa = "Stampato";
                        $salva->Data_Stampa = $data_notifica;
                        $salva->Data_Notifica = $data_notifica;
                        $salva->Cronologico_Vecchio = "si";
                        $salva->Protocollo = $protocollo;
                    }
                }

                $salva->ID_Cronologico = $ID_cronologico;
                $salva->Anno_Cronologico = $anno_cronologico;
                $salva->Data_Calcolo_Interessi = $cls_date->GetDateDB($data_calcolo,"IT");
                $salva->Data_Decorrenza_Interessi = $cls_date->GetDateDB($data_interessi,"IT");
                $salva->Stato_Stampa = $stato_stampa;

                $salva->Atto = "Ingiunzione";
                $salva->Info_Cartella = $info_cart;

                $salva->Data_Elaborazione = $cls_date->GetDateDB($data_elaborazione,"IT");

                if($rettifica_flag=="si"){
                    $salva->Modalita_Stampa = "ordinaria";
                    $salva->Tipo_Ufficiale = "diretta";
                    $salva->Atto_Rettificato = 1;
                }
                else{
                    $salva->Modalita_Stampa = $modalitaStampa;
                    $salva->Tipo_Ufficiale = "diretta";
                }
                $salva->PrinterId = $PrinterId;

                $salva->Riferimento = $riferimento;
                $salva->Note = $note;

                $salva->Spese_Notifica_Precedenti = $spese_not_precedenti;
                $salva->Spese_Notifica = $spese_not;

                //$interessi =(float) number_format($interessi,2,".","");

                $salva->Interessi = number_format($interessi,2,".","");//round($interessi, 2);
                $salva->Interessi_Precedenti = number_format($interessi_prec,2,".","");

                $salva->Diritto_Riscossione_Minimo = number_format($diritto_risc_min,2,".","");
                $salva->Diritto_Riscossione_Massimo = number_format($diritto_risc_max,2,".","");

                $salva->Totale_Dovuto = number_format($totale_dovuto,2,".","");

                $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$salva,"atto"));
                //$control_salva = $salva->Insert(true);

                if($control_salva)
                {
                    $id_ingiunzione = $control_salva;

                    //mysql_query('COMMIT');
                    $cls_db->End_Transaction();

                    $query = "SELECT * FROM atto WHERE ID = ".$id_ingiunzione." AND CC = '".$c."'";
                    $ing = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");
                    //$ing = new atto($id_ingiunzione, $c);
                    $ID_ing = $ing->Comune_ID;
                    $ID_partita = $partita->Comune_ID;


                    $pdf->SetFont('Arial', '', 10);

                    $array_value = array();

                    $array_value[] = $ID_ing;
                    $array_value[] = $ID_partita;
                    $array_value[] = $nome_utente;
                    $array_value[] = $info_cart;
                    $array_value[] = number_format($totale_dovuto,2,",",".");

                    $y = $pdf->setRow($array_value,"down",$styleDash,null,0,$array_width);
                    //$y = crea_riga($pdf , $array_width, $array_value, "down" , $styleDash);

                    if( $y > 266 )
                    {

                        $y2_vert = $pdf->getY();

                        $pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
                        //crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);

                        $pdf->AddPage();
                        $pdf->Ln(10);

                        $pdf->SetFont('Arial', 'B', 11);

                        $y1_vert = $pdf->setRow($array_intestaz,"up_down",$styleRetta,null,0,$array_width);
                        //$y1_vert = crea_riga($pdf , $array_width, $array_intestaz , "up_down" , $styleRetta);

                    }

                    $cont_result++;

                }
                else
                {
                    //mysql_query('ROLLBACK');
                    $cls_db->Rollback();
                    $cls_db->End_Transaction();
                }

                break;

            }

        }

	}
	
	$y2_vert = $pdf->getY();

    $pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
	//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
	
	$pdf->Output( $file_elenco , 'F');

	if($cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else{
        $storico_msg = "";
        if($cls_help->getVar("tipo_partita") != null)
            $storico_msg = " per le entrate di tipo ".$cls_help->getVar("tipo_partita");
        if($da_n_elenco!=null)
            $storico_msg.= " dalla partita ".$da_n_elenco;
        if($a_n_elenco!=null)
            $storico_msg.= " alla partita ".$a_n_elenco;
        
        if($da_anno != null && $da_anno == $ad_anno)
            $storico_msg.= " per l'anno ".$da_anno;
        else{
            if($cls_help->getVar("da_anno")!=null)
                $storico_msg.= " dall'anno ".$da_anno;
            if($cls_help->getVar("ad_anno")!=null)
                $storico_msg.= " all'anno ".$ad_anno;
        }
        $storico->insRow('E', "Elaborata ingiunzione per il contribuente ".$daco." ".$dano."(".$info[0]['Rif_U'].")".$storico_msg." ".$info[0]['Ente']."[".$c."]");
        echo "<script>fine('Elaborazione completata');</script>";
    }

?>

<?php include(INC."/footer.php"); ?>