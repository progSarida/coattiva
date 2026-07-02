<?php

class cls_wsdl extends soapClient{

    public $wsdl;
    public $client;
    public $requestType;
    public $destinationPath;
    public $a_files;
    public $response;

    public function __construct($wsdl, $options=null, $requestType = null, $destinationPath=null)
    {
        $this->wsdl = $wsdl;
        $this->destinationPath = $destinationPath;
        $this->requestType = $requestType;
        $this->client = parent::__construct($this->wsdl,$options);

    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);

        switch($this->requestType){
            case "download":
                return $this->downloadProcess($response);
                break;
            case "upload":
                $headers = $this->__getLastResponseHeaders();

                // Do we have a multipart request?
                if (preg_match('#^Content-Type:.*multipart\/.*#mi', $headers) !== 0) {
                    // Make all line breaks even.
                    $response = str_replace("\r\n", "\n", $response);
        
                    // Split between headers and content.
                    list(, $content) = preg_split("#\n\n#", $response);
                    // Split again for multipart boundary.
                    list($response, ) = preg_split("#\n--#", $content);
                }
                break;
        }

        return $response;
    }


    protected function downloadProcess(?string $response): ?string
    {
        if (!$response) {
            return null;
        }

        // Catch XML response
        $xml_response = null;
        preg_match('/<soap[\s\S]*nvelope>/i', $response, $xml_response);

        if (!is_array($xml_response) || !count($xml_response)) {
            throw new Exception('XML non trovato!');
        }

        $xml_response = reset($xml_response);

        try {
            $dom = new DOMDocument();
            $dom->loadXML($xml_response);

            $xop_elements = $dom->getElementsByTagNameNS('http://www.w3.org/2004/08/xop/include', 'Include');
            $counts = $xop_elements->count() - 1;

            // You can modify, and even delete, nodes from a DOMNodeList if you iterate backwards
            // https://www.php.net/manual/en/class.domnodelist.php#83390
            for ($i = $counts; $i >= 0; $i -= 1) {
                $xop_element = $xop_elements->item($i);


                $cid = $xop_element->getAttribute('href');
                $cid = str_replace('cid:', '', $cid);

                // Find binary
                $content_id_tag = 'Content-ID: <'.$cid.'>';
                $start = strpos($response, $content_id_tag) + strlen($content_id_tag);
                $end = strpos($response, '--uuid:', $start);
                $binary = substr($response, $start, $end - $start);
                $binaryRows = explode("\r\n",$binary);
                $binaryRows = array_values(array_filter($binaryRows,function($v){return (!empty($v));}));
                $a_temp = array();
                foreach($binaryRows as $row){
                    if(strpos($row,"name")!==false){
                        $a_rowElements = explode(";",$row);
                        foreach($a_rowElements as $element){
                            if(strpos($element,"name")!==false)
                                $a_temp['fileName'] = str_replace('"','',explode("name=",$element)[1]);
                        }
                    } 
                    else if(strpos($row,"Content")===false)
                        $a_temp['fileString'] = $row;
                }

                if(!empty($a_temp['fileName']) && !empty($a_temp['fileString'])){
                    file_put_contents($this->destinationPath."/".$a_temp['fileName'], $a_temp['fileString']);

                    $xop_element->parentNode->nodeValue = Base64_encode($a_temp['fileString']);
                    $this->a_files[] = $a_temp;
                }
            }

            // Save modified XML string
            $xml_response = $dom->saveXML();
        } catch (Exception $exception) {
            throw new Exception(sprintf(
                'An error occurred while processing the XML response: %s.',
                $exception->getMessage()
            ));
        }

        return $xml_response;
    }


}


?>