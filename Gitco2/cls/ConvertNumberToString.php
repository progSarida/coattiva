<?php

class ConvertNumberToString
{
    private $decimal = 0;
    private $number = 0;
    private $thousandSeparator = "";
    private $decimalSeparator = "";
    private $onlyWholeNoDec = null;
    private $str = "";

    private $arrayConvert = [
        0 => "zero", 1 => "uno", 2 => "due", 3 => "tre", 4 => "quattro", 5 => "cinque", 6 => "sei", 7 => "sette", 8 => "otto", 9 => "nove", 10 => "dieci", 11 => "undici", 12 => "dodici",
        13 => "tredici", 14 => "quattordici", 15 => "quindici", 16 => "sedici", 17 => "diciassette", 18 => "diciotto", 19 => "diciannove", 20 => "venti", 21 => "ventuno", 22 => "ventidue",
        23 => "ventitre", 24 => "ventiquattro", 25 => "venticinque", 26 => "ventisei", 27 => "ventisette", 28 => "ventotto", 29 => "ventinove", 30 => "trenta", 31 => "trentuno", 32 => "trentadue",
        33 => "trentatre", 34 => "trentaquattro", 35 => "trentacinque", 36 => "trentasei", 37 => "trentasette", 38 => "trentotto", 39 => "trentanove", 40 => "quaranta", 41 => "quarantuno",
        42 => "quarantadue", 43 => "quarantatre", 44 => "quarantaquattro", 45 => "quarantacinque", 46 => "quarantasei", 47 => "quarantasette", 48 => "quarantotto", 49 => "quarantanove",
        50 => "cinquanta", 51 => "cinquantuno", 52 => "cinquantadue", 53 => "cinquantatre", 54 => "cinquantaquattro", 55 => "cinquantacinque", 56 => "cinquantasei", 57 => "cinquantasette",
        58 => "cinquantotto", 59 => "cinquantanove", 60 => "sessanta", 61 => "sessantuno", 62 => "sessantadue", 63 => "sessantatre", 64 => "sessantaquattro", 65 => "sessantacinque", 66 => "sessantasei",
        67 => "sessantasette", 68 => "sessantotto", 69 => "sessantanove", 70 => "settanta", 71 => "settantuno", 72 => "settantadue", 73 => "settantatre", 74 => "settantaquattro", 75 => "settantacinque",
        76 => "settantasei", 77 => "settantasette", 78 => "settantotto", 79 => "settantanove", 80 => "ottanta", 81 => "ottantuno", 82 => "ottantadue", 83 => "ottantatre", 84 => "ottantaquattro",
        85 => "ottantacinque", 86 => "ottantasei", 87 => "ottantasette", 88 => "ottantotto", 89 => "ottantanove", 90 => "novanta", 91 => "novantuno", 92 => "novantadue", 93 => "novantatre",
        94 => "novantaquattro", 95 => "novantacinque", 96 => "novantasei", 97 => "novantasette", 98 => "novantotto", 99 => "novantanove", 100 => "cento", 200 => "duecento", 300 => "trecento",
        400 => "quattrocento", 500 => "cinquecento", 600 => "seicento", 700 => "settecento", 800 => "ottocento", 900 => "novecento", 1000 => "mille", 2000 => "duemila", 3000 => "tremila",
        4000 => "quattromila", 5000 => "cinquemila", 6000 => "seimila", 7000 => "settemila", 8000 => "ottomila", 9000 => "novemila"
    ];

    private $arrayCasiParticolari = [
        100 => "cento",
        1000 => "mille",
        1000000 => "un milione",
        1000000000 => "un miliardo"
    ];

    private $arrayEstensioni = [
        3 => "cento",
        4 => "mila",
        5 => "mila",
        6 => "mila",
        7 => "milioni",
        8 => "milioni",
        9 => "milioni",
        10 => "miliardi",
        11 => "miliardi",
        12 => "miliardi"

    ];

    public function __construct($decimalSeparator = ".",$thousandSeparator = "", $onlyWholeNoDec = true, $betweenAlphaNumber = ",")
    {

        $this->thousandSeparator = $thousandSeparator;
        $this->decimalSeparator = $decimalSeparator;
        $this->onlyWholeNoDec = $onlyWholeNoDec;
        $this->betweenAlphaNumber = $betweenAlphaNumber;


        //dd($this->convert($this->number));
        //dd($this->number." - ".$this->decimal);
    }

    public function resetSeparator($decimalSeparator = ".",$thousandSeparator = "", $onlyWholeNoDec = true, $betweenAlphaNumber = ","){
        $this->thousandSeparator = $thousandSeparator;
        $this->decimalSeparator = $decimalSeparator;
        $this->onlyWholeNoDec = $onlyWholeNoDec;
        $this->betweenAlphaNumber = $betweenAlphaNumber;
    }

    public function setNumber($number){
        
        $noThousand = str_replace($this->thousandSeparator,"",$number);
        if(strpos($noThousand,$this->decimalSeparator)) {
            $arrNum = explode($this->decimalSeparator, $noThousand);
            $this->number = $arrNum[0];
            $this->decimal = $arrNum[1];
        }
        else {
            $this->number = $noThousand;
            $this->decimal = null;
        }
    }

    public function getNumber(){
        if($this->onlyWholeNoDec){
            $this->convert($this->number);
            if($this->decimal != null)
                $this->str .= $this->betweenAlphaNumber.$this->decimal;
        }
        else{
            $this->convert($this->number);
            if($this->decimal != null) {
                $str1 = $this->str . " e ";
                $this->convert($this->decimal);
                $str1 .= $this->str;
                $this->str = $str1;
            }
        }

        return $this->str;
    }

    public function convert($number,$str = ""){

        $number .= "";
        if(strlen($number) <= 2) {
            $str .= $this->arrayConvert[$number];
            $this->str = $str;
            return true;
        }
        else{
            $length = strlen($number);
            $arrCharTemp = array();

            for($i = $length - 1; $i >= 0 ; $i--){
                if(count($arrCharTemp) == 3)
                    $arrCharTemp = array();

                $arrCharTemp[] = $number[$i];
            }
            $arrChar = array();
            for($i=count($arrCharTemp)-1; $i >= 0 ; $i--)
                $arrChar[] = $arrCharTemp[$i];

            if(count($arrChar) == 1) {
                if ($arrChar[0] == 1) {
                    $partialstr = $arrChar[0];
                    for($i=0; $i < $length - 1; $i++)
                        $partialstr .= "0";

                    $str .= $this->arrayCasiParticolari[$partialstr];

                    if($number - (int)$partialstr == 0) {
                        $this->str = $str;
                        return true;
                    }

                    $this->convert($number - (int)$partialstr, $str);
                }
                else{
                    $str .= $this->arrayConvert[$arrChar[0]].$this->arrayEstensioni[$length];
                    $partialstr = $arrChar[0];
                    for($i=0; $i < $length - 1; $i++)
                        $partialstr .= "0";

                    if($number - (int)$partialstr == 0) {
                        $this->str = $str;
                        return $str;
                    }

                    $this->convert($number - (int)$partialstr, $str);
                }
            }
            else
            {

                if(count($arrChar) <= 2){
                    $tempNum = "";
                    for($i=0; $i < count($arrChar); $i++)
                        $tempNum .= $arrChar[$i];
                    $str .= $this->arrayConvert[(int)$tempNum].$this->arrayEstensioni[$length];

                    $partialstr = $tempNum;
                    for($i=0; $i < $length - strlen($tempNum); $i++)
                        $partialstr .= "0";

                    if($number - (int)$partialstr == 0) {
                        $this->str = $str;
                        return true;
                    }

                    $this->convert($number - (int)$partialstr, $str);
                }
                else{
                    if($length > 3)

                        if($arrChar[1].$arrChar[2] == "00") $this->arrayConvert[$arrChar[0]."00"].$this->arrayEstensioni[$length];
                        else $str .= $this->arrayConvert[$arrChar[0]."00"].$this->arrayConvert[(int)($arrChar[1].$arrChar[2])].$this->arrayEstensioni[$length];
                    else {
                        if($arrChar[1] . $arrChar[2] == "00") $str .= $this->arrayConvert[$arrChar[0] . "00"];
                        else $str .= $this->arrayConvert[$arrChar[0] . "00"]. $this->arrayConvert[(int)($arrChar[1] . $arrChar[2])];
                    }


                    $partialstr = $arrChar[0].$arrChar[1].$arrChar[2];
                    for($i=0; $i < $length - 3; $i++)
                        $partialstr .= "0";


                    if((int)$number - (int)$partialstr == 0){
                        $this->str = $str;
                        return true;
                    }

                    $this->convert((int)$number - (int)$partialstr, $str);
                }
            }
        }
    }
}
