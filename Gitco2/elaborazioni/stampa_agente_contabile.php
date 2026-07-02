<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once INC . "/header.php";
include_once INC . "/menu.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_file.php";
include_once ELABORAZIONI_CLS . "/cls_CreaAgenteContabilePdf.php";

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$errore = $cls_help->getVar("errore");
$messaggio = $cls_help->getVar("messaggio");
$cls_db = new cls_db();


function FaiSignatureAgenteContabile() 
{
    global $a_enteAdmin,$c;
    $signature = array();
    $signature['type'] = "file";
    $signature['header'] = "L'Agente Contabile";
    $signature['file'] = $a_enteAdmin['Gestore_File_Firma'];
    $signature['filePath'] = FIRME."/".$c."/".$a_enteAdmin['Gestore_File_Firma'];
    $signature['fileWebPath'] = FIRMEWEB."/".$c."/".$a_enteAdmin['Gestore_File_Firma'];
    $signature['name'] = "";
    return $signature;
}
function setHtmlSignature($signature){
    if(isset($signature['type'])){
        if($signature['type']=="file"){
            $cls_file = new cls_file();
            $imgDim = $cls_file->imageSize($signature['filePath'],140*0.8,45*0.8);

            $htmlSign= "<img src=\"".$signature['fileWebPath']."\" style=\"width: ".$imgDim[0]."px; height: ".$imgDim[1]."px;\" /><br>";
            $htmlSign.= "<span>".strtoupper($signature['name'])."</span>";

        }
        else if($signature['type']=="text"){
            $htmlSign= "<span>".$signature['replacementText']."</span><br>";
            $htmlSign.= "<span>".strtoupper($signature['name'])."</span>";
        }
    }
    else{
        $htmlSign = "<span>!!!FIRMA ASSENTE!!!</span><br>";
    }
    return $htmlSign;
}

if ($errore==1)
{
    echo "<h3>Inserisci i dati mancanti in Parametri->Ente->Gestore  o clicca sul link.<br>";
    echo $messaggio."<br>";
    echo "<a href='".WEB_ROOT."/parametri/gestore.php?c=".$c."&a=".$a."'>Parametri Gestore</a></h3>";
    die;
}
$a_el = array("Intestatario","Conto_Corrente","IBAN","Scadenza_Giorno","Scadenza_Mese","File_Firma");
function Controllo($a_enteAdmin,$a_el)
{
	//ogni potenza di due equivale ad una "posizione" nell'array... se 0 vuol dire che non è presente
	$controllo_gestore = 0;
	$index = 1;
	array_map(function($item)use ($a_enteAdmin,&$controllo_gestore,&$index)
	{ 
		$controllo_gestore+= is_null($a_enteAdmin["Gestore_$item"])? 0 : pow(2,$index);
		$index++;

	},$a_el);
	return $controllo_gestore;
}

function FaiMessaggio($controllo_gestore,$a_el)
{
	//aggiungo una potenza di due in più (128) escludo 2^0
	$messaggio = "<br>";
	$controllo_gestore+=128;
    $arr = str_split(substr(strrev(decbin($controllo_gestore)),1));
    foreach($arr as $index=>$a)
    {	
        if($a==0) $messaggio.= "    -Manca ".$a_el[$index]."<br>";
    }
	return $messaggio;
}
$controllo_gestore = Controllo($a_enteAdmin,$a_el);
if(($controllo_gestore<126)){
    
	$messaggio = FaiMessaggio($controllo_gestore,$a_el);
    echo "<script>location.href = 'stampa_agente_contabile.php?c=".$c."&a=".$a."&errore=1&messaggio=$messaggio';</script>";
    die;
}

$pdf = new CreaGenteContabilePdf();

$pdf->anno = $a;
$pdf->a = $a;
$pdf->c= $c;
$pdf->SedeEnteGestore =$a_enteAdmin['Gestore_Comune'].", ".$a_enteAdmin['Gestore_Via']." ".$a_enteAdmin['Gestore_Civico'];
$pdf->EnteGestore = $a_enteAdmin['Gestore_Denominazione'];
$pdf->EnteGestito = $a_enteAdmin['Denominazione'];
$pdf->Gestione = "Riscossione coattiva";
$pdf->SignEnteGestore = setHtmlSignature(FaiSignatureAgenteContabile());
$pdf->PrimaPagina();
$pdf->Tabella();
$pdf->Stampa();

include_once INC . "/footer.php";

?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="stampa_agente_contabile.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        location.href="stampa_agente_contabile.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        //showFileOnModal('".$pdfWebPath."','Agente contabile','pdf');
    }
    
</script>