<?php
    include_once CLS . "/cls_pdf.php";
    include_once CLS . "/cls_Utils.php";

    class CreaGenteContabilePdf
    {
        public $EnteGestore;
        public $anno;
        public $SedeEnteGestore;
        public $c;
        public $a;
        public $SignEnteGestore;

        public $EnteGestito;
        public $Gestione;

        protected $pdf;


        function __construct()
        {
            $this->pdf = new cls_pdf();
            $this->pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

            $this->pdf->setDocParams();
            $this->pdf->SetAutoPageBreak(true);
         }


        public function PrimaPagina()
        {
            $this->pdf->AddPage("P");
            $size = 10;
            $this->pdf->SetFontSize($size*1.2);
            $w = 190;
            $this->pdf->Cell($w*0.15, 6, "Ente: ", '', 0, 'L', 0);
            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell($w*0.75, 6, "$this->EnteGestito", '', 0, 'L', 0);
            $this->pdf->SetFont('');
            $this->pdf->Ln();$this->pdf->Ln();
            $this->pdf->Cell($w*0.15, 6, "Gestione:", '', 0, 'L', 0);
            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell($w*0.75, 6, "$this->Gestione", '', 0, 'L', 0);
            $this->pdf->SetFont('');
            $this->pdf->Ln();$this->pdf->Ln();$this->pdf->Ln();$this->pdf->Ln();
            //$this->pdf->SetFontSize($size);
            $this->pdf->Cell($w, 6, "", 'LRT', 0, 'L', 0);
            $this->pdf->Ln();
            $this->pdf->Cell($w*0.65, 6, "  CONTO DELLA GESTIONE DELL’AGENTE CONTABILE:", 'L', 0, 'L', 0);
            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell($w*0.35, 6, "  $this->EnteGestore", 'R', 0, 'L', 0);
            $this->pdf->SetFont('');
            $this->pdf->Ln();
            $this->pdf->Cell($w*0.15, 6, "  Esercizio:", 'L', 0, 'L', 0);
            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell($w*0.85, 6, "  $this->anno", 'R', 0, 'L', 0);
            $this->pdf->SetFont('');
            $this->pdf->Ln();
            $this->pdf->Cell($w, 6, "", 'LRB', 0, 'L', 0);
            $this->pdf->Ln();$this->pdf->Ln();$this->pdf->Ln();$this->pdf->Ln();
            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell($w, 6, "Modello n° 21", '', 0, 'C', 0);
            $this->pdf->Ln();
            $this->pdf->SetFont('');
            $this->pdf->Cell($w, 6, "per province, comuni, comunità montane,", '', 0, 'C', 0);
            $this->pdf->Ln();
            $this->pdf->Cell($w, 6, "unioni di comuni e città metropolitane", '', 0, 'C', 0);
            $this->pdf->Ln();
        }

        private function CreateTable($header,$data) {
            // Colors, line width and bold font
            $this->pdf->SetFillColor(255, 255, 255);
            $this->pdf->SetTextColor(0);
            $this->pdf->SetDrawColor(0, 0, 0);
            $this->pdf->SetLineWidth(0.3);
            $this->pdf->SetFont('', 'B');
            // Header
            $w = array(15, 50, 50, 35,50,35,45);
            $num_headers = count($header);
            for($i = 0; $i < $num_headers; ++$i) {
                switch($i)
                {
                    case 0:
                    case 6:
                    case 1:    $border ="LRB";
                               break;
                    default :  $border ="LTRB";
                               break;
                }
                $this->pdf->Cell($w[$i], 7, $header[$i], $border, 0, "C", 1);
            }
            $this->pdf->Ln();
            // Color and font restoration
            $this->pdf->SetFillColor(224, 235, 255);
            $this->pdf->SetTextColor(0);
            $this->pdf->SetFont('');
            // Data
            $fill = 1;
            foreach($data as $index=>$row) {
                $this->pdf->SetFillColor(255, 255, 255);
                $this->pdf->Cell($w[0], 6, $row[0], 'LTRB', 0, 'C', $fill);
                $this->pdf->Cell($w[1], 6, $row[1], 'LTRB', 0, 'C', $fill);
                $this->pdf->SetFillColor(255, 255, 153);
                $this->pdf->Cell($w[2], 6, "", 'LTRB', 0, 'R', $fill);
                $this->pdf->Cell($w[3], 6, "", 'LTRB', 0, 'R', $fill);
                $this->pdf->SetFillColor(204, 255, 255);
                $this->pdf->Cell($w[4], 6, "", 'LTRB', 0, 'R', $fill);
                $this->pdf->Cell($w[5], 6, "", 'LTRB', 0, 'R', $fill);
                $this->pdf->SetFillColor(102, 178, 255);
                $bottom = "";$top="";
                if ($index==0) $top="T";
                if ($index==count($data)-1) $bottom = "B";
                $this->pdf->Cell($w[6], 6, "", 'LR'.$bottom.$top, 0, 'R', $fill);
                $this->pdf->Ln();
            }
            $this->pdf->SetFillColor(255, 255, 255);
            
        }
    
        private function Totali()
        {
            
            $w = array(115, 35,50,35,45);
            $this->pdf->SetFillColor(255, 165, 0);
            $this->pdf->Cell($w[0], 6, "TOTALE ", 0, 0, "R", 0);
            $this->pdf->Cell($w[1], 6, "", 'LTRB', 0, "R", 1);
            $this->pdf->Cell($w[2], 6, "TOTALE ", 0, 0, "R", 0);
            $this->pdf->Cell($w[3], 6, "", 'LTRB', 0, "R", 1);
            $this->pdf->SetFillColor(255, 255, 255);
            $this->pdf->Ln();
            $this->pdf->Ln();
        }
        private function Titolo()
        {
            $w = 280;
            $row = array("Ente: $this->EnteGestito - Gestione: $this->Gestione ",
                         "CONTO DELLA GESTIONE DELL’AGENTE CONTABILE: SARIDA S.r.l. -  ANNO  $this->anno");
            $this->pdf->Cell($w, 6, $row[0], '', 0, 'C', 0);
            $this->pdf->Ln();$this->pdf->Ln();
            $this->pdf->SetFont('', 'B');
            $this->pdf->Cell($w, 6, $row[1], '', 0, 'C', 0);
            $this->pdf->Ln();
            $this->pdf->Ln();
        }
        private function SubTitolo()
        {
            $this->pdf->SetFont('', 'B');
            $w = array(15,50, 85,85,45);
            $row = array("N.","PERIODO E OGGETTO","ESTREMI RISCOSSIONE","VERSAMENTO IN TESORERIA","NOTE");
            $fill =0;
            $this->pdf->Cell($w[0], 6, $row[0], 'LTR', 0, 'C', $fill);
            $this->pdf->Cell($w[1], 6, $row[1], 'LTR', 0, 'C', $fill);
            $this->pdf->Cell($w[2], 6, $row[2], 'LTRB', 0, 'C', $fill);
            $this->pdf->Cell($w[3], 6, $row[3], 'LTRB', 0, 'C', $fill);
            $this->pdf->Cell($w[4], 6, $row[4], 'LTR', 0, 'C', $fill);
            $this->pdf->Ln();
        }

        public function Tabella()
        {
            $this->pdf->AddPage("L");
            $this->pdf->SetFillColor(255, 255, 127);
            $size = 10;
            $this->pdf->SetFontSize($size*1.5);
            $this->Titolo();
            $this->pdf->SetFontSize($size);
            $this->SubTitolo();
            $header = array("ORD","DELLA RISCOSSIONE","RICEV.NN.","IMPORTO","QUIET.NN.","IMPORTO","");
            $data = array(array(1,"GENNAIO"),array(2,"FEBBRAIO"),array(3,"MARZO"),
            array(4,"APRILE"),array(5,"MAGGIO"),array(6,"GIUGNO"),
            array(7,"LUGLIO"),array(8,"AGOSTO"),array(9,"SETTEMBRE"),
            array(10,"OTTOBRE"),array(11,"NOVEMBRE"),array(12,"DICEMBRE"));
            $this->CreateTable($header,$data);
            $this->Totali();
            $this->Coda();

        }

        private function Coda()
        {
            $this->pdf->Ln();$this->pdf->Ln();
            $size = 10;
            $this->pdf->SetFontSize($size);
            $w = array(150, 85 ,45);
            $row = array("$this->SedeEnteGestore li ___________","L'AGENTE CONTABILE","");
            $this->pdf->Cell($w[0], 6, $row[0], 0, 0, 'L', 0);
            $this->pdf->Cell($w[1], 6, $row[1], 0, 0, 'C', 0);
            $this->pdf->Ln();$this->pdf->Ln();
            $row = array("Il presente conto contiente N.____ registrazioni in ___ pagine","{SignEnteGestore}","Timbro");
            $this->pdf->Cell($w[0], 6, $row[0], 0, 0, 'L', 0);
            //$this->pdf->Cell($w[1], 6, $row[1], 0, 0, 'C', 0);
            $this->pdf->writeHTMLCell($w[1],6,'','',$this->SignEnteGestore,0,0,0,0,"C");
            $this->pdf->Cell($w[2], 6, $row[2], 0, 0, 'C', 0);
            $this->pdf->Ln();$this->pdf->Ln();
            $row = array("VISTO DI REGOLARITA'","IL RESPONSABILE DEL SERVIZIO FINANZIARIO","dell'ente");
            $this->pdf->Cell($w[0], 6, $row[0], 0, 0, 'L', 0);
            $this->pdf->Cell($w[1], 6, $row[1], 0, 0, 'C', 0);
            $this->pdf->Cell($w[2], 6, $row[2], 0, 0, 'C', 0);
            $this->pdf->Ln();$this->pdf->Ln();
            $row = array("_______________________ li, _______________","________________________________________");
            $this->pdf->Cell($w[0], 6, $row[0], 0, 0, 'L', 0);
            $this->pdf->Cell($w[1], 6, $row[1], 0, 0, 'C', 0);
            //$this->pdf->Cell($w[2], 6, $row[2], 0, 0, 'C', 0);
            $this->pdf->Ln();$this->pdf->Ln();
            $this->pdf->MultiCell(280, 10, 'NOTE', 1, 'L', 0, 0, '', '', true);


        }
        public function Stampa()
        {
            $filename = "Agente_Contabile_".$this->c."_".$this->a."pdf";
            $utils = new cls_Utils();
            $path = $utils->crea_dir(ARCHIVIO ."/TEMP");
            $webPath = ARCHIVIO_WEB."/TEMP";
            $pdfPath = $path ."/".$filename;
            $this->pdf->Output($pdfPath, "F");
            $pdfWebPath = $webPath."/".$filename;

            //echo "<script>window.open('" . $pdfWebPath . "','Merge File','height=700,width=500'); </script>";
            echo "<script>showFileOnModal('".$pdfWebPath."','Agente contabile','pdf');</script>";
        }

    }

?>