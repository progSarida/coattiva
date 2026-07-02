<?php
use Twig\Cache\NullCache;

include_once TCPDF . "/tcpdf.php";
include_once CLS."/cls_file.php";
include_once CLS."/cls_parameters.php";

class cls_pdf extends TCPDF
{
	public $bordino_bollettino = array('LTRB' => array('width' => 0.05));
	public $bordo_bollettino = array('LTRB' => array('width' => 0.2));
	public $bordo_tratt_bollettino = array('LTRB' => array('dash' => '5'));

	private $a_headerFont = array("family"=>"Arial","style"=>"B","size"=>11);
    private $a_footerFont = array("family"=>"helvetica","style"=>"N","size"=>7);

    private $a_mainPageParams = array("title"=>"ENTE","subtitle"=>"ELENCO");

    private $a_headerPage = array();
    private $a_width = array();
    private $a_width_totals = array();

    private $line = "up";
    private $a_align = array();
    private $a_align_totals = array();

    private $a_styleDash = array('dash' => '6,6');
    private $a_styleRetta = array('dash' => '0');

    public $lineY = 0;
	public $footerTime;
    private $headerTitle = "Elenco";
    public $cls_parameters;
    private $a_totalsVar = null;
    private $a_totalsHeader = null;
    private $a_partialTotals = null;
    private $a_totals = null;
	private $mode = 1; // 0 partita sotto riga e tipo a destra // 1 partita livello indirizzo tipo a sinistra

    public function setArray(array $array, $a_name = "a_HeaderFont"){
        $this->$a_name = $array;
    }

    public function setHeaderTitle($title){
        $this->headerTitle = $title;
    }

    public function setDocParams(){
        $this->cls_parameters = new cls_parameters();
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetAutoPageBreak(false);
        $this->SetCellPadding(0);
    }

	public function setDocParamsLAB(){
        $this->cls_parameters = new cls_parameters();
        $this->setPrintHeader(false);
        $this->setPrintFooter(true);
		$this->SetFooterMargin(10);
        $this->SetAutoPageBreak(true,10);
        //$this->SetCellPadding(0);
		
		//$this->setAutoTopMargin = stretch;	
		//$this->setAutoBottomMargin = stretch;

		//$this->autoMarginPadding = 10;
    }

    /*public function temporaryPrinting(){
        $x = $this->GetX();
        $y = $this->GetY();
        $size = $this->getFontSize();
        $font = $this->getFontFamily();

        if($this->CurOrientation=="P")
            $this->SetXY(60, 200);
        else if($this->CurOrientation=="L")
            $this->SetXY(93, 155);

        $this->StartTransform();
        $this->Rotate(50);
        $this->SetFont('Helvetica', '', 32);
        $this->SetTextColor(190);
        $this->Cell('130','','STAMPA PROVVISORIA',0,0,'C');
        $this->StopTransform();

        $this->SetFont($font, '', $size);
        $this->SetTextColor(0);
        $this->SetXY($x, $y);

    }*/

    public function setManagerHeader($a_header,$type = null){

        $this->SetMargins(0, 0, 0);
        $this->ln(0);
        $this->SetLineWidth(0.2);
        $this->Line(7, 6, 203, 6);//Linea di testa

        $cls_file = new cls_file();
        $dim = $cls_file->imageSize($a_header['logoPath'], 18, 22);
        $offsetx = 7 + (20-$dim[0])/2;
        $offsety = 6 + (26-$dim[1])/2;
        $this->Image($a_header['logo'], $offsetx, $offsety, $dim[0], $dim[1],'','','C' );//Logo

        $this->SetMargins(28.0, 6.0, 7.0);	$this->ln(0);

        $this->SetXY(28, 8);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell (85.0, 0, $a_header['left'][0], 0, 1, "L");
        $this->SetFont('Arial', '', 7);
        for($i=1;$i<count($a_header['left']);$i++){
            $this->Cell (85.0, 0, $a_header['left'][$i], 0, 1, "L");
        }

        //UFFICIO
        $this->SetMargins(105.0, 8.0,7.0);	$this->ln(0);
        $this->SetXY( 105 , 8 );

		if($type !== 39){
			$this->SetFont('Arial', 'B', 7);
			$this->Cell (85.0, 0, $a_header['right'][0], 0, 1, "L");
			$this->SetFont('Arial', '', 7);
			for($i=1;$i<count($a_header['right']);$i++){
				$this->Cell (85.0, 0, $a_header['right'][$i], 0, 1, "L");
			}
		}
        $this->SetLineWidth(0.2);
        $this->Line(7, 28, 203, 28);//Linea di chiusura


    }

	public function setManagerHeaderLAB($a_header,$type = null){

        $this->SetMargins(0, 0, 0);
        $this->ln(0);
        //$this->SetLineWidth(0.2);
        //$this->Line(7, 6, 203, 6);//Linea di testa

        

        $this->SetMargins(18.0, 6.0, 7.0);	$this->ln(0);

        $this->SetXY(18, 20);
        $this->SetFont('Arial', 'B', 8);
        //$this->Cell (95.0, 0, $a_header['left'][0], 0, 1, "L");
        //$this->SetFont('Arial', '', 7);
        for($i=0;$i<count($a_header['left']);$i++){
			$this->SetX(18);
            $this->Cell (95.0, 0, $a_header['left'][$i], 0, 1, "L");
        }

		//var_dump($a_header['logo_ente']["webPath"]);die;

		$cls_file = new cls_file();
        $dim = $cls_file->imageSize($a_header['logo_ente']['rootPath'], 14.5,17);
        $offsetx = 15 + (20-$dim[0])/2;
        $offsety = 33 + (26-$dim[1])/2;
        $this->Image($a_header['logo_ente']['webPath'], $offsetx, $offsety, $dim[0], $dim[1],'','','C' );//Logo

		$this->SetXY(34, 40);
        $this->SetFont('Arial', '', 10);
        $this->Cell (81.0, 0, $a_header['left_under'][0], 0, 1, "L");
        $this->SetFont('Arial', '', 7);
        for($i=1;$i<count($a_header['left_under']);$i++){
			$this->SetX(34);
            $this->Cell (81.0, 0, $a_header['left_under'][$i], 0, 1, "L");
        }

        //UFFICIO
        $this->SetMargins(105.0, 8.0,7.0);	$this->ln(0);


			$cls_file = new cls_file();
			$dim = $cls_file->imageSize($a_header['logoPath'], 49, 27);
			$offsetx = 118 + (20-$dim[0])/2;
			$offsety = 15 + (26-$dim[1])/2;
			$this->Image($a_header['logo'], $offsetx, $offsety, $dim[0], $dim[1],'','','C' );//Logo

			$this->SetXY( 153 , 15 );

			$this->SetFont('Arial', '', 7);
			$this->Cell (45.0, 0, $a_header['right'][0], 0, 1, "L");
			//$this->SetFont('Arial', '', 7);
			for($i=1;$i<count($a_header['right']);$i++){
				$this->SetX(153);
				$this->Cell (45.0, 0, $a_header['right'][$i], 0, 1, "L");
			}


    }

	private function setInizioAtto($a_header)
	{
		if (!isset($a_header['Tipo'])) return;
		if($a_header['Tipo']!="lavoro") return;
		
		$this->SetLineWidth(0.2);
		$this->Line(7, 83, 90, 83);
		$this->Line(120, 83, 203, 83);

		$this->SetFont('Arial', 'B', 12);
		$this->SetXY(68 , 80);
		$this->Cell (74, 0, "INIZIO ATTO", 0, 1, "C");
		
	}

	private function setPrintType($a_header)
	{
		if (!isset($a_header['PrintType'])) return;
		$x = $this->mode == 0 ? 111 : 7;
		$this->SetXY($x, 30);
		$this->SetFont('Arial', 'B', 12);
		$this->Cell (85.0, 0, $a_header['PrintType'], 0, 1, "L");
	}

    public function setRecipientHeader($a_header){
        $this->SetFont('Arial', '', 7.8);
		$y =55;

        if (isset($a_header['Tipo']))
			$y = $this->mode ==0 ? 30: 55;

        $this->SetXY( 7 , $y );

        if(!empty($a_header['references'])){
			foreach($a_header['references'] as $ref=>$value){
				$this->Cell(95 , 0, $value, 0, 1, "L");
				$y+=4;
				$this->SetXY( 7 , $y );
			}
        }

		$this->setPrintType($a_header);

        $this->SetFont('Arial', '', 10);
        if(!empty($a_header['placeDate'])){
            $this->SetXY( 111 , 45 );
            $this->Cell (90, 0, $a_header['placeDate'], 0, 1, "L");//Data e luogo
        }

        $this->SetMargins(111.0, 50.0);

        $this->SetXY( 111 , 55.0 );
        for($x_utente = 0;$x_utente<count($a_header['denomination']);$x_utente++){
            if($x_utente==0)
                $strDenom = "SPETT.LE ".$a_header['denomination'][$x_utente];
            else
                $strDenom = $a_header['denomination'][$x_utente];
            $this->Cell (90, 0, strtoupper($strDenom) , 0, 1, "L");//Righe aggiuntive
        }
        for($i=0;$i<count($a_header['addressRow']);$i++)
            $this->Cell (90, 0, strtoupper($a_header['addressRow'][$i]) , 0, 1, "L");


        
        $this->SetMargins(7, 0, 7);
        $this->SetXY( 7 , 88 );
		$this->setInizioAtto($a_header);
		$this->SetXY( 7 , 88 );
		$this->SetFont('Arial', '', 7.8);
		
    }

	public function setRecipientHeaderLAB($a_header){
        /*$this->SetFont('Arial', '', 7.8);
		$y =55;

        if (isset($a_header['Tipo']))
			$y = $this->mode ==0 ? 30: 55;

        $this->SetXY( 7 , $y );

        /*if(!empty($a_header['references'])){
			foreach($a_header['references'] as $ref=>$value){
				$this->Cell(95 , 0, $value, 0, 1, "L");
				$y+=4;
				$this->SetXY( 7 , $y );
			}
        }

		$this->setPrintType($a_header);*/

        $this->SetFont('Arial', 'B', 9);
        /*if(!empty($a_header['placeDate'])){
            $this->SetXY( 111 , 45 );
            $this->Cell (90, 0, $a_header['placeDate'], 0, 1, "L");//Data e luogo
        }*/

        $this->SetMargins(111.0, 50.0);

        $this->SetXY( 111 , 70.0 );
        for($x_utente = 0;$x_utente<count($a_header['denomination']);$x_utente++){
            if($x_utente==0)
                $strDenom = $a_header['denomination'][$x_utente];
            else 
				$strDenom = $a_header['denomination'][$x_utente];

            $this->Cell (90, 0, strtoupper($strDenom) , 0, 1, "L");//Righe aggiuntive

			if($x_utente == 0) $this->SetFont('Arial', '', 9);
        }
        for($i=0;$i<count($a_header['addressRow']);$i++)
            $this->Cell (90, 0, strtoupper($a_header['addressRow'][$i]) , 0, 1, "L");


			/*$this->SetXY(18, 20);
        $this->SetFont('Arial', 'B', 8);
        //$this->Cell (95.0, 0, $a_header['left'][0], 0, 1, "L");
        //$this->SetFont('Arial', '', 7);
        for($i=0;$i<count($a_header['left']);$i++){
			$this->SetX(18);
            $this->Cell (95.0, 0, $a_header['left'][$i], 0, 1, "L");
        }*/

		$this->SetXY( 18 , 102 );
		$this->Cell (85, 0, "Contribuente:" , 0, 1, "L");
		$this->SetX(18);
		$this->Cell (85, 0, strtoupper($a_header['Cognome_Ditta']) , 0, 1, "L");
		$this->SetX(18);
		$this->Cell (85, 0, strtoupper($a_header['CF_PI']) , 0, 1, "L");

        
        $this->SetMargins(7, 0, 7);
        $this->SetXY( 7 , $this->GetY() + 5 );
		$this->setInizioAtto($a_header);
		$this->SetXY( 7 , $this->GetY() + 5 );
		$this->SetFont('Arial', '', 7.8);
		
    }

	public function setRecipientHeaderOriginale($a_header){
        $this->SetFont('Arial', '', 7.8);
        $y = $this->mode ==0 ? 30: 55;
        $this->SetXY( 7 , $y );


        if(!empty($a_header['references'])){
            foreach($a_header['references'] as $ref=>$value){
                $this->Cell(95 , 0, $value, 0, 1, "L");
                $y+=4;
                $this->SetXY( 7 , $y );
            }
        }

		$this->setPrintType($a_header);
        
		$this->SetFont('Arial', '', 10);
        

        
        $this->SetMargins(7, 0, 7);
        $this->SetXY( 7 , 88 );

		$this->setInizioAtto($a_header);

		$this->SetXY( 7 , 88 );
		$this->SetFont('Arial', '', 7.8);

		
    }
    public function setRecipientHeaderAnnul($a_header,$priority,$type){

        $cls_help = new cls_help();
        $this->SetFont('Arial', '', 7.8);

//        $this->SetXY( 7 , 45 );
//        $this->Cell (116, 5, $a_header['placeDate'] ,0, 1, "L");
        $this->SetXY( 7 , 52 );

        if(isset($a_header['references'][0])){
            $this->Cell (116, 5, $a_header['references'][0] ,0, 0, "L");
        }
        else{
            $this->Cell (116, 5, "" ,0, 0, "L");
        }

        $this->Cell (90, 5, isset($a_header['placeDate'])?$a_header['placeDate']:"", 0, 1, "L");//Data e luogo

        $this->SetXY( 7 , 57 );
        $this->Cell ( 95 , 5, isset($a_header['references'][1])?$a_header['references'][1]:"", 0, 0, "L");

        $this->SetXY( 7 , 62 );
        if(isset($a_header['references'][2]))
        {
            $this->Cell (95 , 5,$a_header['references'][2], 0, 0, "L");
        }
        else
            $this->Cell (95, 5, "", 0, 0, "L");//Data e luogo
        if($a_header['denomination'][0]!="")
            $this->Cell ( 18 , 5, "Spett.le", 0, 0, "R");

        //$cls_help->alert($priority);

        switch($priority)
        {
            case 1:
                //DESTINATARIO 1
                /*$this->SetAlpha(1.0);
                $this->RoundedRect(100, 47.5, 70, 35, 3.50, '1111');*/


                $this->SetMargins(123.0, 62.0);	$this->Ln(0);

                if($type != "sgravio") {
                    $this->Cell(90, 5, strtoupper($a_header['denomination'][0]), 0, 1, "L");//Nome Destinatario
                    $this->SetMargins(7.0, 10.0, 7.0);
                    $this->Ln(0);


                    if (isset($a_header['references'][3])) {
                        $this->Cell(95, 5, $a_header['references'][3], 0, 0, "L");
                        //echo $a_header['references'][3]; die;
                    }

                    //DESTINATARIO 2
                    $this->SetMargins(123.0, 62.0);
                    $this->ln(0);
                    for ($x_utente = 1; $x_utente < count($a_header['denomination']); $x_utente++) {
                        $this->Cell(90, 5, strtoupper($a_header['denomination'][$x_utente]), 0, 1, "L");//Righe aggiuntive
                        //echo $a_header['denomination'][$x_utente];
                    }
                    //die;
                    for ($i = 0; $i < count($a_header['addressRow']); $i++) {
                        $this->Cell(90, 5, strtoupper($a_header['addressRow'][$i]), 0, 1, "L");
                        //echo $a_header['addressRow'][$i];
                    }
                    //die;
                    $this->Ln(10);
                    $this->SetMargins(123.0, $this->GetY() + 10);
                    $this->Ln(0);
                }
                $this->Cell (90, 5, strtoupper($a_header["ente"]["Info_Denominazione"]), 0, 1, "L");//Nome Ente
                /*$this->SetMargins(7.0, 10.0,7.0);*/	$this->Ln(0);

                $address1 = $a_header['ente']["Info_Via"];
                if(!empty($a_header['ente']["Info_Civico"]))
                    $address1.= " ".$a_header['ente']["Info_Civico"];
                if(!empty($a_header['ente']["Info_Interno"]))
                    $address1.= "/".$a_header['ente']["Info_Civico"];
                $address2 = $a_header['ente']["Info_Cap"];
                if(!empty($a_header['ente']["Info_Comune"]))
                    $address2.= " ".$a_header['ente']["Info_Comune"];
                if(!empty($a_header['ente']["Info_Provincia"]))
                    $address2.= " ".$a_header['ente']["Info_Provincia"];
                $this->Cell(90, 5, strtoupper($address1), 0, 1, "L");
                $this->Cell(90, 5, strtoupper($address2), 0, 1, "L");
                break;

            case 2:
                //$this->Ln(10);
                /*$this->SetAlpha(1.0);
                $this->RoundedRect(100, 47.5, 70, 35, 3.50, '1111');*/

                $this->SetMargins(123.0, 62.0);	$this->Ln(0);
                $this->Cell (90, 5,  strtoupper($a_header["ente"]["Info_Denominazione"]), 0, 1, "L");//Nome Ente
                /*$this->SetMargins(7.0, 10.0,7.0);*/	$this->Ln(0);

                $address1 = $a_header['ente']["Info_Via"];
                if(!empty($a_header['ente']["Info_Civico"]))
                    $address1.= " ".$a_header['ente']["Info_Civico"];
                if(!empty($a_header['ente']["Info_Interno"]))
                    $address1.= "/".$a_header['ente']["Info_Civico"];
                $address2 = $a_header['ente']["Info_Cap"];
                if(!empty($a_header['ente']["Info_Comune"]))
                    $address2.= " ".$a_header['ente']["Info_Comune"];
                if(!empty($a_header['ente']["Info_Provincia"]))
                    $address2.= " ".$a_header['ente']["Info_Provincia"];
                $this->Cell(90, 5, strtoupper($address1), 0, 1, "L");
                $this->Cell(90, 5, strtoupper($address2), 0, 1, "L");

                if($type != "sgravio") {
                    //DESTINATARIO 1
                    $this->SetMargins(123.0, $this->GetY());
                    $this->Ln(15);
                    $this->Cell(90, 5, $a_header['denomination'][0], 0, 1, "L");//Nome Destinatario
                    $this->SetMargins(7.0, 10.0, 7.0);
                    $this->Ln(0);


                    if (isset($a_header['references'][3])) {
                        $this->Cell(95, 5, $a_header['references'][3], 0, 0, "L");
                        //echo $a_header['references'][3]; die;
                    }

                    //DESTINATARIO 2
                    $this->SetMargins(123.0, $this->GetY() + 1);
                    $this->ln(0);
                    for ($x_utente = 1; $x_utente < count($a_header['denomination']); $x_utente++) {
                        $this->Cell(90, 5, $a_header['denomination'][$x_utente], 0, 1, "L");//Righe aggiuntive
                        //echo $a_header['denomination'][$x_utente];
                    }
                    //die;
                    for ($i = 0; $i < count($a_header['addressRow']); $i++) {
                        $this->Cell(90, 5, $a_header['addressRow'][$i], 0, 1, "L");
                        //echo $a_header['addressRow'][$i];
                    }
                }
                //die;
                break;
        }



        $this->Ln(10);
        $this->SetMargins(7, 10,7);

        $this->SetMargins(7, 0, 7);
        $this->ln(0);
    }

    public function setRecipientHeaderPostal($a_header,$denom_ufficio,$indirizzo_ufficio_comune){

        $cls_help = new cls_help();
        $this->SetFont('Arial', '', 7.8);

//        $this->SetXY( 7 , 45 );
//        $this->Cell (116, 5, $a_header['placeDate'] ,0, 1, "L");
        $this->SetXY( 7 , 52 );

        if(isset($a_header['references'][0])){
            $this->Cell (116, 5, $a_header['references'][0] ,0, 0, "L");
        }
        else{
            $this->Cell (116, 5, "" ,0, 0, "L");
        }

        $this->Cell (90, 5, isset($a_header['placeDate'])?$a_header['placeDate']:"", 0, 1, "L");//Data e luogo

        $this->SetXY( 7 , 57 );
        $this->Cell ( 95 , 5, isset($a_header['references'][1])?$a_header['references'][1]:"", 0, 0, "L");

        $this->SetXY( 7 , 62 );
        if(isset($a_header['references'][2]))
        {
            $this->Cell (95 , 5,$a_header['references'][2], 0, 0, "L");
        }
        else
            $this->Cell (95, 5, "", 0, 0, "L");//Data e luogo
        if($a_header['denomination'][0]!="")
            $this->Cell ( 18 , 5, "Spett.le", 0, 0, "R");

        //DESTINATARIO 1
        $this->SetMargins(123.0, 62.0);	$this->Ln(0);
        foreach($denom_ufficio as $value) {
            $this->Cell(90, 5, $value, 0, 1, "L");//Nome Destinatario
        }
        $this->SetMargins(7.0, 10.0, 7.0);
        $this->Ln(0);
        /*if(isset($a_header['references'][3]))
        {
            $this->Cell (95 , 5, $a_header['references'][3], 0, 0, "L");
        }*/

        //DESTINATARIO 2
        $this->SetMargins(123.0, 62.0);	$this->ln(0);
        /*for($x_utente = 1;$x_utente<count($a_header['denomination']);$x_utente++)
            $this->Cell (90, 5, $a_header['denomination'][$x_utente] , 0, 1, "L");//Righe aggiuntive

        for($i=0;$i<count($a_header['addressRow']);$i++)*/
        foreach($indirizzo_ufficio_comune as $key => $value)
            if($key == "Riga1" || $key == "Riga2" || $key == "Riga3" || $key == "Riga4")
                $this->Cell (90, 5, $value , 0, 1, "L");

        $this->Ln(10);
        $this->SetMargins(7, 10,7);
    }

    public function setRimborsiEnte($a_crediti, $creditoTot){
        $a_widthAmounts = array(160,10,15);
        $a_alignAmounts = array("L","R","R");

        $a_values = array("Totale complessivo", "Euro", number_format($creditoTot,2,",","."));
        $this->setRow($a_values, $linea = "no", $style = array(), $a_alignAmounts,0, $a_widthAmounts);
        $this->Ln(5);
        foreach ($a_crediti as $a_credito){

            $a_values = array($a_credito['Descrizione'], "Euro", number_format($a_credito['Totale'],2,",","."));

            $this->setRow($a_values, $linea = "no", $style = array(), $a_alignAmounts,0, $a_widthAmounts);
            $this->Ln(2);
        }

        $this->Ln(2);

    }

    public function setPrintFromParams( array $a_text, array $a_textParams, array $a_amounts, array $a_signature, $collectorType, $printType, $fontSize ){

        $fontFamily = "Arial";
        $this->Ln(0);
        if($printType == "temp")
            $this->temporaryPrinting();
        $page = 1;
        for($i=1;$i<=count($a_textParams);$i++){
            if($page!=$a_textParams[$i]['page']){
                $this->AddPage("P");
                if($printType == "temp")
                    $this->temporaryPrinting();
                $page++;
            }

            $text = $a_text['field'.$i];
            if($a_textParams[$i]['alignment']=="J")
                $text.= "\n";

            $this->SetFont($fontFamily, $a_textParams[$i]['fontWeight'], $fontSize);

            if($a_textParams[$i]['field']=="oggetto" || $a_textParams[$i]['field']=="sottotitolo oggetto"){
                if($a_textParams[$i]['field']=="oggetto")
                    $title = "OGGETTO:";
                else
                    $title = "";

                $this->Cell(20, 0, $title , 0, 0, 'L', 0, '', 0);
                $this->MultiCell(175, 0, $text , 0, $a_textParams[$i]['alignment'], 0, 1);
            }
            else if(substr($a_textParams[$i]['field'],0,5)=="firma"){

                $this->ln(5);
                if($a_textParams[$i]['alignment']=="L") {

                    $position['x'] = $this->GetX();
                    $position['y'] = $this->GetY() + 3;
                }

                $signature = $this->cls_parameters->getSelectedSignature($text, $a_signature);

                $this->setPrintSignature($signature,$position,$a_textParams[$i]['alignment']);
                if($a_textParams[$i]['alignment']=="R")
                    $this->ln(5);
            }
            else{
                $this->Ln(2);
                $this->MultiCell(0, 0, $text , 0, $a_textParams[$i]['alignment'], 0,1);
            }

            if($a_textParams[$i]['field']=="comunicazione importi"){
                $a_widthAmounts = array(160,10,10,15);
                $a_alignAmounts = array("R","C","R","R");

                for($y = 0; $y<count($a_amounts['single']);$y++){
                    $this->Ln(2);
                    $a_values = array($a_amounts['single'][$y]['label'], $a_amounts['single'][$y]['operator'], "Euro", $a_amounts['single'][$y]['amount']);
                    $this->setRow($a_values, $linea = "no", $style = array(), $a_alignAmounts,0, $a_widthAmounts);
                }
                $this->Ln(2);
                $this->Line(7, $this->getY(), 203, $this->getY(), array());
                $this->SetFont($fontFamily, 'B', $fontSize);

                for($y = 0; $y<count($a_amounts['total']);$y++){
                    $this->Ln(2);
                    $a_values = array($a_amounts['total'][$y]['label'], $a_amounts['total'][$y]['operator'], "Euro", $a_amounts['total'][$y]['amount']);
                    $this->setRow($a_values, $linea = "no", $style = array(), $a_alignAmounts,0, $a_widthAmounts);
                }
                $this->Ln(5);
            }
        }

        switch($collectorType){
            case "diretta":
                $notificationType = "direct";
                break;
            case "riscossione":
                $notificationType = "collector";
                break;
            case "giudiziario":
                $notificationType = "bailiff";
                break;
            default:
                $notificationType = "";
                break;
        }

        if(isset($a_text[$notificationType.'_header'])){
            if($a_text[$notificationType.'_header']!=""){
                if($page!=$a_textParams[1]['notification'][$notificationType]['page']){
                    $this->AddPage("P");
                    if($printType == "temp")
                        $this->temporaryPrinting();
                    $page++;
                }

                $this->Ln(5);
                $this->SetFont('Arial', 'B', $fontSize);
                $this->MultiCell(0, 0, strtoupper($a_text[$notificationType.'_header']) , 0, 'C', 0, 1);
                $this->MultiCell(0, 0, $a_text[$notificationType.'_subheader'] , 0, 'C', 0, 1);
                $this->Ln(2);
                $this->SetFont('Arial', '', $fontSize);
                $this->MultiCell(0, 0, $a_text[$notificationType.'_text']."\n" , 0, 'L', 0, 1);
                $this->Ln(5);
                $signature = $this->cls_parameters->getSignatureByOfficial($collectorType, $a_signature);

                $position['x'] = $this->GetX();
                $position['y'] = $this->GetY() + 3;
                $this->setPrintSignature($signature,$position,"R");
                $this->Ln(5);
            }
        }


        if($page%2!=0){
            $this->AddPage("P");
            if($printType == "temp")
                $this->temporaryPrinting();
            $page++;
        }
    }

    public function setPrintSignature($signature, $position, $alignment)
    {
        $this->SetXY($position['x'],$position['y']-3);
        $fontFamily = "Arial";
        if($signature['type']=="file")
        {
            $cls_file = new cls_file();
            $a_size = $cls_file->imageSize($signature['filePath'], 60, 18);
            $offset = (60-$a_size[0])/2;
            if($alignment=="R")
                $offset+=122;
            $this->Image($signature['fileWebPath'], $position['x']+$offset, $position['y'], $a_size[0], $a_size[1],'','','C' );//Firma1
            $space = 8;
            $signatureRow1 = "";
            $signatureRow2 = $signature['name'];
        }
        else{
            $space = 4;
            $signatureRow1 = $signature['name'];
            $signatureRow2 = $signature['replacementText'];
        }

        if($alignment=="R")
            $this->Cell(122,0, "" , 0, 0,'C',0,'',0 );
        $this->Cell(60, 0, $signature['header'] , 0, 1, 'C', 0, '', 0);

        $this->Ln($space);
        $this->SetFont($fontFamily, '', 7);

        if($alignment=="R")
            $this->Cell(122,0, "" , 0, 0,'C',0,'',0 );
        $this->Cell(60,0, $signatureRow1 , 0, 1,'C',0,'',0 );

        $this->Ln($space);
        $this->SetFont($fontFamily, '', 7);

        if(strlen($signatureRow2)>45){
            $pos = intval(substr_count($signatureRow2,' ')/2);
            $a_firma = $this->split($signatureRow2,' ',$pos);
            if($alignment=="R")
                $this->Cell(122,0, "" , 0, 0,'C',0,'',0 );
            $this->Cell(60, 0, $a_firma[0] , 0, 1, 'C', 0, '', 0);
            if($alignment=="R")
                $this->Cell(122,0, "" , 0, 0,'C',0,'',0 );
            $this->Cell(60, 0, $a_firma[1] , 0, 1, 'C', 0, '', 0);
        }
        else{
            if($alignment=="R")
                $this->Cell(122,0, "" , 0, 0,'C',0,'',0 );
            $this->Cell(60, 0, $signatureRow2 , 0, 1, 'C', 0, '', 0);
        }
    }

    public function setPostalParams(){
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetAutoPageBreak(false);
        $this->SetCellPadding(0);
        $this->SetMargins(0, 0, 0);
    }

    public function setTimeFooter($flag = true){
        $this->footerTime = $flag;
    }

    public function setPostalBill(array $a_postal, $number=2, $printType){

        if(!empty($a_postal[1]['td']) || !empty($a_postal[2]['td'])) {

            $this->setPostalParams();
            $this->AddPage("L");
            if ($printType == "temp")
                $this->temporaryPrinting();


            for ($i = 1; $i <= $number; $i++) {

                if ($a_postal[$i]['authorization'] != false || $a_postal[$i]['td'] == "123") {
					if($a_postal[$i]['td']=="896" && is_null($a_postal[$i]['amount']))
						$a_postal[$i]['td']=="123";

                    if ($i % 2 != 0) {
                        $this->crea_bollettino();
                        $id = "uno";
                    } else {
                        $this->crea_bollettino_inverso();
                        $id = "due";
                    }

                    $this->logo_bollettino($a_postal[$i]['logo'], $id);

                    $this->scelta_td_bollettino(
                        $a_postal[$i]['td'], $a_postal[$i]['clientCode'], $a_postal[$i]['amount'],
                        $a_postal[$i]['checkAmount'], $a_postal[$i]['accountNumber'], $id);

                    $this->iban_bollettino($a_postal[$i]['iban'], $id);
                    $this->intestatario_bollettino($a_postal[$i]['accountHolder'], $id);
                    $this->causale_bollettino($a_postal[$i]['causalRow1'], $a_postal[$i]['causalRow2'], $id);
                    $this->payerZone($a_postal[$i]['recipientRow1'], array($a_postal[$i]['recipientRow2'], $a_postal[$i]['recipientRow3']), $id);
                    $this->autorizzazione_bollettino($a_postal[$i]['authorization'], $id);
                }

            }
            if ($a_postal[1]['authorization'] != false || $a_postal[1]['td'] == "123") {
                $this->AddPage("L");
                if ($printType == "temp")
                    $this->temporaryPrinting();
            }
        }
    }

    public function setSinglePostalBill(array $a_postal, $index=0, $printType = "temp"){

        if($index==0)
            $this->setPostalParams();

        //for($i=0;$i<=$number;$i++){

            if($a_postal['authorization']!=false || $a_postal['td']=="123"){
                if($index%2==0){
                    $this->AddPage("L");
                    if($printType == "temp")
                        $this->temporaryPrinting();
                    $this->crea_bollettino();
                    $id = "uno";
                }
                else{
                    $this->crea_bollettino_inverso();
                    $id = "due";
                }

                $this->logo_bollettino($a_postal['logo'],$id);
                 //var_dump($a_postal);die;

                $this->scelta_td_bollettino(
                    $a_postal['td'], $a_postal['clientCode'] , $a_postal['amount'] ,
                    $a_postal['checkAmount'] , $a_postal['accountNumber'], $id );

                $this->iban_bollettino($a_postal['iban'],$id);
                $this->intestatario_bollettino($a_postal['accountHolder'],$id);
                $this->causale_bollettino($a_postal['causalRow1'], $a_postal['causalRow2'],$id);
                $this->payerZone($a_postal['recipientRow1'], array($a_postal['recipientRow2'],$a_postal['recipientRow3']),$id);
                $this->autorizzazione_bollettino($a_postal['authorization'],$id);
            }

        //}
        /*if($a_postal[1]['authorization']!=false || $a_postal[1]['td']=="123"){
            $this->AddPage("L");
            if($printType == "temp")
                $this->temporaryPrinting();
        }*/

    }

    public function Header() {

        $this->SetFont($this->a_headerFont['family'],$this->a_headerFont['style'], $this->a_headerFont['size']);
        $this->ln(5);
        $this->Cell(0, 5, $this->headerTitle , 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }

    public function Footer() {

        /*$str = "Pag. ". ($this->getAliasNumPage());
        if($this->footerTime)
            $str .= " - ".date("d/m/Y H\hi:s");

        $this->SetFont($this->a_footerFont['family'],$this->a_footerFont['style'], $this->a_footerFont['size']);
        $this->SetY(-10);
        $this->Cell(0, 5, $str, 0, false, 'R', 0, '', 0, false, 'T', 'M');*/

		 // Position at 15 mm from bottom
		 $this->SetY(-10);
		 // Set font
		 //$this->SetFont('helvetica', 'I', 8);
		 $this->SetFont($this->a_footerFont['family'],$this->a_footerFont['style'], $this->a_footerFont['size']);
		 // Page number
		 $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

    }

    public function setHeaderPage($fontSize=10,$lineTop = false){
        $this->SetMargins(10, 10, 10);

        $this->AddPage('L');
        $this->SetFont('Arial', 'B', $fontSize);

        $this->SetAutoPageBreak(false);

        $this->setCellPaddings(2,1,2,0);
        for($i=0;$i<count($this->a_headerPage);$i++){
            $this->lineY = $this->setRow($this->a_headerPage[$i], $this->line , $this->a_styleRetta);
            if($i==count($this->a_headerPage)-2){
                $this->setCellPaddings(2,0,2,1);
                $this->line = "down";
            }
            else{
                $this->line = "no";
                $this->setCellPaddings(2,0,2,0);
            }
        }
    }

    public function setTotalRow($type="partial"){
        $cont = 0;
        if($type=="partial")
            $totals = $this->a_partialTotals;
        else if($type=="total")
            $totals = $this->a_totals;

        for($i=0;$i<count($this->a_totalsHeader);$i++){
            for($y=0;$y<count($this->a_totalsHeader[$i]);$y++){
                if($this->a_totalsHeader[$i][$y]=="{".$cont."}"){
					if(!empty($totals[$cont]))
                    	$a_totals[$i][$y] = number_format($totals[$cont],2,",",".")." Euro";
					else
						$a_totals[$i][$y] = "0,00 Euro";
                    $cont++;
                }
                else if($this->a_totalsHeader[$i][$y]=="{TOTALE}"){
                    if($type=="partial")
                        $a_totals[$i][$y] = "PARZIALI DI PAGINA";
                    else if($type=="total")
                        $a_totals[$i][$y] = "TOTALI";
                }
                else
                    $a_totals[$i][$y] = $this->a_totalsHeader[$i][$y];
            }
        }

        $this->SetFont('Arial', 'B', 8);
        $this->setCellPaddings(2,1,2,0);
        $y = $this->setRow($a_totals[0], "no" , $this->a_styleRetta , $this->a_align_totals,0,$this->a_width_totals );

        for($i=1;$i<count($a_totals);$i++){

            $line = "no";
            $bottom = 0;
            if($i==count($a_totals)-1){
                $line = "down";
                $bottom = 1;
            }

            $this->setCellPaddings(2,1,2,$bottom);
            $y = $this->setRow($a_totals[$i], $line , $this->a_styleRetta , $this->a_align_totals,0,$this->a_width_totals  );
        }

        if($type=="partial")
            $this->a_partialTotals = null;
    }

    public function getTotals(array $a_value){
        $cont = 0;
        foreach($this->a_totalsVar as $row=>$a_rowValues){
            foreach($a_rowValues as $keyValue=>$value){
                $number = $a_value[$row][$value];
                if(isset($this->a_partialTotals[$cont]))
                    $this->a_partialTotals[$cont]+= floatval(str_replace(',', '.', str_replace('.', '', $number)));
                else
                    $this->a_partialTotals[$cont] = floatval(str_replace(',', '.', str_replace('.', '', $number)));

                if(isset($this->a_totals[$cont]))
                    $this->a_totals[$cont]+= floatval(str_replace(',', '.', str_replace('.', '', $number)));
                else
                    $this->a_totals[$cont] = floatval(str_replace(',', '.', str_replace('.', '', $number)));

                $cont++;
            }
        }
    }

    public function setRowPage($a_value, $fontSize=8, $fontHeader=10, $limit=40, $lineTop = false ){
        if(is_array($this->a_totalsHeader)){
            $this->getTotals($a_value);
        }
        $this->SetFont('Arial', '', $fontSize);

        $this->setCellPaddings(2,1,2,0);
        $y = $this->setRow($a_value[0], $this->line , $this->a_styleDash , $this->a_align );

        for($i=1;$i<count($a_value);$i++){
            if($this->line == "no")
                $this->line = "up";
            if($i==count($a_value)-1)
                $bottom = 1;
            else
                $bottom = 0;
            $this->setCellPaddings(2,1,2,$bottom);
            $y = $this->setRow($a_value[$i], "no" , $this->a_styleDash , $this->a_align );
        }

        if($y > ($this->getPageHeight()-$limit) ){
            $this->line = "up";
            $this->addLines();
            $this->setHeaderPage($fontHeader,$lineTop);

            return "addPage";
        }

        return "noAddPage";
    }


    public function setRowPageTotal($a_value, $fontSize=8, $fontHeader=10, $limit=40, $a_total = array(),$force, $printType = "def"){
        if(is_array($this->a_totalsHeader)){
            $this->getTotals($a_value);
        }
        $this->SetFont('Arial', '', $fontSize);

        $this->setCellPaddings(2,1,2,0);
        $y = $this->setRow($a_value[0], $this->line , $this->a_styleDash , $this->a_align );

        for($i=1;$i<count($a_value);$i++){
            if($this->line == "no")
                $this->line = "up";
            if($i==count($a_value)-1)
                $bottom = 1;
            else
                $bottom = 0;
            $this->setCellPaddings(2,1,2,$bottom);
            $y = $this->setRow($a_value[$i], "no" , $this->a_styleDash , $this->a_align );
        }

        if($y > ($this->getPageHeight()-$limit) ){
            $this->addLines();
            for($i=0;$i<count($a_total);$i++){
                if($this->line == "no")
                    $this->line = "up";
                if($i==count($a_total)-1)
                    $bottom = 1;
                else
                    $bottom = 0;
                $this->setCellPaddings(2,1,2,$bottom);
                $y = $this->setRow($a_total[$i], "up_down" , $this->a_styleRetta , $this->a_align );
            }
            $this->setHeaderPage($fontHeader);
			if($printType == "temp")
				$this->temporaryPrinting();
            return true;
        }else if($force && $y <= ($this->getPageHeight()-$limit)){
            $this->addLines();
            for($i=0;$i<count($a_total);$i++){
                if($this->line == "no")
                    $this->line = "up";
                if($i==count($a_total)-1)
                    $bottom = 1;
                else
                    $bottom = 0;
                $this->setCellPaddings(2,1,2,$bottom);
                $y = $this->setRow($a_total[$i], "up_down" , $this->a_styleRetta , $this->a_align );
            }
        }

        return false;
    }

    public function setMainPageParams($a_parameters){
        foreach ($a_parameters as $key=>$value){
            $this->a_mainPageParams[$key] = $value;
        }
    }

    public function setMainPage($a_filters, $recap, $orientation=null,$printType = "def"){

        $this->addPage($orientation);
        if($orientation=="P")
            $this->SetMargins(5, 5, 5);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        $this->setCellPaddings(2,0,2,1);
        $this->ln(10);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 0, $this->a_mainPageParams['title'] , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetFont('Arial', '', 16);
        $this->Cell(0, 0, $this->a_mainPageParams['subtitle'] , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->ln(10);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');

        for($i=0;$i<count($a_filters);$i++){
            $this->SetFont('Arial', '', 12);
            $this->Cell (80, 0, $a_filters[$i]['label'].":", 0, 0, "L");
            $this->SetFont('Arial', 'I', 12);
            $this->Cell ( $this->getPageWidth()-100 , 5, $a_filters[$i]['value'] , 0, 1, "L");
        }

        $this->ln(10);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 0, "RIEPILOGO" , 0, 1, 'L');

        for($i=0;$i<count($recap);$i++){
            $this->SetFont('Arial', '', 12);
            $this->Cell (80, 0, $recap[$i]['label'].":", 0, 0, "L");
            $this->SetFont('Arial', 'I', 12);
            $this->Cell ( $this->getPageWidth()-100 , 5, $recap[$i]['value'] , 0, 1, "L");
        }

		if($printType == "temp")
        	$this->temporaryPrinting();

        $this->movePage($this->PageNo(), 1);
    }

    function addLines($style="retta"){
        $this->verticalLines( $this->lineY , $this->getY(), $this->a_styleDash);
        if($style=="retta")
            $style = $this->a_styleRetta;
        else
            $style = $this->a_styleDash;

        $this->Line( $this->getX(),  $this->getY(), ( array_sum( $this->a_width ) + $this->getMargins()['left'] ) ,  $this->getY(), $style ) ;
        if(is_array($this->a_totalsHeader))
            $this->setTotalRow();
    }

    function setRow( $a_value , $line = "no" , $style=array() , $align = null , $height = 0, $a_width=null ){
        /**
        se $linea = "down" viene creata solo la linea al di sotto della riga
        se $linea = "up_down" vengono create entrambe le linee al di sopra e al di sotto della riga
        se $linea = "up" viene creata solo la linea al di sopra della riga
        se $linea = "no" non vengono create linee
         */

        if($a_width==null)
            $a_width = $this->a_width;

        $tot = array_sum( $a_width );
        $margine = $this->getMargins();

        if( $line == "up" || $line == "up_down" ) 	$this->Line( $this->getX(),  $this->getY(), ( $tot + $margine['left'] ) ,  $this->getY(), $style ) ;

        $y = 0;
        for($k=0 ; $k < count($a_value)-1 ; $k++ ){
            if( $align == null ) 	$allinea = "L";
            else					$allinea = $align[$k];

            $this->startTransaction();
            $this->MultiCell( $a_width[$k] , $height, $a_value[$k] , 0 , $allinea , 0 , 1 , '' , '' , true );
            if( $this->getY() > $y )
                $y = $this->getY();

            $this->rollbackTransaction(true);

            $this->MultiCell( $a_width[$k] , $height, $a_value[$k] , 0 , $allinea , 0 , 0 , '' , '' , true );
        }

        if( $align == null ) 	$allinea = "L";
        else					$allinea = $align[count($a_value)-1];
        $this->MultiCell( $a_width[count($a_value)-1] , $height, $a_value[count($a_value)-1] , 0 , $allinea , 0 , 1 , '' , '' , true );
        if( $this->getY() > $y )
            $y = $this->getY();

        if( $line == "down" || $line == "up_down" ) $this->Line( $this->getX(), $y, ( $tot + $margine['left'] ) , $y, $style ) ;
        $this->setY($y);

        return $y;
    }

    function verticalLines ($y1 , $y2, $style ){
        if($orientation = "V"){
            $margine = $this->getMargins();
            $x = $margine['left'];
            for($k=0 ; $k < count($this->a_width)-1 ; $k++ ){
                $x += $this->a_width[$k];
                $this->Line( $x , $y1 , $x , $y2 , $style );
            }

        }
    }

    public function temporaryPrinting(){
        $x = $this->GetX();
        $y = $this->GetY();
        $size = $this->getFontSize();
        $font = $this->getFontFamily();
        //var_dump($size);die;

        if($this->CurOrientation=="P")
            $this->SetXY(60, 200);
        else if($this->CurOrientation=="L")
            $this->SetXY(93, 155);

        $this->StartTransform();
        $this->Rotate(50);
        $this->SetFont('Helvetica', '', 32);
        $this->SetTextColor(190);
        $this->Cell('130','','STAMPA PROVVISORIA',0,0,'C');
        $this->StopTransform();

        $this->SetFont($font, '', $size);
        $this->SetTextColor(0);
        $this->SetXY($x, $y);
    }


    /**
     *  BOLLETTINO POSTALE
     */
	public function crea_bollettino()
	{
	    ////////////////////////   LINEE BOLLETTINO 1	////////////////////////
		$this->SetLineWidth(0.2);
		
		//LINEA SUPERIORE BOLLETTINO 1
		$this->SetXY(132, -19);
		$this->Line($this->GetX(), $this->GetY(), 297, $this->GetY());
		//LINEA INFERIORE BOLLETTINO 1
		$this->SetXY(132, -102);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX(), 210);
		//LINEA VERTICALE BOLLETTINO 1
		$this->SetXY(0, -98);
		$this->Line($this->GetX(), $this->GetY(), 297, $this->GetY());
		
		/////////////	BARRA SUPERIORE IN GRIGIO BOLLETTINO 1		/////////////
		$this->SetXY(0, -102);
		$this->SetFont('Arial','','7');
		$this->SetFillColor(200);
		//SFONDO GRIGIO
		$this->Cell(297,4,"",0,0,'L',true);
		$this->SetXY(0, -102);
		//RICEVUTA DI VERSAMENTO
		$this->Cell(10,4,"");
		$this->Cell(103.5,4,"CONTI CORRENTI POSTALI - Ricevuta di Versamento");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		//RICEVUTA DI ACCREDITO
		$this->Cell(7.5,4,"");
		$this->Cell(139,4,"CONTI CORRENTI POSTALI - Ricevuta di Accredito");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		
		////////////	INTESTAZIONE VERSAMENTO BOLLETTINO 1	///////////////
		$this->SetFont('Arial','','7');
		$this->SetXY(31, 210-102 + 7.5);
		//IMG EURO VERSAMENTO
		$this->Image(WEB_ROOT."/immagini/euro.png",'', '' ,7);
		//"Sul C/C n."
		$this->SetXY(40, 210-102 + 8.5);
		$this->Cell(7,3,"sul");
		$this->SetXY(40, 210-102 + 11);
		$this->Cell(7,3,"C/C n.");
		//"di euro"
		$this->SetXY(80.5, 210-102 + 11);
		$this->Cell(10,3,"di Euro");
		$this->SetXY(90, 210-102 + 10);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	
		
		$this->SetFont('','','11');
		$this->Cell(1,'',",",0,0,'C');	
		$this->SetFont('Arial','','7');
		
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(31, 210-102 + 15.5);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
	
		//INTESTATO A
		$this->SetXY(10, 210-102 + 22);
		$this->Cell(119,2.5,"INTESTATO A:",0,0,'L');
		//CAUSALE
		$this->SetXY(10, 210-102 + 32.5);
		$this->Cell(119,2.5,"CAUSALE:",0,0,'L');
		//ESEGUITO DA
		$this->SetXY(10, 210-102 + 46);
		$this->Cell(67,2.5,"ESEGUITO DA:",0,0,'L');
		
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(77, 210-24);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		
		///////////		INTESTAZIONE ACCREDITO BOLLETTINO 1		/////////////
		//IMG EURO ACCREDITO
		$this->SetXY(139.5, 210-102 + 7.5);
		$this->Image(WEB_ROOT."/immagini/euro.png", '', '',7);
		//Sul C/C n.
		$this->SetFont('Arial','','7');
		$this->SetXY(148, 210-102 + 11);
		$this->Cell(20,3,"sul C/C n.",0,0,'C');
		//di euro
		$this->SetXY(236, 210-102 + 11);
		$this->Cell(10,3,"di Euro");
		$this->SetXY(248, 210-102 + 10);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	
				
		$this->SetFont('','','11');
		$this->Cell(1,'',",",0,0,'C');	
		$this->SetFont('Arial','','7');
		
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(168, -102+15.5);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
		
		//INTESTATO A
		$this->SetXY(139.5, 210-102 + 22);
		$this->Cell(146,2.5,"INTESTATO A:",0,0,'L');
		//CAUSALE
		$this->SetXY(192, 210-102 + 32.5);
		$this->Cell(95,2.5,"CAUSALE:",0,0,'L');
		//ESEGUITO DA
		$this->SetXY(192, 210-102 + 46);
		$this->Cell(95,2.5,"ESEGUITO DA:",0,0,'L');
		
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(132, 210-24);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		$this->Cell(100,3,"IMPORTANTE NON SCRIVERE NELLA ZONA SOTTOSTANTE",0,0,'C');
		$this->SetFont('Arial','','4');
		$this->SetXY(132, 210-21);
		//codice cliente
		$this->Cell(55,2,"codice cliente",0,0,'C');
		$this->Cell(18.46,2,"",0,0,'C');
		//importo in euro
		$this->Cell(30.54,2,"importo in euro",0,0,'C');
		$this->Cell(5.115,2,"",0,0,'C');
		//numero conto
		$this->Cell(33.085,2,"numero conto",0,0,'C');
		$this->Cell(5.12,2,"",0,0,'C');
		//td
		$this->Cell(10.18,2,"td",0,0,'C');
		
	}
	
	public function crea_bollettino_inverso()
	{
		////////////////////////   LINEE BOLLETTINO 2	////////////////////////
		$this->SetLineWidth(0.2);
		
		//LINEA SUPERIORE BOLLETTINO 1
		$this->SetXY(-132, 19);
		$this->Line($this->GetX(), $this->GetY(), 0, $this->GetY());
		//LINEA INFERIORE BOLLETTINO 1
		$this->SetXY(-132, 102);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX(), 0);
		//LINEA VERTICALE BOLLETTINO 1
		$this->SetXY(0, 98);
		$this->Line($this->GetX(), $this->GetY(), 297, $this->GetY());
		
		/////////////	BARRA SUPERIORE IN GRIGIO BOLLETTINO 2		/////////////
		$this->SetFont('Arial','','7');
		$this->SetFillColor(200);
		$this->SetXY(297, 102);
		$this->StartTransform();
		$this->Rotate(180);
		//SFONDO GRIGIO
		$this->Cell(297,4,"",0,0,'L',true);
		$this->StopTransform();
		
		$this->SetXY(297, 102);
		$this->StartTransform();
		$this->Rotate(180);
		//RICEVUTA DI VERSAMENTO
		$this->Cell(10,4,"");
		$this->Cell(103.5,4,"CONTI CORRENTI POSTALI - Ricevuta di Versamento");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		//RICEVUTA DI ACCREDITO
		$this->Cell(7.5,4,"");
		$this->Cell(139,4,"CONTI CORRENTI POSTALI - Ricevuta di Accredito");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		$this->StopTransform();
		
		
		////////////	INTESTAZIONE VERSAMENTO BOLLETTINO 1	///////////////
		$this->SetFont('Arial','','7');
		
		//IMG EURO VERSAMENTO
		$this->SetXY(297-31, 102 - 7.5);
		$this->StartTransform();
		$this->Rotate(180);		
		$this->Image("/gitco2/immagini/euro.png",'', '' ,7);
		$this->StopTransform();
		
		//"Sul C/C n."
		$this->SetXY(297-40, 102 - 8.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(7,3,"sul");
		$this->StopTransform();		
		$this->SetXY(297-40, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(7,3,"C/C n.");
		$this->StopTransform();
		
		//"di euro"
		$this->SetXY(297-80.5, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(10,3,"di Euro");
		$this->StopTransform();
		$this->SetXY(297-90, 102 - 10);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');			
		
		$this->SetFont('','','11');
		$this->Cell(1,'',",",0,0,'C');	
		$this->SetFont('Arial','','7');
		
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		$this->StopTransform();

		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(297-31, 102 - 15.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
		$this->StopTransform();
		
		//INTESTATO A
		$this->SetXY(297-10, 102 - 22);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(119,2.5,"INTESTATO A:",0,0,'L');
		$this->StopTransform();
		//CAUSALE
		$this->SetXY(297-10, 102 - 32.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(119,2.5,"CAUSALE:",0,0,'L');
		$this->StopTransform();
		//ESEGUITO DA
		$this->SetXY(297-10, 102 - 46);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(67,2.5,"ESEGUITO DA:",0,0,'L');
		$this->StopTransform();
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(297-77, 24);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		$this->StopTransform();
		
		///////////		INTESTAZIONE ACCREDITO BOLLETTINO 1		/////////////
		//IMG EURO ACCREDITO
		$this->SetXY(297-139.5, 102 - 7.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Image("/gitco2/immagini/euro.png", '', '',7);
		$this->StopTransform();
		//Sul C/C n.
		$this->SetFont('Arial','','7');
		$this->SetXY(297-148, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(20,3,"sul C/C n.",0,0,'C');
		$this->StopTransform();
		//di euro
		$this->SetXY(297-236, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(10,3,"di Euro");
		$this->StopTransform();
		$this->SetXY(297-248, 102 - 10);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->SetFont('','','11');	$this->Cell(1,'',",",0,0,'C');	$this->SetFont('Arial','','7');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		$this->StopTransform();
		
		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(-168, 102-15.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
		$this->StopTransform();
			
		//INTESTATO A
		$this->SetXY(-139.5, 102 - 22);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(146,2.5,"INTESTATO A:",0,0,'L');
		$this->StopTransform();
		//CAUSALE
		$this->SetXY(-192, 102 - 32.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(95,2.5,"CAUSALE:",0,0,'L');
		$this->StopTransform();
		//ESEGUITO DA
		$this->SetXY(-192, 102 - 46);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(95,2.5,"ESEGUITO DA:",0,0,'L');
		$this->StopTransform();
		
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(297-132, 24);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		$this->Cell(100,3,"IMPORTANTE NON SCRIVERE NELLA ZONA SOTTOSTANTE",0,0,'C');
		$this->StopTransform();
		$this->SetFont('Arial','','4');
		$this->SetXY(297-132, 21);
		$this->StartTransform();
		$this->Rotate(180);
		//codice cliente
		$this->Cell(55,2,"codice cliente",0,0,'C');
		$this->Cell(18.46,2,"",0,0,'C');
		//importo in euro
		$this->Cell(30.54,2,"importo in euro",0,0,'C');
		$this->Cell(5.115,2,"",0,0,'C');
		//numero conto
		$this->Cell(33.085,2,"numero conto",0,0,'C');
		$this->Cell(5.12,2,"",0,0,'C');
		//td
		$this->Cell(10.18,2,"td",0,0,'C');
		$this->StopTransform();
	}
	
	public function scelta_td_bollettino($td , $codice_cliente, $importo, $ctrl_importo, $num_conto, $id_bollettino = 'uno')
	{
		switch($td)
		{
			case "123":	$this->td_123_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino);								break;
			case "451":	$this->td_451_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino);								break;
			case "674":	$this->td_674_bollettino($codice_cliente, $importo, $ctrl_importo, $num_conto, $id_bollettino);				break;
			case "896":	$this->td_896_bollettino($codice_cliente, $importo, $num_conto, $id_bollettino);	                        break;
		}
	}
	
	public function td_896_bollettino($codice_cliente, $importo, $num_conto, $id_bollettino = 'uno')
	{
		$this->set_codice_cliente_bollettino($codice_cliente, $id_bollettino);
		$this->set_importo_bollettino($importo, 'si', $id_bollettino);
		$this->set_num_conto_bollettino($num_conto, $id_bollettino);
		$this->set_td_bollettino('896',$id_bollettino);
		
		$this->codice_barcode_bollettino($codice_cliente, $importo, $num_conto, '896', $id_bollettino);
	}
	
	public function td_674_bollettino($codice_cliente, $importo, $ctrl_importo, $num_conto, $id_bollettino = 'uno')
	{
		
		$this->importo_in_lettere_bollettino($id_bollettino);
		$this->set_codice_cliente_bollettino($codice_cliente, $id_bollettino);
		$this->set_num_conto_bollettino($num_conto, $id_bollettino);
		$this->set_td_bollettino('674',$id_bollettino);
		
		if($ctrl_importo == 'si')	$this->set_importo_bollettino($importo, 'no', $id_bollettino);
	}
	
	public function td_451_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino = 'uno')
	{
		$this->importo_in_lettere_bollettino($id_bollettino);
		$this->set_td_bollettino('451',$id_bollettino);
		$this->set_num_conto_bollettino($num_conto, $id_bollettino);	

		if($ctrl_importo == 'si')	$this->set_importo_bollettino($importo, 'no', $id_bollettino);
	}
	
	public function td_123_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino = 'uno')
	{
		$this->set_num_conto_bollettino($num_conto, $id_bollettino, true);
		$this->linee_testi_bollettino($id_bollettino);
		$this->importo_in_lettere_bollettino($id_bollettino);
		$this->set_td_bollettino('123',$id_bollettino);
		
		if($ctrl_importo == 'si')	$this->set_importo_bollettino($importo, 'no', $id_bollettino);
	}
	
	public function codice_barcode_bollettino ($codice_cliente, $importo, $num_conto, $td, $id_bollettino)
	{
		$codice_barcode = strlen($codice_cliente).$codice_cliente;
		
		$codice_barcode.='12';
		$conto = str_split($num_conto);
		
		for($i=0;$i<12-count($conto);$i++)
			$codice_barcode.='0';
		for($i=0;$i<count($conto);$i++)
			$codice_barcode.=$conto[$i];
		
		$codice_barcode.='10';
		$importo = explode(',',$importo);
		$interi = str_split($importo[0]);
		$decimali = str_split($importo[1]);
		
		for($i=0;$i<8-count($interi);$i++)
			$codice_barcode.='0'; 
		for($i=0;$i<count($interi);$i++)
			$codice_barcode.=$interi[$i]; 
		for($i=0;$i<count($decimali);$i++)
			$codice_barcode.=$decimali[$i];
		
		$codice_barcode.=strlen($td).$td;
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-82, +18);
			$this->StartTransform();
			$this->Rotate(180);
			$this->write2DBarcode($codice_barcode, 'DATAMATRIX', 297-82, 18, 45, 16);
			$this->StopTransform();
			
			$this->SetXY(-192, -171);
			$this->StartTransform();
			$this->Rotate(180);
			$this->write1DBarcode($codice_barcode,'C128C', 297-192, 210-171, 95, 12);
			$this->StopTransform();
			
			$this->SetXY(-192, -183);
			$this->StartTransform();
			$this->Rotate(180);
			$this->SetFont('Arial','','6');
			$this->Cell(95,2,$codice_barcode,0,0,'C');
			
			$this->StopTransform();
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(82, -18);
			$this->write2DBarcode($codice_barcode, 'DATAMATRIX', 82, 210-18,45,16);
		
			$this->write1DBarcode($codice_barcode,'C128C', 192, 171, 95, 12);
			$this->SetXY(192, 183);
			$this->SetFont('Arial','','6');
			$this->Cell(95,2,$codice_barcode,0,0,'C');
		}				
	}
	
	public function importo_in_lettere_bollettino($id_bollettino = 'uno')
	{
		$this->SetFont('Arial','','6');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-31, 102-19.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(31, -102+19.5);
		}
		
		$this->Cell(25,5,"IMPORTO IN LETTERE",0,0,'L');
		$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+73, $this->GetY()+4);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-168, 102-19.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(168, -102+19.5);
		}
		
		$this->Cell(25,5,"IMPORTO IN LETTERE",0,0,'L');
		$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+94, $this->GetY()+4);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function set_codice_cliente_bollettino( $codice, $id_bollettino = 'uno' )
	{		
		if(strlen($codice)!=18)	return false;
		
		$this->SetFont('','','11');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-139.5, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -8.5);
		}
			
		$cod = str_split($codice);
		
		$this->Cell(2.545,'',"<");
		
		for($i=0;$i<count($cod);$i++)
			$this->Cell(2.545,'',$cod[$i]);
			
		$this->Cell(2.545,'',">");
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-139.5, 102-42);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -102+42);
		}
		
		for($i=0;$i<count($cod);$i++)
			$this->Cell(2.545,'',$cod[$i]);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
	}
	
	public function set_quinto_campo($td, $quinto_campo, $codeline = false )
	{
		if($td == "896" || $td == "674")
		{
			$ritorno = "";
			
			if($codeline === true)
				$ritorno.= "<";
			
			$ritorno.= $quinto_campo;
			
			if($codeline === true)
				$ritorno.= ">";
		}
		else
			$ritorno = "";
		
		return $ritorno;
	} 
	
	public function barcode_importo_bollettino($td, $importo)
	{
		if($td == "896")
		{
			$importo = explode(',',$importo);
			$interi = str_split($importo[0]);
			$decimali = str_split($importo[1]);
		
			$ritorno = "";
			for($i=0;$i<8-count($interi);$i++)
				$ritorno .= "0";			
			for($i=0;$i<count($interi);$i++)
				$ritorno .= $interi[$i];
	
			$ritorno .= "+";
			
			for($i=0;$i<count($decimali);$i++)
				$ritorno .= $decimali[$i];
			
			$ritorno .= ">";
		}
		else 
			$ritorno = "";
		
		return $ritorno;
	}
	
	public function set_importo_bollettino( $importo, $barcode='si', $id_bollettino = 'uno' )
	{
        if($importo == null) $importo = array("","");
		else $importo = explode(',',$importo);
		$interi = str_split($importo[0]);
		$decimali = str_split($importo[1]);
		
		$this->SetFont('','','11');
	
		if($barcode == "si")
		{
			
		if($id_bollettino=='due')
		{
			$this->SetXY(-205.46, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(205.46, -8.5);
		}
			
		for($i=0;$i<8-count($interi);$i++)
			$this->Cell(2.545,'','0');
		for($i=0;$i<count($interi);$i++)
			$this->Cell(2.545,'',$interi[$i]);
				
			$this->Cell(2.545,'',"+");
			
		for($i=0;$i<count($decimali);$i++)
			$this->Cell(2.545,'',$decimali[$i]);
	
			$this->Cell(2.545,'',">");
			
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-90, 102-10);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(90, -102+10);
		}
		
		for($i=0;$i<8-count($interi);$i++)
		{
			$this->Cell(3,'','',0,0,'C');
			$this->Cell(1,'','');
		}
		for($i=0;$i<count($interi);$i++)
		{
			$this->Cell(3,'',$interi[$i],0,0,'C');
			$this->Cell(1,'','');
		}									
		for($i=0;$i<count($decimali);$i++)
		{
			$this->Cell(3,'',$decimali[$i],0,0,'C');
			$this->Cell(1,'','');
		}
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-248, 102-10);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(248, -102+10);
		}
		
		for($i=0;$i<8-count($interi);$i++)
		{
			$this->Cell(3,'','',0,0,'C');
			$this->Cell(1,'','');
		}
		for($i=0;$i<count($interi);$i++)
		{
			$this->Cell(3,'',$interi[$i],0,0,'C');
			$this->Cell(1,'','');
		}
		for($i=0;$i<count($decimali);$i++)
		{
			$this->Cell(3,'',$decimali[$i],0,0,'C');
			$this->Cell(1,'','');
		}
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function linee_testi_bollettino($id_bollettino = 'uno')
	{
		$this->SetLineWidth(0.05);
		if($id_bollettino=='due')
		{
			$this->SetXY(-48, 102-10);
			$this->Line($this->GetX()-28, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-168, 102-10);
			$this->Line($this->GetX()-35, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-10, 102-25.5);
			$this->Line($this->GetX()-119, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-139.5, 102-25.5);
			$this->Line($this->GetX()-147.5, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-10, 102-36);
			$this->Line($this->GetX()-119, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-119, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->SetXY(-192, 102-36);
			$this->Line($this->GetX()-95, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-95, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->SetXY(-10, 102-50);
			$this->Line($this->GetX()-67, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-67, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->Line($this->GetX()-67, $this->GetY()-12, $this->GetX(), $this->GetY()-12);
			$this->SetXY(-192, 102-50);
			$this->Line($this->GetX()-95, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-95, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->Line($this->GetX()-95, $this->GetY()-12, $this->GetX(), $this->GetY()-12);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(48, -102+10);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+28, $this->GetY()+4);
			$this->SetXY(168, -102+10);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+35, $this->GetY()+4);
			$this->SetXY(10, -102+25.5);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+119, $this->GetY()+4);
			$this->SetXY(139.5, -102+25.5);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+147.5, $this->GetY()+4);
			$this->SetXY(10, -102+36);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+119, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+119, $this->GetY()+8);
			$this->SetXY(192, -102+36);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+95, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+95, $this->GetY()+8);
			$this->SetXY(10, -102+50);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+67, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+67, $this->GetY()+8);
			$this->Line($this->GetX(), $this->GetY()+12, $this->GetX()+67, $this->GetY()+12);
			$this->SetXY(192, -102+50);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+95, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+95, $this->GetY()+8);
			$this->Line($this->GetX(), $this->GetY()+12, $this->GetX()+95, $this->GetY()+12);
		}
		
		
	}
	
	public function barcode_conto_bollettino($td, $num_conto)
	{
		if($td == "896" || $td == "674" || $td == "451")
		{
			$conto = str_split($num_conto);
			
			$ritorno = "";
			for($i=0;$i<12-count($conto);$i++)
				$ritorno .= "0";
			for($i=0;$i<count($conto);$i++)
				$ritorno .= $conto[$i];
			$ritorno .= "<";
		}
		else 
			$ritorno = "";
		
		return $ritorno;
	}
	
	public function set_num_conto_bollettino( $num_conto, $id_bollettino = 1, $no_barcode = false )
	{		
		$this->SetFont('','','11');
		$conto = str_split($num_conto);
		
		if($no_barcode === false)
		{
			
		if($id_bollettino=='due')
		{
			$this->SetXY(-241.115, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(241.115, -8.5);
		}		
				
		for($i=0;$i<12-count($conto);$i++)
			$this->Cell(2.545,'','0');
		for($i=0;$i<count($conto);$i++)
			$this->Cell(2.545,'',$conto[$i]);
		
		$this->Cell(2.545,'',"<");
		
		if($id_bollettino == 'due')
		{
			$this->StopTransform();
		}
		
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-48, 102-10 );
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino == 'uno')
		{
			$this->SetXY(48, -102+10 );
		}		
			
		for($i=0;$i<count($conto);$i++)
			$this->Cell(2.545,'',$conto[$i]);
		
		if($id_bollettino == 'due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-168, 102-10);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino == 'uno')
		{
			$this->SetXY(168, -102+10);
		}
			
		for($i=0;$i<count($conto);$i++)
			$this->Cell(2.545,'',$conto[$i]);
		
		if($id_bollettino == 'due')
		{
			$this->StopTransform();
		}
	}
	
	public function set_td_bollettino( $td, $id_bollettino = 'uno' )
	{
		if(strlen($td)!=3)	return false;

		$this->SetFont('','','11');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-139.5, 102-16.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -102+16.5);
		}
		
		$this->Cell(20,'',"TD ".$td);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-279.32, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(279.32, -8.5);
		}
			
		$td = str_split($td);
	
		for($i=0;$i<count($td);$i++)
			$this->Cell(2.545,'',$td[$i]);
	
		$this->Cell(2.545,'',">");
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function iban_bollettino( $iban, $id_bollettino = 'uno' )
	{	
		$iban_arr = str_split($iban);
		if($iban_arr[0]=='*' || $iban_arr[0] == "")
		{
			$iban = '***************************';
			$iban_arr = str_split($iban);
		}
		
		if(strlen($iban)!=27)	return false;
		
		$this->SetFont('','','9');
	
		if($id_bollettino=='due')
		{
			$this->SetXY(-51, 102-15.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(51, -102+15.5);
		}		
	
		for($i=0;$i<count($iban_arr);$i++)
			$this->Cell(2.4,'',$iban_arr[$i],0,0,'C');
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-188, 102-15.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(188, -102+15.5);
		}		
	
		for($i=0;$i<count($iban_arr);$i++)
			$this->Cell(2.4,'',$iban_arr[$i],0,0,'C');
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function intestatario_bollettino ($intestatario , $id_bollettino = 'uno')
	{
		$intestatario = strtoupper($intestatario);
		$this->SetFont('','B','9');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-10, 102-25.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+25.5);
		}
		
		$this->Cell(119,4,$intestatario);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-139.5, 102-25.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -102+25.5);
		}
			
		$this->SetFont('','B','11');
		
		$this->Cell(146,4,$intestatario);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function causale_bollettino ($riga1 , $riga2, $id_bollettino = 'uno')
	{
		$riga1 = strtoupper($riga1);
		$riga2 = strtoupper($riga2);
		$this->SetFont('','B','7');
	
		if($id_bollettino=='due')
		{
			$this->SetXY(-10, 102-36);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+36);
		}
	
		$this->Cell(119,4,$riga1);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-10, 102-40);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+40);
		}
		
		$this->Cell(119,4,$riga2);
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-192, 102-36);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+36);
		}
		
		$this->Cell(95,4,$riga1);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-192, 102-40);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+40);
		}
		
		$this->Cell(95,4,$riga2);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}

    public function payerZone ($nome_utente , $a_rows , $id_bollettino = 'uno')
    {
        $riga1 = strtoupper($nome_utente);
        $riga2 = strtoupper($a_rows[0]);
        $riga3 = strtoupper($a_rows[1]);
        $this->SetFont('','B','7');

        if($id_bollettino=='due')
        {
            $this->SetXY(-10, 102-50);
            $this->StartTransform();
            $this->Rotate(180);
        }
        else if($id_bollettino=='uno')
        {
            $this->SetXY(10, -102+50);
        }

        $this->Cell(67,4,$riga1);

        if($id_bollettino=='due')
        {
            $this->StopTransform();
            $this->SetXY(-10, 102-54);
            $this->StartTransform();
            $this->Rotate(180);
        }
        else if($id_bollettino=='uno')
        {
            $this->SetXY(10, -102+54);
        }

        $this->Cell(67,4,$riga2);

        if($id_bollettino=='due')
        {
            $this->StopTransform();
            $this->SetXY(-10, 102-58);
            $this->StartTransform();
            $this->Rotate(180);
        }
        else if($id_bollettino=='uno')
        {
            $this->SetXY(10, -102+58);
        }

        $this->Cell(67,4,$riga3);

        if($id_bollettino=='due')
        {
            $this->StopTransform();
        }

        if($id_bollettino=='due')
        {
            $this->SetXY(-192, 102-50);
            $this->StartTransform();
            $this->Rotate(180);
        }
        else if($id_bollettino=='uno')
        {
            $this->SetXY(192, -102+50);
        }

        $this->Cell(95,4,$riga1);

        if($id_bollettino=='due')
        {
            $this->StopTransform();
            $this->SetXY(-192, 102-54);
            $this->StartTransform();
            $this->Rotate(180);
        }
        else if($id_bollettino=='uno')
        {
            $this->SetXY(192, -102+54);
        }

        $this->Cell(95,4,$riga2);

        if($id_bollettino=='due')
        {
            $this->StopTransform();
            $this->SetXY(-192, 102-58);
            $this->StartTransform();
            $this->Rotate(180);
        }
        else if($id_bollettino=='uno')
        {
            $this->SetXY(192, -102+58);
        }

        $this->Cell(95,4,$riga3);

        if($id_bollettino=='due')
        {
            $this->StopTransform();
        }
    }

	public function zona_cliente_bollettino ($nome_utente , $indirizzo_destinatario , $id_bollettino = 'uno')
	{
		$riga1 = strtoupper($nome_utente);
		$riga2 = strtoupper($indirizzo_destinatario['Riga1']." - ".$indirizzo_destinatario['Riga2']);
		if($indirizzo_destinatario['Riga4']!="")
			$riga3 = strtoupper($indirizzo_destinatario['Riga3'].", ".$indirizzo_destinatario['Riga4']);
		else 
			$riga3 = strtoupper($indirizzo_destinatario['Riga3']);
		$this->SetFont('','B','7');
	
		if($id_bollettino=='due')
		{
			$this->SetXY(-10, 102-50);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+50);
		}
	
		$this->Cell(67,4,$riga1);
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-10, 102-54);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+54);
		}
	
		$this->Cell(67,4,$riga2);
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-10, 102-58);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+58);
		}
		
		$this->Cell(67,4,$riga3);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-192, 102-50);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+50);
		}
		
		$this->Cell(95,4,$riga1);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-192, 102-54);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+54);
		}
		
		$this->Cell(95,4,$riga2);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-192, 102-58);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+58);
		}
		
		$this->Cell(95,4,$riga3);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	function logo_bollettino( $logo, $id_bollettino = 'uno' )
	{
	    $cls_file = new cls_file();
	    //var_dump($logo);
	    $size = $cls_file->imageSize($logo['rootPath'], 20, 11);
				
		if($id_bollettino=='uno')
		{
			$this->SetXY(10, 210-102 + 5.5 + (5.5-$size[1]/2));
		}
		else if($id_bollettino=='due')
		{
			$this->SetXY(297-10, 102 - 5.5 - (5.5-$size[1]/2));
			$this->StartTransform();
			$this->Rotate(180);
		}
		
		$this->Image($logo['webPath'],'', '' ,$size[0]);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function autorizzazione_bollettino( $testo, $id_bollettino = 'uno' )
	{
		$this->SetFont('','','6');
		if($id_bollettino=='uno')
		{
			$this->SetXY(-5, -19);
			$this->StartTransform();
			$this->Rotate(90);
		}
		else if($id_bollettino=='due')
		{
			$this->SetXY(5, 19);
			$this->StartTransform();
			$this->Rotate(-90);
		}
		
		
		$this->Cell(79,4,$testo,0,0,'C');
		$this->StopTransform();
	}

    public function split($string,$needle,$nth){
        $max = strlen($string);
        $n = 0;
        for($i=0;$i<$max;$i++){
            if($string[$i]==$needle){
                $n++;
                if($n>=$nth){
                    break;
                }
            }
        }
        $arr[] = substr($string,0,$i);
        $arr[] = substr($string,$i+1,$max);

        return $arr;
    }
}

?>