<?php 
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_excel.php";
include_once CLS . "/cls_pdf.php";
require_once IOFACTORY;

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

class CreaExcel
{
    public $mod_st;
    public $cls_db;
    public $filename;
    public $rowCount;

    public function __construct($cls_db,$filename)
    {
        $this->mod_st = new PHPExcel();
        $this->cls_db = $cls_db;
        $this->filename = $filename;
    }

    protected function CreaCella($colonna,$riga,$stringa)
    {
        $dimensione = strlen($stringa)*2.5;
        return $this->mod_st
        ->getActiveSheet()->SetCellValue($colonna.$riga, $stringa)
        ->getColumnDimension($colonna)->setWidth($dimensione);
    }

    
    protected function FaiBold()
    {
        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)->getFont()->setBold(true);
    }
    protected function FaiRightAlign()
    {

        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()
            ->getStyle($arg)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    }
    protected function FaiLeftAlign()
    {

        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()
            ->getStyle($arg)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    }
    protected function FaiBordo()
    {

        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()
            ->getStyle($arg)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
            ->getColor()
            ->setRGB('000000');
    }

    protected function FaiValuta()
    {
        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)->getNumberFormat()
            ->setFormatCode('[$€ ]#,##0.00_-');

    }
    protected function FaiTop()
    {
        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)
            ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

    }
    protected function FaiMerge()
    {
        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->mergeCells($arg);

    }
    protected function FaiCentro()
    {
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)->applyFromArray($style);

    }
    protected function FaiColore()
    {
        $args = func_get_args();
        $col = array_shift($args);

        if ($col == "giallo") $colore = "FFFF99";
        if ($col == "azzurro") $colore = "CCFFFF";
        if ($col == "blu") $colore = "99CCFF";
        if ($col == "oro") $colore = "CCCC0C";
        if ($col == "") $colore = "000000";

        $style = array(
            'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => $colore)
            )
        );

        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)->applyFromArray($style);

    }

    protected function FaiFontSize()
    {
        $args = func_get_args();
        $size = array_shift($args);

       
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)->getFont()->setSize($size);

    }

    protected function FaiDimensioneColonna()
    {
        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getColumnDimension($arg)->setAutoSize(true);
    }

    protected function Ingrandisci($size)
    {
        $this->mod_st->getActiveSheet()->getDefaultRowDimension()->setRowHeight($size);
    }

    public function Salva()
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->mod_st, 'Excel2007');
        
        $objWriter->save($this->filename);
        
    }

    protected function GetFileNamePdf()
    {
        $a_name  = explode(".",$this->filename);
        return $a_name[0].".pdf";
    }

    public function SalvaPdf($fine)
    {
        
        $rendererName = PHPExcel_Settings::PDF_RENDERER_TCPDF;
        $rendererLibraryPath = TCPDF;
        echo DOMPDF."<br>";
        echo TCPDF."<br>";
        $objPHPExcel = $this->mod_st;
        //orientamento e dimensione foglio
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getStyle("A1:G$fine")->applyFromArray(
            array(
               'font'  => array(
                   'size'  => 8
               )
            )
           );
        //print area
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea("A1:G$fine");
        //margini
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(1);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(1);

        //griglia
        $objPHPExcel->getActiveSheet()->setShowGridlines(false);

        if (!PHPExcel_Settings::setPdfRenderer(
            $rendererName,
            $rendererLibraryPath
        )) {
        die(
            'NOTICE: Please set the $rendererName and $rendererLibraryPath values' .EOL.
            'at the top of this script as appropriate for your directory structure'
        );
        }
        // $filepdf = $this->GetFileNamePdf();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="prova.pdf"');
        // header('Cache-Control: max-age=0');
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        // ob_end_clean();
        // $objWriter->save('php://output');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        $objWriter->save($this->GetFileNamePdf());
        
    }
}
?>