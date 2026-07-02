<?php

include_once CLS . "/cls_file.php";
class cls_parameters
{
    public $a_responsabili = null;
    public $a_generali = null;
    public $a_signature = array("funzionario"=>null,"procedimento"=>null,"richieste"=>null,"ufficiale"=>null);

    public function getRecordsQuery ($paramsType, $cc, $type=null){
        $query = "SELECT * FROM parametri_".$paramsType." WHERE CC = '" . $cc . "' ";
        if($type!=null)
            $query.= "AND Tipo_Riscossione = '".$type."' ";
        if($paramsType=="annuali")
            $query.= "AND Anno=".date("Y")." ";
        return $query;
    }

    public function getAllYearsParamsQuery($cc){
        $query = "SELECT * FROM parametri_annuali WHERE CC = '" . $cc . "' ORDER BY Anno ASC";
        return $query;
    }

    public function setArray($paramsType, array $a_params){
        $arrayType = "a_".$paramsType;
        $this->$arrayType = $a_params;
    }

    public function getSignatures($managerType){
        if($this->a_responsabili['Funzionario_Responsabile']!=""){
            $this->a_signature['funzionario']['replacementText'] = $this->a_responsabili['Testo_Sostitutivo'];
            if($this->a_responsabili['Funzionario_Testo']=="si")
                $this->a_signature['funzionario']['type'] = "text";
            else
                $this->a_signature['funzionario']['type'] = "file";
            if($managerType!="Gestore")
                $this->a_signature['funzionario']['header'] = "Il Funzionario responsabile";
            else
                $this->a_signature['funzionario']['header'] = "Il Legale rappresentante";
            $this->a_signature['funzionario']['name'] = $this->a_responsabili['Funzionario_Responsabile'];
            $this->a_signature['funzionario']['file'] = $this->a_responsabili['Funzionario_Firma'];
            $this->a_signature['funzionario']['fileWebPath'] = FIRMEWEB."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Funzionario_Firma'];
            $this->a_signature['funzionario']['filePath'] = FIRME."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Funzionario_Firma'];
        }

        if($this->a_responsabili['Responsabile_Procedimento']!=""){
            $this->a_signature['procedimento']['replacementText'] = $this->a_responsabili['Testo_Sostitutivo'];
            if($this->a_responsabili['Responsabile_Testo']=="si")
                $this->a_signature['procedimento']['type'] = "text";
            else
                $this->a_signature['procedimento']['type'] = "file";
            $this->a_signature['procedimento']['header'] = "Il Responsabile del procedimento";
            $this->a_signature['procedimento']['name'] = $this->a_responsabili['Responsabile_Procedimento'];
            $this->a_signature['procedimento']['file'] = $this->a_responsabili['Responsabile_Firma'];
            $this->a_signature['procedimento']['fileWebPath'] = FIRMEWEB."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Responsabile_Firma'];
            $this->a_signature['procedimento']['filePath'] = FIRME."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Responsabile_Firma'];
        }

        if($this->a_responsabili['Responsabile_Richieste']!=""){
            $this->a_signature['richieste']['replacementText'] = $this->a_responsabili['Testo_Sostitutivo'];
            if($this->a_responsabili['Responsabile_Richieste_Testo']=="si")
                $this->a_signature['richieste']['type'] = "text";
            else
                $this->a_signature['richieste']['type'] = "file";
            $this->a_signature['richieste']['header'] = "Il Responsabile delle richieste";
            $this->a_signature['richieste']['name'] = $this->a_responsabili['Responsabile_Richieste'];
            $this->a_signature['richieste']['file'] = $this->a_responsabili['Responsabile_Richieste_Firma'];
            $this->a_signature['richieste']['fileWebPath'] = FIRMEWEB."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Responsabile_Richieste_Firma'];
            $this->a_signature['richieste']['filePath'] = FIRME."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Responsabile_Richieste_Firma'];
        }

        if($this->a_responsabili['Ufficiale_Riscossione']!=""){
            $this->a_signature['ufficiale']['replacementText'] = $this->a_responsabili['Testo_Sostitutivo'];
            if($this->a_responsabili['Ufficiale_Testo']=="si")
                $this->a_signature['ufficiale']['type'] = "text";
            else
                $this->a_signature['ufficiale']['type'] = "file";

            $this->a_signature['ufficiale']['header'] = "L'Ufficiale della riscossione";

            $this->a_signature['ufficiale']['name'] = $this->a_responsabili['Ufficiale_Riscossione'];
            $this->a_signature['ufficiale']['file'] = $this->a_responsabili['Ufficiale_Firma'];
            $this->a_signature['ufficiale']['fileWebPath'] = FIRMEWEB."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Ufficiale_Firma'];
            $this->a_signature['ufficiale']['filePath'] = FIRME."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Ufficiale_Firma'];
        }
    }

    public function getSignatureByOfficial($officialType, $a_signature){
        if($officialType=="riscossione")
            $signature = $a_signature['ufficiale'];
        else if($officialType=="giudiziario"){
            $signature['header'] = "L'Ufficiale giudiziario";
            $signature['type'] = "text";
            $signature['name'] = "";
            $signature['file'] = "";
            $signature['replacementText'] = "";
            $signature['fileWebPath'] = "";
            $signature['filePath'] = "";
        }
        else if($officialType=="diretta"){
            $signature = $this->a_signature['richieste'];
        }

        return $signature;
    }

    public function getSelectedSignature($variable, $a_signature){
        switch($variable){
            case "{FUNZIONARIORESPONSABILE}":
                $signature = $a_signature['funzionario'];
                break;
            case "{RESPONSABILEPROCEDIMENTO}":
                $signature = $a_signature['procedimento'];
                break;
            case "{RESPONSABILERICHIESTE}":
                $signature = $a_signature['richieste'];
                break;
            case "{UFFICIALERISCOSSIONE}":
                $signature = $a_signature['ufficiale'];
                break;
        }

        return $signature;
    }

    public function getHtmlSignature($variable){
        switch($variable){
            case "{SignLegale}":
                $signature = $this->a_signature['funzionario'];
                break;
            case "{SignRespProcedimento}":
                $signature = $this->a_signature['procedimento'];
                break;
            case "{SignRespRichieste}":
                $signature = $this->a_signature['richieste'];
                break;
            case "{SignUfficiale}":
                $signature = $this->a_signature['ufficiale'];
                break;
        }

        if(isset($signature['type'])){
            if($signature['type']=="file"){
                $cls_file = new cls_file();
                $imgDim = $cls_file->imageSize($signature['filePath'],140,45);

                $htmlSign = "<img src=\"".$signature['fileWebPath']."\" style=\"width: ".$imgDim[0]."px; height: ".$imgDim[1]."px;\" /><br>";
                $htmlSign.= "<span>".strtoupper($signature['name'])."</span>";

            }
            else if($signature['type']=="text"){
                $htmlSign = "<span>".$signature['replacementText']."</span><br>";
                $htmlSign.= "<span>".strtoupper($signature['name'])."</span>";
            }
        }
        else{
            $htmlSign = "<span>!!!FIRMA ASSENTE!!!</span><br>";
            $htmlSign.= "<span>".$variable."</span>";
        }


        return $htmlSign;
    }

}

?>