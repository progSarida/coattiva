<?php
include_once(CLS . "/cls_excel.php");
/**
 * Class cls_290
 */
class cls_290
{
    public $file = "";
    public $cityCode;
    public $cls_help;
    public $cls_registry;
    public $cls_db;
    public $a_params = array();

    public $a_290;

    public $checkDataDecorrenza = 0;
    public $a_required = array();
    public $a_model = array();
    public $a_checkPartita = array();
    public $a_checkUtente = array("N2" => array(), "N3" => array());
    public $a_codiciTributo = array("DB" => array(), "290" => array());
    public $a_count = array("N0" => 0, "N1" => 0, "N2" => 0, "N3" => 0, "N4" => 0, "N5" => 0, "N9" => 0, "NR" => 0);
    public $a_countCheck = array("errori" => 0, "importato" => 0, "scarti" => 0, "omonimie" => 0, "toImport" => 0, "coobbligati" => 0);

    public function __construct($a_params)
    {
        $this->setParams($a_params);
    }

    public function setClass($cls_name, $cls)
    {
        $this->$cls_name = $cls;
    }

    public function getCodiciTributo()
    {
        $query = "SELECT DISTINCT Codice_Tributo, Settore, Sottosettore FROM codice_tributo";
        $this->a_codiciTributo['DB'] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query), "array", "Codice_Tributo");
    }

    public function setParams($a_params)
    {
        $this->a_params = array_merge($this->a_params, $a_params);
    }

    public function getFile($filePath)
    {
        $fopen = fopen($filePath, "r");
        $this->file = fread($fopen, filesize($filePath));
        fclose($fopen);
    }

    public function getExcelDate($cellValue)
    {
        $UNIX_DATE = ($cellValue - 25569) * 86400;
        return gmdate("Y-m-d", $UNIX_DATE);
    }

    public function readXlsxModel($file){
        $spreadsheet = PHPExcel_IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();

        $setHeader = 0;
        $countRows = 0;
        $break = 0;

        $rowType = null;
        $breakAll = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            if ($breakAll == 1) {
                unset($this->a_model[$countRows]);
                break;
            }

            if ($rowType == "amount") {
                $this->checkModelRow($countRows);
                $countRows++;
            }

            $rowType = null;
            foreach ($cellIterator as $key => $cell) {
                $value = $cell->getValue();
                if ($key == 0 && $value == "290")
                    $setHeader = 1;
                else if ($setHeader == 1)
                    $a_header[$key] = $value;
                else {
                    if ($key == 0) {
                        switch ($value) {
                            case "PARTITA CONTABILE":
                                $rowType = "partita";
                                break;
                            case "CODICE TRIBUTO":
                                $rowType = "code";
                                break;
                            case "ANNO CODICE":
                                $rowType = "year";
                                break;
                            case "IMPORTO CODICE":
                                $rowType = "amount";
                                break;
                        }
                    } else {
                        switch ($rowType) {
                            case "partita":
                                if ($a_header[$key] == "CODICE_CATASTALE_COMUNE" && $value == "")
                                    $breakAll = 1;
                                else {
                                    if ($a_header[$key] == "DATA_DECORRENZA_INTERESSI" && $value > 0)
                                        $value = $this->getExcelDate($value);

                                    $this->a_model[$countRows][$a_header[$key]] = $value;
                                }

                                break;
                            case "code":
                            case "year":
                            case "amount":
                                if ($value == "")
                                    $break = 1;
                                else
                                    $this->a_model[$countRows]['CODICI_TRIBUTO'][$key][$rowType] = $value;
                                break;
                        }
                        if ($break == 1) {
                            $break = 0;
                            break;
                        }
                    }
                }
            }
            if ($setHeader == 1) {
                $setHeader = 0;
            }
        }
        $this->a_count['N2'] = count($this->a_model);
    }

    public function checkModelRow($rowNumber)
    {
        $this->a_model[$rowNumber]['STATUS'] = array(
            "check" => "OK",
            "status" => "DA IMPORTARE",
            "msg" => "",
            "class" => "RowLabel"
        );

        $this->checkCodiceCatastale($rowNumber);
        $this->checkCodiciTributo($rowNumber);
        $this->checkCCModel($rowNumber);
        $this->checkUtenteModel($rowNumber);
        $this->checkPartitaModel($rowNumber);

        if ($this->a_model[$rowNumber]['STATUS']['check'] == "OK")
            $this->a_countCheck["toImport"]++;
    }

    public function checkPartitaModel($rowNumber)
    {
        $query = "SELECT PA.ID, PA.Comune_ID, PA.Tipo, SUM(T.Imposta) AS Importo_Codici, PA.Ruolo_ID FROM partita_tributi PA ";
        $query .= "JOIN tributo T ON PA.ID=T.Partita_ID ";
        $query .= "WHERE PA.CC='" . $this->a_params['CC'] . "' AND T.Info_Cartella=\"" . $this->a_model[$rowNumber]['INFORMAZIONI_CARTELLA'] . "\" GROUP BY PA.ID";

        $a_check = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if (!is_null($a_check)) {
            $a_extra = array("ID" => $a_check['ID'], "Comune_ID" => $a_check['Comune_ID'], "Ruolo_ID" => $a_check['Ruolo_ID']);
            $this->addImportedModel($rowNumber, $a_extra);
        }
    }

    public function checkCCModel($rowNumber)
    {

        $where = "";

        if ((strtoupper($this->a_model[$rowNumber]['PAESE_DESTINATARIO']) != "ITALIA" && !empty($this->a_model[$rowNumber]['PAESE_DESTINATARIO'])) || substr($this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'], 0, 1) == "Z") {
            if (!empty($this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO']))
                $where = "CC_Paese_Estero = '" . $this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'] . "'";

            if (!empty($this->a_model[$rowNumber]['PAESE_DESTINATARIO'])) {
                if ($where != "")
                    $where .= " OR ";
                $where .= "Nome = '" . $this->a_model[$rowNumber]['PAESE_DESTINATARIO'] . "'";
            }

            if ($where == "") {
                $this->addScartoModel($rowNumber, "CODICE CATASTALE E PAESE DI RESIDENZA ASSENTI");
            } else {
                $query = "SELECT Nome, CC_Paese_Estero FROM paesi_esteri_lista WHERE " . $where;
                $a_paese = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
                if (!is_null($a_paese)) {
                    $this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'] = $a_paese['CC_Paese_Estero'];
                    $this->a_model[$rowNumber]['PAESE_DESTINATARIO'] = $a_paese['Nome'];
                } else {
                    $msg = "PAESE E/O CODICE CATASTALE ERRATI ";
                    $msg .= "(CC " . $this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'] . " - PAESE " . $this->a_model[$rowNumber]['PAESE_DESTINATARIO'] . ")";
                    $this->addScartoModel($rowNumber, $msg);
                }
            }

            //            echo "(CC ".$this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO']." - PAESE ".$this->a_model[$rowNumber]['PAESE_DESTINATARIO'].")";
        } else if (strtoupper($this->a_model[$rowNumber]['PAESE_DESTINATARIO']) == "ITALIA" || substr($this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'], 0, 1) != "Z") {
            if (!empty($this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO']))
                $where .= "Com_Codice_Catastale = '" . $this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'] . "'";

            if (!empty($this->a_model[$rowNumber]['COMUNE_DESTINATARIO'])) {
                if ($where != "")
                    $where .= " OR ";
                $where .= "Com_Nome = \"" . trim(strtolower(ucwords($this->a_model[$rowNumber]['COMUNE_DESTINATARIO']))) . "\"";
            }

            if ($where == "") {
                $this->addScartoModel($rowNumber, "COMUNE E CODICE CATASTALE ASSENTI");
            } else {
                $query = "SELECT Com_Codice_Catastale, Com_Nome, Com_Cap FROM comuni_lista WHERE " . $where . " LIMIT 1";
                $a_comune = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
                if (!is_null($a_comune)) {
                    $this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'] = $a_comune['Com_Codice_Catastale'];
                    $this->a_model[$rowNumber]['COMUNE_DESTINATARIO'] = $a_comune['Com_Nome'];
                    if (empty($this->a_model[$rowNumber]['CAP_DESTINATARIO']))
                        $this->a_model[$rowNumber]['CAP_DESTINATARIO'] = $a_comune['Com_Cap'];
                } else {
                    $msg = "COMUNE E/O CODICE CATASTALE ERRATI ";
                    $msg .= "(CC " . $this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO'] . " - COMUNE " . $this->a_model[$rowNumber]['COMUNE_DESTINATARIO'] . ")";
                    $this->addScartoModel($rowNumber, $msg);
                }
            }
            //            echo "(CC ".$this->a_model[$rowNumber]['CODICE_CATASTALE_DESTINATARIO']." - COMUNE ".$this->a_model[$rowNumber]['COMUNE_DESTINATARIO'].")";
        } else
            $this->addScartoModel($rowNumber, "STATO DI RESIDENZA ASSENTE");
    }

    public function checkCodiceCatastale($rowNumber)
    {
        if ($this->a_model[$rowNumber]['CODICE_CATASTALE_COMUNE'] != $this->a_params['CC']) {
            $msg = "IL CODICE CASTALE '" . $this->a_model[$rowNumber]['CODICE_CATASTALE_COMUNE'] . "' NON CORRISPONDE ";
            $msg .= "A QUELLO DEL COMUNE '" . $this->a_params['CC'] . "'";
            $this->addScartoModel($rowNumber, $msg);
        }
    }

    public function checkUtenteModel($rowNumber)
    {
        if ($this->a_model[$rowNumber]['TIPO_SOGGETTO'] != "DITTA") {

            if ($this->cls_registry->decode_CF($this->a_model[$rowNumber]['CODICE_FISCALE']) === false) {
                $this->addScartoModel($rowNumber, "CODICE FISCALE " . $this->a_model[$rowNumber]['CODICE_FISCALE'] . " ERRATO");
                return true;
            }

            $query = "SELECT ID, Comune_ID FROM utente WHERE CC_Comune='" . $this->a_params['CC'] . "' AND Codice_Fiscale='" . $this->a_model[$rowNumber]['CODICE_FISCALE'] . "'";
        } else if ($this->a_model[$rowNumber]['TIPO_SOGGETTO'] == "DITTA") {

            if (
                strlen($this->a_model[$rowNumber]['PARTITA_IVA']) != 11
                || preg_match(
                    "/[^0-9]/",
                    $this->a_model[$rowNumber]['PARTITA_IVA']
                )
                || !(int) $this->a_model[$rowNumber]['PARTITA_IVA'] > 0
            ) {
                $this->addScartoModel($rowNumber, "PARTITA IVA " . $this->a_model[$rowNumber]['PARTITA_IVA'] . " ERRATO");
                return true;
            }

            $query = "SELECT ID, Comune_ID FROM utente WHERE CC_Comune='" . $this->a_params['CC'] . "' AND Partita_Iva='" . $this->a_model[$rowNumber]['PARTITA_IVA'] . "'";
        }

        $a_db = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if (!is_null($a_db)) {
            $a_extra = array("ID" => $a_db['ID'], "Comune_ID" => $a_db['Comune_ID']);
            $this->addOmonimiaModel($rowNumber, $a_extra);
        }
    }

    public function checkCodiciTributo($rowNumber)
    {

        /** 
         * Due correzzioni:
         * - Eliminati i valori null dei codici tributo nel conteggio.
         * - Eliminato il GroupBy ed aggiunto il Distinct nella select in modo da verificare se corrispondono i codici tributo salvati a db
         *   con quelli presi dal tracciato 290. Prima col GroupBy e Usando ArrayLine per la query se si splittavano i risultati grazie
         *   al GroupBy veniva presa solo la prima riga e quindi falsava il risultato.
         *  **/
        $this->a_model[$rowNumber]['SOTTOTIPO_PARTITA'] = "";
        switch ($this->a_model[$rowNumber]['TIPO_RISCOSSIONE']) {
            case "CDS / AMMINISTRATIVA":
                $this->a_model[$rowNumber]['TIPO_PARTITA'] = "CDS";
                break;
            case "IMU":
            case "TASI":
            case "ICI":
                $this->a_model[$rowNumber]['TIPO_PARTITA'] = "IMMOBILI";
                $this->a_model[$rowNumber]['SOTTOTIPO_PARTITA'] = $this->a_model[$rowNumber]['TIPO_RISCOSSIONE'];
                break;
            case "TARES":
            case "TARI":
            case "TSRSU":
                $this->a_model[$rowNumber]['TIPO_PARTITA'] = "RIFIUTI";
                $this->a_model[$rowNumber]['SOTTOTIPO_PARTITA'] = $this->a_model[$rowNumber]['TIPO_RISCOSSIONE'];
                break;
            default:
                $this->a_model[$rowNumber]['TIPO_PARTITA'] = $this->a_model[$rowNumber]['TIPO_RISCOSSIONE'];
                break;
        }

        if ($this->a_model[$rowNumber]['DATA_DECORRENZA_INTERESSI'] == "")
            $this->addScartoModel($rowNumber, "DATA DECORRENZA INTERESSI ASSENTE");

        $whereIn = "";
        $a_code = array();
        foreach ($this->a_model[$rowNumber]['CODICI_TRIBUTO'] as $key => $a_codice) {

            if ($a_codice['code'] !== null) {
                if (isset($a_code[$a_codice['code']]))
                    $a_code[$a_codice['code']]++;
                else
                    $a_code[$a_codice['code']] = 1;

                if (!isset($a_codice['year']) || !$a_codice['year'] > 0)
                    $this->addScartoModel($rowNumber, "ANNO CODICE TRIBUTO " . $a_codice['code']);
                if (!isset($a_codice['amount']) || !$a_codice['amount'] > 0)
                    $this->addScartoModel($rowNumber, "IMPORTO CODICE TRIBUTO " . $a_codice['code']);
            }
        }

        foreach ($a_code as $code => $countCode) {
            if ($code !== null) {
                if ($whereIn != "")
                    $whereIn .= ", ";
                $whereIn .= "'" . $code . "'";
            }
        }

        if (count($this->a_model[$rowNumber]['CODICI_TRIBUTO']) > 0) {
            $query = "SELECT COUNT(DISTINCT(Codice_Tributo)) as countCodes FROM codice_tributo WHERE Codice_Tributo IN (" . $whereIn . ") ";
            $query .= "AND (";

            if ($this->a_model[$rowNumber]['SOTTOTIPO_PARTITA'] != "")
                $query .= " ( ";
            $query .= "Settore='" . $this->a_model[$rowNumber]['TIPO_PARTITA'] . "' ";

            if ($this->a_model[$rowNumber]['SOTTOTIPO_PARTITA'] != "")
                $query .= "AND Sottosettore='" . $this->a_model[$rowNumber]['SOTTOTIPO_PARTITA'] . "') ";

            $query .= "OR Settore='SARIDA') ";
            //$query .= "GROUP BY Settore, Sottosettore ";

            $a_result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

            if (is_null($a_result) || $a_result['countCodes'] < count($a_code))
                $this->addScartoModel($rowNumber, "CODICE TRIBUTO NON TROVATO (" . $whereIn . ")");
        } else
            $this->addScartoModel($rowNumber, "CODICI TRIBUTO ASSENTI");
    }

    public function addScartoModel($rowNumber, $msg)
    {
        $this->a_model[$rowNumber]['STATUS']["class"] = "RowLabelScarto";
        $this->a_model[$rowNumber]['STATUS']["check"] = "scarto";
        $this->a_model[$rowNumber]['STATUS']["status"] = "SCARTO";
        if ($this->a_model[$rowNumber]['STATUS']["msg"] != "")
            $this->a_model[$rowNumber]['STATUS']["msg"] .= " - " . $msg;
        else
            $this->a_model[$rowNumber]['STATUS']["msg"] = $msg;

        $this->a_countCheck["scarti"]++;
    }

    public function addOmonimiaModel($rowNumber, $a_utente)
    {
        if ($this->a_model[$rowNumber]['STATUS']["check"] != "scarto") {
            $linkUtente = "<a href='" . WEB_ROOT . "/anagrafe/dati_soggetto.php?";
            $linkUtente .= "p=" . $a_utente['ID'] . "&c=" . $this->a_params['CC'] . "'>" . $a_utente['Comune_ID'] . "</a>";

            $this->a_model[$rowNumber]['STATUS']["class"] = "RowLabel";
            $this->a_model[$rowNumber]['STATUS']["check"] = "omonimia";
            $this->a_model[$rowNumber]['STATUS']["status"] = "DA IMPORTARE (UTENTE " . $linkUtente . ")";
            if ($this->a_model[$rowNumber]['STATUS']["msg"] != "")
                $this->a_model[$rowNumber]['STATUS']["msg"] .= " - ";

            $this->a_model[$rowNumber]['STATUS']["Utente_ID"] = $a_utente['ID'];
            $this->a_model[$rowNumber]['STATUS']["msg"] .= "UTENTE " . $a_utente['Comune_ID'] . " PRESENTE IN ARCHIVIO";
            $this->a_countCheck["omonimie"]++;
            $this->a_countCheck["toImport"]++;
        }
    }

    public function addImportedModel($rowNumber, $a_partita)
    {
        if ($this->a_model[$rowNumber]['STATUS']["check"] != "scarto") {
            $linkPartita = "<a href='" . WEB_ROOT . "/coattiva/gestione_partita.php?";
            $linkPartita .= "partita=" . $a_partita['ID'] . "&c=" . $this->a_params['CC'] . "&a='>" . $a_partita['Comune_ID'] . "</a>";

            if ((int) $a_partita['Ruolo_ID'] == (int) $this->a_params['Ruolo_ID']) {
                $this->a_countCheck["importato"]++;
                $this->a_model[$rowNumber]['STATUS']["check"] = "imported";
                $this->a_model[$rowNumber]['STATUS']["class"] = "RowLabelImport";
            } else {
                $this->a_countCheck["scarti"]++;
                $this->a_model[$rowNumber]['STATUS']["class"] = "RowLabelScarto";
                $this->a_model[$rowNumber]['STATUS']["check"] = "scarto";
            }


            $this->a_model[$rowNumber]['STATUS']["Ruolo_ID"] = $a_partita['Ruolo_ID'];
            $this->a_model[$rowNumber]['STATUS']["status"] = "IMPORTATA (PARTITA " . $linkPartita . ")";
            if ($this->a_model[$rowNumber]['STATUS']["msg"] != "")
                $this->a_model[$rowNumber]['STATUS']["msg"] .= " - ";

            $this->a_model[$rowNumber]['STATUS']["msg"] .= "PARTITA " . $a_partita['Comune_ID'] . " PRESENTE IN ARCHIVIO";

        }
    }

    public function read290()
    {
        $separator = "\r\n";
        $line = strtok($this->file, $separator);
        $contN1 = 0;
        $contN2 = -1;
        while ($line !== false) {
            $lineId = substr($line, 0, 2);
            switch ($lineId) {
                case "N0":
                    $this->a_290['N0'] = $this->readN0($line);
                    break;
                case "NR":
                    $this->a_290['NR'][$contN1] = $this->readNR($line);
                    break;
                case "N1":
                    $this->a_290['N1'][$contN1] = $this->readN1($line);
                    break;
                case "N2":
                    $contN2++;
                    $this->a_290['N2'][$contN1][$contN2] = $this->readN2($line);
                    break;
                case "N3":
                    $this->a_290['N3'][$contN1][$contN2][] = $this->readN3($line);
                    break;
                case "N4":
                    $this->a_290['N4'][$contN1][$contN2][] = $this->readN4($line);
                    break;
                case "N5":
                    $this->a_290['N5'][$contN1] = $this->readN5($line);
                    $contN2 = -1;
                    break;
                case "N9":
                    $this->a_290['N9'] = $this->readN9($line);
                    break;
            }
            $line = strtok($separator);
        }
    }

    public function readN0($line)
    {
        $this->a_count['N0']++;
        $g_fornitura = trim(substr($line, 13, 2));
        $m_fornitura = trim(substr($line, 11, 2));
        $a_fornitura = (int) trim(substr($line, 7, 4));
        if ($a_fornitura > 0)
            $dataFornitura = $a_fornitura . "-" . $m_fornitura . "-" . $g_fornitura;
        else
            $dataFornitura = null;
        $a_N0 = array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            //"N0"
            "CodiceEnte" => array(
                "value" => (int) substr($line, 2, 5),
                "type" => "numeric",
                "required" => 1
            ),
            //Codice Ente impositore secondo la codifica CNC
            "DataFornitura" => array(
                "value" => $dataFornitura,
                "type" => "date",
                "required" => 1
            ), //Date("Ymd");//8
        );

        return $a_N0;
    }

    public function readNR($line)
    {
        $this->a_count['NR']++;

        $a_NR = array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            //"N1"
            "CodiceProvinciaComune" => array(
                "value" => (int) substr($line, 2, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice Provincia/Comune di iscrizione a ruolo (Codifica CNC)
            "ProgressivoMinuta" => array(
                "value" => (int) substr($line, 8, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Progr minuta
            "Informazioni" => array(
                "value" => trim(substr($line, 10, 290)),
                "type" => "string",
                "required" => 0
            )
        );

        return $a_NR;
    }

    public function readN1($line)
    {
        $this->a_count['N1']++;
        return array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            //"N1"
            "CodiceProvinciaComune" => array(
                "value" => (int) substr($line, 2, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice Provincia/Comune di iscrizione a ruolo (Codifica CNC)
            "ProgressivoMinuta" => array(
                "value" => (int) substr($line, 8, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Progr minuta
            "TipoRuolo" => array(
                "value" => (int) substr($line, 10, 1),
                "type" => "numeric",
                "required" => 0
            ),
            //Tipo ruolo (1=Principale/2=Suppletivo/3=Straordinario/4=Speciale/5=Fallito)
            "NumeroRuolo" => array(
                "value" => (int) substr($line, 11, 4),
                "type" => "numeric",
                "required" => 0
            ),
            //Numero ruolo
            "NumeroRate" => array(
                "value" => (int) substr($line, 15, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Numero ruolo
            "Ruolo" => array(
                "value" => (int) substr($line, 17, 1),
                "type" => "numeric",
                "required" => 0
            ),
            //0=ruolo normale, 1=ruolo coattivo
            "CodiceSede" => array(
                "value" => substr($line, 18, 4),
                "type" => "string",
                "required" => 0
            ),
            //Codice Sede
            "TipoCompenso" => array(
                "value" => (int) substr($line, 22, 1),
                "type" => "numeric",
                "required" => 0
            ),
            //Tipo compenso (4=compenso a carico del contribuente/5=compenso a carico dell’Ente impositore)
            "ICIAP" => array(
                "value" => substr($line, 41, 1),
                "type" => "string",
                "required" => 0
            ),
            //1=ruoli ICIAP, “ “ negli altri casi
            "NumeroConvenzione" => array(
                "value" => (int) substr($line, 42, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Numero convenzione
            "FlagArticoli" => array(
                "value" => substr($line, 44, 1),
                "type" => "string",
                "required" => 0
            ), //Flag Art. 89/64/65 (4=Art. 64 DPR43/88, 5=Art. 65 DPR43/88, 9=Art. 89 DPR43/88)
        );
    }

    public function readN2($line)
    {
        $this->a_count['N2']++;
        $a_N2['Check'] = array("scarto" => 0, "omonimia" => 0, "Utente_ID" => null);
        $a_N2['Utente'] = array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            "CodiceProvinciaComune" => array(
                "value" => (int) substr($line, 2, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice Provincia/Comune di iscrizione a ruolo (Codifica CNC)
            "ProgressivoMinuta" => array(
                "value" => (int) substr($line, 8, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Progr minuta
            "CodicePartita" => array(
                "value" => trim(substr($line, 10, 14)),
                "type" => "string",
                "required" => 1
            ),
            //Codice partita
            "CodiceContribuente" => array(
                "value" => (int) substr($line, 40, 8),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice contribuente
            "CodiceControlloContribuente" => array(
                "value" => (int) substr($line, 48, 2),
                "type" => "numeric",
                "required" => 0
            ),
            "NaturaGiuridica" => array(
                "value" => (int) substr($line, 208, 1),
                "type" => "numeric",
                "required" => 1
            ),
            //Natura giuridica
            "TipoDebitori" => array(
                "value" => trim(substr($line, 285, 1)),
                "type" => "numeric",
                "required" => 0
            ), //Tipo debitori, solo per ruoli consortili. C=Debitori contestatari/ Negli altri casi gli eventuali record N3 sono da intendersi come coobbligati.
        );

        if ($a_N2['Utente']['NaturaGiuridica']['value'] == 1) {
            $a_N2['Utente']['PI'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['Ditta'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['CF'] = array(
                "value" => substr($line, 24, 16),
                "type" => "string",
                "required" => 1
            );
            $a_N2['Utente']['Cognome'] = array(
                "value" => trim(substr($line, 209, 24)),
                "type" => "string",
                "required" => 1
            );
            $a_N2['Utente']['Nome'] = array(
                "value" => trim(substr($line, 233, 20)),
                "type" => "string",
                "required" => 1
            );
            $a_N2['Utente']['Sesso'] = array(
                "value" => trim(substr($line, 253, 1)),
                "type" => "string",
                "required" => 1
            );
            $g_nascita = trim(substr($line, 254, 2));
            $m_nascita = trim(substr($line, 256, 2));
            $a_nascita = (int) trim(substr($line, 258, 4));
            if ($a_nascita > 0)
                $a_N2['Utente']['DataNascita'] = array(
                    "value" => $a_nascita . "-" . $m_nascita . "-" . $g_nascita,
                    "type" => "string",
                    "required" => 0
                );
            else
                $a_N2['Utente']['DataNascita'] = array(
                    "value" => null,
                    "type" => "string",
                    "required" => 0
                );
            $a_N2['Utente']['CCNascita'] = array(
                "value" => trim(substr($line, 262, 4)),
                "type" => "string",
                "required" => 0
            );

            $a_decodeCF = $this->cls_registry->decode_CF($a_N2['Utente']['CF']['value']);
            if ($a_decodeCF !== false) {
                $a_N2['Utente']['Sesso']['value'] = $a_decodeCF['SESSO'];
                $a_N2['Utente']['DataNascita']['value'] = $a_decodeCF['DATA_NASCITA'];
                $a_N2['Utente']['CCNascita']['value'] = $a_decodeCF['CC_NASCITA'];
            }

        } else if ($a_N2['Utente']['NaturaGiuridica']['value'] == 2) {
            $a_N2['Utente']['PI'] = array(
                "value" => substr($line, 24, 11),
                "type" => "string",
                "required" => 1
            );
            $a_N2['Utente']['Ditta'] = array(
                "value" => trim(substr($line, 209, 76)),
                "type" => "string",
                "required" => 1
            );

            $a_N2['Utente']['CF'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['Cognome'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['Nome'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['Sesso'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['DataNascita'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N2['Utente']['CCNascita'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
        }

        $kmInt = (int) substr($line, 93, 3);
        if (!$kmInt > 0)
            $kmInt = 0;
        $kmDec = (int) substr($line, 96, 3);
        if (!$kmDec > 0)
            $kmDec = 0;
        $a_N2['Residenza'] = array(
            "CodiceIndirizzo" => array(
                "value" => (int) substr($line, 50, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice indirizzo CNC
            "Indirizzo" => array(
                "value" => trim(substr($line, 56, 30)),
                "type" => "string",
                "required" => 1
            ),
            //Indirizzo
            "Civico" => array(
                "value" => trim(substr($line, 86, 5)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Lettera" => array(
                "value" => trim(substr($line, 91, 2)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Km" => array(
                "value" => (float) ($kmInt . "." . $kmDec),
                "type" => "numeric",
                "required" => 0
            ),
            //Km 3 int e 3 dec
            "CAP" => array(
                "value" => substr($line, 99, 5),
                "type" => "string",
                "required" => 1
            ),
            //Cap
            "CC" => array(
                "value" => trim(substr($line, 104, 4)),
                "type" => "string",
                "required" => 1
            ),
            //Codice belfiore
            "Localita" => array(
                "value" => trim(substr($line, 108, 21)),
                "type" => "string",
                "required" => 0
            ), //Località/frazione
        );

        $kmInt = (int) substr($line, 172, 3);
        if (!$kmInt > 0)
            $kmInt = 0;
        $kmDec = (int) substr($line, 175, 3);
        if (!$kmDec > 0)
            $kmDec = 0;
        $a_N2['Recapito'] = array(
            "CodiceIndirizzo" => array(
                "value" => (int) substr($line, 129, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice indirizzo CNC
            "Indirizzo" => array(
                "value" => trim(substr($line, 135, 30)),
                "type" => "string",
                "required" => 0
            ),
            //Indirizzo
            "Civico" => array(
                "value" => trim(substr($line, 165, 5)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Lettera" => array(
                "value" => trim(substr($line, 178, 2)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Km" => array(
                "value" => (float) ($kmInt . "." . $kmDec),
                "type" => "numeric",
                "required" => 0
            ),
            "CAP" => array(
                "value" => substr($line, 178, 5),
                "type" => "string",
                "required" => 0
            ),
            //Cap
            "CC" => array(
                "value" => trim(substr($line, 183, 4)),
                "type" => "string",
                "required" => 0
            ),
            //Codice belfiore
            "Localita" => array(
                "value" => trim(substr($line, 187, 21)),
                "type" => "string",
                "required" => 0
            ), //Località/frazione
        );

        if ($a_N2['Residenza']['CC']['value'] != "" || $a_N2['Residenza']['Localita']['value'] != "") {
            $a_comune = $this->checkCC($a_N2['Residenza']['CC']['value'], $a_N2['Residenza']['Localita']['value']);
            if ($a_comune) {
                $a_N2['Residenza']['CC']['value'] = $a_comune['CC'];
                $a_N2['Residenza']['Comune']['value'] = $a_comune['Comune'];
            }
        }

        if ($a_N2['Recapito']['CC']['value'] != "" || $a_N2['Recapito']['Localita']['value'] != "") {
            $a_comune = $this->checkCC($a_N2['Recapito']['CC']['value'], $a_N2['Recapito']['Localita']['value']);
            if ($a_comune) {
                $a_N2['Recapito']['CC']['value'] = $a_comune['CC'];
                $a_N2['Recapito']['Comune']['value'] = $a_comune['Comune'];
            }
        }

        return $a_N2;
    }

    public function readN3($line)
    {
        $this->a_count['N3']++;
        $a_N3['Utente'] = array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            "CodiceProvinciaComune" => array(
                "value" => (int) substr($line, 2, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice Provincia/Comune di iscrizione a ruolo (Codifica CNC)
            "ProgressivoMinuta" => array(
                "value" => (int) substr($line, 8, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Progr minuta
            "CodicePartita" => array(
                "value" => trim(substr($line, 10, 14)),
                "type" => "string",
                "required" => 1
            ),
            //Codice partita
            "NaturaGiuridica" => array(
                "value" => (int) substr($line, 198, 1),
                "type" => "numeric",
                "required" => 1
            ), //Natura giuridica
        );

        if ($a_N3['Utente']['NaturaGiuridica']['value'] == 1) {
            $a_N3['Utente']['PI'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['Ditta'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['CF'] = array(
                "value" => substr($line, 24, 16),
                "type" => "string",
                "required" => 1
            );
            $a_N3['Utente']['Cognome'] = array(
                "value" => trim(substr($line, 199, 24)),
                "type" => "string",
                "required" => 1
            );
            $a_N3['Utente']['Nome'] = array(
                "value" => trim(substr($line, 223, 20)),
                "type" => "string",
                "required" => 1
            );
            $a_N3['Utente']['Sesso'] = array(
                "value" => trim(substr($line, 243, 1)),
                "type" => "string",
                "required" => 1
            );
            $g_nascita = trim(substr($line, 244, 2));
            $m_nascita = trim(substr($line, 246, 2));
            $a_nascita = (int) trim(substr($line, 248, 4));
            if ($a_nascita > 0)
                $a_N3['Utente']['DataNascita'] = array(
                    "value" => $a_nascita . "-" . $m_nascita . "-" . $g_nascita,
                    "type" => "string",
                    "required" => 0
                );
            else
                $a_N3['Utente']['DataNascita'] = array(
                    "value" => null,
                    "type" => "string",
                    "required" => 0
                );
            $a_N3['Utente']['CCNascita'] = array(
                "value" => trim(substr($line, 252, 4)),
                "type" => "string",
                "required" => 0
            );

            $a_decodeCF = $this->cls_registry->decode_CF($a_N3['Utente']['CF']['value']);
            if ($a_decodeCF !== false) {
                $a_N3['Utente']['Sesso']['value'] = $a_decodeCF['SESSO'];
                $a_N3['Utente']['DataNascita']['value'] = $a_decodeCF['DATA_NASCITA'];
                $a_N3['Utente']['CCNascita']['value'] = $a_decodeCF['CC_NASCITA'];
            }

        } else if ($a_N3['Utente']['NaturaGiuridica']['value'] == 2) {
            $a_N3['Utente']['PI'] = array(
                "value" => substr($line, 24, 11),
                "type" => "string",
                "required" => 1
            );
            $a_N3['Utente']['Ditta'] = array(
                "value" => trim(substr($line, 199, 76)),
                "type" => "string",
                "required" => 1
            );

            $a_N3['Utente']['CF'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['Cognome'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['Nome'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['Sesso'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['DataNascita'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
            $a_N3['Utente']['CCNascita'] = array(
                "value" => "",
                "type" => "string",
                "required" => 0
            );
        }

        $kmInt = (int) substr($line, 83, 3);
        if (!$kmInt > 0)
            $kmInt = 0;
        $kmDec = (int) substr($line, 86, 3);
        if (!$kmDec > 0)
            $kmDec = 0;
        $a_N3['Residenza'] = array(
            "CodiceIndirizzo" => array(
                "value" => (int) substr($line, 40, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice indirizzo CNC
            "Indirizzo" => array(
                "value" => trim(substr($line, 46, 30)),
                "type" => "string",
                "required" => 1
            ),
            //Indirizzo
            "Civico" => array(
                "value" => trim(substr($line, 76, 5)),
                "type" => "string",
                "required" => 1
            ),
            //
            "Lettera" => array(
                "value" => trim(substr($line, 81, 2)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Km" => array(
                "value" => (float) ($kmInt . "." . $kmDec),
                "type" => "numeric",
                "required" => 0
            ),
            //Km 3 int e 3 dec
            "CAP" => array(
                "value" => substr($line, 89, 5),
                "type" => "string",
                "required" => 1
            ),
            //Cap
            "CC" => array(
                "value" => trim(substr($line, 94, 4)),
                "type" => "string",
                "required" => 1
            ),
            //Codice belfiore
            "Localita" => array(
                "value" => trim(substr($line, 98, 21)),
                "type" => "string",
                "required" => 0
            ), //Località/frazione
        );

        $kmInt = (int) substr($line, 162, 3);
        if (!$kmInt > 0)
            $kmInt = 0;
        $kmDec = (int) substr($line, 165, 3);
        if (!$kmDec > 0)
            $kmDec = 0;
        $a_N3['Recapito'] = array(
            "CodiceIndirizzo" => array(
                "value" => (int) substr($line, 119, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice indirizzo CNC
            "Indirizzo" => array(
                "value" => trim(substr($line, 125, 30)),
                "type" => "string",
                "required" => 0
            ),
            //Indirizzo
            "Civico" => array(
                "value" => trim(substr($line, 155, 5)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Lettera" => array(
                "value" => trim(substr($line, 160, 2)),
                "type" => "string",
                "required" => 0
            ),
            //
            "Km" => array(
                "value" => (float) ($kmInt . "." . $kmDec),
                "type" => "numeric",
                "required" => 0
            ),
            "CAP" => array(
                "value" => substr($line, 168, 5),
                "type" => "string",
                "required" => 0
            ),
            //Cap
            "CC" => array(
                "value" => trim(substr($line, 173, 4)),
                "type" => "string",
                "required" => 0
            ),
            //Codice belfiore
            "Localita" => array(
                "value" => trim(substr($line, 177, 21)),
                "type" => "string",
                "required" => 0
            ), //Località/frazione
        );

        if ($a_N3['Residenza']['CC']['value'] != "" || $a_N3['Residenza']['Localita']['value'] != "") {
            $a_comune = $this->checkCC($a_N3['Residenza']['CC']['value'], $a_N3['Residenza']['Localita']['value']);
            if ($a_comune) {
                $a_N3['Residenza']['CC']['value'] = $a_comune['CC'];
                $a_N3['Residenza']['Localita']['value'] = $a_comune['Comune'];
            }
        }

        if ($a_N3['Recapito']['CC']['value'] != "" || $a_N3['Recapito']['Localita']['value'] != "") {
            $a_comune = $this->checkCC($a_N3['Recapito']['CC']['value'], $a_N3['Recapito']['Localita']['value']);
            if ($a_comune) {
                $a_N3['Recapito']['CC']['value'] = $a_comune['CC'];
                $a_N3['Recapito']['Localita']['value'] = $a_comune['Comune'];
            }
        }

        return $a_N3;
    }

    public function checkCC($cc, $comune)
    {
        $a_return = array("CC" => "", "Comune" => "");
        $a_comune = null;

        $where = "";
        if (!empty($cc))
            $where .= "Com_Codice_Catastale = '" . $cc . "'";

        if (!empty($comune)) {
            if ($where != "")
                $where .= " OR ";
            $where .= "Com_Nome = \"" . trim(strtolower(ucwords($comune))) . "\"";
        }

        $query = "SELECT Com_Codice_Catastale, Com_Nome FROM comuni_lista WHERE " . $where . " LIMIT 1";
        $a_comune = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if (!is_null($a_comune)) {
            $a_return["CC"] = $a_comune['Com_Codice_Catastale'];
            $a_return["Comune"] = $a_comune['Com_Nome'];
        } else if (!empty($cc)) {
            $query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE CC_Paese_Estero='" . $cc . "' LIMIT 1";
            $a_comune = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            if (!is_null($a_comune)) {
                $a_return["CC"] = $a_comune['CC_Paese_Estero'];
            }
        }

        return $a_return;
    }

    public function readN4($line)
    {
        $this->a_count['N4']++;
        $imponibInt = (int) substr($line, 32, 11);
        $imponibDec = (int) substr($line, 43, 2);
        $impostaInt = (int) substr($line, 45, 11);
        $impostaDec = (int) substr($line, 56, 2);
        if (!$imponibInt > 0)
            $imponibInt = 0;
        if (!$imponibDec > 0)
            $imponibDec = 0;
        if (!$impostaInt > 0)
            $impostaInt = 0;
        if (!$impostaDec > 0)
            $impostaDec = 0;

        $g_decorrenza = trim(substr($line, 60, 2));
        $m_decorrenza = trim(substr($line, 62, 2));
        $a_decorrenza = (int) trim(substr($line, 64, 4));
        if ($a_decorrenza > 0)
            $dataDecorrenzaInteressi = $a_decorrenza . "-" . $m_decorrenza . "-" . $g_decorrenza;
        else
            $dataDecorrenzaInteressi = null;

        $a_N4 = array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            //"N4"
            "CodiceProvinciaComune" => array(
                "value" => (int) substr($line, 2, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice Provincia/Comune di iscrizione a ruolo (Codifica CNC)
            "ProgressivoMinuta" => array(
                "value" => (int) substr($line, 8, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Progr minuta
            "CodicePartita" => array(
                "value" => trim(substr($line, 10, 14)),
                "type" => "string",
                "required" => 1
            ),
            //Codice partita
            "AnnoTributo" => array(
                "value" => (int) substr($line, 24, 4),
                "type" => "numeric",
                "required" => 1
            ),
            //Anno tributo
            "CodiceTributo" => array(
                "value" => trim(substr($line, 28, 4)),
                "type" => "string",
                "required" => 1
            ),
            //Codice tributo
            "Imponibile" => array(
                "value" => (float) ($imponibInt . "." . $imponibDec),
                "type" => "numeric",
                "required" => 0
            ),
            //Imponibile
            "Imposta" => array(
                "value" => (float) ($impostaInt . "." . $impostaDec),
                "type" => "string",
                "required" => 1
            ),
            //Imposta
            "SemestriInteressi" => array(
                "value" => (int) substr($line, 58, 2),
                "type" => "string",
                "required" => 0
            ),
            "DataDecorrenzaInteressi" => array(
                "value" => $dataDecorrenzaInteressi,
                "type" => "date",
                "required" => 1
            ),
            "CodiceReparto" => array(
                "value" => trim(substr($line, 68, 2)),
                "type" => "string",
                "required" => 0
            ),
            "InformazioniCartella" => array(
                "value" => trim(substr($line, 70, 75)),
                "type" => "string",
                "required" => 1
            ),
            "TipoInformazioni" => array(
                "value" => strtoupper(trim(substr($line, 145, 1))),
                "type" => "string",
                "required" => 0
            )
        );

        if (is_null($a_N4['DataDecorrenzaInteressi']['value'])) {
            $a_N4['DataDecorrenzaInteressi']['value'] = $this->a_290['N0']['DataFornitura']['value'];
            if ($this->checkDataDecorrenza == 0)
                $this->checkDataDecorrenza = 1;
        }

        if (isset($this->a_codiciTributo[$a_N4['CodiceTributo']['value']]))
            $this->a_codiciTributo['290'][$a_N4['CodiceTributo']['value']]++;
        else
            $this->a_codiciTributo['290'][$a_N4['CodiceTributo']['value']] = 1;

        $a_N4['E'] = null;
        $a_N4['S'] = null;
        $a_N4['M'] = null;
        switch ($a_N4['TipoInformazioni']['value']) { //Informazioni da riportare sul ruolo

            case "E": //iscrizioni coattive delle entrate
                $a_N4['E'] = array(
                    "Titolo" => array(
                        "value" => trim(substr($line, 146, 11)),
                        "type" => "string",
                        "required" => 0
                    ),
                    "Descrizione" => array(
                        "value" => trim(substr($line, 157, 15)),
                        "type" => "string",
                        "required" => 0
                    ),
                );

                break;
            case "S": //sanzioni amministrative
                $g_sanzione = trim(substr($line, 160, 2));
                $m_sanzione = trim(substr($line, 162, 2));
                $a_sanzione = (int) trim(substr($line, 164, 2));
                if ($a_sanzione > 0)
                    $dataSanzione = "20" . $a_sanzione . "-" . $m_sanzione . "-" . $g_sanzione;
                else
                    $dataSanzione = null;
                $a_N4['S'] = array(
                    "Tipo" => array(
                        "value" => trim(substr($line, 146, 2)),
                        "type" => "string",
                        "required" => 0
                    ),
                    "Titolo" => array(
                        "value" => trim(substr($line, 148, 12)),
                        "type" => "string",
                        "required" => 0
                    ),
                    "Data" => array(
                        "value" => $dataSanzione,
                        "type" => "date",
                        "required" => 0
                    ),
                    "Targa" => array(
                        "value" => trim(substr($line, 166, 12)),
                        "type" => "string",
                        "required" => 0
                    ),
                );
                break;
            case "M": //numero di matricola
                $a_N4['M'] = array(
                    "Matricola" => array(
                        "value" => trim(substr($line, 146, 10)),
                        "type" => "string",
                        "required" => 0
                    ),
                );
                break;
        }
        return $a_N4;
    }

    public function readN5($line)
    {
        $this->a_count['N5']++;
        $impInt = (int) substr($line, 38, 13);
        $impDec = (int) substr($line, 51, 2);
        if (!$impInt > 0)
            $impInt = 0;
        if (!$impDec > 0)
            $impDec = 0;
        return array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            //"N5"
            "CodiceProvinciaComune" => array(
                "value" => (int) substr($line, 2, 6),
                "type" => "numeric",
                "required" => 0
            ),
            //Codice Provincia/Comune di iscrizione a ruolo (Codifica CNC)
            "ProgressivoMinuta" => array(
                "value" => (int) substr($line, 8, 2),
                "type" => "numeric",
                "required" => 0
            ),
            //Progr minuta
            "RecordRuolo" => array(
                "value" => (int) substr($line, 10, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record compresi il tipo record N1 e N5
            "RecordN2" => array(
                "value" => (int) substr($line, 17, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record N2
            "RecordN3" => array(
                "value" => (int) substr($line, 24, 7),
                "type" => "numeric",
                "required" => 0
            ),
            //Totale record N3
            "RecordN4" => array(
                "value" => (int) substr($line, 31, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record N4
            "TotaleImposta" => array(
                "value" => (float) ($impInt . "." . $impDec),
                "type" => "numeric",
                "required" => 1
            ), //Totale Imposta
        );
    }

    public function readN9($line)
    {
        $this->a_count['N9']++;
        return array(
            "RecordType" => array(
                "value" => substr($line, 0, 2),
                "type" => "string",
                "required" => 1
            ),
            //"N9"
            "CodiceEnte" => array(
                "value" => (int) substr($line, 2, 5),
                "type" => "numeric",
                "required" => 1
            ),
            //Codice Ente impositore secondo la codifica CNC
            "RecordTotali" => array(
                "value" => (int) substr($line, 7, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record
            "RecordN1" => array(
                "value" => (int) substr($line, 14, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record N1
            "RecordN2" => array(
                "value" => (int) substr($line, 21, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record N2
            "RecordN3" => array(
                "value" => (int) substr($line, 28, 7),
                "type" => "numeric",
                "required" => 0
            ),
            //Totale record N3
            "RecordN4" => array(
                "value" => (int) substr($line, 35, 7),
                "type" => "numeric",
                "required" => 1
            ),
            //Totale record N4
            "RecordN5" => array(
                "value" => (int) substr($line, 42, 7),
                "type" => "numeric",
                "required" => 1
            ), //Totale record N5
        );
    }

    public function saveFile($filePath)
    {
        chmod($filePath, 0777);
        $OutFile = fopen($filePath, "w");
        if (!$OutFile)
            die("Unable to open file!");

        fwrite($OutFile, $this->file);
        fclose($OutFile);
    }

    public function showFile()
    {
        echo "<pre>" . $this->file . "</pre>";
    }

    public function getErrorHtml($html)
    {
        return '<div class="col-lg-2 RowLabelError">ERRORE ' . $this->a_countCheck['errori'] . '</div>
            <div class="col-lg-10 RgetScartoHtmlowInput"><b>' . $html . '</b></div>
            <div class="clean_row HSpace1"></div>';
    }

    public function getNoticeHtml($html, $text, $coobbligato = false)
    {
        if($coobbligato){
            $class = "RowLabelCoobbligato";
            $count = $this->a_countCheck['coobbligati'];
        }
        else{
            $class = "RowLabel";
            $count = $this->a_countCheck['omonimie'];
        }
            
        return '<div class="col-lg-2 '.$class.'">' . $text . ' ' . $count . '</div>
            <div class="col-lg-10 RowInput"><b title="' . $html . '">' . $html . '</b></div>
            <div class="clean_row HSpace1"></div>';
    }

    public function getScartoHtml($html)
    {
        return '<div class="col-lg-2 RowLabelScarto">SCARTO ' . $this->a_countCheck['scarti'] . '</div>
            <div class="col-lg-10 RowInput"><b title="' . $html . '">' . $html . '</b></div>
            <div class="clean_row HSpace1"></div>';
    }

    public function getImportatoHtml($html)
    {
        return '<div class="col-lg-2 RowLabelImport">IMPORTATO ' . $this->a_countCheck['importato'] . '</div>
            <div class="col-lg-10 RowInput"><b title="' . $html . '">' . $html . '</b></div>
            <div class="clean_row HSpace1"></div>';
    }

    public function getImportatoDiversoHtml($html)
    {
        return '<div class="col-lg-2 RowLabelScarto">IMPORTATO ' . $this->a_countCheck['importato'] . '</div>
            <div class="col-lg-10 RowInput"><b title="' . $html . '">' . $html . '</b></div>
            <div class="clean_row HSpace1"></div>';
    }

    public function checkRecordType()
    {
        $a_check = array(
            "RecordTypes" => array(),
            "Check" => true,
            "Html" => ""
        );
        if (!isset($this->a_290['N0']))
            $a_check['RecordTypes'][] = "N0";
        if (!isset($this->a_290['N1']))
            $a_check['RecordTypes'][] = "N1";
        if (!isset($this->a_290['N2']))
            $a_check['RecordTypes'][] = "N2";
        if (!isset($this->a_290['N4']))
            $a_check['RecordTypes'][] = "N4";
        if (!isset($this->a_290['N5']))
            $a_check['RecordTypes'][] = "N5";
        if (!isset($this->a_290['N9']))
            $a_check['RecordTypes'][] = "N9";

        if (count($a_check['RecordTypes']) > 0) {
            $a_check['Check'] = false;
            $this->a_countCheck['errori']++;

            $check = 0;
            $htmlError = "Tipi di record assenti: ";
            foreach ($a_check['RecordTypes'] as $key => $value) {
                if ($check != 0)
                    $htmlError .= ", ";
                $htmlError .= $value;
                if ($check == 0)
                    $check = 1;
            }

            $a_check['Html'] = $this->getErrorHtml($htmlError);
        }

        return $a_check;
    }

    public function checkCodiceEnte()
    {
        $a_check = array(
            "Check" => true,
            "Html" => ""
        );
        if (!$this->a_params['290Code'] > 0) {
            $this->a_countCheck['errori']++;
            $a_check['Html'] .= $this->getErrorHtml("Il Codice 290 non risulta impostato nei parametri Dati Ente");
        }
        if ($this->a_290['N0']['CodiceEnte']['value'] != $this->a_290['N9']['CodiceEnte']['value']) {
            $this->a_countCheck['errori']++;
            $a_check['Html'] .= $this->getErrorHtml("Il Codice ente impositore N0 (" . $this->a_290['N0']['CodiceEnte']['value'] . ") è diverso dal Codice ente impositore N9 (" . $this->a_290['N9']['CodiceEnte']['value'] . ")");
        }
        if ($this->a_290['N0']['CodiceEnte']['value'] != $this->a_params['290Code']) {
            $this->a_countCheck['errori']++;
            $a_check['Html'] .= $this->getErrorHtml("Il Codice 290 impostato nei parametri Dati Ente (" . (int) $this->a_params['290Code'] . ") non corrisponde con il Codice ente impositore (" . $this->a_290['N0']['CodiceEnte']['value'] . ")");
        }
        if ($a_check['Html'] != "")
            $a_check['Check'] = false;
        return $a_check;
    }

    public function checkCodiceProvinciaComune()
    {
        $a_check = array(
            "Check" => true,
            "Html" => ""
        );

        foreach ($this->a_290['N1'] as $n1 => $a_n1) {
            if ($a_n1['CodiceProvinciaComune']['value'] != $this->a_290['N5'][$n1]['CodiceProvinciaComune']['value']) {
                $this->a_countCheck['errori']++;
                $a_check['Html'] .= $this->getErrorHtml("Ruolo " . ($n1 + 1) . ": Il Codice Provincia Comune N1 è diverso dal Codice Provincia Comune N5");
            }
        }

        if ($a_check['Html'] != "")
            $a_check['Check'] = false;

        return $a_check;
    }

    public function checkProgressivoMinuta()
    {
        $a_check = array(
            "Check" => true,
            "Html" => ""
        );

        foreach ($this->a_290['N1'] as $n1 => $a_n1) {
            if ($a_n1['ProgressivoMinuta']['value'] != $this->a_290['N5'][$n1]['ProgressivoMinuta']['value']) {
                $this->a_countCheck['errori']++;
                $a_check['Html'] .= $this->getErrorHtml("Ruolo " . ($n1 + 1) . ": Il Progressivo minuta N1 è diverso dal Progressivo minuta N5");
            }
        }

        if ($a_check['Html'] != "")
            $a_check['Check'] = false;

        return $a_check;
    }

    public function checkTotaliRecord()
    {
        $a_check = array(
            "Check" => true,
            "Html" => ""
        );

        $totalRecords = 0;
        foreach ($this->a_count as $key => $value) {
            $totalRecords += $value;
            if ($key != "N0" && $key != "N9" && $key != "NR" && $this->a_290['N9']['Record' . $key]['value'] != $value) {
                $this->a_countCheck['errori']++;
                $a_check['Html'] .= $this->getErrorHtml("Il totale di righe " . $key . " del file (" . $value . ") non corrisponde al totale in N9 (" . $this->a_290['N9']['Record' . $key]['value'] . ")");
            }
        }

        if ($this->a_290['N9']['RecordTotali']['value'] != $totalRecords) {
            $this->a_countCheck['errori']++;
            $a_check['Html'] .= $this->getErrorHtml("Il totale di righe del file (" . $totalRecords . ") non corrisponde al totale in N9 (" . $this->a_290['N9']['RecordTotali']['value'] . ")");
        }

        if ($a_check['Html'] != "")
            $a_check['Check'] = false;

        return $a_check;
    }

    public function getHtmlFileChecks()
    {
        flush();
        ob_flush();
        $html = "";
        $html .= $this->checkRecordType()['Html']; //Controllo presenza di tutti i tipi di linea obbligatori
        if ($html != "")
            return $html;

        $html .= $this->checkCodiceEnte()['Html']; //Controllo Codice ente impositore e codice 290 N0 N9
        $html .= $this->checkCodiceProvinciaComune()['Html']; //Controllo Codice Provincia Comune N1 N5
        $html .= $this->checkProgressivoMinuta()['Html']; //Controllo Progressivo Minuta N1 N5
        $html .= $this->checkTotaliRecord()['Html']; //Controllo corrispondenza dei totali dei record
        $html .= $this->getHtmlErrorRequired(); //Controllo campi richiesti
        $html .= $this->getHtmlCodiciTributo(); //Controllo campi richiesti

        $html .= $this->getHtmlPartita();
        $html .= $this->getHtmlUtente("N2");
        $html .= $this->getHtmlUtente("N3");

        return $html;

    }

    public function getHtmlModel()
    {
        $html = "";
        $a_header = array(
            array('cols' => 3, 'text' => 'PARTITA CONTABILE', 'class' => 'RowLabel'),
            array('cols' => 3, 'text' => 'UTENTE', 'class' => 'RowLabel'),
            array('cols' => 2, 'text' => 'CF/PI', 'class' => 'RowLabel'),
            array('cols' => 3, 'text' => 'STATUS', 'class' => 'RowLabel')
        );
        $html .= $this->getRowHtml($a_header, "");

        foreach ($this->a_model as $row => $a_row)
            $html .= $this->getRowHtml($this->setRowCheck($a_row), $row + 1);

        return $html;
    }

    public function getRowHtml($a_html, $rowNumber)
    {
        $html = '<div class="col-lg-1 RowLabel text-center">' . $rowNumber . '</div>';

        foreach ($a_html as $key => $a_value) {
            if (isset($a_value['class']))
                $class = $a_value['class'];
            else
                $class = "RowInput";
            if (isset($a_value['title']))
                $title = $a_value['title'];
            else
                $title = $a_value['text'];
            $html .= '<div class="col-lg-' . $a_value['cols'] . ' ' . $class . '"><b title="' . $title . '">' . $a_value['text'] . '</b></div>';
        }
        $html .= '<div class="clean_row HSpace1"></div>';
        return $html;
    }

    public function setRowCheck($a_row)
    {
        $class = $a_row['STATUS']['class'];
        $title = $a_row['STATUS']['msg'];
        $status = $a_row['STATUS']['status'];

        $a_html = array(
            array('cols' => 3, 'text' => $a_row['INFORMAZIONI_CARTELLA']),
            array('cols' => 3, 'text' => $a_row['COGNOME_DITTA'] . " " . $a_row['NOME']),
            array('cols' => 2, 'text' => $a_row['CODICE_FISCALE'] . $a_row['PARTITA_IVA']),
            array('cols' => 3, 'text' => $status, 'title' => $title, 'class' => $class)
        );

        return $a_html;
    }

    public function getHtmlCodiciTributo()
    {
        $html = "";
        $this->getCodiciTributo();
        foreach ($this->a_codiciTributo['290'] as $codice => $countCodice) {
            if (!isset($this->a_codiciTributo['DB'][$codice])) {
                $this->a_countCheck['errori']++;
                $html .= $this->getErrorHtml("Codice Tributo " . $codice . " da registrare su Gitco per importazione!");
            }
        }
        return $html;
    }

    public function check290()
    {
        $a_position = array(
            "N1" => null,
            "N2" => null,
            "N3" => null,
            "N4" => null,
            "Extra" => null
        );
        if (isset($this->a_290["N0"]))
            $this->checkRequired("N0", $this->a_290["N0"], $a_position);
        if (isset($this->a_290["N9"]))
            $this->checkRequired("N9", $this->a_290["N9"], $a_position);
        if (isset($this->a_290["N1"])) {
            foreach ($this->a_290["N1"] as $a_position['N1'] => $a_n1) {
                $this->checkRequired("N1", $a_n1, $a_position);
                if (isset($this->a_290["N2"][$a_position['N1']])) {
                    foreach ($this->a_290["N2"][$a_position['N1']] as $a_position['N2'] => $a_n2) {
                        if (isset($this->a_290["N4"][$a_position['N1']][$a_position['N2']])) {
                            $InformazioniCartella = $this->setInformazioniCartella($this->a_290["N4"][$a_position['N1']][$a_position['N2']]);
                            $ImportoTotale290 = 0;
                            foreach ($this->a_290["N4"][$a_position['N1']][$a_position['N2']] as $a_position['N4'] => $a_n4) {
                                $this->a_290["N4"][$a_position['N1']][$a_position['N2']][$a_position['N4']]['InformazioniCartella']['value'] = $InformazioniCartella;
                                $ImportoTotale290 += $this->a_290["N4"][$a_position['N1']][$a_position['N2']][$a_position['N4']]['Imposta']['value'];
                                $this->checkRequired("N4", $a_n4, $a_position);
                                $a_position['Extra'] = $a_n4['TipoInformazioni']['value'];
                                if (isset($a_n4[$a_position['Extra']]))
                                    $this->checkRequired("N4", $a_n4[$a_position['Extra']], $a_position);
                                $a_position['Extra'] = null;
                            }
                            $this->checkInformazioniCartella($InformazioniCartella, $ImportoTotale290, $a_position);
                            $a_position['N4'] = null;
                        } else
                            $this->a_checkUtente['N2']['NO_N4'][] = $this->getPositionArray($a_position);


                        $this->checkUtente("N2", $a_n2, $a_position);
                        $this->checkRequired("N2", $a_n2['Utente'], $a_position);
                        $a_position['Extra'] = 'Residenza';
                        $this->checkRequired("N2", $a_n2['Residenza'], $a_position);
                        $a_position['Extra'] = 'Recapito';
                        $this->checkRequired("N2", $a_n2['Recapito'], $a_position);
                        $a_position['Extra'] = null;
                        if(isset($this->a_290["N3"][$a_position['N1']][$a_position['N2']])){
                           foreach ($this->a_290["N3"][$a_position['N1']][$a_position['N2']] as $a_position['N3']=>$a_n3){
                               $this->checkUtente("N3", $a_n3, $a_position);
                               $this->checkRequired("N3", $a_n3['Utente'], $a_position);
                               $a_position['Extra'] = 'Residenza';
                               $this->checkRequired("N3", $a_n3['Residenza'], $a_position);
                               $a_position['Extra'] = 'Recapito';
                               $this->checkRequired("N3", $a_n3['Recapito'], $a_position);
                               $a_position['Extra'] = null;
                           }
                           $a_position['N3'] = null;
                       }
                    }
                }
            }
        }
    }

    public function setInformazioniCartella($a_n4)
    {
        $a_temp = array("InformazioniCartella" => null, "AnnoTributo" => null);
        $a_infoCartella = array();
        $contInfo = 0;
        $contAnno = 0;
        foreach ($a_n4 as $key => $a_row) {
            if (is_null($a_temp["InformazioniCartella"])) {
                $a_temp = array("InformazioniCartella" => $a_row['InformazioniCartella']['value'], "AnnoTributo" => $a_row['AnnoTributo']['value']);
                $a_infoCartella[$contInfo]['InformazioniCartella'] = $a_row['InformazioniCartella']['value'];
                $a_infoCartella[$contInfo]['AnnoTributo'][$contAnno] = $a_row['AnnoTributo']['value'];
            }

            if ($a_row['InformazioniCartella']['value'] == $a_temp["InformazioniCartella"] && $a_row["AnnoTributo"]['value'] != $a_temp["AnnoTributo"]) {
                $a_temp["AnnoTributo"] = $a_row['AnnoTributo']['value'];
                $contAnno++;
            } else if ($a_row['InformazioniCartella']['value'] != $a_temp["InformazioniCartella"]) {
                $a_temp = array("InformazioniCartella" => $a_row['InformazioniCartella']['value'], "AnnoTributo" => $a_row['AnnoTributo']['value']);
                $contInfo++;
                $contAnno = 0;
            }
            $a_infoCartella[$contInfo]['InformazioniCartella'] = $a_row["InformazioniCartella"]['value'];
            $a_infoCartella[$contInfo]['AnnoTributo'][$contAnno] = $a_row["AnnoTributo"]['value'];
        }

        $InformazioniCartella = "";
        for ($x = 0; $x < count($a_infoCartella); $x++) {
            if ($x > 0)
                $InformazioniCartella .= " - ";

            $InformazioniCartella .= $a_infoCartella[$x]['InformazioniCartella'];
            if (count($a_infoCartella[$x]['AnnoTributo']) > 1)
                $InformazioniCartella .= " ANNI";
            else
                $InformazioniCartella .= " ANNO";

            for ($x_anno = 0; $x_anno < count($a_infoCartella[$x]['AnnoTributo']); $x_anno++)
                $InformazioniCartella .= " " . $a_infoCartella[$x]['AnnoTributo'][$x_anno];
        }
        return $InformazioniCartella;
    }

    public function getHtmlErrorRequired()
    {
        $requiredErrors = "";
        foreach ($this->a_required as $RecordType => $a_fields) {
            switch ($RecordType) {
                case "N0":
                case "N9":
                    foreach ($a_fields as $missingField => $a_field) {
                        $this->a_countCheck['errori']++;
                        $requiredErrors .= $this->getErrorHtml($this->writeRequired($RecordType, $missingField, $a_field));
                    }
                    break;
                case "N1":
                case "N5":
                    foreach ($a_fields as $n1 => $a_n1) {
                        foreach ($a_n1 as $missingField => $a_field) {
                            $this->a_countCheck['errori']++;
                            $requiredErrors .= $this->getErrorHtml($this->writeRequired($RecordType, $missingField, $a_field));
                        }
                    }
                    break;
                case "N2":
                    foreach ($a_fields as $n1 => $a_n1) {
                        foreach ($a_n1 as $n2 => $a_n2) {
                            $this->a_countCheck['scarti']++;
                            $this->a_290['N2'][$n1][$n2]['Check']['scarto'] = 1;
                            foreach ($a_n2 as $missingField => $a_field) {
                                $requiredErrors .= $this->getScartoHtml($this->writeRequired($RecordType, $missingField, $a_field));
                            }
                        }
                    }
                    break;
                default:
                    foreach ($a_fields as $n1 => $a_n1) {
                        foreach ($a_n1 as $n2 => $a_n2) {
                            $this->a_countCheck['scarti']++;
                            $this->a_290['N2'][$n1][$n2]['Check']['scarto'] = 1;
                            foreach ($a_n2 as $n34 => $a_n34) {
                                foreach ($a_n34 as $missingField => $a_field) {
                                    $requiredErrors .= $this->getScartoHtml($this->writeRequired($RecordType, $missingField, $a_field));
                                }
                            }
                        }
                    }
            }
        }
        return $requiredErrors;
    }

    public function writeRequired($RecordType, $missingField, $a_field)
    {
        $html = "'" . $missingField . "' assente in " . $RecordType;
        if ($a_field['position']['Extra'] != "")
            $html .= " " . $a_field['position']['Extra'];
        if ($RecordType == "N4") {
            $html .= " - Codice tributo " . $this->a_290['N4'][$a_field['position']['N1']][$a_field['position']['N2']][$a_field['position']['N4']]['CodiceTributo']['value'];
            $html .= " / " . $this->a_290['N4'][$a_field['position']['N1']][$a_field['position']['N2']][$a_field['position']['N4']]['AnnoTributo']['value'];
        }
        $html .= " - (" . ($a_field['position']['N2'] + 1) . ") " . $a_field['Utente'] . " - " . $a_field['InformazioniCartella'];
        return $html;
    }

    public function getHtmlUtente($recordType)
    {
        $N2error = "";
        foreach ($this->a_checkUtente[$recordType] as $checkType => $a_checks) {
            foreach ($a_checks as $key => $a_check) {
                switch ($checkType) {
                    case "CF":
                        $html = "Il Codice Fiscale " . $a_check['CF'] . " non è corretto";
                        break;
                    case "PI":
                        $html = "La Partita Iva " . $a_check['PI'] . " non è corretta";
                        break;
                    case "DB":
                        $linkUtente = "<a href='" . WEB_ROOT . "/anagrafe/dati_soggetto.php?";
                        $linkUtente .= "p=" . $a_check['ID'] . "&c=" . $this->a_params['CC'] . "'>" . $a_check['Comune_ID'] . "</a>";

                        $html = "Utente presente in archivio. ID " . $linkUtente . " (" . $a_check['ID'] . ")";
                        break;
                    case "NO_N4":
                        $html = "N4 ASSENTI";
                        break;
                }

                $coobbligato = false;
                $userType = "UTENTE";
                if ($a_check['position']['Extra'] != "")
                    $html .= " " . $a_check['position']['Extra'];
                if ($recordType == "N2")
                    $html .= " - [ N2 " . ($a_check['position']['N2'] + 1) . " ] " . $a_check['Utente'] . " - " . $a_check['InformazioniCartella'];
                else if ($recordType == "N3") {
                    $coobbligato = $this->a_290['N3'][$a_check['position']['N1']][$a_check['position']['N2']][$a_check['position']['N3']]['Utente'];
                    $utenteN3 = $coobbligato['Cognome']['value'].$coobbligato['Ditta']['value']." ".$coobbligato['Nome']['value'];
                    $html .= " - [ N2 " . ($a_check['position']['N2'] + 1) . " - N3 " . ($a_check['position']['N3'] + 1) . " ] " . $utenteN3 . " - " . $a_check['InformazioniCartella'];
                    $coobbligato = true;
                    $userType = "COOBBLIGATO";
                }

                if ($checkType == "DB") {
                    
                    if ($recordType == "N2") {
                        $this->a_countCheck['omonimie']++;
                        $this->a_290['N2'][$a_check['position']['N1']][$a_check['position']['N2']]['Check']["omonimia"] = 1;
                        $this->a_290['N2'][$a_check['position']['N1']][$a_check['position']['N2']]['Check']["Utente_ID"] = $a_check['ID'];
                    } else if ($recordType == "N3") {
                        $this->a_countCheck['coobbligati']++;
                        $this->a_290['N3'][$a_check['position']['N1']][$a_check['position']['N2']][$a_check['position']['N3']]['Check']["omonimia"] = 1;
                        $this->a_290['N3'][$a_check['position']['N1']][$a_check['position']['N2']][$a_check['position']['N3']]['Check']["Utente_ID"] = $a_check['ID'];
                    }

                    $N2error .= $this->getNoticeHtml($html, $userType, $coobbligato);
                } else {
                    $this->a_countCheck['scarti']++;
                    $this->a_290['N2'][$a_check['position']['N1']][$a_check['position']['N2']]['Check']['scarto'] = 1;
                    $N2error .= $this->getScartoHtml($html);
                }

            }
        }
        return $N2error;
    }

    public function getHtmlPartita()
    {
        $partitaHtml = "";
        foreach ($this->a_checkPartita as $key => $a_check) {
            $linkPartita = "<a href='" . WEB_ROOT . "/coattiva/gestione_partita.php?";
            $linkPartita .= "partita=" . $a_check['ID'] . "&c=" . $this->a_params['CC'] . "&a='>" . $a_check['Comune_ID'] . "</a>";

            $html = "Partita presente in archivio. ID " . $linkPartita . " (" . $a_check['ID'] . ")";
            $html .= " - Importo Partita " . number_format($a_check['Importo_Partita'], 2, ",", "") . " - Importo 290 " . number_format($a_check['Importo_290'], 2, ",", "");
            $html .= " - [ N2 " . ($a_check['position']['N2'] + 1) . " ] " . $a_check['Utente'] . " - " . $a_check['InformazioniCartella'];

            $this->a_290['N2'][$a_check['position']['N1']][$a_check['position']['N2']]['Check']['scarto'] = 1;
            $this->a_countCheck['importato']++;
            if ((int) $a_check['Ruolo_ID'] == (int) $this->a_params['Ruolo_ID']) {
                $partitaHtml .= $this->getImportatoHtml($html);
            } else {
                $partitaHtml .= $this->getImportatoDiversoHtml($html);
            }

        }

        return $partitaHtml;
    }

    public function getPositionArray($a_position)
    {
        if (!isset($this->a_290['N4'][$a_position['N1']][$a_position['N2']]))
            $infoCartella = "";
        else
            $infoCartella = $this->a_290['N4'][$a_position['N1']][$a_position['N2']][0]['InformazioniCartella']['value'];
        return array(
            "position" => $a_position,
            "InformazioniCartella" => $infoCartella,
            "Utente" => $this->a_290['N2'][$a_position['N1']][$a_position['N2']]['Utente']['Cognome']['value'] .
            $this->a_290['N2'][$a_position['N1']][$a_position['N2']]['Utente']['Ditta']['value'] . " " .
            $this->a_290['N2'][$a_position['N1']][$a_position['N2']]['Utente']['Nome']['value'],
            "CF" => $this->a_290['N2'][$a_position['N1']][$a_position['N2']]['Utente']['CF']['value'],
            "PI" => $this->a_290['N2'][$a_position['N1']][$a_position['N2']]['Utente']['PI']['value']
        );
    }

    public function checkRequired($recordType, $a_row, $a_position)
    {
        foreach ($a_row as $key => $a_elem) {
            if ($this->checkRequiredField($a_elem)) {
                switch ($recordType) {
                    case "N0":
                    case "N9":
                        $this->a_required[$recordType][$key] = $this->getPositionArray($a_position);
                        break;
                    case "N1":
                    case "N5":
                        $this->a_required[$recordType][$a_position['N1']][$key] = $this->getPositionArray($a_position);
                        break;
                    case "N2":
                        $this->a_required[$recordType][$a_position['N1']][$a_position['N2']][$key] = $this->getPositionArray($a_position);
                        break;
                    default:
                        $this->a_required[$recordType][$a_position['N1']][$a_position['N2']][$a_position[$recordType]][$key] = $this->getPositionArray($a_position);
                }
            }
        }
    }

    public function checkInformazioniCartella($InformazioniCartella, $ImportoTotale290, $a_position)
    {

        $query = "SELECT PA.ID, PA.Comune_ID, PA.Tipo, SUM(T.Imposta) AS Importo_Codici, PA.Ruolo_ID FROM partita_tributi PA ";
        $query .= "JOIN tributo T ON PA.ID=T.Partita_ID ";
        $query .= "WHERE PA.CC='" . $this->a_params['CC'] . "' AND T.Info_Cartella=\"" . $InformazioniCartella . "\" GROUP BY PA.ID";

        $a_db = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if (!is_null($a_db)) {
            $a_extra = array(
                "ID" => $a_db['ID'],
                "Comune_ID" => $a_db['Comune_ID'],
                "Tipo" => $a_db['Tipo'],
                "Ruolo_ID" => $a_db['Ruolo_ID'],
                "Importo_290" => $ImportoTotale290,
                "Importo_Partita" => $a_db['Importo_Codici']
            );
            $this->a_checkPartita[] = array_merge($this->getPositionArray($a_position), $a_extra);
        }
    }

    public function checkUtente($recordType, $a_utente, $a_position)
    {
        if ($a_utente['Utente']['NaturaGiuridica']['value'] == 1) {
            if ($this->cls_registry->decode_CF($a_utente['Utente']['CF']['value']) === false) {
                $this->a_checkUtente[$recordType]['CF'][] = $this->getPositionArray($a_position);
                return true;
            }
            $query = "SELECT ID, Comune_ID FROM utente WHERE CC_Comune='" . $this->a_params['CC'] . "' AND Codice_Fiscale='" . $a_utente['Utente']['CF']['value'] . "'";
        } else if ($a_utente['Utente']['NaturaGiuridica']['value'] == 2) {
            if (
                strlen($a_utente['Utente']['PI']['value']) != 11
                || preg_match(
                    "/[^0-9]/",
                    $a_utente['Utente']['PI']['value']
                )
                || !(int) $a_utente['Utente']['PI']['value'] > 0
            ) {
                $this->a_checkUtente[$recordType]['PI'][] = $this->getPositionArray($a_position);
                return true;
            }
            $query = "SELECT ID, Comune_ID FROM utente WHERE CC_Comune='" . $this->a_params['CC'] . "' AND Partita_Iva='" . $a_utente['Utente']['PI']['value'] . "'";
        }

        $a_db = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if (!is_null($a_db)) {
            $a_extra = array("ID" => $a_db['ID'], "Comune_ID" => $a_db['Comune_ID']);
            $this->a_checkUtente[$recordType]['DB'][] = array_merge($this->getPositionArray($a_position), $a_extra);
        }
    }

    public function checkRequiredField($a_elem)
    {
        $check = 0;
        if (isset($a_elem['required']) && $a_elem['required'] == 1) {
            switch ($a_elem['type']) {
                case "string":
                    if ($a_elem['value'] == "")
                        $check = 1;
                    break;
                case "numeric":
                    if (!$a_elem['value'] > 0)
                        $check = 1;
                    break;
                case "date":
                    if (is_null($a_elem['value']))
                        $check = 1;
                    break;
            }
        }

        if ($check == 1)
            return true;

        return false;
    }

    public function getHtmlImportsList($a_imports, $a_usersAdmin, $a_importStatus, $a_importTypes)
    {
        $html = '<div class="col-lg-1 RowLabel RowLabelHeight3">CC</div>
                <div class="col-lg-2 RowLabel RowLabelHeight3">Tipo file</div>
                <div class="col-lg-3 RowLabel RowLabelHeight3">File</div>
                <div class="col-lg-2 RowLabel RowLabelHeight3">Status</div>
                <div class="col-lg-2 RowLabel RowLabelHeight3">Status Info</div>
                <div class="col-lg-1 RowLabel RowLabelHeight3">Posizioni</div>
                <div class="col-lg-1 RowLabel RowLabelHeight3"></div>
                ';
        $html .= '<div class="HSpace4 clean_row"></div>';
        foreach ($a_imports as $key => $a_import) {
            $info = "";
            if ($a_import['Import_User_Id'] > 0) {
                $info = $a_usersAdmin[$a_import['Import_User_Id']]['User'];
                if (!is_null($a_import['Import_Datetime']))
                    $info .= " - " . date('d/m/Y H:i', strtotime($a_import['Import_Datetime']));
            } else if ($a_import['Upload_User_Id'] > 0) {
                $info = $a_usersAdmin[$a_import['Upload_User_Id']]['User'];
                if (!is_null($a_import['Upload_Datetime']))
                    $info .= " - " . date('d/m/Y H:i', strtotime($a_import['Upload_Datetime']));
            }

            $positions = "";
            if ($a_import['Total_Positions'] > 0) {
                if (is_null($a_import['Imported_Positions']))
                    $a_import['Imported_Positions'] = 0;
                $positions = $a_import['Imported_Positions'] . "/" . $a_import['Total_Positions'];
            }

            $html .= '
            <div class="col-lg-1 RowInput RowInputHeight3 RowCell">' . $a_import['CC'] . '</div>
            <div class="col-lg-2 RowInput RowInputHeight3 RowCell">' . $a_importTypes[$a_import['Import_Type_Id']]['Name'] . '</div>
            <div class="col-lg-3 RowInputHeight3 RowInput RowCell">
                <a title="' . $a_import['Filename'] . '" href="' . DUENOVANTA_WEB . '/toImport/' . $a_import['Filename'] . '">' . $a_import['Name'] . '</a>
            </div>
            <div class="col-lg-2 RowInput RowInputHeight3 RowCell">' . $a_importStatus[$a_import['Import_Status_Id']]['Name'] . '</div>
            <div class="col-lg-2 RowInput RowInputHeight3 RowCell">' . $info . '</div>
            <div class="col-lg-1 RowInput RowInputHeight3 RowCell">' . $positions . '</div>
            <div class="col-lg-1 RowInput RowInputBtnHeight3 RowCell text-center">
                <input type="button" class="btn btn-gitco 290_details" style="width: 100%; background-color: #356bc1; color:white; font-weight: bold;" id="' . $a_import['Id'] . '" value="Visualizza">
            </div>
            
            ';
        }
        return $html;
    }

    public function getHtmlPrintImportsList($a_imports, $a_usersAdmin, $a_importStatus, $a_importTypes)
    {
        $html = '<div class="col-lg-2 RowLabel RowLabelHeight3">Tipo file</div>
                <div class="col-lg-4 RowLabel RowLabelHeight3">File</div>
                <div class="col-lg-2 RowLabel RowLabelHeight3">Status File</div>
                <div class="col-lg-2 RowLabel RowLabelHeight3" style="text-align: center;">Stampe</div>
                <div class="col-lg-2 RowLabel RowLabelHeight3">Status Stampa</div>
                ';
        $html .= '<div class="HSpace4 clean_row"></div>';
        foreach ($a_imports as $key => $a_import) {
            $info = "";
            if ($a_import['Import_User_Id'] > 0) {
                $info = $a_usersAdmin[$a_import['Import_User_Id']]['User'];
                if (!is_null($a_import['Import_Datetime']))
                    $info .= " - " . date('d/m/Y H:i', strtotime($a_import['Import_Datetime']));
            } else if ($a_import['Upload_User_Id'] > 0) {
                $info = $a_usersAdmin[$a_import['Upload_User_Id']]['User'];
                if (!is_null($a_import['Upload_Datetime']))
                    $info .= " - " . date('d/m/Y H:i', strtotime($a_import['Upload_Datetime']));
            }

            $html .= '
            <div class="col-lg-2 RowInput RowInputHeight3 RowCell">' . $a_importTypes[$a_import['Import_Type_Id']]['Name'] . '</div>
            <div class="col-lg-4 RowInputHeight3 RowInput RowCell">
                <a title="' . $a_import['Filename'] . '" href="' . DUENOVANTA_WEB . '/toImport/' . $a_import['Filename'] . '">' . $a_import['Name'] . '</a>
            </div>
            <div class="col-lg-2 RowInput RowInputHeight3 RowCell">' . $a_importStatus[$a_import['Import_Status_Id']]['Name'] . '</div>';

            if($a_import['Import_Status_Id'] == 2){
                if($a_import['Flag_Print_Def'] == 0)
                $html .= '<div class="col-lg-1 RowInput RowInputBtnHeight3 RowCell text-center">
                            <input type="button" class="btn btn-gitco 290_print" style="width: 100%; background-color: #356bc1; color:white; font-weight: bold;" id="' . $a_import['Id'] . '" value="Provvisoria" onclick="print(`prov`,`' . $a_import['Id'] . '`)">
                        </div>
                        <div class="col-lg-1 RowInput RowInputBtnHeight3 RowCell text-center">
                            <input type="button" class="btn btn-gitco 290_print" style="width: 100%; background-color: #356bc1; color:white; font-weight: bold;" id="' . $a_import['Id'] . '" value="Definitiva" onclick="print(`def`,`' . $a_import['Id'] . '`)">
                        </div>
                        <div class="col-lg-2 RowInput RowInputHeight3 RowCell">Elenco non stampato</div>';
                else
                    $html .= '<div class="col-lg-1 RowInput RowInputBtnHeight3 RowCell text-center">
                                <input type="button" class="btn btn-gitco" style="width: 100%; background-color: #e60000; color:black; font-weight: bold;" id="' . $a_import['Id'] . '" value="Provvisoria" disabled>
                            </div>
                            <div class="col-lg-1 RowInput RowInputBtnHeight3 RowCell text-center">
                                <input type="button" class="btn btn-gitco" style="width: 100%; background-color: #e60000; color:black; font-weight: bold;" id="' . $a_import['Id'] . '" value="Definitiva" disabled>
                            </div>
                            <div class="col-lg-2 RowInput RowInputHeight3 RowCell">Elenco Stampato</div>';
                }
            else{
                $html .= '<div class="col-lg-4 RowInput RowInputBtnHeight3 RowCell text-center"><b>Importazione non completata</b></div>';
            }

        }
        return $html;
    }

}