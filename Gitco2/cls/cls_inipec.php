<?php
include_once(CLS."/cls_wsdl.php");
require_once CONFIG_ROOT."/_inipecServer.php";
class cls_inipec extends cls_wsdl{

    public $idRichiesta;
    public $username;
    public $a_check;
    public function __construct($username, $password)
    {
        $this->username = $username;
        $options = array(
            "trace"     => 1, 
            'login'     => $username,
            'password'  => $password,
        );
        return parent::__construct(INIPEC_URL, $options);
    }

    function richiesta($filePath){
        $this->requestType = "upload";
        $parameter = array(
            'elencoCf' => array(
                'nomeDocumento'                 => basename($filePath, ".zip"),
                'tipoDocumento'                 => 'zip',
                'documento'                     => file_get_contents($filePath)
            )
        );

        $this->response = $this->richiestaFornituraPec($parameter);
        if(!empty($this->response->identificativiFornitura->tokenRichiestaInfocamere->idRichiesta))
            $this->idRichiesta = $this->response->identificativiFornitura->tokenRichiestaInfocamere->idRichiesta;

        $this->checkResponse();
    }

    function scarico($destinationPath, $idRichiesta = null){
        if(!empty($idRichiesta))
            $this->idRichiesta = $idRichiesta;
        $this->requestType = "download";
        $this->destinationPath = $destinationPath;
        $parameter = array(
            'tokenRichiestaInfocamere' => array(
                'tipoRichiesta'               => 'FORNITURA_FPEC',
                'idRichiesta'                 => (string)$this->idRichiesta,
            )
        );

        $this->response = $this->scaricoFornituraPec($parameter);
        $this->checkResponse();

    }

    function getPecList($zipPath){
        if($this->a_check['esito']===false){
            var_dump($this->a_check);
            return array();
        }

        $a_csv=array();
        $zip = new ZipArchive();
        if($zip->open($zipPath."/".$this->a_check['scarico']->nomeDocumento, ZipArchive::CREATE)){
            $fileName = $zip->getNameIndex(0);
            $zip->extractTo($zipPath);
            $zip->close();
            if (($data = fopen($zipPath."/".$fileName, "r")) !== false) {
                
                $numRow = 0;
                while (($row = fgetcsv($data, 0, "~")) !== false) {
                    if($numRow>0)
                        $a_csv[] = $row;
                    $numRow++;
                }
                fclose($data);
                unlink($zipPath."/".$fileName);
            }
            unlink($zipPath."/".$this->a_check['scarico']->nomeDocumento);
        }

        $a_pec = array();
        foreach($a_csv as $key=>$a_cf){
            if(empty($a_cf[0]))
                continue;
            $a_temp = array(
                "CF_PI" => $a_cf[0],
                "PEC" => null,
                "IMPRESA" => array(
                    "PEC" => $a_cf[7],
                    "ESITO" => $a_cf[8],
                    "DESCRIZIONE_ESITO" => $a_cf[9],
                ),
                "PROFESSIONISTA" => array(
                    "PEC" => $a_cf[17],
                    "ESITO" => $a_cf[18],
                    "DESCRIZIONE_ESITO" => $a_cf[19],
                )
            );

            if($a_temp["IMPRESA"]["ESITO"]=="OK")
                $a_temp['PEC'] = $a_temp["IMPRESA"]["PEC"];
            else if($a_temp["PROFESSIONISTA"]["ESITO"]=="OK")
                $a_temp['PEC'] = $a_temp["IMPRESA"]["PEC"];
            
            $a_pec[] = $a_temp;
        }

        return $a_pec;
        
    }
    

    function checkResponse(){
        $a_check = array();
        if(!isset($this->response->esito->esito)){
            $a_check['esito'] = false;
            $a_check['msg'] = "Response non trovato!";
            $a_check['code'] = false;
            return $a_check;
        }
        
        $a_check['esito'] = $this->response->esito->esito;
        switch($a_check['esito']){
            case true:
                $a_check['msg'] = "Richiesta avvenuta con successo";
                $a_check['code'] = $this->response->esito->codiceErrore;
                break;
            case false:
                $a_check['msg'] = $this->response->esito->descrizioneErrore;
                $a_check['code'] = $this->response->esito->codiceErrore;
                break;
        }

        if(isset($this->response->identificativiFornitura->tokenRichiestaInfocamere))
            $a_check['richiesta'] = $this->response->identificativiFornitura->tokenRichiestaInfocamere;
        else if(isset($this->response->fornitura))
            $a_check['scarico'] = $this->response->fornitura;

        $this->a_check = $a_check;
    }

}


?>