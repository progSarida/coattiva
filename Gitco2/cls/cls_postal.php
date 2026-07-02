<?php

include_once CLS."/cls_literalNumber.php";

class cls_postal{

    public $a_payment;
    public $a_recipient;
    public $clientCode;
    public $a_causal;
    public $cls_literalNumber;

    public function __construct(array $a_paymentParams){
        $this->a_payment = $a_paymentParams;
        $this->cls_literalNumber = new cls_literalNumber();
    }

    public function setPostalParams(array $a_recipientHeader, array $a_causal, $clientCode){
        $this->a_recipient = $a_recipientHeader;
        $this->a_causal = $a_causal;
        $this->clientCode = $clientCode;
    }

    public function getPostalArray($number, $a_logo, $amount){

        //var_dump($this->a_payment);
        if($number==1)
            $stemma = "Stemma";
        else
            $stemma = "Stemma_2";

        if($this->a_payment[$stemma]=="" || $this->a_payment[$stemma]=="nessuno")
            $logo = $a_logo['auto'];
        else if($this->a_payment[$stemma] == "ente")
            $logo = $a_logo['ente'];
        else if($this->a_payment[$stemma] == "gestore")
            $logo = $a_logo['gestore'];
        if(!empty($amount)){
            $amountDisp = number_format($amount,2,",","");
            $literalDisp = $this->cls_literalNumber->getPostalFormat(number_format($amount,2,".",""));
        }
        else{
            $amountDisp = null;
            $literalDisp = null;
        }

        $a_postal[$number] = array(
            "logo"=>$logo,
            "td"=> $this->a_payment['Bollettino_'.$number],
            "authorization" => $this->a_payment['Autorizzazione_'.$number],
            "accountNumber" => $this->a_payment['Numero_Conto'],
            "accountHolder" => $this->a_payment['Intestatario_Conto'],
            "iban" => $this->a_payment['IBAN'],
            "amount" => $amountDisp,
            "checkAmount" => $this->a_payment['Importo_'.$number],
            "literalAmount" => $literalDisp,

            "recipientRow1" => implode(" ",$this->a_recipient['denomination']),
            "recipientRow2" => $this->a_recipient['addressRow'][0],
            "recipientRow3" => $this->a_recipient['addressRow'][1],

            "causalRow1" => $this->a_causal[0],
            "causalRow2" => $this->a_causal[1],

            "clientCode" => $this->setClientCode( $this->a_payment['Bollettino_'.$number], $this->clientCode),
            "barCode_clientCode" => $this->setClientCode( $this->a_payment['Bollettino_'.$number], $this->clientCode,true),
            "barCode_amount" => $this->setBarcodeAmount($this->a_payment['Bollettino_'.$number], number_format((double) $amount,2,",","")),
            "barCode_accountNumber" => $this->setBarcodeAccount($this->a_payment['Bollettino_'.$number], $this->a_payment['Numero_Conto']),
            "barCode_td" => $this->a_payment['Bollettino_'.$number].">"
        );

        return $a_postal[$number];

    }

    public function setClientCode($td, $clientCode, $codeLine=false )
    {
        $ritorno = "";
        if($td == "896" || $td == "674"){
            if($codeLine === true)
                $ritorno.= "<";
            $ritorno.= $clientCode;
            if($codeLine === true)
                $ritorno.= ">";
        }

        return $ritorno;
    }

    public function setBarcodeAmount($td, $amount)
    {
        $ritorno = "";
        if($td == "896"){
            $amount = str_replace(".",",",$amount);
            $a_amount = explode(',',$amount);
            $interi = str_split($a_amount[0]);
            $decimali = str_split($a_amount[1]);

            for($i=0;$i<8-count($interi);$i++)
                $ritorno .= "0";
            for($i=0;$i<count($interi);$i++)
                $ritorno .= $interi[$i];

            $ritorno .= "+";

            for($i=0;$i<count($decimali);$i++)
                $ritorno .= $decimali[$i];

            $ritorno .= ">";
        }

        return $ritorno;
    }

    public function setBarcodeAccount($td, $account)
    {
        $ritorno = "";
        if($td == "896" || $td == "674" || $td == "451"){
            $account = str_split($account);

            for($i=0;$i<12-count($account);$i++)
                $ritorno .= "0";
            for($i=0;$i<count($account);$i++)
                $ritorno .= $account[$i];
            $ritorno .= "<";
        }

        return $ritorno;
    }

}


?>