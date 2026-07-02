<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
//include(INC."/menu.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once CLS . "/cls_CoazioneUtils.php";
include_once CLS . "/cls_pdf.php";
//include SUPER_ROOT . "/cls/tcpdf/tcpdf.php";

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_utils = new cls_Utils();
$cls_coaz = new cls_Coazione();


/*class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		$this->Cell(0, 5, "Elenco scarti importazione 290" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage()) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}*/

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$progr_n0 = $cls_help->getVar('id_n0');

//PREPARAZIONE ELENCO
$elenco_dir = $cls_utils->crea_dir( DUENOVANTA . "/Importazioni/Scarti/".$c );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_scarti_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = SUPER_WEB_ROOT."/".$cls_utils->mostra_file_path($download);

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
	
	sleep(1000);

	mostra_file();
	//$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco' onclick='mostra_file();'>");
}

function mostra_file()
{
	window.name = "Scarti";
	window.open('<?php echo $vedi_file; ?>',"Scarti");
}

</script>


    <div class="row justify-content-md-center " style="margin-top: 1%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font18 under_decor">Elenco scarti 290</span>
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

//$duenovanta = new N0N9( null );
$scarti = $cls_coaz->array_scarti($progr_n0);

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
sleep(2);

/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
	
	//$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
    $cls_pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
    $cls_pdf->SetMargins(10, 10, 10);
	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');

    $cls_pdf->AddPage('L');
    $cls_pdf->SetFont('Arial', 'B', 10);
	
	$dim_pag = $cls_pdf->getPageDimensions();
	$larghezza_pag = $cls_pdf->getPageWidth();
	$altezza_pag = $cls_pdf->getPageHeight();

$cls_pdf->SetAutoPageBreak(false);
$cls_pdf->Ln(5);
	
	$array_width = array();
	$array_intestaz_1 = array();
	$array_intestaz_2 = array();
	
	$array_width[] = 25;						$array_intestaz_1[] = "Rif. utente";					$array_intestaz_2[] = "Anno partita";
	$array_width[] = 70;						$array_intestaz_1[] = "Utente";							$array_intestaz_2[] = "Codice partita";
	$array_width[] = 52;						$array_intestaz_1[] = "CF / PI";						$array_intestaz_2[] = "Causa dello scarto";
	$array_width[] = 130;						$array_intestaz_1[] = "Residenza";						$array_intestaz_2[] = "Informazioni cartella";


$cls_pdf->setCellPaddings(2,1,2,0);
$y1_vert = $cls_pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);
	//$y1_vert = crea_riga($cls_pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);

$cls_pdf->setCellPaddings(2,0,2,1);
$y1_vert = $cls_pdf->setRow($array_intestaz_2,"down",$styleRetta,null,0,$array_width);
	//$y2_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
	
/**
	//////////////////////////////////////////////////////////////////////////////
*/

	$ctrl_linea = "no";
		
	for($j=0;$j<count($scarti);$j++)
	{
		set_time_limit(500);
		echo "<script>update(".ceil($j*100/count($scarti)).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		$cc_res = $scarti[$j]["CC_Indirizzo_Res"];
		
		
		if( substr($cc_res,0,1) != "Z")
		{
			//$com_res = new comune($cc_res);
            $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$cc_res."'";
            $com_res = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");
			$denom_res = $com_res["Com_Nome"];
		}
		else
		{
			//$stato_res = new stato_estero($cc_res);
            $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$cc_res."'";
            $stato_res = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista");
			$denom_res = $stato_res["Nome"]." ".$scarti[$j]["Frazione_Res"];
		}
		
		$rif_utente = $scarti[$j]["Numero_Contribuente"];
		$denom_utente = $scarti[$j]["Ditta"].$scarti[$j]["Cognome"]." ".$scarti[$j]["Nome"];
		if(strlen($denom_utente)>35)	$denom_utente = substr($denom_utente, 0,33)."...";
		$cf_pi = $scarti[$j]["Codice_Fiscale"];
		$ind_utente = $scarti[$j]["Indirizzo_Res"]." ".$scarti[$j]["Civico_Res"].", ".$denom_res;
		
		$cod_partita = $scarti[$j]["Codice_Partita"];
		$anno_tributo = $scarti[$j]["n4"][0]["Anno_Tributo"];
		$info_cartella = $scarti[$j]["n4"][0]["Info_Cartella"];
		
		$errore_scarto = $scarti[$j]["Flag_Importazione"];
		switch($errore_scarto)
		{
			case "N2_NG":			$errore_scarto = "Natura giuridica N2"; 			break;
			case "N2_IND":			$errore_scarto = "Residenza/domicilio N2"; 			break;
			case "N2_CF":			$errore_scarto = "CF/PI N2"; 						break;
			case "N3_NG":			$errore_scarto = "Natura giuridica N3"; 			break;
			case "N3_IND":			$errore_scarto = "Residenza/domicilio N3"; 			break;
			case "N3_CF":			$errore_scarto = "CF/PI N3"; 						break;
			case "N1_N2_MINUTA":	$errore_scarto = "Codice Comune o Minuta N1N2"; 	break;
			case "N3_N2_MINUTA":	$errore_scarto = "Codice Comune o Minuta N3N2"; 	break;
			case "N4_N2_MINUTA":	$errore_scarto = "Codice Comune o Minuta N4N2"; 	break;
			case "N3_N2_PARTITA":	$errore_scarto = "Codice Partita N3N2"; 			break;
			case "N4_N2_PARTITA":	$errore_scarto = "Codice Partita N4N2"; 			break;
			case "N4_INSERITO":		$errore_scarto = "Partita gia inserita"; 			break;
			case "SALDATO_ING":		$errore_scarto = "Ingiunzione saldata"; 			break;
			case "ANNULLATO_ING":	$errore_scarto = "Ingiunzione annullata"; 			break;
			case "FERMO_ING":		$errore_scarto = "Ingiunzione fermo"; 				break;
			case "DECEDUTO_ING":	$errore_scarto = "Ingiunzione deceduto"; 			break;
            case "FSCV":
            {
                $allField = json_decode($scarti[$j]["Json_Error"]);
                //var_dump($allField);
                $errore_scarto = "Valore campi (";
                for($z = 0; $z < count($allField); $z++) {
                    if($z > 0) $errore_scarto .= ", ".$allField[$z]->table.".".$allField[$z]->field." (tipo errore ".$allField[$z]->code_error."): ".str_replace("\"","'",$allField[$z]->text);
                    else $errore_scarto .= $allField[$z]->table.".".$allField[$z]->field." (tipo errore ".$allField[$z]->code_error."): ".str_replace("\"","'",$allField[$z]->text);
                }
                $errore_scarto .= ") errati";
                break;
            }
		}

        $cls_pdf->SetFont('Arial', '', 9);
		
		$array_value_1 = array();
		$array_value_2 = array();
			
		$array_value_1[] = $rif_utente;
		$array_value_1[] = $denom_utente;
		$array_value_1[] = $cf_pi;
		$array_value_1[] = $ind_utente;	
			
		$array_value_2[] = $anno_tributo;
		$array_value_2[] = $cod_partita;		
		$array_value_2[] = $errore_scarto;
		$array_value_2[] = $info_cartella;
	
			
		$array_align = array("L","L","L","L");

        $cls_pdf->setCellPaddings(2,2,2,0);
		//$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align );
        $y = $cls_pdf->setRow($array_value_1,$ctrl_linea,$styleDash,$array_align,0,$array_width);

        $cls_pdf->setCellPaddings(2,0,2,2);
		//$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
        $y = $cls_pdf->setRow($array_value_2,"no",$styleDash,$array_align,0,$array_width);
		
		if($ctrl_linea == "no")	$ctrl_linea = "up";
		
		if( $y > $altezza_pag - 30)
		{
			$y2_vert = $cls_pdf->getY();

            $cls_pdf->addLines();

			/*crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
			
			$tot = array_sum( $array_width );
			$margine = $pdf->getMargins();
			
			$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY(), $styleRetta ) ;*/

            $cls_pdf->AddPage();
            $cls_pdf->Ln(5);

            $cls_pdf->SetFont('Arial', 'B', 10);

            $cls_pdf->setCellPaddings(2,1,2,0);
			//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
            $y1_vert = $cls_pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);

            $cls_pdf->setCellPaddings(2,0,2,1);
			//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
            $y1_vert = $cls_pdf->setRow($array_intestaz_2,"down",$styleRetta,null,0,$array_width);
		
			$ctrl_linea = "no";
		
		}
	
	}
	
	$y2_vert = $cls_pdf->getY();

    $cls_pdf->addLines();//crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);

	/*$tot = array_sum( $array_width );
	$margine = $pdf->getMargins();


	$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY(), $styleRetta ) ;*/
	//die;
	if(count($scarti)>0)
	{
        $cls_pdf->Output( $file_elenco , 'F');
		echo "<script>fine('Elaborazione completata');</script>";
	}
	else
		echo "<script>nessun_risultato();</script>";

?>

<?php include(INC."/footer.php"); ?>