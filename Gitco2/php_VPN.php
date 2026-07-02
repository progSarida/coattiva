<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
// include CLASSI . "/cls_ws_MCTC.php";

$client = new SoapClient("https://e-servizicoll.dtt.ilportaledellautomobilista.it/Info-ws-sh/services/dettaglioPatenteBase/dettaglioPatenteBase.wsdl");


die;

$result = $client->__soapCall('dettaglioPatente');


function Dom2Array($root) {
	$array = array();
	//list attributes
	if($root->hasAttributes()) {
		foreach($root->attributes as $attribute) {
			$array['_attributes'][$attribute->name] = $attribute->value;
		}
	}
	//handle classic node
	if($root->nodeType == XML_ELEMENT_NODE) {
		$array['_type'] = $root->nodeName;
		if($root->hasChildNodes()) {
			$children = $root->childNodes;
			for($i = 0; $i < $children->length; $i++) {
				$child = Dom2Array( $children->item($i) );
				//don't keep textnode with only spaces and newline
				if(!empty($child)) {
					$array['_children'][] = $child;
				}
			}
		}
		//handle text node
	} elseif($root->nodeType == XML_TEXT_NODE || $root->nodeType == XML_CDATA_SECTION_NODE) {
		$value = $root->nodeValue;
		if(!empty($value)) {
			$array['_type'] = '_text';
			$array['_content'] = $value;
		}
	}
	return $array;
}

$cls_ws = new cls_ws_MCTC(); 
$cls_ws->dettaglioPatenteRequest();

$arrayDom = Dom2Array($cls_ws->xml);

print_r($arrayDom);
die;

$client = new SoapClient("https://e-servizicoll.dtt.ilportaledellautomobilista.it/Info-ws-sh/services/dettaglioPatenteBase/dettaglioPatenteBase.wsdl");
$result = $client->__soapCall('dettaglioPatente');
if($client->fault){
	echo $client->faultcode;
	echo $client->faultstring;
}
print_r($result);
die;



?>


<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:inf="http://www.dtt.it/xsd/INFOWS">
	
	<wsse:Security 	xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" 
					SOAP-ENV:mustUnderstand="1">
	<wsse:UsernameToken	xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"
						wsu:id="XWSSGID-1253605895203984534550">
	<wsse:Username>PRFR000134</wsse:Username>
	<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">8X1V6C4N</wsse:Password></wsse:UsernameToken></wsse:Security>
		
	<soapenv:Header/>
	
	<soapenv:Body>
		
		<inf:dettaglioPatenteRequest>
			<inf:login>
				<!--Optional:-->
				<inf:codicePin>?</inf:codicePin>
			</inf:login>
			
			<inf:ambitoPatenteBase>
				
				<!--You have a CHOICE of the next 4 items at this level-->
				<inf:patente>
					<inf:numeroPatente>U1L213095N</inf:numeroPatente>
				</inf:patente>
				
				<inf:anagraficaRicerca>
					<inf:cognome>?</inf:cognome>
					<!--Optional:-->
					<inf:nome>?</inf:nome>
					<!--Optional:-->
					<inf:anagraficaSpeciale>
						<inf:tipoAnagraficaSpeciale>?</inf:tipoAnagraficaSpeciale>
					</inf:anagraficaSpeciale>
					<!--You have a CHOICE of the next 2 items at this level-->
					<inf:dataNascita>?</inf:dataNascita>
					<inf:annoNascita>?</inf:annoNascita>
					<inf:origineNascita>?</inf:origineNascita>
					<!--You have a CHOICE of the next 2 items at this level-->
					<inf:luogoNascitaItaliano>
						<inf:siglaProvincia>?</inf:siglaProvincia>
						<inf:descrizioneComune>?</inf:descrizioneComune>
					</inf:luogoNascitaItaliano>
					<!--Optional:-->
					<inf:siglaStatoEsteroNascita>?</inf:siglaStatoEsteroNascita>
				</inf:anagraficaRicerca>
				
				<inf:codiceFiscale>?</inf:codiceFiscale>
				
				<inf:anagraficaEstesa>
					<inf:cognome>?</inf:cognome>
					<!--Optional:-->
					<inf:nome>?</inf:nome>
					<!--Optional:-->
					<inf:anagraficaSpeciale>
						<inf:tipoAnagraficaSpeciale>?</inf:tipoAnagraficaSpeciale>
					</inf:anagraficaSpeciale>
					<!--You have a CHOICE of the next 2 items at this level-->
					<inf:dataNascita>?</inf:dataNascita>
					<inf:annoNascita>?</inf:annoNascita>
					<inf:origineNascita>?</inf:origineNascita>
					<!--You have a CHOICE of the next 2 items at this level-->
					<inf:luogoNascitaItaliano>
						<inf:siglaProvincia>?</inf:siglaProvincia>
						<inf:descrizioneComune>?</inf:descrizioneComune>
					</inf:luogoNascitaItaliano>
					<!--Optional:-->
					<inf:siglaStatoEsteroNascita>?</inf:siglaStatoEsteroNascita>
					<inf:codiceSinonimia>?</inf:codiceSinonimia>
					<inf:progressivoSinonimia>?</inf:progressivoSinonimia>
				</inf:anagraficaEstesa>
				
			</inf:ambitoPatenteBase>
			
			<inf:pdf>true</inf:pdf>
			<inf:pdfAnteprimaPatente>?</inf:pdfAnteprimaPatente>
		</inf:dettaglioPatenteRequest>
		
	</soapenv:Body>

</soapenv:Envelope>


				

<xml version="1.0" encoding="UTF-8">

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
	
	<SOAP-ENV:Header>
		<wsse:Security  xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
						SOAP-ENV:mustUnderstand="1">
			<wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"
								wsu:Id="XWSSGID-1253605895203984534550">
				<wsse:Username>PRFR000134</wsse:Username>
				<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">
					8X1V6C4N
				</wsse:Password>
			</wsse:UsernameToken>
		</wsse:Security>
	</SOAP-ENV:Header>
	
	<SOAP-ENV:Body>
		<inf:dettaglioPatenteRequest>
	    	<inf:login>
	        	<!--Optional:-->
	            <inf:codicePin>1234</inf:codicePin>
	        </inf:login>
	        <inf:ambitoPatenteBase>
	        	<!--You have a CHOICE of the next 4 items at this level-->
	            <inf:patente>
	            	<inf:numeroPatente>CR5015675N</inf:numeroPatente>
	            </inf:patente>
	        </inf:ambitoPatenteBase>
        	<inf:pdf>true</inf:pdf>
		</inf:dettaglioPatenteRequest>
	</SOAP-ENV:Body>

</SOAP-ENV:Envelope>
<?php 

// $pathVPN = "c:/progra~2/cisco systems/vpn client/";
// $command = $pathVPN." vpnclient connect Moto user PRI.185450860 pwd Sarida12 cliauth";

// $pathZOC = "C:/progra~2/ZOC5/";
// $pathfileZOC = "C:/Users/Mirko/Documents/";

// $CF = "PSSGNN46M26Z605T";

// $cmdZOC = "start ".$pathZOC."zoc /RUN:".$pathfileZOC."motor.zrx /RUNARG:".$CF;
// // alert($cmdZOC);



// exec($cmdZOC);


?>

