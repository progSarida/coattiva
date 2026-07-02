<?php
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_math.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_elaborazioniUtils.php";

class cls_GP
{
    public $cls_db;
    public $cls_date;
    public $cls_math;
    public $cls_elab;

    public function __construct()
    {
        $this->cls_db = new cls_db();
        $this->cls_date = new cls_DateTimeI("IT", false);
        $this->cls_math = new cls_math();
        $this->cls_elab = new cls_elaborazioniUtils();
        $this->cls_help = new cls_help();
    }

    public function trova_ufficio($c, $tipo_ufficio)
    {
        $query = "SELECT ID FROM ufficio_comune WHERE Tipo ='" . $tipo_ufficio . "' AND CC = '" . $c . "'";

        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        if (count($result) != 1)
            return count($result) . " ";
        else
            return "ID " . $result[0]['ID'];

    }

    public function intestazione_gestore($servizio, $nome_comune, $gestore)
    {
        $tipo = $gestore->Tipo;
        if ($tipo == "Ufficio") return false;
        $intestazione = array();

        $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '" . $gestore->CC . "'";
        $comune_gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "comuni_lista");//new comune($gestore->CC);

        $query = "SELECT * FROM province_lista WHERE Pro_Codice='" . $comune_gestore->Com_Codice_Provincia . "'";
        $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "province_lista");//safe_query($query);

        $provincia = $result->Pro_Nome;
        unset($comune_gestore);

        if ($tipo == "Comune") {
            //RIGA 1
            $intestazione['Riga1'] = $gestore->Denominazione;

            //RIGA 2
            $intestazione['Riga2'] = "Provincia di " . $provincia;

            //RIGA 3
            $intestazione['Riga3'] = $this->riga_indirizzo($gestore);

            //RIGA 4
            $intestazione['Riga4'] = $this->riga_PI_CF($gestore);

            //RIGA 5
            $intestazione['Riga5'] = $this->riga_Tel_Fax($gestore);

            //RIGA 6
            $intestazione['Riga6'] = $this->riga_Mail_Sito($gestore);

            //RIGA 7
            $intestazione['Riga7'] = "Servizio: " . $servizio;
        } else if ($tipo == "Concessionario") {
            //RIGA 1
            $intestazione['Riga1'] = $tipo . " " . $gestore->Denominazione;

            //RIGA 2
            $intestazione['Riga2'] = $this->riga_indirizzo($gestore);

            //RIGA 3
            $intestazione['Riga3'] = $this->riga_PI_CF($gestore);

            //RIGA 4
            $intestazione['Riga4'] = $this->riga_Tel_Fax($gestore);

            //RIGA 5
            $intestazione['Riga5'] = $this->riga_Mail_Sito($gestore);

            //RIGA 6
            $intestazione['Riga6'] = "Servizio: " . $servizio;

            //RIGA 7
            $intestazione['Riga7'] = "Gestione: " . $nome_comune;
        }

        return $intestazione;
    }

    public function riga_PI_CF($gestore)
    {
        $riga_CF_PI = "";
        if ($gestore->Partita_Iva != "" || $gestore->Codice_Fiscale != "") {
            $riga_CF_PI = "P.I.: " . $gestore->Partita_Iva . "  -  C.F.: " . $gestore->Codice_Fiscale;

            if ($gestore->Partita_Iva == "")
                $riga_CF_PI = "C.F.: " . $gestore->Codice_Fiscale;
            else if ($gestore->Codice_Fiscale == "")
                $riga_CF_PI = "P.I.: " . $gestore->Partita_Iva;
        }

        return $riga_CF_PI;
    }

    public function riga_Tel_Fax($gestore)
    {
        $riga_tel_fax = "";
        if ($gestore->Telefono != "" || $gestore->Fax != "") {
            $riga_tel_fax = "Tel: " . $gestore->Telefono . "  -  Fax: " . $gestore->Fax;

            if ($gestore->Telefono == "")
                $riga_tel_fax = "Fax: " . $gestore->Fax;
            else if ($gestore->Fax == "")
                $riga_tel_fax = "Tel: " . $gestore->Telefono;
        }

        return $riga_tel_fax;
    }

    public function riga_Mail_Sito($gestore)
    {
        $riga_mail_sito = "";
        if ($gestore->Mail != "" || $gestore->Sito != "") {
            $riga_mail_sito = "eMail: " . $gestore->Mail . "  -  Sito: " . $gestore->Sito;

            if ($gestore->Mail == "")
                $riga_mail_sito = "Sito: " . $gestore->Sito;
            else if ($gestore->Sito == "")
                $riga_mail_sito = "eMail: " . $gestore->Mail;
        }
        return $riga_mail_sito;
    }


    public function riga_indirizzo($gestore)
    {
        $riga_indirizzo = "";
        if ($gestore->Toponimo != "") {
            $riga_indirizzo = ucwords(strtolower($gestore->Toponimo));

            if ($gestore->Civico != "" && $gestore->Civico != 0)
                $riga_indirizzo .= ", " . $gestore->Civico;
            if ($gestore->Esponente)
                $riga_indirizzo .= $gestore->Esponente;
            if ($gestore->Interno)
                $riga_indirizzo .= "/" . $gestore->Interno;
            if ($gestore->Dettagli)
                $riga_indirizzo .= ", " . $gestore->Dettagli;

            if ($gestore->Comune != "")
                $riga_indirizzo .= " - " . $gestore->Cap . " " . $gestore->Comune . " (" . $gestore->Provincia . ")";
        }

        return $riga_indirizzo;
    }

    public function riga_Mail_PEC($ufficio)
    {
        $riga_mail_sito = "";
        if ($ufficio->Mail != "" || $ufficio->PEC != "") {
            $riga_mail_sito = "eMail: " . $ufficio->Mail . "  -  PEC: " . $ufficio->PEC;

            if ($ufficio->Mail == "")
                $riga_mail_sito = "PEC: " . $ufficio->PEC;
            else if ($ufficio->PEC == "")
                $riga_mail_sito = "eMail: " . $ufficio->Mail;
        }
        return $riga_mail_sito;
    }

    public function righe_orario($ufficio)
    {
        $orario = $ufficio->Orario;
        $array_orario['Riga1'] = "";
        $array_orario['Riga2'] = "";

        if ($orario != "") {
            $lunghezza = strlen($orario);
            if ($lunghezza <= 50) {
                $array_orario['Riga1'] = $orario;
                $array_orario['Riga2'] = "";
            } else {
                $pos = 50;
                //echo $pos;
                for ($i = 0; $i < $pos; $i++) {
                    $carattere = substr($orario, $pos - $i, 1);
                    //echo $carattere."*";
                    if ($carattere == " ") {
                        //echo $pos-$i;
                        $pos = $pos - $i;
                        break;
                    }
                }

                $array_orario['Riga1'] = substr($orario, 0, $pos);
                $array_orario['Riga2'] = substr($orario, $pos + 1);
            }
        }

        return $array_orario;
    }

    public function getDataComune($c)
    {
        $comune = new stdClass();

        $query = "SELECT * FROM enti_gestiti WHERE CC = '" . $c . "'";
        $comune = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "enti_gestiti");

        $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
        $comune->Info = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "gestore");

        if ($comune->Gestore_ID != 0) {
            $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
            $comune->Gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "gestore");
        } else {
            $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
            $comune->Gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "gestore");
        }

        $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Ufficio_ID . "'";
        $comune->Ufficio = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "gestore");

        if ($comune->Stemma_1 != "" || $comune->Stemma_2 != "") {
            $tempStemma1 = $comune->Stemma_1;
            $tempStemma2 = $comune->Stemma_2;
            if ($comune->Stemma_1 != "") {
                $comune->Stemma_Principale = "si";
                $comune->Stemma_1 = STEMMIWEB . "/" . $comune->CC . "/" . $tempStemma1;
                $path_1 = STEMMI . "/" . $comune->CC . "/" . $tempStemma1;

                if ($comune->Stemma_2 != "") {
                    $comune->Stemma_Secondario = "si";
                    $comune->Stemma_2 = STEMMIWEB . "/" . $comune->CC . "/" . $tempStemma2;
                    $path_2 = STEMMI . "/" . $comune->CC . "/" . $tempStemma2;
                } else {
                    $comune->Stemma_Secondario = "no";
                    $comune->Stemma_2 = STEMMIWEB . "/" . $comune->CC . "/" . $tempStemma1;
                    $path_2 = STEMMI . "/" . $comune->CC . "/" . $tempStemma1;
                }
            } else {
                $comune->Stemma_Principale = "no";
                $comune->Stemma_Secondario = "si";

                $comune->Stemma_1 = STEMMIWEB . "/" . $comune->CC . "/" . $tempStemma2;
                $path_1 = STEMMI . "/" . $comune->CC . "/" . $tempStemma2;
                $comune->Stemma_2 = STEMMIWEB . "/" . $comune->CC . "/" . $tempStemma2;
                $path_2 = STEMMI . "/" . $comune->CC . "/" . $tempStemma2;
            }

            $comune->Path_Stemma_1 = $path_1;
            $comune->Path_Stemma_2 = $path_2;

            if (file_exists($path_1))
                $comune->dim_stemma_1 = getimagesize($path_1);
            if (file_exists($path_2))
                $comune->dim_stemma_2 = getimagesize($path_2);
        } else {
            $comune->Stemma_Principale = "no";
            $comune->Stemma_Secondario = "no";

            $comune->Stemma_1 = "";
            $path_1 = "";
            $comune->Stemma_2 = "";
            $path_2 = "";

            $comune->Path_Stemma_1 = $path_1;
            $comune->Path_Stemma_2 = $path_2;

            $comune->dim_stemma_1 = "";
            $comune->dim_stemma_2 = "";
        }

        if ($comune->Stemma_3 != "") {
            $Stemma3Nome = $comune->Stemma_3;
            $comune->Stemma_Targhe_Estere = "si";
            $comune->Stemma_3 = STEMMIWEB . "/" . $comune->CC . "/" . $Stemma3Nome;
            $path_3 = STEMMI . "/" . $comune->CC . "/" . $Stemma3Nome;

            $comune->Path_Stemma_3 = $path_3;
            if (file_exists($path_3))
                $comune->dim_stemma_3 = getimagesize($path_3);
        } else {
            $comune->Stemma_Targhe_Estere = "no";

            $comune->Stemma_3 = "";
            $path_3 = "";

            $comune->Path_Stemma_3 = $path_3;

            $comune->dim_stemma_3 = "";
        }

        return $comune;
    }

    public function intestazione_ufficio($ufficio)
    {
        if ($ufficio->Tipo != "Ufficio") return false;
        $intestazione = array();

        //RIGA 1
        $intestazione['Riga1'] = $ufficio->Denominazione;

        //RIGA 2
        $intestazione['Riga2'] = $this->riga_indirizzo($ufficio);

        //RIGA 3
        $intestazione['Riga3'] = $this->riga_Tel_Fax($ufficio);

        //RIGA 4
        $intestazione['Riga4'] = $this->riga_Mail_PEC($ufficio);

        //RIGA 5-6
        $orario = $this->righe_orario($ufficio);
        $intestazione['Riga5'] = "Orario: " . $orario['Riga1'];
        $intestazione['Riga6'] = $orario['Riga2'];

        return $intestazione;
    }

    public function GetDataToponimo($utente)
    {
        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '" . $utente->ID . "' AND Tipo = 'res'";
        $utente->Residenza = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if ($utente->Residenza != null)
            if ($utente->Residenza->Via_ID != 1) {
                $query = "SELECT * FROM toponimo WHERE ID = '" . $utente->Residenza->Via_ID . "' AND CC_Comune = '" . $utente->CC_Comune . "'";
                $utente->Residenza->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "toponimo");
            } else if ($utente->Residenza->Via_Cap_ID != 1) {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '" . $utente->Residenza->Via_Cap_ID . "'";
                $utente->Residenza->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "toponimi_cappati");
            } else
                $utente->Residenza->Toponimo = null;


        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '" . $utente->ID . "' AND Tipo = 'dom'";
        $utente->Domicilio = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if ($utente->Domicilio != null)
            if ($utente->Domicilio->Via_ID != 1) {
                $query = "SELECT * FROM toponimo WHERE ID = '" . $utente->Domicilio->Via_ID . "' AND CC_Comune = '" . $utente->CC_Comune . "'";
                $utente->Domicilio->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "toponimo");
            } else if ($utente->Domicilio->Via_Cap_ID != 1) {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '" . $utente->Domicilio->Via_Cap_ID . "'";
                $utente->Domicilio->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "toponimi_cappati");
            } else
                $utente->Domicilio->Toponimo = null;


        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '" . $utente->ID . "' AND Tipo = 'rec'";
        $utente->Recapito = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if ($utente->Recapito != null)
            if ($utente->Recapito->Via_ID != 1) {
                $query = "SELECT * FROM toponimo WHERE ID = '" . $utente->Recapito->Via_ID . "' AND CC_Comune = '" . $utente->CC_Comune . "'";
                $utente->Recapito->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "toponimo");
            } else if ($utente->Recapito->Via_Cap_ID != 1) {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '" . $utente->Recapito->Via_Cap_ID . "'";
                $utente->Recapito->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "toponimi_cappati");
            } else
                $utente->Recapito->Toponimo = null;

        return $utente;
    }

    public function righe_indirizzoUtente($utente)
    {
        if ($utente->Recapito != null)
            $indirizzo = $utente->Recapito;
        else if ($utente->Domicilio != null)
            $indirizzo = $utente->Domicilio;
        else
            $indirizzo = $utente->Residenza;

        if (strtoupper($indirizzo->Paese) == "ITALIA") {
            $ind_1 = $indirizzo->Toponimo->Nome;
            if ($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione . ", " . $ind_1;

            if ($indirizzo->Civico)
                $ind_1 .= ", " . $indirizzo->Civico;
            if ($indirizzo->Esponente)
                $ind_1 .= " " . $indirizzo->Esponente;
            if ($indirizzo->Interno)
                $ind_1 .= "/" . $indirizzo->Interno;
            if ($indirizzo->Dettagli)
                $ind_1 .= ", " . $indirizzo->Dettagli;

            $ind_3 = "";
        } else {
            $ind_1 = $indirizzo->Toponimo->Nome;
            if ($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione . ", " . $ind_1;

            $ind_3 = $indirizzo->Paese;
        }

        $ind_2 = $indirizzo->Cap . " " . $indirizzo->Comune;
        $ind_2_senza_prov = $ind_2;
        if ($indirizzo->Provincia != null)
            $ind_2 .= " " . $indirizzo->Provincia;

        $indirizzo_destinatario = array();
        $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if ($lunghezza < 50) {
            $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
            $indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
            $indirizzo_destinatario['Riga4'] = "";
        } else if ($lunghezza <= 100) {
            $pos = $lunghezza / 2;
            //echo $pos;
            for ($i = 0; $i < $pos; $i++) {
                $carattere = substr(strtoupper($ind_1), $pos - $i, 1);
                //echo $carattere."*";
                if ($carattere == " ") {
                    //echo $pos-$i;
                    $pos = $pos - $i;
                    break;
                }
            }

            $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0, $pos);
            $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos + 1);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
        }
        ///////////////////////

        $indirizzo_destinatario['Completo'] = strtoupper($ind_1) . " - " . strtoupper($ind_2);
        if ($ind_3 != "")
            $indirizzo_destinatario['Completo'] .= ", " . strtoupper($ind_3);

        $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1) . " - " . strtoupper($ind_2_senza_prov);
        if ($ind_3 != "")
            $indirizzo_destinatario['Senza_Provincia'] .= ", " . strtoupper($ind_3);

        if ($utente->Genere == "D") {
            $indirizzo_destinatario['Destinatario'] = $utente->Ditta;
            if ($utente->Sigla_Forma_Giuridica != null)
                $indirizzo_destinatario['Destinatario'] .= " " . $utente->Sigla_Forma_Giuridica;
        } else {
            $indirizzo_destinatario['Destinatario'] = $utente->Cognome . " " . $utente->Nome;
        }

        if (isset($utente->Recapito))
            if ($utente->Recapito->ID > 0)
                $indirizzo_destinatario['Destinatario'] .= " C/O " . strtoupper($utente->Recapito->Presso);

        if (strlen($indirizzo_destinatario['Destinatario']) > 45) {
            $a_destinatario = array();
            $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
            $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0]) + 1, 40);
            $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
        }

        return $indirizzo_destinatario;
    }

    public function righe_indirizzo($gestore)
    {
        if ($gestore->Paese == "Italia") {
            $ind_1 = $gestore->Toponimo;
            if ($gestore->Frazione)
                $ind_1 = $gestore->Frazione . ", " . $ind_1;

            if ($gestore->Civico)
                $ind_1 .= ", " . $gestore->Civico;
            if ($gestore->Esponente)
                $ind_1 .= $gestore->Esponente;
            if ($gestore->Interno)
                $ind_1 .= "/" . $gestore->Interno;
            if ($gestore->Dettagli)
                $ind_1 .= ", " . $gestore->Dettagli;

            $ind_3 = "";
        } else {
            $ind_1 = $gestore->Toponimo;
            if ($gestore->Frazione)
                $ind_1 = $gestore->Frazione . ", " . $ind_1;

            $ind_3 = $gestore->Paese;
        }


        $ind_2 = $gestore->Cap . " " . $gestore->Comune;
        $ind_2_senza_prov = $ind_2;
        if ($gestore->Provincia != null)
            $ind_2 .= " " . $gestore->Provincia;

        $fax = "FAX " . $gestore->Fax;
        if ($gestore->Fax == "")
            $fax = "";

        $indirizzo_destinatario = array();
        $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if ($lunghezza < 50) {
            $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
            $indirizzo_destinatario['Riga2'] = strtoupper($ind_2) . " " . strtoupper($ind_3);
            $indirizzo_destinatario['Riga3'] = $fax;
            $indirizzo_destinatario['Riga4'] = "";
        } else if ($lunghezza <= 100) {
            $pos = $lunghezza / 2;
            //echo $pos;
            for ($i = 0; $i < $pos; $i++) {
                $carattere = substr(strtoupper($ind_1), $pos - $i, 1);
                //echo $carattere."*";
                if ($carattere == " ") {
                    //echo $pos-$i;
                    $pos = $pos - $i;
                    break;
                }
            }

            $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0, $pos);
            $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos + 1);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_2) . " " . strtoupper($ind_3);
            $indirizzo_destinatario['Riga4'] = $fax;
        }
        ///////////////////////

        $indirizzo_destinatario['Completo'] = strtoupper($ind_1) . " - " . strtoupper($ind_2);
        if ($ind_3 != "")
            $indirizzo_destinatario['Completo'] .= ", " . strtoupper($ind_3);

        $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1) . " - " . strtoupper($ind_2_senza_prov);
        if ($ind_3 != "")
            $indirizzo_destinatario['Senza_Provincia'] .= ", " . strtoupper($ind_3);

        $indirizzo_destinatario['Destinatario'] = $gestore->Denominazione;

        return $indirizzo_destinatario;

    }

    public function CercaParametroData($CCcomune, $dataConfronto, $table)
    {
        $query = "SELECT ID FROM " . $table . " WHERE CC = '" . $CCcomune . "' ";
        $query .= "AND Data_Creazione_Parametri <= '" . $dataConfronto . "' ORDER BY Data_Creazione_Parametri DESC";
        $ID = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), $table)["ID"];

        if ($ID != null) {
            // anche se sono tante righe, la prima che trovo � quella in vigore alla dataConfronto
            //$id = $rigaParametro['ID'];
            return $ID;
        } else {
            // se non sono MAI stati inseriti parametri per questo comune,
            // prendo il PIU' recente da un qualsiasi comune!
            $query = "SELECT ID FROM " . $table . " WHERE Data_Creazione_Parametri <= '" . $dataConfronto . "' ";
            $query .= "ORDER BY Data_Creazione_Parametri DESC";
            return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), $table)["ID"];

        }
    }

    public function firme_responsabili($val)
    {
        $firma_path = "/archivio/Firme/" . $val->CC . "/";
        $percorso = FIRME . "/" . $val->CC . "/";

        $firma = array();
        $firma['Funzionario'] = $firma_path . $val->Funzionario_Firma;
        $firma['Funzionario_Path'] = $percorso . $val->Funzionario_Firma;
        $firma['Funzionario_Nome'] = $val->Funzionario_Responsabile;
        $firma['Funzionario_Intestazione'] = "Il Funzionario responsabile";
        $firma['Funzionario_Testo'] = $val->Funzionario_Testo;

        if ($val->Funzionario_Firma == "" && $val->Funzionario_Testo != "si") {
            $firma['Funzionario'] = "";
            $firma['Funzionario_Path'] = "";
            $firma['Funzionario_Nome'] = "";
            $firma['Funzionario_Intestazione'] = "";
            $firma['Funzionario_Testo'] = "";
        }

        $firma['Responsabile'] = $firma_path . $val->Responsabile_Firma;
        $firma['Responsabile_Path'] = $percorso . $val->Responsabile_Firma;
        $firma['Responsabile_Nome'] = $val->Responsabile_Procedimento;
        $firma['Responsabile_Intestazione'] = "Il Responsabile del procedimento";
        $firma['Responsabile_Testo'] = $val->Responsabile_Testo;

        if ($val->Responsabile_Firma == "" && $val->Responsabile_Testo != "si") {
            $firma['Responsabile'] = "";
            $firma['Responsabile_Path'] = "";
            $firma['Responsabile_Nome'] = "";
            $firma['Responsabile_Intestazione'] = "";
            $firma['Responsabile_Testo'] = "";
        }


        $firma['Ufficiale'] = $firma_path . $val->Ufficiale_Firma;
        $firma['Ufficiale_Path'] = $percorso . $val->Ufficiale_Firma;
        $firma['Ufficiale_Nome'] = $val->Ufficiale_Riscossione;
        $firma['Ufficiale_Intestazione'] = "L'Ufficiale della riscossione";
        $firma['Ufficiale_Testo'] = $val->Ufficiale_Testo;
        if ($val->Ufficiale_Firma == "" && $val->Ufficiale_Testo != "si") {
            $firma['Ufficiale'] = "";
            $firma['Ufficiale_Path'] = "";
            $firma['Ufficiale_Nome'] = "";
            $firma['Ufficiale_Intestazione'] = "";
            $firma['Ufficiale_Testo'] = "";
        }

        $firma['Responsabile_Richieste'] = $firma_path . $val->Responsabile_Richieste_Firma;
        $firma['Responsabile_Richieste_Path'] = $percorso . $val->Responsabile_Richieste_Firma;
        $firma['Responsabile_Richieste_Nome'] = $val->Responsabile_Richieste;
        $firma['Responsabile_Richieste_Intestazione'] = "Responsabile della richiesta";
        $firma['Responsabile_Richieste_Testo'] = $val->Responsabile_Richieste_Testo;
        if ($val->Responsabile_Richieste_Firma == "" && $val->Responsabile_Richieste_Testo != "si") {
            $firma['Responsabile_Richieste'] = "";
            $firma['Responsabile_Richieste_Path'] = "";
            $firma['Responsabile_Richieste_Nome'] = "";
            $firma['Responsabile_Richieste_Intestazione'] = "";
            $firma['Responsabile_Richieste_Testo'] = "";
        }

        return $firma;
    }

    public function carica_firme($val, $firma1, $firma2, $firma3, $firma4 = null)
    {
        $firma = $this->firme_responsabili($val);

        if ($firma1 != "") {
            $temp[1]['intestazione'] = $firma[$firma1 . "_Intestazione"];
            $temp[1]['nome'] = $firma[$firma1 . "_Nome"];
            if ($firma[$firma1 . "_Testo"] == "si")
                $temp[1]['firma'] = $val->Testo_Sostitutivo;
            else
                $temp[1]['firma'] = $firma[$firma1];
        } else {
            $temp[1]['intestazione'] = "";
            $temp[1]['nome'] = "";
            $temp[1]['firma'] = "";
        }

        if ($firma2 != "") {
            $temp[2]['intestazione'] = $firma[$firma2 . "_Intestazione"];
            $temp[2]['nome'] = $firma[$firma2 . "_Nome"];
            if ($firma[$firma2 . "_Testo"] == "si")
                $temp[2]['firma'] = $val->Testo_Sostitutivo;
            else
                $temp[2]['firma'] = $firma[$firma2];
        } else {
            $temp[2]['intestazione'] = "";
            $temp[2]['nome'] = "";
            $temp[2]['firma'] = "";
        }

        if ($firma3 != "") {
            $temp[3]['intestazione'] = $firma[$firma3 . "_Intestazione"];
            $temp[3]['nome'] = $firma[$firma3 . "_Nome"];
            if ($firma[$firma3 . "_Testo"] == "si")
                $temp[3]['firma'] = $val->Testo_Sostitutivo;
            else
                $temp[3]['firma'] = $firma[$firma3];
        } else {
            $temp[3]['intestazione'] = "";
            $temp[3]['nome'] = "";
            $temp[3]['firma'] = "";
        }

        if ($firma4 != "") {
            $temp[4]['intestazione'] = $firma[$firma4 . "_Intestazione"];
            $temp[4]['nome'] = $firma[$firma4 . "_Nome"];
            if ($firma[$firma4 . "_Testo"] == "si")
                $temp[4]['firma'] = $val->Testo_Sostitutivo;
            else
                $temp[4]['firma'] = $firma[$firma4];
        } else {
            $temp[4]['intestazione'] = "";
            $temp[4]['nome'] = "";
            $temp[4]['firma'] = "";
        }

        return $temp;
    }

    public function Get_Data_Sede_Legale($progr, $c)
    {

        $query = "SELECT * FROM utente WHERE ID = '" . $progr . "' AND CC_Comune = '" . $c . "' LOCK IN SHARE MODE";
        $val = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));// safe_query($query);


        // assegna un valore ai puntatori $prev e $next:
        // se progr=0 (nuovo inserimento) prev punta all'ultimo e next punta al primo
        if ($progr == 0) {
            $query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
            $query .= "WHERE Cognome != \"\" AND CC_Comune = \"" . $c . "\" ) ";
            $query .= "UNION ";
            $query .= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
            $query .= "WHERE Ditta != \"\" AND CC_Comune = \"" . $c . "\" )";
            $query .= "ORDER BY utente_nome ASC, nome ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//safe_query($query);
            //$array_result = mysql_fetch_array($result);
            $val["next_alfa"] = $result['ID'];

            $query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
            $query .= "WHERE Cognome != \"\" AND CC_Comune = \"" . $c . "\" ) ";
            $query .= "UNION ";
            $query .= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
            $query .= "WHERE Ditta != \"\" AND CC_Comune = \"" . $c . "\" )";
            $query .= "ORDER BY utente_nome DESC, nome DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//safe_query($query);
            //$array_result = mysql_fetch_array($result);
            $val["prev_alfa"] = $result['ID'];

            $query = "SELECT ID FROM utente where CC_Comune='$c' ORDER BY ID ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["next"] = $result["ID"];//single_answer_query($query);

            $query = "SELECT * FROM utente WHERE CC_Comune='$c' ORDER BY ID DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["prev"] = $result["ID"];//$this->prev = single_answer_query($query);

            $query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
            $query .= "WHERE pa.Utente_ID = u.ID AND pa.Anno_Riferimento = '" . $a . "' AND pa.CC = '" . $c . "' ORDER BY u.ID DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["prev_ruolo"] = $result["ID"];

            $query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
            $query .= "WHERE pa.Utente_ID = u.ID  AND pa.Anno_Riferimento = '" . $a . "' AND pa.CC = '" . $c . "' ORDER BY u.ID ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["next_ruolo"] = $result["ID"];
        } else {
            if ($val["Cognome"] != '')
                $utente_nome = $val["Cognome"];
            else if ($val["Ditta"] != '')
                $utente_nome = $val["Ditta"];
            else
                $utente_nome = "";

            $query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
            $query .= "WHERE Cognome != \"\" AND CC_Comune = \"" . $c . "\" AND Cognome > \"" . $utente_nome . "\" )";
            $query .= "UNION ";
            $query .= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
            $query .= "WHERE Ditta != \"\" AND CC_Comune = \"" . $c . "\" AND Ditta > \"" . $utente_nome . "\" )";
            $query .= "ORDER BY utente_nome ASC, Nome ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//safe_query($query);
            //$array_result = mysql_fetch_array($result);
            $val["next_alfa"] = $result['ID'];
            /*$result = safe_query($query);
            $array_result = mysql_fetch_array($result);
            $this->next_alfa = $array_result['ID'];*/

            $query = "(SELECT ID, Nome, Cognome AS utente_nome FROM utente ";
            $query .= "WHERE Cognome != \"\" AND CC_Comune = \"" . $c . "\" AND Cognome < \"" . $utente_nome . "\" ) ";
            $query .= "UNION ";
            $query .= "(SELECT ID, Nome, Ditta AS utente_nome FROM utente ";
            $query .= "WHERE Ditta != \"\" AND CC_Comune = \"" . $c . "\" AND Ditta < \"" . $utente_nome . "\" )";
            $query .= "ORDER BY utente_nome DESC , Nome DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));//safe_query($query);
            //$array_result = mysql_fetch_array($result);
            $val["prev_alfa"] = $result['ID'];
            /*$result = safe_query($query);
            $array_result = mysql_fetch_array($result);
            $this->prev_alfa = $array_result['ID'];*/


            $query = "SELECT ID FROM utente WHERE ( (ID>'" . $val['ID'] . "') AND (CC_Comune='$c') ) ORDER BY ID ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["next"] = $result["ID"];
            /*$result = safe_query($query);
            $array_result = mysql_fetch_array($result);
            $this->next = $array_result['ID'];*/

            $query = "SELECT ID FROM utente WHERE ( (ID<'" . $val['ID'] . "') AND (CC_Comune='$c') ) ORDER BY ID DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["prev"] = $result["ID"];
            /*$result = safe_query($query);
            $array_result = mysql_fetch_array($result);
            $this->prev = $array_result['ID'];*/

            $query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
            $query .= "WHERE pa.Utente_ID = u.ID AND (u.ID<'" . $val['ID'] . "')  AND pa.Anno_Riferimento = '" . $a . "' AND pa.CC = '" . $c . "' ORDER BY u.ID DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["prev_ruolo"] = $result["ID"];
            //$this->prev_ruolo = single_answer_query($query);

            $query = "SELECT DISTINCT u.ID FROM utente AS u, partita_tributi AS pa ";
            $query .= "WHERE pa.Utente_ID = u.ID AND (u.ID>'" . $val['ID'] . "')  AND pa.Anno_Riferimento = '" . $a . "' AND pa.CC = '" . $c . "' ORDER BY u.ID ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["next_ruolo"] = $result["ID"];
            //$this->next_ruolo = single_answer_query($query);
        }

        return $val;
    }

    public function getDataScorporo($p, $c, $a)
    {
        $query = "SELECT * FROM partita_tributi WHERE ID = '" . $p . "' AND CC = '" . $c . "' AND Anno_Riferimento = '" . $a . "'";
        $val = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "partita_tributi");

        $query = "SELECT ID FROM pignoramento_generale WHERE Partita_ID = '" . $val['ID'] . "'";
        $pignoramento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $query = "SELECT ID FROM atto WHERE Partita_ID = '" . $val['ID'] . "'";
        $atto_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $query = "SELECT ID FROM tributo WHERE Partita_ID = '" . $val["ID"] . "' ORDER BY Codice_Tributo ASC";
        $tributo_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        for ($i = 0; $i < count($tributo_id); $i++) {
            $query = "SELECT * FROM tributo WHERE ID = '" . $tributo_id[$i]['ID'] . "' AND CC = '" . $c . "'";
            $array_tributo = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "tributo");

            $query = "SELECT Tipo_Codice FROM codice_tributo WHERE Codice_Tributo = '" . $array_tributo['Codice_Tributo'] . "'";
            $array_codTributo = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "codice_tributo");

            //echo "<h1>Data ".$this->cls_date->Get_DateNewFormat($array_tributo["Data_Decorrenza_Interessi"],"DB")." --- ".$array_codTributo["Tipo_Codice"]."</h1>";

            if ($array_codTributo["Tipo_Codice"] == "IMPORTO" && $this->cls_date->Get_DateNewFormat($array_tributo["Data_Decorrenza_Interessi"], "DB") != "") {
                //  echo "<h1>Data ".$this->cls_date->Get_DateNewFormat($val["Data_Inizio_Interessi"],"DB")."</h1>";
                if (!isset($val["Data_Inizio_Interessi"]))
                    $val["Data_Inizio_Interessi"] = $array_tributo["Data_Decorrenza_Interessi"];

                //$data_decorrenza = $array_tributo->Data_Decorrenza_Interessi;
            }
        }

        //  echo "<h1>CountP: ".count($pignoramento_id)."</h1>";

        for ($y = 0; $y < count($pignoramento_id); $y++) {
            $query = "SELECT * FROM pignoramento_generale WHERE ID = '" . $pignoramento_id[$y]["ID"] . "' AND CC = '" . $c . "'";
            $val["Pignoramento"][$y] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "pignoramento_generale"); //new pignoramento( $pignoramento_id[$y]['ID'] , $c );


            $query = "SELECT ID FROM pagamento WHERE Atto_ID = '" . $val["Pignoramento"][$y]["ID"] . "' AND Partita_ID = '" . $val["Pignoramento"][$y]["Partita_ID"] . "' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
            $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

//echo "<h1>CountPag: ".count($pagamento_id)."</h1>";
            for ($x = 0; $x < count($pagamento_id); $x++) {
                $query = "SELECT * FROM pagamento WHERE ID = '" . $pagamento_id[$x]['ID'] . "' AND CC = '" . $c . "'";
                $val["Pignoramento"][$y]["Pagamento"][$x] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "pagamento");

            }

            $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = '" . $pignoramento_id[$y]["ID"] . "' AND CC = '" . $c . "'";
            $data["Pignoramento"][$y]["Spese_Pignoramento"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "pignoramento_spese");

        }

        $testoSemestri = "";
        $countGiri = 0;
        $dataInizioInteressi = isset($val["Data_Inizio_Interessi"]) ? $val["Data_Inizio_Interessi"] : "";// CAPIRE COSA FARE CON QUESTA VARIABILE

        for ($i = 0; $i < count($atto_id); $i++) {
            $query = "SELECT * FROM atto WHERE ID = '" . $atto_id[$i]['ID'] . "' AND CC = '" . $c . "'";
            $val["Atto"][$i] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "atto");
            //$this->Atto[$i] = new atto( $atto_id[$i]['ID'] , $c );

            $val["Atto"][$i]["Semestri"] = "";
            if ($val["Atto"][$i]["Atto"] == "Ingiunzione" || $val["Atto"][$i]["Atto"] == "Avviso di intimazione ad adempiere" || $val["Atto"][$i]["Atto"] == "Avviso di messa in mora") {
                $val["ultimo_atto"] = $atto_id[$i]['ID'];


                $data1 = new DateTime($dataInizioInteressi);
                $data2 = new DateTime($val["Atto"][$i]["Data_Calcolo_Interessi"]);
                $interval = $data1->diff($data2);
                $val["Atto"][$i]["Data_Inizio_Calcolo"] = $dataInizioInteressi;
                $semestri = floor($interval->format('%a') / 182.5);
                if ($val["Atto"][$i]["Interessi"] > 0) {
                    if ($val["Tipo"] == "CDS") {
                        if ($semestri <= 1)
                            $semestri = "1 semestre calcolato";
                        else
                            $semestri .= " semestri calcolati";
                    } else {
                        $semestri = "Interesse calcolato";
                    }

                    if ($countGiri > 0)
                        $testoSemestri .= " + ";

                    $testoSemestri .= $semestri . " dal " . $this->cls_date->Get_DateNewFormat($val["Atto"][$i]["Data_Inizio_Calcolo"], "DB") . " al " . $this->cls_date->Get_DateNewFormat($val["Atto"][$i]["Data_Calcolo_Interessi"], "DB");

                    $countGiri++;
                }

                $val["Atto"][$i]["Semestri"] = $testoSemestri;

                $dataInizioInteressi = $val["Atto"][$i]["Data_Calcolo_Interessi"];


                if ($val["Atto"][$i]["Atto"] == "Ingiunzione") {
                    if ($val["Atto"][$i]["Data_Notifica"] < date("Y-m-d", strtotime(date('Y-m-d') . "-1 year")))
                        $val["ultimo_atto_scaduto"] = $atto_id[$i]['ID'];
                } else {
                    $val["ultimo_avviso"] = $atto_id[$i]['ID'];

                    if ($val["Atto"][$i]["Data_Notifica"] < date("Y-m-d", strtotime(date('Y-m-d') . "-180 days")))
                        $val["ultimo_atto_scaduto"] = $atto_id[$i]['ID'];
                }
            }

            $val["Atto_Not"] = $i + 1;


            if ($val["Atto"][$i]["Stato"] != "Annullata") {
                $val["Atto_Calc"] = $i + 1;
            }


            $query = "SELECT ID FROM pagamento WHERE Atto_ID = '" . $val["Atto"][$i]["ID"] . "' AND Partita_ID = '" . $val["Atto"][$i]["Partita_ID"] . "' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
            $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
            //$pagamento_id = select_mysql_array("ID", "pagamento","Atto_ID = '".$this->ID."' AND Partita_ID = '".$this->Partita_ID."' AND Tipo_Atto LIKE 'Pignoramento%'","Rata");
            //  echo "<h1>Num Pag : ".count($pagamento_id)."</h1>";
            for ($x = 0; $x < count($pagamento_id); $x++) {
                $query = "SELECT * FROM pagamento WHERE ID = '" . $pagamento_id[$x]['ID'] . "' AND CC = '" . $c . "'";
                $val["Atto"][$i]["Pagamento"][$x] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "pagamento");
                //$this->Pagamento[$i] = new pagamento( $pagamento_id[$i]['ID'] , $c );
            }
        }
        return $val;
    }

    public function getTotalAmountDuePigno($pigno)
    {
        $result = $this->gestione_totali($pigno);
        $a_amount['tot'] = 0;
        $a_amount['spese_accessorie'] = 0;
        if ($pigno["Rate_Previste"] > 0) {
            $a_amount['tot'] = $result["Totali_Array"][$pigno["Tipo_Totale_Rate"]];
            $a_amount['spese_accessorie'] = $result["Parziali_Spese_Accessorie"][$pigno["Tipo_Totale_Rate"]];
        } else {
            $pagamento_pigno = isset($pigno["Pagamento"][0]["Importo"]) ? $pigno["Pagamento"][0]["Importo"] : 0.00;

            //echo "--> ".$result["Totali_Array"][3];
            if ($pagamento_pigno == number_format((double)$result["Totali_Array"][3], 2, ",", ".")) {
                $a_amount['tot'] = number_format((double)$result["Totali_Array"][3], 2, ",", ".");
                $a_amount['spese_accessorie'] = $result["Parziali_Spese_Accessorie"][3];
            } else if ($pagamento_pigno == number_format((double)$result["Totali_Array"][2], 2, ",", ".")) {
                $a_amount['tot'] = number_format((double)$result["Totali_Array"][2], 2, ",", ".");
                $a_amount['spese_accessorie'] = $result["Parziali_Spese_Accessorie"][2];
            } else {
                $a_amount['tot'] = number_format((double)$result["Totali_Array"][1], 2, ",", ".");
                $a_amount['spese_accessorie'] = $result["Parziali_Spese_Accessorie"][1];
            }
        }

        $a_amount['spese_pignoramento'] = isset($result["Totale_Spese_Notifica"]) ? $result["Totale_Spese_Notifica"] : 0.00;

        return $a_amount;
    }


    public function gestione_totali($data)
    {
        if (isset($data["Spese_Pignoramento"]))
            $totali_spese = $this->totali_spese($data["Spese_Pignoramento"]);
        else {
            $totali_spese = array('totale_1' => 0, 'totale_2' => 0, 'totale_3' => 0);
        }

        $dataArray["Parziali_Spese_Accessorie"][1] = $totali_spese['totale_1'];
        $dataArray["Parziali_Spese_Accessorie"][2] = $totali_spese['totale_1'] + $totali_spese['totale_2'];
        $dataArray["Parziali_Spese_Accessorie"][3] = $totali_spese['totale_1'] + $totali_spese['totale_2'] + $totali_spese['totale_3'];
        $totale_1 = 0;
        $totale_2 = 0;
        $totale_3 = 0;

        if ($totali_spese['totale_3'] != 0) {
            $totale_3 = $data["Totale_Dovuto"];
            $totale_2 = $totale_3 - $totali_spese['totale_3'];
            $totale_1 = $totale_2 - $totali_spese['totale_2'];
        } else {
            if ($totali_spese['totale_2'] != 0) {
                $totale_2 = $data["Totale_Dovuto"];
                $totale_1 = $totale_2 - $totali_spese['totale_2'];
            } else {
                if ($data["Totale_Dovuto"] != "")
                    $totale_1 = $data["Totale_Dovuto"];
            }
        }

        $dataArray["Totali_Array"][1] = number_format($totale_1, 2, ",", ".");
        $dataArray["Totali_Array"][2] = number_format($totale_2, 2, ",", ".");
        $dataArray["Totali_Array"][3] = number_format($totale_3, 2, ",", ".");

        return $dataArray;
    }

    public function totali_spese($data)
    {
        $totale['totale_1'] = 0;
        $totale['totale_2'] = 0;
        $totale['totale_3'] = 0;
        switch ($data["Tipo_Totale_1"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_1"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_1"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_1"];
                break;
        }
        switch ($data["Tipo_Totale_2"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_2"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_2"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_2"];
                break;
        }
        switch ($data["Tipo_Totale_3"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_3"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_3"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_3"];
                break;
        }
        switch ($data["Tipo_Totale_4"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_4"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_4"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_4"];
                break;
        }
        switch ($data["Tipo_Totale_5"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_5"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_5"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_5"];
                break;
        }
        switch ($data["Tipo_Totale_6"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_6"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_6"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_6"];
                break;
        }
        switch ($data["Tipo_Totale_7"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_7"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_7"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_7"];
                break;
        }
        switch ($data["Tipo_Totale_8"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_8"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_8"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_8"];
                break;
        }
        switch ($data["Tipo_Totale_9"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_9"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_9"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_9"];
                break;
        }
        switch ($data["Tipo_Totale_10"]) {
            case 1:
                $totale['totale_1'] += $data["Rimborso_10"];
                break;
            case 2:
                $totale['totale_2'] += $data["Rimborso_10"];
                break;
            case 3:
                $totale['totale_3'] += $data["Rimborso_10"];
                break;
        }

        //$this->Totali_Array[1] = conv_num(number_format($totale['totale_1'],2));
        //$this->Totali_Array[2] = conv_num(number_format($totale['totale_1']+$totale['totale_2'],2));
        //$this->Totali_Array[3] = conv_num(number_format($totale['totale_1']+$totale['totale_2']+$totale['totale_3'],2));

        return $totale;
    }

    public function getTotalAmountDue($atto)
    {
        $a_amount['tot'] = $atto["Totale_Dovuto"];
        if ($atto["Rate_Previste"] > 0) {
            if ($atto["Tipo_Totale_Rate"] == 1) {
                $a_amount['tot'] += $atto["Diritto_Riscossione_Minimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Minimo"];
            } else {
                $a_amount['tot'] += $atto["Diritto_Riscossione_Massimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Massimo"];
            }
        } else {
            $data_not = new DateTime($atto["Data_Notifica"]);
            $data_not->modify("+2 months");
            if ($this->controlloDataPrimoPagamento($atto) > $data_not->format('Y-m-d')) {
                $a_amount['tot'] += $atto["Diritto_Riscossione_Massimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Massimo"];
            } else {
                $a_amount['tot'] += $atto["Diritto_Riscossione_Minimo"];
                $a_amount['diritto'] = $atto["Diritto_Riscossione_Minimo"];
            }
        }

        $a_amount['tot_residuo'] = $a_amount['tot'] - $this->pagamenti_completi($atto["ID"], $atto["Partita_ID"]);

        return $a_amount;
    }

    public function pagamenti_completi($ID, $Partita_ID)
    {
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID <= " . $ID . " AND Partita_ID = " . $Partita_ID . " AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
        $results = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

        //echo $query;
        //$results = mysql_query($query);

        //$line = mysql_fetch_array($results, MYSQL_ASSOC);
        return isset($results['TOTALE_PAGAMENTI'])?$results['TOTALE_PAGAMENTI']:0;
    }

    public function controlloDataPrimoPagamento($atto)
    {
        if (isset($atto["Pagamento"][0])) {
            return $atto["Pagamento"][0]["Data_Pagamento"];
        } else
            return null;
    }

    public function getDataPartita($progr = 0, $c, $a = null)
    {

        $query = "SELECT PT.*, R.Data_Fornitura, I.Id AS Import_Id, E.Elaboration_Status_Id, E.Document_Type_Id AS Elaboration_DocumentTypeId FROM partita_tributi AS PT ";
        $query .= "JOIN ruolo AS R on R.ID = PT.Ruolo_ID ";
        $query .= "LEFT JOIN elaborations AS E ON E.Id=PT.Elaboration_Id ";
        $query .= "LEFT JOIN imports AS I ON I.Ruolo_ID=PT.Ruolo_ID ";
        $query .= "WHERE PT.ID = '" . $progr . "'";

        $val = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "partita_tributi");

        $coo = isset($val['Coo_ID']) ? $val['Coo_ID'] : "";
        if ($coo != "" && $coo != null) {
            $coo = explode("*", $coo);
            for ($i = 1; $i < count($coo); $i++)
                $val["Coo_ID"][$i - 1] = $coo[$i];
        } else
            $val["Coo_ID"] = null;

        $query = "SELECT TR.*, CT.Descrizione as Tipo_Tributo, CT.Tipo_Codice, CT.Testo_Codice, CT.Codice_Scorporo FROM tributo AS TR ";
        $query .= "JOIN codice_tributo as CT ON CT.Codice_Tributo=TR.Codice_Tributo WHERE TR.Partita_ID=" . $val['ID'];
        if ($val['ID'] == "" || $val['ID'] == null)
            $a_tributi = null;
        else
            $a_tributi = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $query = "SELECT * FROM pagamento WHERE Partita_ID = '" . $val['ID'] . "' AND Tipo_Atto != 'Precedenti' AND Tipo_Atto NOT LIKE '%Pignoramento%' ORDER BY Rata DESC";
        $val["Pagamento"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));


        $contaTributi = 0;
        $codice_tributo = "";
        $val["Data_Inizio_Interessi"] = null;
        if ($a_tributi != null && $a_tributi != array()) {
            for ($i = 0; $i < count($a_tributi); $i++) {

                $val['Tributo'][$i] = $a_tributi[$i];
                if ($val["Tributo"][$i]["Tipo_Codice"] == "IMPORTO" && $this->cls_date->Get_DateNewFormat($val["Tributo"][$i]["Data_Decorrenza_Interessi"], "DB") != "") {
                    if ($this->cls_date->Get_DateNewFormat($val["Data_Inizio_Interessi"], "DB") == "")
                        $val["Data_Inizio_Interessi"] = $val["Tributo"][$i]["Data_Decorrenza_Interessi"];
                }

                if ($codice_tributo != $val["Tributo"][$i]["Codice_Tributo"]) {
                    if ($i > 0)
                        $contaTributi++;
                    $val["a_tributi"][$contaTributi] = $val["Tributo"][$i];//$array_tributo == $val["Tributo"][$i];
                }
                else
                    $val["a_tributi"][$contaTributi]["Imposta"] += $val["Tributo"][$i]["Imposta"];

                $codice_tributo = $val["Tributo"][$i]["Codice_Tributo"];
            }
        }
        else
            $val["Tributo"] = null;

        $temp = "";
        if (isset($val["a_tributi"])) {
            for ($i = 0; $i < count($val["a_tributi"]); $i++) {
                if ($val["a_tributi"][$i]["Codice_Tributo"] == "5243") {
                    $temp = $val["a_tributi"][$i];
                    unset($val["a_tributi"][$i]);
                    $val["a_tributi"] = array_values($val["a_tributi"]);
                }
            }
        }
        else
            $val["a_tributi"] = null;

        if ($temp != "")
            $val["a_tributi"][] = $temp;

        $query = "SELECT A.*, F.UploadDate, D.File 
                FROM atto AS A 
                LEFT JOIN flows AS F ON A.FlowId = F.Id 
                LEFT JOIN documento AS D ON D.CC = A.CC AND D.Atto_ID = A.ID
                WHERE A.Partita_ID = '" . $val['ID'] . "'";
        $val["Atto"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));


        $testoSemestri = "";
        $countGiri = 0;
        $dataInizioInteressi = isset($val["Data_Inizio_Interessi"]) ? $val["Data_Inizio_Interessi"] : null;
        $val["Somma_Spese_Notifica"] = 0;
        foreach($val["Atto"] as $i=>$a_atto){

            $data_preav = explode("**", $a_atto["Date_Stampe_Preavvisi_Ing"]);
            for ($x = 0; $x < count($data_preav); $x++)
                $val["Atto"][$i]["Date_Preavvisi"][$x] = $this->cls_date->Get_DateNewFormat($data_preav[$x], "DB");

            $val["Somma_Spese_Notifica"] += $a_atto["Spese_Notifica"];
            $val["Somma_Spese_Notifica"] += $a_atto["CAN"];
            $val["Somma_Spese_Notifica"] += $a_atto["CAD"];

            $val["Atto"][$i]["Semestri"] = "";
            if ($a_atto["Atto"] == "Ingiunzione" || $a_atto["Atto"] == "Avviso di intimazione ad adempiere" || $a_atto["Atto"] == "Avviso di messa in mora") {
                $val["ultimo_atto"] = $a_atto['ID'];

                if ($a_atto["Interessi"] > 0) {
                    $val["Atto"][$i]["Data_Inizio_Calcolo"] = $dataInizioInteressi;
                    if ($val["Tipo"] == "CDS") {
                        $query = "SELECT * FROM lockup_periods WHERE (CC='*****' OR CC='".$c."') AND Lockup_Type_Id<=3 ORDER BY Start_Date ASC";
                        $a_blockPeriods = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"array","Id");//new ente_gestito($c);
                        $a_params = array(
                            "CalcType" => $val['Tipo'],
                            "StartDate" => $this->cls_help->toDbDate($dataInizioInteressi),
                            "EndDate" => $this->cls_help->toDbDate($a_atto["Data_Calcolo_Interessi"]),
                            "a_blocks" => $a_blockPeriods
                        );

                        $days = $this->cls_elab->calcDays($dataInizioInteressi, $a_atto["Data_Calcolo_Interessi"]);
                        $blockDays = $this->cls_elab->calcBlockDays($a_params);
                        $semestri = floor(($days-$blockDays) / 180);

                        if ($semestri <= 1)
                            $semestri = "1 semestre calcolato";
                        else
                            $semestri .= " semestri calcolati";
                    } else {
                        $semestri = "Interesse calcolato";
                    }

                    if ($countGiri > 0)
                        $testoSemestri .= " + ";

                    $testoSemestri .= $semestri . " dal " . $this->cls_date->Get_DateNewFormat($val["Atto"][$i]["Data_Inizio_Calcolo"], "DB") . " al " . $this->cls_date->Get_DateNewFormat($val["Atto"][$i]["Data_Calcolo_Interessi"], "DB");

                    $countGiri++;
                }

                $val["Atto"][$i]["Semestri"] = $testoSemestri;

                $dataInizioInteressi = $val["Atto"][$i]["Data_Calcolo_Interessi"];

                if ($a_atto["Atto"] == "Ingiunzione") {
                    if ($a_atto["Data_Notifica"] < date("Y-m-d", strtotime(date('Y-m-d') . "-1 year")))
                        $val["ultimo_atto_scaduto"] = $a_atto['ID'];
                } else {
                    $val["ultimo_avviso"] = $a_atto['ID'];

                    if ($a_atto["Data_Notifica"] < date("Y-m-d", strtotime(date('Y-m-d') . "-180 days")))
                        $val["ultimo_atto_scaduto"] = $a_atto['ID'];
                }
            }

            $val["Atto_Not"] = $i + 1;

            if ($a_atto["Stato"] != "Annullata") {
                $val["Atto_Calc"] = $i + 1;
            }
        }

        if ($val["ID"]>0){
            $query = "SELECT ID FROM partita_tributi WHERE ID>".$val["ID"]." AND CC='".$c."' AND Anno_Riferimento = '".$a."' ORDER BY ID ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["next"] = isset($result['ID']) ? $result['ID'] : null;

            $query = "SELECT ID FROM partita_tributi WHERE ID<".$val["ID"]." AND CC='".$c."' AND Anno_Riferimento = '".$a."' ORDER BY ID DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["prev"] = isset($result['ID']) ? $result['ID'] : null;
        }
        else
        {
            $query = "SELECT ID FROM partita_tributi WHERE CC='" . $c . "' AND Anno_Riferimento = '" . $a . "' ORDER BY ID ASC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["next"] = isset($result['ID']) ? $result['ID'] : null;

            $query = "SELECT ID FROM partita_tributi WHERE CC='" . $c . "' AND Anno_Riferimento = '" . $a . "' ORDER BY ID DESC LIMIT 1";
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            $val["prev"] = isset($result['ID']) ? $result['ID'] : null;

        }

        return $val;
    }

    public function crono_atto($val)
    {
        if (strpos($val["Tipo_Atto"], 'Pignoramento') === false) {
            //$atto = new atto($this->Atto_ID, $this->CC);
            $query = "SELECT * FROM atto WHERE ID = " . $val["Atto_ID"] . " AND CC = '" . $val["CC"] . "'";
            $atto = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        } else {
            //$atto = new pignoramento($this->Atto_ID, $this->CC);
            $query = "SELECT * FROM pignoramento_generale WHERE ID = '" . $val["Atto_ID"] . "' AND CC = '" . $val["CC"] . "'";
            $atto = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        }

        return $atto["ID_Cronologico"] . "/" . $atto["Anno_Cronologico"];
    }

    public function info_spedizione($atto)
    {
        switch ($atto["Atto"]) {
            case "Sollecito pre ingiunzione":
                $tipo_atto = "SOLL_PRE";
                break;
            case "Ingiunzione":
                $tipo_atto = "INGIUNZIONE";
                break;
            case "Sollecito di pagamento":
                $tipo_atto = "SOLLECITOINGIUNZIONE";
                break;
            case "Avviso di intimazione ad adempiere":
                $tipo_atto = "AVVISOINTIMAZIONE";
                break;
            case "Avviso di messa in mora":
                $tipo_atto = "AV_MORA";
                break;
        }
        $ID = isset($atto["Atto_ID"]) ? $atto["Atto_ID"] : $atto["ID"];

        $queryCerca = "SELECT * FROM notifiche_importate ";
        $queryCerca .= "WHERE CC_Comune = '" . $atto["CC"] . "' AND Tipo_Atto = '" . $tipo_atto . "' AND Riferimento = '" . $ID . "' ";

        //echo $queryCerca;
        $spedizione = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($queryCerca));

        //if(!$spedizione) $spedizione = null;

        return $spedizione;
    }

    public function totale_pagamenti($ID, $Partita_ID, $c)
    {
        $query = "SELECT ID FROM pagamento WHERE Atto_ID = '" . $ID . "' AND Partita_ID = '" . $Partita_ID . "' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
        $pagamento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query)); //select_mysql_array("ID", "pagamento","Atto_ID = '".$ID."' AND Partita_ID = '".$Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%'", "Rata");
        $Pagamento = array();

        for ($i = 0; $i < count($pagamento_id); $i++) {
            $query = "SELECT Importo FROM pagamento WHERE ID = '" . $pagamento_id[$i]['ID'] . "' AND CC = '" . $c . "'";
            $Pagamento[$i] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query)); // new pagamento( $pagamento_id[$i]['ID'] , $c );
        }

        //$pagamenti = $this->Pagamento;
        $tot_pagamenti = 0;
        for ($q = 0; $q < count($Pagamento); $q++) {
            $tot_pagamenti += $Pagamento[$q]["Importo"];
        }

        return $tot_pagamenti;
    }

    public function Options_Anni_Veloci($c, $gestione, $pagina)
    {
        $where = "CC_Anno = '" . $c . "' ";

        switch ($gestione) {
            case "COATTIVA":
                $where .= " AND Gestione_Coattiva = 'Y' ";
                break;
            case "TARGHEESTERE":
                $where .= " AND Gestione_Targhe_Estere = 'Y' ";
                break;
            case "PUBBLICITA":
                $where .= " AND Gestione_Pubblicita = 'Y' ";
                break;
            default:
                alert("Parametro assente!");
                break;
        }

        $query = "SELECT * FROM anni_gestiti WHERE " . $where . " ORDER BY Anno DESC";
        $array_anni = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        //$array_anni = $this->Array_Selezione_Anni($c, $gestione);

        $select = "<select id='select_anno_veloce' onchange='conferma_anno_js(\"" . $pagina . "\",\"" . $c . "\")'>";

        for ($i = 0; $i < count($array_anni); $i++)
            $select .= "<option value='" . $array_anni[$i]['Anno'] . "'>" . $array_anni[$i]['Anno'] . "</option>";

        $select .= "</select>";

        return $select;

    }


    public function pagamenti_precedenti($ID, $Partita_ID)
    {
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID < " . $ID . " AND Partita_ID = " . $Partita_ID . " AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
        //echo "<br>".$query."<br>";
        $results = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query)); // mysql_query($query);
        //var_dump($results);
        return isset($results["TOTALE_PAGAMENTI"]) ? $results["TOTALE_PAGAMENTI"] : null; //mysql_fetch_array($results, MYSQL_ASSOC);
        //$line['TOTALE_PAGAMENTI'];
    }

    public function array_notifica($c = "*****")
    {
        $return = array();
        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'modalita' AND Tipo = 'A mani' ORDER BY Descrizione ASC";
        $return["Mode_A_Mani"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));// select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'A mani'", "Descrizione" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'modalita' AND Tipo = 'Per posta' ORDER BY Descrizione ASC";
        $return["Mode_Per_Posta"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Per posta'", "Descrizione" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'modalita' AND Tipo = 'Eccezionali' ORDER BY Descrizione ASC";
        $return["Mode_Eccezionali"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'modalita' AND Tipo = 'Eccezionali'", "Descrizione" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'stato' ORDER BY Descrizione ASC";
        $return["Stati"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'stato'", "Descrizione" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'motivo' ORDER BY Descrizione ASC";
        $return["Motivi"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'motivo'", "Descrizione" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'tipo_importato' ORDER BY ID ASC";
        $return["Tipo_Mercurio"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'tipo_importato'", "ID" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'stato_importato' ORDER BY ID ASC";
        $return["Stato_Mercurio"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'MERCURIO' AND Tipo = 'stato_importato'", "ID" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'blocco' ORDER BY Descrizione ASC";
        $return["BloccoCoattiva"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'blocco'", "Descrizione" );

        $query = "SELECT * FROM parametri_notifica WHERE CC = '" . $c . "' AND Tipo_Dato = 'sospensione' ORDER BY Descrizione ASC";
        $return["SospensioneCoattiva"] = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//select_mysql_array( "*" , "parametri_notifica" , "CC = '".$c."' AND Tipo_Dato = 'sospensione'", "Descrizione" );

        return $return;
    }

    function options_select_array($array, $campo = "Descrizione", $campo_trailer = null)
    {
        $options = "";
        for ($i = 0; $i < count($array); $i++) {
            $options .= "<option value='" . $array[$i]['ID'] . "'>" . $array[$i][$campo];

            if ($campo_trailer != null)
                $options .= " - " . $array[$i][$campo_trailer];

            $options .= "</option>";
        }

        return $options;
    }

    public function dovuto_senza_pagamenti($atto, $c)
    {
        $rimanenza['tot_pagamenti'] = $this->totale_pagamenti($atto["ID"], $atto["Partita_ID"], $c);
        $rimanenza['totale'] = $atto["Totale_Dovuto"] - $rimanenza['tot_pagamenti'];

        $interessi_ing = $atto["Interessi_Precedenti"] + $atto["Interessi"];
        $importo_ing = $atto["Importo"] + $atto["Spese_Notifica"] + $atto["CAD"] + $atto["CAN"];

        $rimanenza['addizionali'] = $atto["Addizionale"];

        if ($rimanenza['tot_pagamenti'] < $interessi_ing) {
            $rimanenza['interessi'] = $interessi_ing - $rimanenza['tot_pagamenti'];
            $rimanenza['importo'] = $importo_ing;
        } else {
            $rimanenza['interessi'] = 0.00;
            $rimanenza['importo'] = $importo_ing - ($rimanenza['tot_pagamenti'] - $interessi_ing);
        }

        return $rimanenza;
    }

    public function getDataParametri($c, $data, $tipo)
    {
        //$this->cls_date = new cls_DateTimeI("DB",false);

        /*  if( substr($data,2,1) == "/" )
            $data = to_mysql_date($data);*/

        $a = substr($data, 0, 4);

        $query = "SELECT * FROM parametri_annuali WHERE CC = '" . $c . "' AND Anno = '" . $a . "' AND Tipo_Riscossione = '*****'";
        //$result = safe_query($query);
        //$val = mysql_fetch_array($result);
        $val = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query), "parametri_annuali");

        $val["CAN"] = $val['CAN'];
        if ($data >= $val['CAN_Data'] && $val['CAN_Data'] != null) {
            $val["CAN"] = $val['CAN_New'];
        }

        $val["CAD"] = $val['CAD'];
        if ($data >= $val['CAD_Data'] && $val['CAD_Data'] != null) {
            $val["CAD"] = $val['CAD_New'];
        }

        $val["Diritto_Riscossione_Minimo"] = $val['Diritto_Riscossione_Minimo'];
        $val["Diritto_Riscossione_Massimo"] = $val['Diritto_Riscossione_Massimo'];


        return $val;
    }

    function attoStampato($tipo_atto, $tipo_stampa, $atto)
    {
        if ($tipo_atto == "Ingiunzione") {
            $cartella = "Ingiunzioni";
            $prefisso = "Ingiunzione_";
        } else if ($tipo_atto == "Avviso di intimazione ad adempiere") {
            $cartella = "Avvisi_di_intimazione";
            $prefisso = "Avviso_di_intimazione_";
        } else if ($tipo_atto == "Sollecito di pagamento") {
            $cartella = "Solleciti";
            $prefisso = "Sollecito_";
        } else if ($tipo_atto == "Sollecito pre ingiunzione" || $tipo_atto == "SOLL_PRE") {
            $cartella = "Solleciti_Pre_Ingiunzione";
            $prefisso = "sollecitoPreIngiunzione_";
        } else if ($tipo_atto == "Avviso di messa in mora" || $tipo_atto == "AV_MORA") {
            $cartella = "Avvisi_Messa_In_Mora";
            $prefisso = "avvisoMessaInMora_";
        }

        //echo "<br>Tipo --> ".$tipo_stampa."<br>Preavviso --> ".$prefisso."<br>Cartella --> ".$cartella;

        if ($tipo_stampa == "DEFINITIVA") {

            $sottoCartella = "STAMPE DEFINITIVE";

            if ($atto["Data_Stampa"] == null)
                return "notFound";

            $file = array();

            $link = ATTI . "/" . $atto["CC"] . "/" . $cartella . "/" . $sottoCartella . "/";

            $link .= $prefisso;
            $link .= $atto["CC"] . "_";
            $link .= $atto["Anno_Cronologico"] . "_";
            $link .= $atto["ID_Cronologico"] . "_";
            $link .= $atto["Data_Stampa"] . ".pdf";

            //echo "<br>Link --> ".$link;
            $file[0] = $link;
            if (is_file($link))
                return $file;
            else
                return "notFound";


        } else if ($tipo_stampa == "FLUSSO") {
            //echo "<h1>GP 794 --> ".$atto["Data_Flusso"]." - ".$atto["Data_Flusso"]."</h1>";
            $sottoCartella = "FLUSSI";
            if ($atto["Data_Flusso"] == null)
                return "notFound";

            $file = array();

            $dir = ATTI . "/" . $atto["CC"] . "/" . $cartella . "/" . $sottoCartella;
            $this->crea_dir($dir);
            $handle = opendir($dir);
            while (($link = readdir($handle)) != false) {
                if ($link != "." && $link != ".." && $link != "thumbs.db" && $link != "ELIMINATI") {
                    $explodePunto = explode(".", $link);
                    $estensione = $explodePunto[1];

                    //echo "<h1>".$explodePunto[0]."</h1>";
                    $explode = explode("_", $explodePunto[0]);
                    //var_dump($explode);
                    $control_comune = $explode[2];
                    $control_anno = $explode[3];
                    $control_numero = $explode[4];
                    $control_data = $explode[5];

                    if (strtoupper($estensione) == "RAR" &&
                        $atto["CC"] == $control_comune &&
                        $atto["Anno_Flusso"] == $control_anno &&
                        $atto["Numero_Flusso"] == $control_numero) {
                        $file[1] = $dir . "/" . $link;
                    }

                    if (strtoupper($estensione) == "TXT" &&
                        $atto["CC"] == $control_comune &&
                        $atto["Anno_Flusso"] == $control_anno &&
                        $atto["Numero_Flusso"] == $control_numero &&
                        $atto["Data_Flusso"] == $control_data) {
                        $file[0] = $dir . "/" . $link;
                    }
                }
            }

            //var_dump($file);
            closedir($handle);
            if (count($file) != 2)
                return "notFound";
            else
                return $file;
        }

    }

    function crea_dir($path)
    {
        if (!is_dir($path)) {
            $folder = explode("/", $path);

            $control_path = $folder[0];

            for ($l = 1; $l < count($folder); $l++) {
                $control_path .= "/" . $folder[$l];
                if (is_dir($control_path) == false) {
                    mkdir($control_path);
                }
            }
        }
        return $path;
    }

    private function sottotipi()
    {
        $array_sottotipi = array();

        $array_sottotipi['RIFIUTI'][] = "TARES";
        $array_sottotipi['RIFIUTI'][] = "TSRSU";
        $array_sottotipi['RIFIUTI'][] = "TARI";

        $array_sottotipi['IMMOBILI'][] = "ICI";
        $array_sottotipi['IMMOBILI'][] = "IMU";
        $array_sottotipi['IMMOBILI'][] = "TASI";

        $array_sottotipi['CDS'] = array();
        $array_sottotipi['IRPEF'] = array();
        $array_sottotipi['PATRIMONIALE'] = array();
        $array_sottotipi['OSAP'] = array("PERMANENTE", "TEMPORANEA");
        $array_sottotipi['PUBBLICITA'] = array("PERMANENTE", "AFFISSIONI", "TEMPORANEA");

        return $array_sottotipi;
    }

    private function option_da_array($array)
    {
        $option = "";
        for ($i = 0; $i < count($array); $i++) {
            $option .= "<option value='" . $array[$i] . "' class='aggiunta_option'>" . $array[$i] . "</option>";
        }

        return $option;
    }

    public function sottotipi_option()
    {
        $sottotipi = $this->sottotipi();

        $option['RIFIUTI'] = $this->option_da_array($sottotipi['RIFIUTI']);
        $option['IMMOBILI'] = $this->option_da_array($sottotipi['IMMOBILI']);
        $option['CDS'] = $this->option_da_array($sottotipi['CDS']);
        $option['IRPEF'] = $this->option_da_array($sottotipi['IRPEF']);
        $option['PATRIMONIALE'] = $this->option_da_array($sottotipi['PATRIMONIALE']);
        $option['OSAP'] = $this->option_da_array($sottotipi['OSAP']);
        $option['PUBBLICITA'] = $this->option_da_array($sottotipi['PUBBLICITA']);

        return $option;
    }
}

?>
