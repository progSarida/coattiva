<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_Utils.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
$cls_text = new cls_textParameters();
$cls_utils = new cls_Utils();

$pdf->setHeaderTitle("");                                                                                       // Tolgo titolo di dafault

$c = $cls_help->getVar("c");
$type = $cls_help->getVar("type");
$title = $cls_help->getVar("title");

$a_header = array("left"=>array(),"right"=>array(),"logo"=>"","logoPath"=>"");

$text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery($c, $type)));	

if($text == null){
    echo json_encode([
        "error" => 2,
        "msg" => "Il tipo di testo selezionato non è presente per questo ente!"
    ]);
    
    die;
}	

$cls_text->setHtmlBody($text['Content']);

$m_header = array();
$m_header["left"][0] = "";
$m_header["left"][1] = "";
$m_header["left"][2] = "";
$m_header["left"][3] = "";
$m_header["left"][4] = "";
$m_header["left"][5] = "";
$m_header["right"][0] = "";
$m_header["right"][1] = "";
$m_header["right"][2] = "";
$m_header["right"][3] = "";
$m_header["right"][4] = "";
$m_header["right"][5] = "";
$m_header["logo"] = "";
$m_header["logoPath"] = "";

$r_header = array();
$r_header["addressName"] = "";
$r_header["addressCap"] = "";
$r_header["addressCity"] = "";
$r_header["addressProvince"] = "";
$r_header["addressCountry"] = "";
$r_header["addressRow"][0] = "";
$r_header["addressRow"][1] = "";
$r_header["addressRow"][2] = "";
$r_header["address"] = "";
$r_header["recipient"] = "";
$r_header["denomination"][0] = "";
$r_header['references'][0] = "";
$r_header['references'][1] = "";
$r_header['references'][2] = "";
$r_header['references'][3] = "";
$r_header["placeDate"] = "";


$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->AddPage("P");

//$pdf->setManagerHeader($m_header);
//$pdf->setRecipientHeader($r_header);

$pdf->MultiCell(0,75,"I N T E S T A Z I O N E",1,'C', false, 1, '', '', true, 0, false, true, 75, 'M');

$pdf->SetXY( 7 , 88 );

$pdf->SetFont('Arial', '', 7.8);
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->writeHTML($cls_text->html_body);


$pathFILE = $cls_utils->crea_dir(ARCHIVIO."/temp");
$nameFILE = "anteprima_testo_".$title.".pdf";
$pathFILE .= "/".$nameFILE;
$pdf->Output($pathFILE, 'F');

$file = ARCHIVIO_WEB."/temp/".$nameFILE;

echo json_encode([
    "path" => $file,
    "error" => 0,
    "msg" => "File stampato correttamente!"
]);