<?php

include_once CLS."/cls_LOG.php";

class SoapCMN
{
    private $parameter;
    private $options;
    private $client;

    private $log;

    public function __construct($username = "passano.gest", $password = "Sarida2021")
    {
        try {

            $this->log = new LOG();

            ini_set('soap.wsdl_cache_enabled',0);
            ini_set('soap.wsdl_cache_ttl',0);
            ini_set('display_errors','1');
            error_reporting(E_ALL);

            $this->parameter = array(
                "username" => $username,
                "password" => $password,
                //"clientId" => $clientId,
                "FormatoRisposta" => "XML",
                "DataRichiesta" => date("Y-m-d"),
                //"DataNascita" => "1989-01-16",
                //"Pagina" => "1",
                //"RowIdDa" => "1",
                //"RowIdA" => "1"
            );

            $this->options = array(
                //'classmap' => array('Veicolo' => "veicolo"),
                "trace" => 1,
                //"soap_version" => SOAP_1_2,
                'debug' => TRUE,
                'wsdl' => TRUE,
                'cache_wsdl' => WSDL_CACHE_BOTH
            );

            $this->SetClient();
        }
        catch(SoapFault $exception){
            //LOG
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    private function SetClient()
    {
        //$wsdlFilePath = ROOT.'/anagrafe/wdslFile/visure_nominative.wsdl';
        $wsdlFilePath = "http://esepra2.servizi.aci/VisureNominative/services/visurenominative.wsdl";
        //$wsdlFilePath = "http://ESEPRA2.servizi.aci/VisureVeicolo/services/visureveicolo.wsdl";
        $this->client = new SoapClient($wsdlFilePath,$this->options);
        $this->client->__setLocation('http://esepra2.servizi.aci/VisureNominative/services/visure_nominative.xsd');

    }

    public function getAllFunction()
    {
        try {
            return $this->client->__getFunctions();
        }
        catch(SoapFault $ex)
        {
            //LOG
            $this->log->error($ex->getMessage());
            return false;
        }
    }

    public function getAllTypes()
    {
        try {
            return $this->client->__getTypes();
        }
        catch(SoapFault $ex)
        {
            //LOG
            $this->log->error($ex->getMessage());
            return false;
        }
    }

    public function SearchForCF($CodiceFiscale)
    {
        try {
            $a_add = array(
                "CodiceFiscale" => $CodiceFiscale,
                "TipoRichiesta" => "CodiceFiscale"
            );

            $temp = array_merge($this->parameter, $a_add);

            //return $this->client->__soapCall("RicercaPerCodiceFiscale",$temp);
            return $this->client->RicercaPerCodiceFiscale($temp);
        }
        catch(SoapFault $ex)
        {
            //LOG
            $this->log->error($ex->getMessage());
            if($ex->getCode() == 0) return 0;
            return false;
        }

    }

    public function SearchForPI($PartitaIVA)
    {
        try {
            $temp = array(
                "PartitaIva" => $PartitaIVA,
                "TipoRichiesta" => "PartitaIva"
            );

            $temp = array_merge($this->parameter, $temp);
            //var_dump($temp);
            //var_dump($this->getAllFunction());
            //return $this->client->__soapCall("RicercaPerPartitaIva", $temp);
            return $this->client->RicercaPerPartitaIva($temp);
        }
        catch(SoapFault $ex)
        {
            //LOG
            $this->log->error($ex->getMessage());
            return false;
        }

    }

    public function SearchForNominative($TipoPersona,$Cognome,$Nome = "")
    {
        try {
            if($TipoPersona == "Fisica"){
                $temp = array(
                    "TipoPersona" => $TipoPersona,
                    "Cognome" => $Cognome,
                    "Nome" => $Nome,
                    "TipoRichiesta" => "Nominativo"
                );
            }
            else{
                $temp = array(
                    "TipoPersona" => $TipoPersona,
                    "Cognome" => $Cognome,
                    "TipoRichiesta" => "Nominativo"
                );

            }

            $temp = array_merge($this->parameter, $temp);

            return $this->client->RicercaPerNominativo($temp);
            //$response = $this->client->RicercaPerCodiceFiscale($this->parameter);
        }
        catch(SoapFault $ex)
        {
            //LOG
            $this->log->error($ex->getMessage());
            return false;
        }

    }
}