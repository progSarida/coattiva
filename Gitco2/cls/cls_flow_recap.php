<?php


class cls_flow_recap{

    public $header;

    public function getFlowListQuery($where=null, $limit=0){
    $query = "SELECT flows.*, document_type.Description AS DocumentType, ";
    $query.= "PRINT_INVOICE.Number AS PrintInvoiceNumber, PRINT_INVOICE.Year AS PrintInvoiceYear, PRINT_INVOICE.Date AS PrintInvoiceDate, ";
    $query.= "POSTAGE_INVOICE.Number AS PostageInvoiceNumber, POSTAGE_INVOICE.Year AS PostageInvoiceYear, POSTAGE_INVOICE.Date AS PostageInvoiceDate ";
    $query.= "FROM flows ";
    $query.= "JOIN document_type ON document_type.Id=flows.DocumentTypeId ";
    $query.= "LEFT JOIN flow_invoices AS PRINT_INVOICE ON PRINT_INVOICE.Id=flows.PrintInvoiceId ";
    $query.= "LEFT JOIN flow_invoices AS POSTAGE_INVOICE ON POSTAGE_INVOICE.Id=flows.PostageInvoiceId ";
    if($where!=null)
        $query.= "WHERE ".$where." ";
    $query.= "ORDER BY flows.Year DESC, flows.Number DESC ";
    if($limit>0)
        $query.= "LIMIT ".$limit;

    return $query;
}

    public function getInvoicesListQuery($where=null, $limit=0){
        $query = "SELECT flow_invoices.*, 0.00 AS PrintAmount, 0.00 AS PostageAmount ";
        $query.= "FROM flow_invoices ";
        $query.= "LEFT JOIN flows AS PRINT_FLOWS ON PRINT_FLOWS.PrintInvoiceId = flow_invoices.Id ";
        $query.= "LEFT JOIN flows AS POSTAGE_FLOWS ON POSTAGE_FLOWS.PostageInvoiceId = flow_invoices.Id ";
        if($where!=null)
            $query.= "WHERE ".$where." ";
        $query.= "GROUP BY flow_invoices.ID ";
        $query.= "ORDER BY flow_invoices.Year DESC, flow_invoices.Number DESC ";
        if($limit>0)
            $query.= "LIMIT ".$limit;



        return $query;
    }

    public function setHeaderByArray(array $a_header){
        $this->a_header = $a_header;

        $this->file = fopen ($this->flowFile, "w+");
        $this->addHeaderFromArray();
    }

    public function setFlowName(){

        $file = $this->myCartella;
        $file .= $this->myPrefisso . "_";
        $file .= $this->myTipo . "_";
        $file .= $this->myComune . "_";
        $file .= $this->myAnno . "_";
        $file .= $this->myNumero . "_";
        $file .= $this->myData . "_";
        $file .= $this->myOra . ".txt";

        return $file;
    }

    public function getFlowName ($a_params){
        //$a_params['flowYear']
        //$a_params['flowNumber']
        //$a_params['flowCC']
        //$a_params['docType']
        //
        $dir = "";
        $checkDoc = 0;
        switch ($a_params['docType']) {
            case "Preavviso di pagamento";
                $dir = "/Preavvisi";
                break;
            case "Ingiunzione";
                $dir = "/Ingiunzioni";
                break;
            case "Avviso di intimazione ad adempiere";
                $dir = "/Avvisi_di_intimazione";
                break;
            case "Sollecito di pagamento";
                $dir = "/Solleciti";
                break;
            case "Sollecito pre ingiunzione";
                $dir = "/Solleciti_pre_ingiunzione";
                break;
            case "Avviso di messa in mora";
                $dir = "/Avvisi_Messa_In_Mora";
                break;
            default:
                $expDoc = explode(" ",$a_params['docType']);
                if($expDoc[0]=="Pignoramento"){
                    $checkDoc = 1;
                    $dir = "/Pignoramenti";
                    switch ($expDoc[1]) {
                        case "veicolo":
                            $dir .= "/Veicolo";
                            break;
                        case "terzi":
                            $dir .= "/Presso_Terzi";
                            switch ($expDoc[2]) {
                                case "banca":
                                    $dir .= "/Banca";
                                    break;
                                case "lavoro":
                                    $dir .= "/Datore_di_Lavoro";
                                    break;
                                case "inps":
                                    $dir .= "/Inps";
                                    break;
                            }
                            break;
                    }
                }

                break;
        }

        $flowName = "";
        $a_check = array();
        $cls_file = new cls_file();
        $flowDir = $cls_file->folderCreation(SUPER_ROOT . "/archivio/Atti/" . $a_params['flowCC'] . $dir . "/FLUSSI");
        $handle = opendir($flowDir);
        while (($link = readdir($handle)) !== false) {
            if ($link != "." && $link != ".." && $link != "thumbs.db" && $link != "ELIMINATI") {
                $explodePunto = explode(".", $link);
                $a_check['extension'] = $explodePunto[1];

                $explode = explode("_", $explodePunto[0]);
                if($checkDoc==1){
                    $a_check['flowCC'] = $explode[3];
                    $a_check['flowYear'] = $explode[4];
                    $a_check['flowNumber'] = $explode[5];
                }
                else{
                    $a_check['flowCC'] = $explode[2];
                    $a_check['flowYear'] = $explode[3];
                    $a_check['flowNumber'] = $explode[4];
                }

                if (strtoupper($a_check['extension']) == "RAR" &&
                    $a_params['flowCC'] == $a_check['flowCC'] &&
                    $a_params['flowYear'] == $a_check['flowYear'] &&
                    $a_params['flowNumber'] == $a_check['flowNumber'])
                    $flowName = $link;
            }
        }

        closedir($handle);

        return $flowName;
    }

    public function setHeader(){

        $this->header = array(

            "NOME_FLUSSO",
            "TIPOLOGIA_STAMPA",
            "TIPOLOGIA_ATTO",

            "COLORE_STAMPA",

            "CODICE_ENTE",
            "DENOMINAZIONE_ENTE",

            "SPESE_ANTICIPATE",

            "INTESTATARIO_SMA",
            "NUMERO_SMA",

            "MOD23_SOGGETTO_MITTENTE",
            "MOD23_ENTE_GESTITO",
            "MOD23_RECAPITO_SOGGETTO",
            "MOD23_INDIRIZZO_SOGGETTO",
            "MOD23_CITTA_SOGGETTO",

            "ID",
            "TABLE_NAME",

            "DESTINATARIO",
            "DESTINATARIO_INDIRIZZO",
            "DESTINATARIO_CAP",
            "DESTINATARIO_COMUNE",
            "DESTINATARIO_PROVINCIA",
            "DESTINATARIO_STATO"

        );
    }

}

