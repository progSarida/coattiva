<?php

/**
 * Class cls_290
 */
class cls_dischargeExtraction
{
    public $a_file = array();
    public $cityCode;
    public $a_count = array();

    public function __construct($cityCode)
    {
        $this->cityCode = $cityCode;
    }

    public function addRow($filename, $row, $rowCount=1, $endLine=1){
        if(!isset($this->a_file[$filename]))
            $this->a_file[$filename] = $row;
        else
            $this->a_file[$filename].= $row;

        if($endLine==1)
            $this->a_file[$filename].= "\r\n";

        if($rowCount==1){
            if(!isset($this->a_count[$filename]))
                $this->a_count[$filename] = 1;
            else
                $this->a_count[$filename]++;
        }
    }

    public function saveFile($filename, $filePath){
        if(is_file($filePath))
            chmod($filePath, 0777);
        $OutFile = fopen($filePath, "w");
        if(!$OutFile)
            die("Unable to open file!");

        fwrite($OutFile, $this->a_file[$filename]);
        fclose($OutFile);

    }

    public function showFile($filename){
        echo "<pre>".$this->a_file[$filename]."</pre>";
    }

    public function completeDischargeRow($a_data, $codEnte = null){
//        var_dump($a_data);

        $a_cods = explode("*",$a_data['Codici_Tributo']);
        if(!isset($a_cods[0]) || $a_cods[0]=="")
            return true;

        $a_split_payment = explode("*",$a_data['Codici_Scorporo']);
        $a_imp_cod = explode("*",$a_data['Importi_Codici_Tributo']);
        $a_anni_cod = explode("*",$a_data['Anni_Tributo']);

        $info_cart = substr(str_replace("NUMERO","",$a_data['Info_Cartella']),0,20);

        $totaleCodici = 0;
        for ($i=0; $i<count($a_cods); $i++){
            $totaleCodici+=$a_imp_cod[$i];
        }
        $a_cod = null;
        for ($i=0; $i<count($a_cods); $i++){
            switch($a_split_payment[$i]){
                case 1:
                    $tipoCodice = "I";//IMPOSTA
                    break;
                case 7:
                case 8:
                case 9:
                    $tipoCodice = "S";//SANZIONE
                    break;
            }

            $a_cod = array(
                "codice"=>$a_cods[$i],
                "anno"=>$a_anni_cod[$i],
                "importo"=>$totaleCodici,
                "tipo"=>$tipoCodice
            );

            if($tipoCodice!="")
                break;
        }

        if(is_null($a_cod)){
            $a_cod = array(
                "codice"=>$a_cods[0],
                "anno"=>$a_anni_cod[0],
                "importo"=>$totaleCodici,
                "tipo"=>"A"
            );
        }

        $row = "126";//AGENTE RISCOSSIONE 3
        if(is_null($codEnte))
            $row.= $this->addStringToFile(1, $a_data['CC']);//ENTE 5
        else
            $row.= $this->addStringToFile(1, $codEnte);//ENTE 5
        $row.= $this->addStringToFile(1, "");//TIPO ENTE 1
        $row.= $this->addStringToFile(6, "");//COD UFFICIO 6
        $row.= $this->addStringToFile(4, $a_data['Anno_Riferimento']);//ANNO RUOLO 4
        $row.= $this->addNumberToFile(6, $a_data['Comune_ID'],"0");//NUMERO RUOLO 6

        $row.= 1;//SPECIE RUOLO 1
        $row.= $this->addStringToFile(16, $a_data['CF_PI']);//COD FISCALE / PARTITA IVA 16
        $row.= $this->addStringToFile(20, $info_cart);//INFO CARTELLA 20
        $row.= "  1";//PROGR TRIBUTO IN CARTELLA
        $row.= $this->addStringToFile(4, $a_cod['codice']);//COD TRIBUTO 4
        $row.= $this->addStringToFile(4, $a_cod['anno']);//ANNO TRIBUTO 4

        $row.= $a_cod['tipo'];//SPECIE RUOLO 1
        $row.= $this->addNumberToFile(17, $a_cod['importo']*100);//IMPORTO TRIBUTO

        if(is_null($a_data['Totale_Dovuto']))
            $tot = 0.00;
        else
            $tot = $a_data['Totale_Dovuto'];
        if(is_null($a_data['Totale_Pagamenti']))
            $pag = 0.00;
        else
            $pag = $a_data['Totale_Pagamenti'];

        if($tot-$pag>0)
            $residuo = (number_format($tot,2,".","")-number_format($pag,2,".",""))*100;
        else
            $residuo = 0;

        $row.= $this->addNumberToFile(17, $residuo);//RESIDUO PARTITA
        $this->addRow("complete", $row);


    }

    public function simpleDischargeRow($cf_pi){

        if($cf_pi!="" && $cf_pi!="0" && $cf_pi!="00000000000"){
            $this->addRow("simple", $cf_pi);
        }
    }



    public function addFiller($fillerNumber, $char=" "){
        $filler = "";
        for($i=1;$i<=$fillerNumber;$i++) {
            $filler .= $char;
        }
        return $filler;
    }

    public function addNumberToFile($size, $number, $type=1, $pos = "R")
    {
        $a_number = explode(".", $number);
        $int = $a_number[0];
        if (isset($a_number[1]))
            $flt = (strlen($a_number[1]) == 2) ? $a_number[1] : $a_number[1] . "0";
        else
            $flt = "";

        $out_number = $int . $flt;
        if($type==0)
            return $this->addMissingSpace($size, $out_number);
        else if($type==1)
            return $this->addMissingZero($size, $out_number);
    }

    public function addStringToFile($size, $string, $type=0, $pos = "L")
    {
        if($type==0)
            return $this->addMissingSpace($size, trim($string), $pos);
        else if($type==1)
            return $this->addMissingZero($size, trim($string), $pos);
    }

    public function addMissingZero($size, $dataToInsert, $pos= "R"){
        $zeros = "";
        for ($i = 1; $i <= ($size - strlen($dataToInsert)); $i++) {
            $zeros .= "0";
        }
        if($pos=="R")
            $str_out = $zeros.$dataToInsert;
        else
            $str_out = $dataToInsert.$zeros;
        return $str_out;
    }

    public function addMissingSpace($size, $dataToInsert, $pos= "L"){
        $spaces = "";
        for ($i = 1; $i <= ($size - strlen($dataToInsert)); $i++) {
            $spaces .= " ";
        }
        if($pos=="R")
            $str_out = $spaces.$dataToInsert;
        else
            $str_out = $dataToInsert.$spaces;
        return $str_out;
    }

}