<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once(CLS."/cls_file.php");
//include_once(SUPER_CLS."/chilkat/chilkat_9_5_0.php");
include_once("c:/wamp64/bin/php/php7.4.26/ext/chilkat_9_5_0.php");




//$glob = new CkGlobal();
//$success = $glob->UnlockBundle('Anything for 30-day trial');
//if ($success != true) {
//    print $glob->lastErrorText() . "\n";
//    exit;
//}
//
//$status = $glob->get_UnlockStatus();
//if ($status == 2) {
//    print 'Unlocked using purchased unlock code.' . "\n";
//}
//else {
//    print 'Unlocked in trial mode.' . "\n";
//}
//
//// The LastErrorText can be examined in the success case to see if it was unlocked in
//// trial more, or with a purchased unlock code.
//print $glob->lastErrorText() . "\n";

$file = file_get_contents("prova.zip");


$length = strlen($file);
$result = '';

for ($i = 0; $i < $length; $i++) {
    $result .= str_pad(decbin(ord($file[$i])), 8, '0', STR_PAD_LEFT);
}

//echo $result."<br>";

function check_gsm($str)
{
    $arr = array(
        "0x00", "0x01", "0x02", "0x03", "0x04", "0x05","0x06","0x07","0x08","0x09",
        "0x0A","0x0B","0x0C","0x0D","0x0E","0x0F","0x10","0x11","0x12","0x13",
        "0x14","0x15","0x16","0x17","0x18","0x19","0x1A","0x1B","0x1B0A",
        "0x1B14","0x1B28","0x1B29","0x1B2F","0x1B3C","0x1B3D","0x1B3E",
        "0x1B40","0x1B65","0x1C","0x1D","0x1E","0x1F","0x20","0x21","0x22",
        "0x23","0x24","0x25","0x26","0x27","0x28","0x29","0x2A","0x2B","0x2C",
        "0x2D","0x2E","0x2F","0x30","0x31","0x32","0x33","0x34","0x35","0x36",
        "0x37","0x38","0x39","0x3A","0x3B","0x3C","0x3D","0x3E","0x3F","0x40",
        "0x41","0x42","0x43","0x44","0x45","0x46","0x47","0x48","0x49","0x4A",
        "0x4B","0x4C","0x4D","0x4E","0x4F","0x50","0x51","0x52","0x53","0x54",
        "0x55","0x56","0x57","0x58","0x59","0x5A","0x5B","0x5C","0x5D","0x5E",
        "0x5F","0x60","0x61","0x62","0x63","0x64","0x65","0x66","0x67","0x68",
        "0x69","0x6A","0x6B","0x6C","0x6D","0x6E","0x6F","0x70","0x71","0x72",
        "0x73","0x74","0x75","0x76","0x77","0x78","0x79","0x7A","0x7B","0x7C",
        "0x7D","0x7E","0x7F");

    $hexFile = "";
    $strl = strlen($str);
    $j=0;
    for ($i = 0;$i < $strl; $i++)
    {
        $char = '0x' . bin2hex(substr($str,$i,1));
        $hexFile.=$char;
        echo "[".$char."]";
        $pos = in_array($char,$arr);
        if ($pos == 1)
        {
            $j++;
        }
    }

    if ($j < $strl)
    {
        return false;
    }
    else
    {
        return $hexFile;
    }
}

//
$hexFile = check_gsm($file);


$http = new CkHttp();

$soapXml = new CkXml();

$soapXml->put_Tag('soap:Envelope');
$success = $soapXml->AddAttribute('xmlns:soap','http://schemas.xmlsoap.org/soap/envelope/');
$success = $soapXml->AddAttribute('xmlns:ws','http://ws.fpec.gemo.infocamere.it');

$soapXml->NewChild2('soap:Body','');
$success = $soapXml->GetChild2(0);

$soapXml->NewChild2('ws:richiestaRichiestaFornituraPec','');
$success = $soapXml->GetChild2(0);

$soapXml->NewChild2('elencoCf','');
$success = $soapXml->GetChild2(0);

$soapXml->NewChild2('nomeDocumento','prova');
$soapXml->NewChild2('tipoDocumento','zip');
$soapXml->NewChild2('documento','');
$success = $soapXml->GetChild2(0);

$soapXml->NewChild2('xop:Include','');
$success = $soapXml->GetChild2(0);
$success = $soapXml->AddAttribute('xmlns:xop','http://www.w3.org/2004/08/xop/include');
$success = $soapXml->AddAttribute('href','cid:prova.zip');

$soapXml->GetRoot2();
$soapXml->put_EmitXmlDecl(false);

$xmlBody = $soapXml->getXml();
print $xmlBody . "\n";

$wsdl = "https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl";
$req = new CkHttpRequest();
$req->put_HttpVerb('POST');
$req->put_Path("/fpec/ServizioFornituraPec");
$req->put_HttpVersion(1.1);

$req->put_ContentType('multipart/related; start="<rootpart@soapui.org>"; start-info="text/xml"; type="application/xop+xml"');
$req->AddHeader('SOAPAction','richiestaFornituraPec');
$req->AddHeader('Accept-Encoding','gzip,deflate');
$req->AddHeader('Mime-Version','1.0');
$req->AddHeader('Connection','Keep-Alive');
$req->AddHeader('User-Agent','Apache-HttpClient/4.1.1 (java 1.5)');
$req->AddHeader('Authorization','Basic S1NWMDAyOmdlbW9zYXZvbmExOA==');

$success = $req->AddStringForUpload2('','',$xmlBody,'utf-8','application/xop+xml; charset=UTF-8; type="text/xml"');
$success = $req->AddSubHeader(0,'Content-Transfer-Encoding','8bit');
$success = $req->AddSubHeader(0,'Content-ID','<rootpart@soapui.org>');
$success = $req->AddSubHeader(0,'Content-Disposition','');

$fileStringa = "PK[0x3][0x4]\n";
$fileStringa.= "[0x0][0x0][0x0][0x0][0x0]#[0x8a][0xda]N[0xff]<[0xd3][0x8d][0x18][0x0][0x0][0x0][0x18][0x0][0x0][0x0][0x9][0x0][0x1c][0x0]prova.csvUT[0x9][0x0][0x3]r[0x8c][0x13]][0x1][0x4]&]ux[0xb][0x0][0x1][0x4][0xe8][0x3][0x0][0x0][0x4][0xe8][0x3][0x0][0x0]01684390998\r\n";
$fileStringa.= "02059280236PK[0x1][0x2][0x1e][0x3]\n";
$fileStringa.= "[0x0][0x0][0x0][0x0][0x0]#[0x8a][0xda]N[0xff]<[0xd3][0x8d][0x18][0x0][0x0][0x0][0x18][0x0][0x0][0x0][0x9][0x0][0x18][0x0][0x0][0x0][0x0][0x0][0x1][0x0][0x0][0x0][0xb4][0x81][0x0][0x0][0x0][0x0]prova.csvUT[0x5][0x0][0x3]r[0x8c][0x13]]ux[0xb][0x0][0x1][0x4][0xe8][0x3][0x0][0x0][0x4][0xe8][0x3][0x0][0x0]PK[0x5][0x6][0x0][0x0][0x0][0x0][0x1][0x0][0x1][0x0]O[0x0][0x0][0x0][[0x0][0x0][0x0][0x0][0x0]";
//
//$fileStringa = "PK[0x3][0x4]\n";
//$fileStringa.= "[0x0][0x0][0x0][0x0][0x0]#[0x8a][0xda]N[0xff]<[0xd3][0x8d][0x18][0x0][0x0][0x0][0x18][0x0][0x0][0x0][0x9][0x0][0x1c][0x0]prova.csvUT[0x9][0x0][0x3]r[0x8c][0x13]][0x1][0x4]&]ux[0xb][0x0][0x1][0x4][0xe8][0x3][0x0][0x0][0x4][0xe8][0x3][0x0][0x0]01684390998\r\n";
//$fileStringa.= "02059280236PK[0x1][0x2][0x1e][0x3]\n";
//$fileStringa.= "[0x0][0x0][0x0][0x0][0x0]#[0x8a][0xda]N[0xff]<[0xd3][0x8d][0x18][0x0][0x0][0x0][0x18][0x0][0x0][0x0][0x9][0x0][0x18][0x0][0x0][0x0][0x0][0x0][0x1][0x0][0x0][0x0][0xb4][0x81][0x0][0x0][0x0][0x0]prova.csvUT[0x5][0x0][0x3]r[0x8c][0x13]]ux[0xb][0x0][0x1][0x4][0xe8][0x3][0x0][0x0][0x4][0xe8][0x3][0x0][0x0]PK[0x5][0x6][0x0][0x0][0x0][0x0][0x1][0x0][0x1][0x0]O[0x0][0x0][0x0][[0x0][0x0][0x0][0x0][0x0]";

function strigToBinary($string)
{
    $characters = str_split($string);

    $binary = [];
    foreach ($characters as $character) {
        $data = unpack('H*', $character);
        $binary[] = base_convert($data[1], 16, 2);
    }

    return implode(' ', $binary);
}

// The bytes will be sent as binary (not base64 encoded).
$success = $req->AddFileForUpload2('','prova.zip','application/octet-stream');

//$success = $req->AddStringForUpload2('','prova.zip',strigToBinary($file),'utf-8','application/zip"');
$success = $req->AddSubHeader(1,'Content-Transfer-Encoding','binary');
$success = $req->AddSubHeader(1,'Content-ID','<prova.zip>');
$success = $req->AddSubHeader(1,'Content-Disposition','attachment; name="prova.zip"');

$http->put_FollowRedirects(true);

// For debugging, set the SessionLogFilename property
// to see the exact HTTP request and response in a log file.
// (Given that the request contains binary data, you'll need an editor
// that can gracefully view text + binary data.  I use EmEditor for most simple editing tasks..)
$http->put_SessionLogFilename('mtom_sessionLog.txt');

$useTls = true;
// Note: Please don't run this example without changing the domain to your own domain...
// resp is a CkHttpResponse
$resp = $http->SynchronousRequest("fpecws.infocamere.it",443,$useTls,$req);
if ($http->get_LastMethodSuccess() != true) {
    print $http->lastErrorText() . "\n";
    exit;
}

$xmlResponse = new CkXml();
$success = $xmlResponse->LoadXml($resp->bodyStr());
print $xmlResponse->getXml() . "\n";

die;


$debugStr = <<<DEBUG
"POST /fpec/ServizioFornituraPec HTTP/1.1[\r][\n]"
"Accept-Encoding: gzip,deflate[\r][\n]"
"Content-Type: multipart/related; type="application/xop+xml"; start="<rootpart@soapui.org>"; start-info="text/xml"; boundary="----=_Part_0_7907988.1562828579816"[\r][\n]"
"SOAPAction: "richiestaFornituraPec"[\r][\n]"
"Authorization: Basic S1NWMDAyOmdlbW9zYXZvbmExOA==[\r][\n]"
"MIME-Version: 1.0[\r][\n]"
"Content-Length: 1137[\r][\n]"
"Host: fpecws.infocamere.it[\r][\n]"
"Connection: Keep-Alive[\r][\n]"
"User-Agent: Apache-HttpClient/4.1.1 (java 1.5)[\r][\n]"
"[\r][\n]"
"[\r][\n]"
"------=_Part_0_7907988.1562828579816"
"[\r][\n]"
"Content-Type: application/xop+xml; charset=UTF-8; type="text/xml""
"[\r][\n]"
"Content-Transfer-Encoding: 8bit"
"[\r][\n]"
"Content-ID: <rootpart@soapui.org>"
"[\r][\n]"
"[\r][\n]"
"<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.fpec.gemo.infocamere.it">[\n]"
"   <soapenv:Header/>[\n]"
"   <soapenv:Body>[\n]"
"      <ws:richiestaRichiestaFornituraPec>[\n]"
"         <elencoCf>[\n]"
"            <nomeDocumento>prova</nomeDocumento>[\n]"
"            <tipoDocumento>zip</tipoDocumento>[\n]"
"            <documento><inc:Include href="cid:prova.zip" xmlns:inc="http://www.w3.org/2004/08/xop/include"/></documento>[\n]"
"         </elencoCf>[\n]"
"      </ws:richiestaRichiestaFornituraPec>[\n]"
"   </soapenv:Body>[\n]"
"</soapenv:Envelope>"
"[\r][\n]"
"------=_Part_0_7907988.1562828579816"
"[\r][\n]"
"Content-Type: application/zip"
"[\r][\n]"
"Content-Transfer-Encoding: binary"
"[\r][\n]"
"Content-ID: <prova.zip>"
"[\r][\n]"
"Content-Disposition: attachment; name="prova.zip""
"[\r][\n]"
"[\r][\n]"
"PK[0x3][0x4][\n]"
"[0x0][0x0][0x0][0x0][0x0]#[0x8a][0xda]N[0xff]<[0xd3][0x8d][0x18][0x0][0x0][0x0][0x18][0x0][0x0][0x0][0x9][0x0][0x1c][0x0]prova.csvUT[0x9][0x0][0x3]r[0x8c][0x13]][0x1][0x4]&]ux[0xb][0x0][0x1][0x4][0xe8][0x3][0x0][0x0][0x4][0xe8][0x3][0x0][0x0]01684390998[\r][\n]"
"02059280236PK[0x1][0x2][0x1e][0x3][\n]"
"[0x0][0x0][0x0][0x0][0x0]#[0x8a][0xda]N[0xff]<[0xd3][0x8d][0x18][0x0][0x0][0x0][0x18][0x0][0x0][0x0][0x9][0x0][0x18][0x0][0x0][0x0][0x0][0x0][0x1][0x0][0x0][0x0][0xb4][0x81][0x0][0x0][0x0][0x0]prova.csvUT[0x5][0x0][0x3]r[0x8c][0x13]]ux[0xb][0x0][0x1][0x4][0xe8][0x3][0x0][0x0][0x4][0xe8][0x3][0x0][0x0]PK[0x5][0x6][0x0][0x0][0x0][0x0][0x1][0x0][0x1][0x0]O[0x0][0x0][0x0][[0x0][0x0][0x0][0x0][0x0]"
"[\r][\n]"

POST https://fpecws.infocamere.it/fpec/ServizioFornituraPec HTTP/1.1
Accept-Encoding: gzip,deflate
Content-Type: multipart/related; type="application/xop+xml"; start="<rootpart@soapui.org>"; start-info="text/xml"; boundary="----=_Part_2_6804433.1562828608577"
SOAPAction: "richiestaFornituraPec"
Authorization: Basic S1NWMDAyOmdlbW9zYXZvbmExOA==
MIME-Version: 1.0
Content-Length: 1137
Host: fpecws.infocamere.it
Connection: Keep-Alive
User-Agent: Apache-HttpClient/4.1.1 (java 1.5)

DEBUG;


$wsdl = "https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl";
$http_headers = array(
    'POST /fpec/ServizioFornituraPec HTTP/1.1\r\n',
    'Host: fpecws.infocamere.it\r\n',
    'Accept-Encoding: gzip,deflate\r\n',
    'Content-Type: multipart/related; type="application/xop+xml"; start-info="text/xml; start="<gitco@sarida.it>"\r\n',
    'SOAPAction: "richiestaFornituraPec"\r\n',
    'Authorization: Basic S1NWMDAyOmdlbW9zYXZvbmExOA==\r\n',
    'MIME-Version: 1.0\r\n',
    'Connection: Keep-Alive\r\n\r\n'
);

$file = file_get_contents("prova.zip");
$fp = fsockopen($wsdl,443);
if ($fp) {
    $out = "";
    for($i=0;$i<count($http_headers);$i++){
        $out.= $http_headers[$i];
    }

    fwrite($fp, $out);
//    fwrite($fp, $file);
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }
    fclose($fp);
    echo $response;
    $o = array();
//    if (preg_match_all('/\{"upload":\{"token":"(.+)"\}\}/ms', $response, $o)) {
//        return $o[1][0];
//    }
}


die;



$host = "fpecws.infocamere.it";
$wsdl = "https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl";
//$options = array( "login" => "KSV002", "password" => "gemosavona18", "trace"=>1, "exceptions"=>0, "uri"=>"", "location"=>"");
$options = array( "login" => "KSV002", "password" => "Sarida2023!", "trace"=>1, "exceptions"=>0, "uri"=>"", "location"=>"");
$fileMTOM = base64_encode(file_get_contents("prova.zip"));
$action = "richiestaFornituraPec";
$headers = array(
    'POST /fpec/ServizioFornituraPec HTTP/1.1',
    'Accept-Encoding: gzip,deflate',
    'Content-Type: multipart/related; type="application/xop+xml"; start-info="text/xml; start="<gitco@sarida.it>"',
    'SOAPAction: "richiestaFornituraPec"',
    'Authorization: Basic S1NWMDAyOmdlbW9zYXZvbmExOA==',
    'MIME-Version: 1.0',
    'Host: fpecws.infocamere.it',
    'Connection: Keep-Alive',
    'Expect:'
);

$fields = array("xmlRequest"=>
'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.fpec.gemo.infocamere.it">
            <soapenv:Header/>
            <soapenv:Body>
                <ws:richiestaRichiestaFornituraPec>
                    <elencoCf>
                        <nomeDocumento>prova</nomeDocumento>
                        <tipoDocumento>zip</tipoDocumento>
                        <documento><inc:Include href="cid:prova.zip" xmlns:inc="http://www.w3.org/2004/08/xop/include"/></documento>
                    </elencoCf>
                </ws:richiestaRichiestaFornituraPec>
            </soapenv:Body>
        </soapenv:Envelope>',
    "file"=>@$fileMTOM
    );

ob_start();
$out = fopen("file.txt","w");
$curl = curl_init();
curl_setopt($curl,CURLOPT_VERBOSE, true);
curl_setopt($curl,CURLOPT_STDERR, $out);

curl_setopt_array($curl, array(
    CURLOPT_URL => $wsdl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS => $fields,
    CURLOPT_HTTPHEADER => $headers,
));

$response = curl_exec($curl);
fclose($out);
$debug = fopen("file.txt","r");
echo "<br><br>".fread($debug,filesize("file.txt"))."<br><br>";

$err = curl_error($curl);

curl_close($curl);



if ($err) {
    echo "cURL Error #:" . $err;
} else {
    echo $response;
}




die;











include_once(CLS."/cls_soapClient.php");









$message = <<<MSG
Content-Type: multipart/related; boundary=MIME_boundary;
type="application/xop+xml"; start="<invioXml>";
start-info="text/xml; charset=utf-8"

--MIME_boundary
content-type: application/xop+xml; charset=utf-8; type="application/soap+xml;"
content-transfer-encoding: binary
content-id: <invioXml>

<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:Soap-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="$wsdl">
    <soap:Body>
        <ns1:richiestaRichiestaFornituraPec>
            <ns1:elencoCf>
                <ns1:nomeDocumento>prova</ns1:nomeDocumento>
                <ns1:tipoDocumento>zip</ns1:tipoDocumento>
                <ns1:documento>cid:prova.zip</ns1:documento>
            </ns1:elencoCf>
        </ns1:richiestaRichiestaFornituraPec>
    </soap:Body>
</soap:Envelope>

--MIME_boundary
Content-Type: application/octet-stream
Content-Transfer-Encoding: binary
Content-ID: <prova.zip>

$fileMTOM

--MIME_boundary    
MSG;

try{

    $cls_soap = new MySoapClient($wsdl, $options);
//    print_r($cls_soap->__getTypes());

    $a_params = array("elencoCf"=>array("nomeDocumento"=>"prova","tipoDocumento"=>"zip","documento"=>"cid:prova.zip"));
//    $cls_soap->richiestaFornituraPec($a_params);
    $cls_soap->__doRequest($message,$wsdl,$action,SOAP_1_1);
    $lastRequest = $cls_soap->__getLastRequest();
    echo $lastRequest;
}
catch(SoapFault $exception){
    echo $exception->getMessage();
}



$soapRequest = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:Soap-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="$wsdl">
<SOAP-ENV:Body>
<ns1:richiestaRichiestaFornituraPec>
<ns1:elencoCf>
<ns1:nomeDocumento>prova</ns1:nomeDocumento>
<ns1:tipoDocumento>zip</ns1:tipoDocumento>
<ns1:documento>cid:prova.zip</ns1:documento>
</ns1:elencoCf>
</ns1:richiestaRichiestaFornituraPec>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;


$requestFornitura = "<richiestaRichiestaFornituraPec><elencoCf><nomeDocumento>prova</nomeDocumento><tipoDocumento>zip</tipoDocumento>";
$requestFornitura.= "<documento>cid:prova.zip</documento></elencoCf></richiestaRichiestaFornituraPec>";

$downloadFornitura = "<richiestaScaricoFornitura>
<tokenRichiestaInfocamere>
<tipoRichiesta>FORNITURA_PEC</tipoRichiesta>
<idRichiesta>0000000001</idRichiesta>
</tokenRichiestaInfocamere>
</richiestaScaricoFornitura>";


$wsdl = "https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl";




//echo $fileMTOM;



//if (is_soap_fault($result)) {
//    trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
//}
//$client->__doRequest($requestFornitura,$wsdl,$action,SOAP_1_1);


die;

include(INC."/footer.php");

?>