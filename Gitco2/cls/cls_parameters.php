<?php

include_once CLS . "/cls_file.php";
class cls_parameters
{
    public $a_responsabili = null;
    public $a_generali = null;
    public $a_signature = array("funzionario"=>null,"procedimento"=>null,"richieste"=>null,"ufficiale"=>null,"funz_riscossione"=>null);

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
        if($this->a_responsabili['Funzionario_Responsabile']!="" && ($this->a_responsabili['Funzionario_Firma']!="" || $this->a_responsabili['Funzionario_Testo']!="")){
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

        if($this->a_responsabili['Responsabile_Procedimento']!="" && ($this->a_responsabili['Responsabile_Firma']!="" || $this->a_responsabili['Responsabile_Testo']!="")){
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

        if($this->a_responsabili['Responsabile_Richieste']!="" && ($this->a_responsabili['Responsabile_Richieste_Firma']!="" || $this->a_responsabili['Responsabile_Richieste_Testo']!="")){
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

        if($this->a_responsabili['Ufficiale_Riscossione']!="" && ($this->a_responsabili['Ufficiale_Firma']!="" || $this->a_responsabili['Ufficiale_Testo']!="")){
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


        if($this->a_responsabili['Funzionario_Riscossione']!="" && ($this->a_responsabili['Funzionario_Riscossione_Firma']!="" || $this->a_responsabili['Funzionario_Riscossione_Testo']!="")){
            $this->a_signature['funz_riscossione']['replacementText'] = $this->a_responsabili['Testo_Sostitutivo'];
            if($this->a_responsabili['Funzionario_Riscossione_Testo']=="si")
                $this->a_signature['funz_riscossione']['type'] = "text";
            else
                $this->a_signature['funz_riscossione']['type'] = "file";

            $this->a_signature['funz_riscossione']['header'] = "Funzionario della riscossione";

            $this->a_signature['funz_riscossione']['name'] = $this->a_responsabili['Funzionario_Riscossione'];
            $this->a_signature['funz_riscossione']['file'] = $this->a_responsabili['Funzionario_Riscossione_Firma'];
            $this->a_signature['funz_riscossione']['fileWebPath'] = FIRMEWEB."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Funzionario_Riscossione_Firma'];
            $this->a_signature['funz_riscossione']['filePath'] = FIRME."/".$this->a_responsabili['CC']."/".$this->a_responsabili['Funzionario_Riscossione_Firma'];
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
            case "{FUNZIONARIORISCOSSIONE}":
                $signature = $a_signature['funz_riscossione'];
                break;
        }

        return $signature;
    }

    public function getHtmlSignature($variable){
        //var_dump($this->a_signature);
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
            case "{SignFunzRiscossione}":
                $signature = $this->a_signature['funz_riscossione'];
                break;
        }

        return $this->setHtmlSignature($signature);
    }

    private function setHtmlSignature($signature){
        if(isset($signature['type'])){
            if($signature['type']=="file"){
                $cls_file = new cls_file();
                $imgDim = $cls_file->imageSize($signature['filePath'],140,45);

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

    public function GetGenericSignature($signature)
    {
        return $this->setHtmlSignature($signature);
    }
    public function old_getHtmlMultiSignature($var_responsabile, $a_tributi, $a_params,$managerType){

        $cont = 0;
        $htmlHeader = "";
        $htmlFirma = "";
        $html = '<table align="center" border="0" cellpadding="0" cellspacing="0" style="width:100%;"><tbody>';
        foreach($a_tributi as $tributo){
            
            if($cont%3==0){
                $td = "";
                for($i=0; $i<count($a_tributi); $i++)
                    $td .= "<td></td>";

                $html.= "<tr>".$td."</tr>";
                $html.= "<tr>".$td."</tr>";
                $htmlHeader = "";
                $htmlFirma = "";
            }
            
            $this->setArray('responsabili',$a_params[$tributo]);
           
            $this->getSignatures($managerType);
           
           
            switch($var_responsabile){
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
                case "{SignFunzRiscossione}":
                    $signature = $this->a_signature['funz_riscossione'];
                    break;
            }

            $htmlHeader.='<td style="width: 33%; text-align: center;">
                        <div><span>'.$signature['header'].'</span><br><span>Gestione: '.$tributo.'</span></div>
                    </td>';

            $htmlFirma.='<td style="width: 33%; text-align: center;">
                        <div>'.$this->setHtmlSignature($signature).'</div>
                    </td>';
           
            $cont++;
            
        }
        if($cont%10!=0){
            $html.= "<tr>".$htmlHeader."</tr>";
            $html.= "<tr>".$htmlFirma."</tr>";
        }
        
        $html.= "</tbody></table>";
               
        return $html;
    }

    public function getHtmlMultiSignature($var_responsabile, $a_tributi, $a_params,$managerType){

        $cont = 1;
        $htmlHeader = "";
        $htmlFirma = "";
        $html = '<table align="center" border="0" cellpadding="0" cellspacing="0" style="width:100%;"><tbody>';
        foreach($a_tributi as $tributo){
            
            
            
            $this->setArray('responsabili',$a_params[$tributo]);
           
            $this->getSignatures($managerType);
           
           
            switch($var_responsabile){
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
                case "{SignFunzRiscossione}":
                    $signature = $this->a_signature['funz_riscossione'];
                    break;
            }

            $htmlHeader.='<td style="width: 33%; text-align: center;">
                        <div><span>'.$signature['header'].'</span><br><span>Gestione: '.$tributo.'</span></div>
                    </td>';

            $htmlFirma.='<td style="width: 33%; text-align: center;">
                        <div>'.$this->setHtmlSignature($signature).'</div>
                    </td>';
           
            if($cont%3==0){
                $html.= "<tr>".$htmlHeader."</tr>";
                $html.= "<tr>".$htmlFirma."</tr>";
                $htmlHeader="";
                $htmlFirma="";
            } 

            $cont++;
            
        }
       
        if( $htmlHeader!="")
        {
            $html.= "<tr>".$htmlHeader."</tr>";
            $html.= "<tr>".$htmlFirma."</tr>";
        }
        
        $html.= "</tbody></table>";
               
        return $html;
    }
}

?>