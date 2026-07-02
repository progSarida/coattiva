<?php

class cls_xml
{
    public $xml;
    /**
     * @param $version
     * @param $encoding
     * @param bool $format
     * @return DOMDocument
     */
    public function createXml($version, $encoding, $format=true){

        $this->xml = new DOMDocument($version, $encoding);
        $this->xml->formatOutput = $format;
    }

    /**
     * @param $tag
     * @param $parentElement
     * @param null $url
     * @return DOMElement|DOMNode
     */
    public function createElement($tag, $parentElement = null, array $a_attribute = array(), $url = null){
        if($url==null)
            $element = $this->xml->createElement($tag);
        else
            $element = $this->xml->createElementNS( $url , $tag );

        if($parentElement==null)
            $element = $this->xml->appendChild($element);
        else
            $element = $parentElement->appendChild($element);

        if(count($a_attribute)>0)
            $this->setDomAttribute($element, $a_attribute);

        return $element;
    }

    /**
     * @param DOMElement $element
     * @param $text
     */
    public function setValue(DOMElement $element, $text){

        $value = $this->xml->createTextNode($text);
        $element->appendChild($value);
    }

    /**
     * @param DOMElement $element
     * @param array $a_attribute
     */
    public function setDomAttribute(DOMElement $element, Array $a_attribute){

        foreach ($a_attribute as $array){

            if(isset($array['url']))
                $element->setAttributeNS($array['url'], $array['field'], $array['value']);
            else
                $element->setAttribute($array['field'], $array['value']);
        }

    }
}

?>