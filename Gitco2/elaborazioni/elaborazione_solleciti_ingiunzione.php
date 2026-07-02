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

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir( ATTI ."/". $c . "/Solleciti/Elenco_elaborazioni" );
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
        <span class="titolo font18 under_decor">Elaborazione Solleciti di pagamento</span>
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

$primo_sollecito = $cls_help->getVar('primo_sollecito');

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

$danotif = $cls_help->getVar('da_data');
$anotif = $cls_help->getVar('a_data');

$tipo_partita = $cls_help->getVar('tipo_partita');

$data_elaborazione = $cls_help->getVar('data_elab');
$anno_elab = explode("/", $data_elaborazione);
$anno_elab = $anno_elab[2];


$query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".date('Y')."' AND Tipo_Riscossione = '*****'";
$parametri = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_annuali");//new parametri_annuali($c, date('Y-m-d') , $a_partite[$l]['TIPO_PARTITA'] );

if($parametri->ID == null) {
    $cls_help->alert("l'anno " . date('Y') . " non è presente nei parametri annuali!");
    die;
}


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

$y1_vert = $pdf->setRow($array_intestaz, "up_down" , $styleRetta,null,0,$array_width);
//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down" , $styleRetta );

flush();
ob_flush();

echo "<script>inizio();</script>";

flush();
ob_flush();
flush();
ob_flush();
sleep(2);

$query = "SELECT PAR.ID AS PARTITA_ID, PAR.Tipo AS TIPO_PARTITA, PAR.Anno_Riferimento AS ANNO_PARTITA, ";
$query.= "IF(utente.Ditta=\"\",utente.Cognome,utente.Ditta) AS Denominazione_Utente, utente.ID AS UTENTE_ID ";
$query.= "FROM partita_tributi as PAR ";
$query.= "JOIN atto ON atto.Partita_ID = PAR.ID ";
$query.= "JOIN utente ON utente.ID = PAR.Utente_ID ";
$query.= "WHERE PAR.Flag_Blocco_Coazione!=\"si\" AND PAR.CC=\"".$c."\" ";
if($danotif!="")
    $query.= "AND atto.Data_Notifica >= \"".$danotif."\" ";
if($anotif!="")
    $query.= "AND atto.Data_Notifica <= \"".$anotif."\" ";
if($da_anno>0)
    $query.= "AND PAR.Anno_Riferimento >= ".$da_anno." ";
if($ad_anno>0)
    $query.= "AND PAR.Anno_Riferimento <= ".$ad_anno." ";
if($da_n_elenco>0)
    $query.= "AND PAR.Comune_ID >= ".$da_n_elenco." ";
if($a_n_elenco>0)
    $query.= "AND PAR.Comune_ID <= ".$a_n_elenco." ";
if($tipo_partita!="")
    $query.= "AND PAR.Tipo = \"".$tipo_partita."\" ";
if($daco!="")
    $query.= "AND ( utente.Cognome >= \"".$daco."\" AND utente.Ditta=\"\" ";
if($dano!="")
    $query.= "AND utente.Nome >= \"".$dano."\" ";
if($acog!="")
    $query.= "AND utente.Cognome <= \"".$acog."\" ";
if($anom!="")
    $query.= "AND utente.Nome <= \"".$anom."\" ";
if($daco!="")
    $query.= ") OR ( utente.Ditta >= \"".$daco."\" AND utente.Cognome=\"\" ";
if($acog!="")
    $query.= "AND utente.Ditta <= \"".$acog."\" ";
if($daco!="")
    $query.= ") ";

$query.= "GROUP BY PAR.ID ORDER BY PAR.ID, Denominazione_Utente, utente.Nome";

$a_partite = $cls_db->getResults($cls_db->ExecuteQuery($query));// mysql_array( $query );

$cont_result = 0;
	
	for( $l=0; $l < count($a_partite); $l++ )
	{	
		echo "<script>update(".ceil($l*100/count($a_partite)).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();

        set_time_limit(30);


        $spese_not = $parametri->Spese_Postali;
        $importo_minimo = $parametri->Importo_Minimo;

        $partita = $cls_elab->getDataPartita($a_partite[$l]['PARTITA_ID'],$c,$a_partite[$l]['ANNO_PARTITA']);//new partita($a_partite[$l]['PARTITA_ID'], $c, $a_partite[$l]['ANNO_PARTITA']);

        $ultimoAtto = $partita->Atto[count($partita->Atto)-1];

        $a_codici = $cls_elab->totaleCodici($partita);
        $totaleCheck = $a_codici["TOTALE"]+$ultimoAtto->Spese_Notifica_Precedenti+$ultimoAtto->Interessi;
        $totaleCheck+= $ultimoAtto->Interessi_Precedenti+$ultimoAtto->Spese_Notifica+$ultimoAtto->CAN+$ultimoAtto->CAD;
        if( number_format($totaleCheck,2)!=number_format($ultimoAtto->Totale_Dovuto,2)){
            $cls_help->alert("Il sollecito della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborato a causa di incoerenza dei dati!");
            continue;
        }

        $a_params = Array("importo_minimo"=>$importo_minimo);
        if($cls_elab->checkProcessAtto("sollecito",$a_params,$ultimoAtto)===false)
            continue;

        if($primo_sollecito == "si")
        {
            $query = "SELECT ID FROM atto WHERE CC = '".$c."' AND Atto = 'Sollecito di pagamento' AND Partita_ID = ".$a_partite[$l]['PARTITA_ID'];
            $num_solleciti = $cls_db->getNumberRow($cls_db->ExecuteQuery($query));//mysql_num_rows(mysql_query($query));

            if($num_solleciti>0)
                continue;
        }

        $query = "SELECT * FROM utente WHERE ID = '".$a_partite[$l]['UTENTE_ID']."' AND CC_Comune = '".$c."'";
        $utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente( $a_partite[$l]['UTENTE_ID'] , $c );
        $nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        $query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
        $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");
        $comune_id = isset($result["Com"])?$result["Com"]:0;
        //$comune_id = single_query($query);

        $query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Sollecito di Pagamento'";
        $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"atto");
        $crono_id = isset($result["Com"])?$result["Com"]:0;
        //$crono_id = single_query($query);

        $salva = new stdClass();
        $salva->DocumentTypeId = 3;
        $salva->PrintTypeId = $PrintTypeId;
        $salva->CC = $c;
        $salva->Comune_ID = $comune_id + 1;
        $salva->Partita_ID = $partita->ID;
        $salva->Anno_Cronologico = "0";
        $salva->ID_Cronologico = "0";
        $salva->Atto = "Sollecito di pagamento";
        $salva->Info_Cartella = $ultimoAtto->Info_Cartella;
        $salva->Stato_Stampa = "Da stampare";
        $salva->Tipo_Ufficiale = "diretta";
        $salva->PrinterId = $PrinterId;
        $salva->Modalita_Stampa = $modalitaStampa;
        $salva->Data_Elaborazione = $cls_date->GetDateDB($data_elaborazione,"IT");
        $salva->Data_Calcolo_Interessi = $ultimoAtto->Data_Calcolo_Interessi;
        $salva->Spese_Notifica_Precedenti = $ultimoAtto->Spese_Notifica_Precedenti + $ultimoAtto->Spese_Notifica + $ultimoAtto->CAN + $ultimoAtto->CAD;
        $salva->Spese_Notifica = $spese_not;
        $salva->Data_Decorrenza_Interessi = $ultimoAtto->Data_Decorrenza_Interessi;
        $salva->Interessi_Precedenti = $ultimoAtto->Interessi_Precedenti + $ultimoAtto->Interessi;
        $salva->Totale_Dovuto = $ultimoAtto->Totale_Dovuto + $spese_not;
        $salva->Diritto_Riscossione_Minimo = $ultimoAtto->Diritto_Riscossione_Minimo;
        $salva->Diritto_Riscossione_Massimo = $ultimoAtto->Diritto_Riscossione_Massimo;
        $salva->Riferimento = $ultimoAtto->Riferimento;
        $salva->Note = "ID ".$ultimoAtto->Comune_ID." notificata il ".$cls_date->Get_DateNewFormat($ultimoAtto->Data_Notifica,"DB");


        $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array)$salva,"atto"));//$salva->Insert(true);

        if($control_salva)
        {
            $cls_db->End_Transaction();

            $ID_avv = $salva->Comune_ID;
            $ID_partita = $partita->Comune_ID;
            $pdf->SetFont('Arial', '', 10);

            $array_value = array();

            $array_value[] = $ID_avv;
            $array_value[] = $ID_partita;
            $array_value[] = $nome_utente;
            $array_value[] = $salva->Note;
            $array_value[] = $cls_math->conv_num($salva->Totale_Dovuto)." Euro";

            $y = $pdf->setRow($array_value, "down", $styleDash,null,0,$array_width);
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
            $cls_db->End_Transaction();
            //mysql_query('ROLLBACK');
        }
	
	}//CHIUSURA ATTI
	
	$y2_vert = $pdf->getY();

    $pdf->verticalLines($y1_vert , $y2_vert, $styleDash);
	//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
	
	$pdf->Output( $file_elenco , 'F');
	
	if(count($a_partite) == 0 || $cont_result == 0)
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

<?php include(INC."/footer.php"); ?>