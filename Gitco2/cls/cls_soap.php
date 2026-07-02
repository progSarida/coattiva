<?php

class cls_soap{

    public $wsdl;
    public $soapClient;
    public $namespace = "http://ws.fpec.gemo.infocamere.it";
    public function __construct($wsdl, $options=null)
    {
        $this->wsdl = $wsdl;
        if($options!=null)
            $this->soapClient = new SoapClient($this->wsdl,$options);
        else
            $this->soapClient = new SoapClient($this->wsdl);
    }

    public function soapCallManuale($wsdl, $location, $service, $request){
        $client = new SoapClient($wsdl, $this->wsdlOptions);
        $client->__setLocation($location);

        $headerSoap = new SoapHeader($this->wsseUrl,'Security', new SoapVar($this->setHeaderXml(), XSD_ANYXML));
        $client->__setSoapHeaders($headerSoap);

        $client->{$service}(new SoapVar( $request, XSD_ANYXML));

        //echo "<br><br>REQUEST:<br>".htmlspecialchars($client->__getLastRequest())."<br><br>RESPONSE:<br>".htmlspecialchars($client->__getLastResponse());

    }

    public function setHeaderXml(){

        $xml = $this->createXml("1.0", "UTF-8");

        $this->xmlUsername = $this->createElement($xml,"wsdl:Username",null);
        $this->setValue($xml,$this->xmlUsername, $this->username);

        $this->xmlPassword = $this->createElement($xml,"wsdl:Password",null);
        $this->setValue($xml,$this->xmlPassword, $this->password);

        return $xml->saveXML($this->security);
    }

    public function createElement(DOMDocument $xml, $tag, $parentElement, $url=null){
        if($url==null)
            $element = $xml->createElement($tag);
        else
            $element = $xml->createElementNS( $url , $tag );

        if($parentElement==null)
            $element = $xml->appendChild($element);
        else
            $element = $parentElement->appendChild($element);

        return $element;
    }

    /**
     * @param $version
     * @param $encoding
     * @param bool $format
     * @return DOMDocument
     */
    public function createXml($version, $encoding, $format=true){

        $xml = new DOMDocument($version, $encoding);
        $xml->formatOutput = $format;

        return $xml;

    }


}


?>