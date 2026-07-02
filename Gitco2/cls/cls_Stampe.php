<?php
include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";


class cls_Stampe
{
    private $cls_db;
    private $date;
    private $utils;
    private $log;

    public function __construct()
    {
        $this->cls_db = new cls_db();
        $this->utils = new cls_Utils();
        $this->log = new LOG();
        $this->date = new cls_DateTimeI("IT",false);
    }

    function da_a_utente ( $c , $dacognome = null , $acognome = null , $danome = null , $anome = null )
    {
        $query = "(SELECT ID, Nome, Cognome AS utente_cognome FROM utente ";
        $query.= "WHERE Cognome != '' AND CC_Comune = '".$c."' ";
        if($dacognome != null)
        {
            $query.= "AND ( ( Cognome > '".addslashes($dacognome)."' ) ";
            $query.= "AND ( Cognome < '".addslashes($acognome)."' ) ";
            $query.= "OR ( Cognome = '".addslashes($dacognome)."' ";
            if($danome != null)
            {
                $query.= "AND Nome >= '".addslashes($danome)."' ";
            }

            $query.= ") OR ( Cognome = '".addslashes($acognome)."' ";
            if($anome != null)
            {
                $query.= "AND Nome <= '".addslashes($anome)."' ";
            }
            $query.= ") ) ";
        }

        $query.= " ) ";

        $query.= "UNION ";
        $query.= "(SELECT ID, Nome, Ditta AS utente_cognome FROM utente ";
        $query.= "WHERE Ditta != '' AND CC_Comune = '".$c."' ";

        if($dacognome != null)
        {
            $query.= "AND ( Ditta >= '".addslashes($dacognome)."' AND Ditta <= '".addslashes($acognome)."' ) ";
        }
        $query.= ") ";
        $query.= "ORDER BY utente_cognome ASC, Nome ASC";

        return $query;
    }

    function da_a_partita( $c , $da_n_elenco = null , $a_n_elenco = null , $where = null )
    {
        $query = "SELECT * FROM partita_tributi ";
        $query.= "WHERE CC = '".$c."' ";
        if($da_n_elenco != null)
        {
            $query.= "AND ( Comune_ID >= '".$da_n_elenco."' AND Comune_ID <= '".$a_n_elenco."' ) ";
        }
        if($where != null)
        {
            $query.= "AND ".$where." ";
        }

        $query.= "ORDER BY Comune_ID ASC";

        return $query;
    }

    function select_pignoramento_presso_terzi ( $c , $where = null , $order = null, $where2 = null )
    {
        $query = "SELECT DISTINCT TERZI.Azienda, TERZI.Terzo_ID, GEN.Partita_ID FROM pignoramento_presso_terzi AS TERZI, pignoramento_generale AS GEN, tributo, partita_tributi ";

        if($order=="alfabetico")
        {

            $query.= ", ";
            $query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
            $query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
            $query.="UNION ";
            $query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
            $query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
            $query.="AS UNIONE_UTENTE ";

        }

        $query.= "WHERE partita_tributi.ID = GEN.partita_ID AND GEN.CC = '".$c."' AND TERZI.Pignoramento_ID = GEN.ID AND GEN.Tipo = 'terzi' ";
        $query.= "AND tributo.Partita_ID = GEN.Partita_ID AND TERZI.Azienda!='' AND TERZI.Terzo_ID = 0 ";

        if($order=="alfabetico")
        {
            $query.= " AND UNIONE_UTENTE.ID = partita_tributi.Utente_ID ";
        }

        if($where != null)
        {
            $query.= "AND (".$where.") ";
        }

        if($where2 != null)
        {
            $query.= "AND (".$where2.") ";
        }

        $query.= "ORDER BY ";

        if($order=="verbale")
            $query.= "ORDER BY tributo.Anno_Tributo ASC, ABS(tributo.Titolo_Sanzione) ASC ";
        if($order=="info")
            $query.= "tributo.Info_Cartella ASC, ";
        if($order=="alfabetico")
            $query.= "UNIONE_UTENTE.NOME_UTENTE ASC , UNIONE_UTENTE.Nome ASC, ";

        $query.= "partita_tributi.Comune_ID ASC, TERZI.ID ASC";

        return $query;
    }

    public function totale_pagamenti($atto_pignoramento)
    {
        $query = "SELECT * FROM pagamento WHERE Atto_ID = '".$atto_pignoramento->ID."' AND Partita_ID = '".$atto_pignoramento->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
        $Pagamento = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $tot_pagamenti = 0;
        for($q=0;$q<count($Pagamento);$q++)
        {
            $tot_pagamenti+=$Pagamento[$q]["Importo"];
        }

        return $tot_pagamenti;
    }

    public function spese_array($speseObj)
    {
        $spese = array();

        $spese[1]['ID'] 			= 	$speseObj->Spesa_1_ID;
        $spese[2]['ID'] 			= 	$speseObj->Spesa_2_ID;
        $spese[3]['ID'] 			= 	$speseObj->Spesa_3_ID;
        $spese[4]['ID'] 			= 	$speseObj->Spesa_4_ID;
        $spese[5]['ID'] 			= 	$speseObj->Spesa_5_ID;
        $spese[6]['ID'] 			= 	$speseObj->Spesa_6_ID;
        $spese[7]['ID'] 			= 	$speseObj->Spesa_7_ID;
        $spese[8]['ID'] 			= 	$speseObj->Spesa_8_ID;
        $spese[9]['ID'] 			= 	$speseObj->Spesa_9_ID;
        $spese[10]['ID'] 			= 	$speseObj->Spesa_10_ID;

        $spese[1]['tipo_spesa']		= 	$speseObj->Tipo_Spesa_1;
        $spese[2]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_2;
        $spese[3]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_3;
        $spese[4]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_4;
        $spese[5]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_5;
        $spese[6]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_6;
        $spese[7]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_7;
        $spese[8]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_8;
        $spese[9]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_9;
        $spese[10]['tipo_spesa'] 	= 	$speseObj->Tipo_Spesa_10;

        $spese[1]['extra_spesa']	= 	$speseObj->Extra_Spesa_1;
        $spese[2]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_2;
        $spese[3]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_3;
        $spese[4]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_4;
        $spese[5]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_5;
        $spese[6]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_6;
        $spese[7]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_7;
        $spese[8]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_8;
        $spese[9]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_9;
        $spese[10]['extra_spesa'] 	= 	$speseObj->Extra_Spesa_10;

        $spese[1]['rimborso'] 		= 	$speseObj->Rimborso_1;
        $spese[2]['rimborso'] 		= 	$speseObj->Rimborso_2;
        $spese[3]['rimborso'] 		= 	$speseObj->Rimborso_3;
        $spese[4]['rimborso'] 		= 	$speseObj->Rimborso_4;
        $spese[5]['rimborso'] 		= 	$speseObj->Rimborso_5;
        $spese[6]['rimborso'] 		= 	$speseObj->Rimborso_6;
        $spese[7]['rimborso'] 		= 	$speseObj->Rimborso_7;
        $spese[8]['rimborso'] 		= 	$speseObj->Rimborso_8;
        $spese[9]['rimborso'] 		= 	$speseObj->Rimborso_9;
        $spese[10]['rimborso'] 		= 	$speseObj->Rimborso_10;

        $spese[1]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_1;
        $spese[2]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_2;
        $spese[3]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_3;
        $spese[4]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_4;
        $spese[5]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_5;
        $spese[6]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_6;
        $spese[7]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_7;
        $spese[8]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_8;
        $spese[9]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_9;
        $spese[10]['tipo_totale'] 	= 	$speseObj->Tipo_Totale_10;

        return $spese;

    }
    public function GetTribunaleUtente($CC)
    {
        $query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$CC."' AND Tipo = 'tribunale' LIMIT 1";
        return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"ufficio_giudiziario")["Comune"];
    }

    public function totali_spese($spese)
    {
        $Totali_Array = array();
        $totale['totale_1'] = 0;
        $totale['totale_2'] = 0;
        $totale['totale_3'] = 0;

        switch($spese->Tipo_Totale_1)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_1;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_1;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_1;		break;
        }
        switch($spese->Tipo_Totale_2)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_2;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_2;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_2;		break;
        }
        switch($spese->Tipo_Totale_3)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_3;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_3;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_3;		break;
        }
        switch($spese->Tipo_Totale_4)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_4;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_4;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_4;		break;
        }
        switch($spese->Tipo_Totale_5)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_5;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_5;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_5;		break;
        }
        switch($spese->Tipo_Totale_6)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_6;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_6;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_6;		break;
        }
        switch($spese->Tipo_Totale_7)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_7;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_7;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_7;		break;
        }
        switch($spese->Tipo_Totale_8)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_8;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_8;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_8;		break;
        }
        switch($spese->Tipo_Totale_9)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_9;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_9;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_9;		break;
        }
        switch($spese->Tipo_Totale_10)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_10;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_10;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_10;		break;
        }

        $Totali_Array[1] = number_format($totale['totale_1'],2,",","");
        $Totali_Array[2] = number_format($totale['totale_1']+$totale['totale_2'],2,",","");
        $Totali_Array[3] = number_format($totale['totale_1']+$totale['totale_2']+$totale['totale_3'],2,",","");

        $Spese_accessorie[1] = $totale['totale_1'];
        $Spese_accessorie[2] = $totale['totale_1']+$totale['totale_2'];
        $Spese_accessorie[3] = $totale['totale_1']+$totale['totale_2']+$totale['totale_3'];
        $Totali_Array["spese_accessorie"] = $Spese_accessorie;

        //var_dump($Totali_Array);

        return $Totali_Array;
    }

    public function totali_spese_pigno($spese)
    {
        $Totali_Array = array();
        $totale['totale_1'] = 0;
        $totale['totale_2'] = 0;
        $totale['totale_3'] = 0;

        switch($spese->Tipo_Totale_1)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_1;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_1;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_1;		break;
        }
        switch($spese->Tipo_Totale_2)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_2;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_2;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_2;		break;
        }
        switch($spese->Tipo_Totale_3)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_3;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_3;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_3;		break;
        }
        switch($spese->Tipo_Totale_4)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_4;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_4;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_4;		break;
        }
        switch($spese->Tipo_Totale_5)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_5;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_5;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_5;		break;
        }
        switch($spese->Tipo_Totale_6)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_6;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_6;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_6;		break;
        }
        switch($spese->Tipo_Totale_7)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_7;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_7;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_7;		break;
        }
        switch($spese->Tipo_Totale_8)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_8;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_8;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_8;		break;
        }
        switch($spese->Tipo_Totale_9)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_9;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_9;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_9;		break;
        }
        switch($spese->Tipo_Totale_10)
        {
            case 1:		$totale['totale_1']	+=	$spese->Rimborso_10;		break;
            case 2:		$totale['totale_2']	+=	$spese->Rimborso_10;		break;
            case 3:		$totale['totale_3']	+=	$spese->Rimborso_10;		break;
        }

        $Totali_Array[1] = number_format($totale['totale_1'],2,",","");
        $Totali_Array[2] = number_format($totale['totale_1']+$totale['totale_2'],2,",","");
        $Totali_Array[3] = number_format($totale['totale_1']+$totale['totale_2']+$totale['totale_3'],2,",","");

        /*$Spese_accessorie[1] = $totale['totale_1'];
        $Spese_accessorie[2] = $totale['totale_1']+$totale['totale_2'];
        $Spese_accessorie[3] = $totale['totale_1']+$totale['totale_2']+$totale['totale_3'];
        $Totali_Array["spese_accessorie"] = $Spese_accessorie;

        //var_dump($Totali_Array);*/

        return $totale;
    }

    public function spese_notifica_pigno($el,$c,$a)
    {
        $query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Anno = '".$a."' AND Tipo_Riscossione = '*****'";
        $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_annuali");

        $el["Spese_Notifica_Pignoramento"] = $result['Spese_Notifica_Pignoramento'];
        if( date("Y-m-d") >= $result['Spese_Notifica_Pignoramento_Data'] && $result['Spese_Notifica_Pignoramento_Data'] != null )
        {
            $el["Spese_Notifica_Pignoramento"] = $result['Spese_Notifica_Pignoramento_New'];
        }

        $el["Spese_Postali_AG"] = $result['Spese_Postali_AG'];
        if( date("Y-m-d") >= $result['Spese_Postali_Data'] && $result['Spese_Postali_AG_Data'] != null )//Forse qui errore; Non date("Y-m-d") >= Spese_Postali_Data, ma Spese_Postali_AG_Data
        {
            $el["Spese_Postali_AG"] = $result['Spese_Postali_AG_New'];
        }

        return $el;
    }

    public function getStimaBeni($c,$ID=null)
    {
        $query = "SELECT * FROM tariffe_coazione WHERE ID = '".$ID."' AND CC = '".$c."'";
        $val = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"tariffe_coazione");

        $val->Data_Tariffa = date("Y-m-d");

        $query = "SELECT * FROM tariffe_coazione WHERE CC = '".$c."' LIMIT 1";
        $results = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
        if(count($results)==0){
            try {
                // First of all, let's begin a transaction
                $this->cls_db->Start_Transaction();
                $this->cls_db->Begin_Transaction();

                // A set of queries; if one fails, an exception should be thrown
                $query = "CREATE TEMPORARY TABLE tmp_tariffe SELECT * from tariffe_coazione WHERE CC='*****'";
                $this->cls_db->ExecuteQuery($query);
                $query = "ALTER TABLE tmp_tariffe drop ID, drop CC";
                $this->cls_db->ExecuteQuery($query);
                $query = "INSERT INTO tariffe_coazione SELECT 0,'".$c."',tmp_tariffe.* FROM tmp_tariffe";
                $this->cls_db->ExecuteQuery($query);
                $query = "DROP TABLE tmp_tariffe";
                $this->cls_db->ExecuteQuery($query);

            } catch (Exception $e) {
                // An exception has been thrown
                // We must rollback the transaction
                $this->cls_db->Rollback();
                $this->cls_db->End_Transaction();
            }

            // If we arrive here, it means that no exception was thrown
            // i.e. no query has failed, and we can commit the transaction
            $this->cls_db->End_Transaction();
        }

        $query = "SELECT * FROM tariffe_coazione WHERE CC = '".$c."' AND Tipo = 'UNA TANTUM' ORDER BY Descrizione ASC";
        $results = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $tariffe_una_tantum = $this->seleziona_tariffe($results, $val->Data_Tariffa, $val);/********************* LA DATA SARà SEMPRE NULL VEDI PRIMA QUERY CON ID = NULL ***********************************/

        $stima_beni = 0.00;
        for($i=0;$i<count($tariffe_una_tantum);$i++)
        {
            if($tariffe_una_tantum[$i]['Descrizione']=="Valutazione/Stima dei beni pignorati e formazione fascicolo")/******************* ESISTE SOLO UNA DESCRIZIONE DI QUEL TIPO? *******************************************/
                return number_format($tariffe_una_tantum[$i]['Importo'],2,",","");
        }

        return null;
        //return $stima_beni;
    }

    public function seleziona_tariffe( $array , $date , $val)
    {
        $array_di_selezione = array();

        for( $i=0; $i<count($array); $i++)
        {
            $k=0;
            $array_controllo = array();
            $array_controllo[$k] = $array[$i];

            for($y = $i+1; $y<count($array); $y++)
            {
                if($array[$i]['Descrizione'] == $array[$y]['Descrizione'] && $array[$i]['Deposito_Portata'] == $array[$y]['Deposito_Portata'])
                {
                    $k++;
                    $array_controllo[$k] = $array[$y];
                }
            }

            usort($array_controllo, array($this, "date_compare"));

            $control_index = "no";

            if( $date != null)
            {
                for($j = 0; $j<count($array_controllo); $j++)
                {
                    if( $j == count($array_controllo) - 1 )
                    {
                        if( $date >= $array_controllo[$j]['Data_Inizio'] )
                        {
                            $index_giusto = $j;
                            $control_index = "si";
                        }
                    }
                    else
                    {
                        if( $array_controllo[$j+1]['Data_Inizio'] > $date && $date >= $array_controllo[$j]['Data_Inizio'] )
                        {
                            $index_giusto = $j;
                            $control_index = "si";
                            break;
                        }
                    }
                }
            }
            else
            {
                $index_giusto = count($array_controllo)-1;
                $control_index = "si";
            }


            if( $control_index == "si" )
            {
                $control_element = $this->trova_ID( $array_di_selezione , $array_controllo[$index_giusto]['ID'] );
                if($control_element === false )
                {
                    $array_di_selezione[] = $array_controllo[$index_giusto];
                }
            }

        }

        return $array_di_selezione;

    }

    function trova_ID($products, $needle)
    {
        foreach($products as $key => $product)
        {
            if ( $product['ID'] === $needle )
                return $key;
        }

        return false;
    }

    private function date_compare($a, $b)
    {
        $t1 = strtotime($a['Data_Inizio']);
        $t2 = strtotime($b['Data_Inizio']);
        return $t1 - $t2;
    }

    public function getDataVisura($el,$ID,$c)
    {
        $query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = ".$ID." AND CC = '".$c."'";
        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        //echo "<br>".$query."<br>";

        if(count($result) > 0)
        {
            $pre_tipo_veicolo = "il";
            if($result[0]["Tipo_Veicolo"] == "autoveicolo") $pre_tipo_veicolo = "l'";

            $el["Data_Visura"] = $this->date->Get_DateNewFormat($result[0]["Data_Visura"],"DB");
            $el["Marca_Veicolo"] = strtoupper($result[0]["Marca_Veicolo"]);
            $el["Modello_Veicolo"] = strtoupper($result[0]["Modello_Veicolo"]);
            $el["Targa_Veicolo"] = strtoupper($result[0]["Targa_Veicolo"]);
            $el["Tipo_Veicolo"] = $pre_tipo_veicolo." ".$result[0]["Tipo_Veicolo"];
            $el["Fonte_Dati"] = strtoupper($result[0]["Fonte_Dati"]);
        }
        else
        {
            $el["Data_Visura"] = null;
            $el["Marca_Veicolo"] = null;
            $el["Modello_Veicolo"] = null;
            $el["Targa_Veicolo"] = null;
            $el["Tipo_Veicolo"] = null;
            $el["Fonte_Dati"] = null;
        }
        return $el;
    }

    public function ultimo_id ($anno_in_corso,$CC)
    {

        $query = "SELECT MAX(ID_Cronologico) + 1 as Max_ID ";
        $query.= "FROM atto ";
        $query.= "WHERE CC = '".$CC."' AND (Cronologico_Vecchio != 'si' OR Cronologico_Vecchio is NULL) AND ";
        $query.= "Anno_Cronologico = ".$anno_in_corso." ";
        $query.= "ORDER BY ID_Cronologico LIMIT 1";

        $return = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        //$return = single_query($query);

        if($return["Max_ID"] == null) return 1;

        return $return["Max_ID"];

    }

    public function IndirizzoEnte($el,$c)
    {
        $query = "SELECT * FROM gestore WHERE CC = '" . $c . "'";
        $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"gestore");

        $riga_indirizzo = "";
        if($result->Toponimo!="")
        {
            $riga_indirizzo = ucwords(strtolower($result->Toponimo));

            if($result->Civico!="" && $result->Civico!=0)
                $riga_indirizzo.=", ".$result->Civico;
            if($result->Esponente)
                $riga_indirizzo .= $result->Esponente;
            if($result->Interno)
                $riga_indirizzo.="/".$result->Interno;
            if($result->Dettagli)
                $riga_indirizzo.=", ".$result->Dettagli;

            if($result->Comune != "")
                $riga_indirizzo.= " - ".$result->Cap." ".$result->Comune." (".$result->Provincia. ")";
        }

        $el["indirizzoEnte"] = $riga_indirizzo;
        return $el;
    }

    public function riga_indirizzo($el)
    {
        $riga_indirizzo = "";
        if($el->Toponimo!="")
        {
            $riga_indirizzo = ucwords(strtolower($el->Toponimo));

            if($el->Civico!="" && $el->Civico!=0)
                $riga_indirizzo.=", ".$el->Civico;
            if($el->Esponente)
                $riga_indirizzo .= $el->Esponente;
            if($el->Interno)
                $riga_indirizzo.="/".$el->Interno;
            if($el->Dettagli)
                $riga_indirizzo.=", ".$el->Dettagli;

            if($el->Comune != "")
                $riga_indirizzo.= " - ".$el->Cap." ".$el->Comune." (".$el->Provincia. ")";
        }

        return $riga_indirizzo;
    }

    public function riga_Tel_Fax($el)
    {
        $riga_tel_fax = "";
        if($el->Telefono!="" || $el->Fax!="")
        {
            $riga_tel_fax = "Tel: " . $el->Telefono ."  -  Fax: ".$el->Fax;

            if($el->Telefono == "")
                $riga_tel_fax = "Fax: ".$el->Fax;
            else if($el->Fax == "")
                $riga_tel_fax = "Tel: " . $el->Telefono;
        }

        return $riga_tel_fax;
    }

    public function riga_Mail_PEC($el)
    {
        $riga_mail_sito = "";
        if($el->Mail!="" || $el->PEC!="")
        {
            $riga_mail_sito = "eMail: " . $el->Mail ."  -  PEC: ".$el->PEC;

            if($el->Mail == "")
                $riga_mail_sito = "PEC: ".$el->PEC;
            else if($el->PEC == "")
                $riga_mail_sito = "eMail: " . $el->Mail;
        }
        return $riga_mail_sito;
    }

    public function righe_orario($el)
    {
        $orario = $el->Orario;
        $array_orario['Riga1'] = "";
        $array_orario['Riga2'] = "";

        if($orario!="")
        {
            $lunghezza = strlen($orario);
            if($lunghezza <= 50)
            {
                $array_orario['Riga1'] = $orario;
                $array_orario['Riga2'] = "";
            }
            else
            {
                $pos = 50;
                //echo $pos;
                for( $i=0; $i<$pos; $i++)
                {
                    $carattere = substr($orario, $pos-$i,1);
                    //echo $carattere."*";
                    if($carattere==" ")
                    {
                        //echo $pos-$i;
                        $pos = $pos-$i;
                        break;
                    }
                }

                $array_orario['Riga1'] = substr($orario, 0 , $pos);
                $array_orario['Riga2'] = substr($orario, $pos+1);
            }
        }

        return $array_orario;
    }

    public function intestazione_ufficio($el)
    {
        if($el->Tipo!="Ufficio")	return false;
        $intestazione = array();

        //RIGA 1
        $intestazione['Riga1'] = $el->Denominazione;

        //RIGA 2
        $intestazione['Riga2'] = $this->riga_indirizzo($el);

        //RIGA 3
        $intestazione['Riga3'] = $this->riga_Tel_Fax($el);

        //RIGA 4
        $intestazione['Riga4'] = $this->riga_Mail_PEC($el);

        //RIGA 5-6
        $orario = $this->righe_orario($el);
        $intestazione['Riga5'] = "Orario: ".$orario['Riga1'];
        $intestazione['Riga6'] = $orario['Riga2'];

        return $intestazione;
    }

    public function riga_PI_CF($el)
    {
        $riga_CF_PI = "";
        if($el->Partita_Iva!="" || $el->Codice_Fiscale!="")
        {
            $riga_CF_PI = "P.I.: " . $el->Partita_Iva ."  -  C.F.: ".$el->Codice_Fiscale;

            if($el->Partita_Iva == "")
                $riga_CF_PI = "C.F.: ".$el->Codice_Fiscale;
            else if($el->Codice_Fiscale == "")
                $riga_CF_PI = "P.I.: " . $el->Partita_Iva;
        }

        return $riga_CF_PI;
    }

    public function riga_Mail_Sito($el)
    {
        $riga_mail_sito = "";
        if($el->Mail!="" || $el->Sito!="")
        {
            $riga_mail_sito = "eMail: " . $el->Mail ."  -  Sito: ".$el->Sito;

            if($el->Mail == "")
                $riga_mail_sito = "Sito: ".$el->Sito;
            else if($el->Sito == "")
                $riga_mail_sito = "eMail: " . $el->Mail;
        }
        return $riga_mail_sito;
    }

    public function intestazione_gestore( $servizio , $nome_comune , $el )
    {
        $tipo = $el->Tipo;
        if($tipo=="Ufficio")	return false;
        $intestazione = array();

        $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$el->CC."'";
        $comune_gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"comuni_lista");//new comune($el->CC);

        $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune_gestore->Com_Codice_Provincia."'";
        $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"province_lista");

        $provincia = $result->Pro_Nome;
        //unset($comune_gestore);

        if($tipo == "Comune")
        {
            //RIGA 1
            $intestazione['Riga1'] = $el->Denominazione;

            //RIGA 2
            $intestazione['Riga2'] = "Provincia di ".$provincia;

            //RIGA 3
            $intestazione['Riga3'] = $this->riga_indirizzo($el);

            //RIGA 4
            $intestazione['Riga4'] = $this->riga_PI_CF($el);

            //RIGA 5
            $intestazione['Riga5'] = $this->riga_Tel_Fax($el);

            //RIGA 6
            $intestazione['Riga6'] = $this->riga_Mail_Sito($el);

            //RIGA 7
            $intestazione['Riga7'] = "Servizio: ".$servizio;
        }
        else if($tipo == "Concessionario")
        {
            //RIGA 1
            $intestazione['Riga1'] = $tipo." ".$el->Denominazione;

            //RIGA 2
            $intestazione['Riga2'] = $this->riga_indirizzo($el);

            //RIGA 3
            $intestazione['Riga3'] = $this->riga_PI_CF($el);

            //RIGA 4
            $intestazione['Riga4'] = $this->riga_Tel_Fax($el);

            //RIGA 5
            $intestazione['Riga5'] = $this->riga_Mail_Sito($el);

            //RIGA 6
            $intestazione['Riga6'] = "Servizio: ".$servizio;

            //RIGA 7
            $intestazione['Riga7'] = "Gestione: ".$nome_comune;
        }

        return $intestazione;
    }

    public function InfoETotPag($el,$partitaId, $attoID, $yearParams)
    {
        $query= " SELECT * FROM atto WHERE Partita_ID = ".$partitaId." AND ID != ".$attoID." AND (DocumentTypeId = 2 or DocumentTypeId = 4) order by ID ASC";
        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $query = "SELECT SUM(Importo) AS TOTPAG FROM pagamento WHERE Partita_ID = ".$partitaId." AND DocumentTableTypeId = 1 AND Atto_ID < ".$attoID;
        $result1 = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if(empty($result1["TOTPAG"]))
            $tot_pag = 0.00;
        else
            $tot_pag = (float)$result1["TOTPAG"];

        $el["totalePagamenti"] = "meno i pagamenti effettuati, di ".$tot_pag." Euro,";

        $tot_compl_1 = 0.00;
        $tot_compl_2 = 0.00;
        $tot_compl_3 = 0.00;

        if(count($result) > 0)
        {
            $tot_compl_1 = $result[count($result)-1]["Totale_Dovuto"]-$tot_pag;
            $tot_compl_2 = number_format(( $tot_compl_1  + $yearParams["CAD"] ) ,2,",","")." Euro";
            $tot_compl_3 = number_format(( $tot_compl_1  + $yearParams["A_Mani"]) ,2,",","")." Euro";

            $tot_compl_1 = number_format( $tot_compl_1 , 2 ,",","")." Euro";

            //echo "<h1>CAD: ".$yearParams["CAD"]." --- AMANI: ".$yearParams["A_Mani"]." --- ".$tot_compl_1."</h1>";
        }

        $info = array();
        $totPag = 0.00;

        for($i=(count($result)-1); $i>=0; $i--)
        {
            if($i == (count($result)-1))
            {
                array_push($info,$result[$i]["Info_Cartella"]);
                //$info[$x] = $result[$i]["Info_Cartella"];
                //$x++;
            }

            if($result[$i]["DocumentTypeId"] == 2)
            {
                $totPag = number_format(($result[$i]["Totale_Dovuto"] + $result[$i]["Diritto_Riscossione_Massimo"]),2,",","");

                $numeroatto = $result[$i]["ID_Cronologico"]." del ".$result[$i]["Anno_Cronologico"];
                if($result[$i]["Cronologico_Vecchio"]!="si")
                    array_push($info,strtoupper("di cui all'".$result[$i]["Atto"]." N. ".$numeroatto." NOTIFICATO IL ".$this->date->Get_DateNewFormat($result[$i]["Data_Notifica"],"DB")));
                else
                    array_push($info,strtoupper("di cui all'".$result[$i]["Atto"]." con pari num. cronologico notificato il ".$this->date->Get_DateNewFormat($result[$i]["Data_Notifica"],"DB")));
                break;
            }
            else if($result[$i]["DocumentTypeId"] == 4)
            {
                $numeroatto = $result[$i]["ID_Cronologico"]." del ".$result[$i]["Anno_Cronologico"];
                if($result[$i]["Cronologico_Vecchio"]!="si")
                    array_push($info,strtoupper("di cui all'".$result[$i]["Atto"]." N. ".$numeroatto." NOTIFICATO IL ".$this->date->Get_DateNewFormat($result[$i]["Data_Notifica"],"DB")));
                else
                    array_push($info,strtoupper("di cui all'".$result[$i]["Atto"]." con pari num. cronologico notificato il ".$this->date->Get_DateNewFormat($result[$i]["Data_Notifica"],"DB")));
            }

        }

        if(count($info) > 0) {
            $infoOrder = $info[0];
            for($y = (count($info)-1); $y >= 1; $y--)
            {
                $infoOrder .= ", ".$info[$y];
            }
        }
        else
            $infoOrder = null;


        $el["info"] = $infoOrder;
        $el["TotalePag1"] = $tot_compl_1;
        $el["TotalePag2"] = $tot_compl_2;
        $el["TotalePag3"] = $tot_compl_3;
        $el["ImportoSenzaSpese"] = $totPag;
        return $el;
    }

    public function gestione_totali($el)
    {
        /*if($el->Spese_Pignoramento!=null)
            var_dump($el->Spese_Pignoramento);*/
        if($el->Spese_Pignoramento!=null)
            $totali_spese = $this->totali_spese_pigno($el->Spese_Pignoramento);
        else{
            $totali_spese = array('totale_1'=>0,'totale_2'=>0,'totale_3'=>0);
        }

        $el->Parziali_Spese_Accessorie[1] = $totali_spese['totale_1'];
        $el->Parziali_Spese_Accessorie[2] = $totali_spese['totale_1']+$totali_spese['totale_2'];
        $el->Parziali_Spese_Accessorie[3] = $totali_spese['totale_1']+$totali_spese['totale_2']+$totali_spese['totale_3'];
        $totale_1 = 0;
        $totale_2 = 0;
        $totale_3 = 0;

        if($totali_spese['totale_3']!=0)
        {
            $totale_3 = $el->Totale_Dovuto;
            $totale_2 = $totale_3 - $totali_spese['totale_3'];
            $totale_1 = $totale_2 - $totali_spese['totale_2'];
        }
        else
        {
            if($totali_spese['totale_2']!=0)
            {
                $totale_2 = $el->Totale_Dovuto;
                $totale_1 = $totale_2 - $totali_spese['totale_2'];
            }
            else
            {
                if($el->Totale_Dovuto!="")
                    $totale_1 = $el->Totale_Dovuto;
            }
        }

        $el->Totali_Array[1] = (double) $totale_1;
        $el->Totali_Array[2] = (double) $totale_2;
        $el->Totali_Array[3] = (double) $totale_3;

        return $el;
    }

    public function DataGestore($el,$c)
    {
        $gestore = null;
        $query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
        $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

        if(isset($result['Gestore_ID']))
        {
            if( $result['Gestore_ID'] != 0 )
            {
                $query = "SELECT * FROM gestore WHERE ID = '" . $result['Gestore_ID'] . "'";
                $gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"gestore");
            }
            else
            {
                $query = "SELECT * FROM gestore WHERE ID = '" . $result['Info_ID'] . "'";
                $gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"gestore");
            }

            $recapiti_gestore = "";
            if($gestore->Telefono!="")
                $recapiti_gestore.= "Tel: ".$gestore->Telefono;
            if($gestore->Fax!="")
                $recapiti_gestore.= " - Fax: ".$gestore->Fax;
            if($gestore->Mail!="")
                $recapiti_gestore.= " - Mail: ".$gestore->Mail;
            if($gestore->PEC!="")
                $recapiti_gestore.= " - PEC: ".$gestore->PEC;

            $el["managerOffice"] = $this->righe_indirizzo($gestore);
            $el["managerContactDetails"] = $recapiti_gestore;
        }
        else{
            $el["managerOffice"] = null;
            $el["managerContactDetails"] = null;
        }

        return $el;
    }

    public function indirizzoUtente($ID,$c)
    {
        $query = "SELECT * FROM utente WHERE ID = ".$ID." AND CC_Comune = '".$c."'";
        $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"utente");

        //$query = "SELECT * FROM forma_giuridica_societa WHERE ID = ".$result->Forma_Giuridica." AND CC = '*****'";
        //$this->Sigla_Forma_Giuridica = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"forma_giuridica_societa")->Sigla;

        $query = "SELECT * FROM indirizzo WHERE Utente_ID = ".$ID." AND Tipo = 'res'";
        $result->Residenza = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));
        $result->Residenza = $this->getToponimo($result->Residenza,$c);
        $tribunale = $this->getTribunale($result->Residenza->CC_Indirizzo,"tribunale");
        $istituto_vendite = $this->getTribunale($tribunale->CC_Ufficio, "istituto");
        $indirizzo_istituto = $this->righe_indirizzo_istituto_vendite($istituto_vendite);
        //echo "<h1>qui 1</h1>".$result->Residenza->ID;
        if(!isset($result->Residenza->ID)) {

            $query = "SELECT * FROM indirizzo WHERE Utente_ID = " . $ID . " AND Tipo = 'dom'";
            $result->Domicilio = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));
            //echo "<h1>qui 2</h1>".$result->Domicilio->ID;
            if (!isset($result->Domicilio->ID)) {
                $result->Domicilio = null;
                $tribunale = null;
            } else {
                $result->Domicilio = $this->getToponimo($result->Domicilio, $c);
                $tribunale = $this->getTribunale($result->Domicilio->CC_Indirizzo, "tribunale");
                $istituto_vendite = $this->getTribunale($tribunale->CC_Ufficio, "istituto");
                $indirizzo_istituto = $this->righe_indirizzo_istituto_vendite($istituto_vendite);
            }
        }
        if(!isset($result->Residenza->ID) && !isset($result->Domicilio->ID)) {

            $query = "SELECT * FROM indirizzo WHERE Utente_ID = " . $ID . " AND Tipo = 'rec'";
            $result->Recapito = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));
            //echo "<h1>qui 3</h1>".$result->Recapito->ID;
            if (!isset($result->Recapito->ID)) {
                $result->Recapito = null;
                $tribunale = null;
            } else {
                $result->Recapito = $this->getToponimo($result->Recapito, $c);
                $tribunale = $this->getTribunale($result->Recapito->CC_Indirizzo, "tribunale");
                $istituto_vendite = $this->getTribunale($tribunale->CC_Ufficio, "istituto");
                $indirizzo_istituto = $this->righe_indirizzo_istituto_vendite($istituto_vendite);
            }
        }

        $recapiti_istituto = "";
        if($istituto_vendite->Telefono!="")
            $recapiti_istituto.= "Tel: ".$istituto_vendite->Telefono;
        if($istituto_vendite->Fax!="")
            $recapiti_istituto.= " - Fax: ".$istituto_vendite->Fax;
        if($istituto_vendite->Mail!="")
            $recapiti_istituto.= " - Mail: ".$istituto_vendite->Mail;
        if($istituto_vendite->PEC!="")
            $recapiti_istituto.= " - PEC: ".$istituto_vendite->PEC;

        //var_dump($tribunale);
        $return["indirizzo_utente"] = $this->righe_indirizzo_utente($result);
        $return["tribunale_utente"] = $tribunale;
        $return["istituto_vendite"] = $istituto_vendite;
        $return["indirizzo_istituto"] = $indirizzo_istituto;
        $return["recapiti_istituto"] = $recapiti_istituto;
        return $return;
    }

    private function righe_indirizzo_istituto_vendite($val)
    {
        $ind_1 = $val->Toponimo;

        if($val->Civico)
            $ind_1.= ", ".$val->Civico;
        if($val->Esponente)
            $ind_1.= $val->Esponente;
        if($val->Interno)
            $ind_1.="/".$val->Interno;
        if($val->Dettagli)
            $ind_1.=", ".$val->Dettagli;

        $ind_3 = "";

        $ind_2 = $val->Cap." ".$val->Comune;
        $ind_2_senza_prov = $ind_2;
        if($val->Provincia!=null)
            $ind_2.= " ".$val->Provincia;

        $indirizzo_destinatario = array();
        $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if($lunghezza<50)
        {
            $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
            $indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
            $indirizzo_destinatario['Riga4'] = "";
        }
        else if($lunghezza<=100)
        {
            $pos = $lunghezza/2;
            //echo $pos;
            for( $i=0; $i<$pos; $i++)
            {
                $carattere = substr(strtoupper($ind_1), $pos-$i,1);
                //echo $carattere."*";
                if($carattere==" ")
                {
                    //echo $pos-$i;
                    $pos = $pos-$i;
                    break;
                }
            }

            $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0 , $pos);
            $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos+1);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
        }
        ///////////////////////

        $indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
        if($ind_3!="")
            $indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

        $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);

        if($ind_3!="")
            $indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

        if(isset($this->Denominazione))
            $indirizzo['Destinatario'] = $this->Denominazione;
        else
            $indirizzo['Destinatario'] = "";

        return $indirizzo_destinatario;
    }

    private function getTribunale($c , $tipo = null, $tipo_CC = "comune")
    {
        $query = "SELECT * FROM ufficio_giudiziario WHERE ";

        if($tipo_CC=="comune")
            $query.= "CC = '".$c."' ";
        else if($tipo_CC=="ufficio")
            $query.= "CC_Ufficio = '".$c."' ";

        if($tipo!=null)
            $query.= "AND Tipo = '".$tipo."' ";

        $query.= "LIMIT 1";
        //echo "<h1>".$query."</h1>";

        return $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"ufficio_giudiziario");
    }

    public function GetAttoPrec($ID,$c)
    {
        $query = "SELECT * FROM atto WHERE ID = ".$ID." AND CC = '".$c."'";
        $attoPrec = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"atto");

        return $attoPrec->Atto." n.".$attoPrec->ID_Cronologico." del ".$attoPrec->Anno_Cronologico;
    }

    public function GetTerziPigno($ID, $Tipo, $c,$docType)
    {
        if($Tipo == "terzi" || $Tipo = "veicolo")
        {

            $query = "SELECT notifica_atto.ID as ID_Notifica_Atto, notifica_atto.*, pignoramento_presso_terzi.* FROM notifica_atto 
            LEFT JOIN pignoramento_presso_terzi on notifica_atto.ID_Collegamento = pignoramento_presso_terzi.ID  
            WHERE notifica_atto.Tipo_Atto_Notificato = 'pignoramento' AND notifica_atto.Atto_Notificato_ID = ".$ID." AND notifica_atto.CC = '".$c."'
            AND notifica_atto.Tipo_Notifica <> 'debitore'";
            $TERZI = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            $query = "select ID from notifica_atto where Atto_Notificato_ID = ".$ID." AND Tipo_Notifica = 'debitore' AND Modalita_Stampa = 'pec'";
            $flagPecDebitore = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            if(count($TERZI) > 0)
            {
                $nomi_terzi = "";
                $terzi_pro_tempore = "";
                for($i=0;$i<count($TERZI);$i++) {

                    if($TERZI[$i]["Tipo_Terzi"]=="banca") {
                        $query = "SELECT * FROM banca WHERE ID = '" . $TERZI[$i]["Terzo_ID"] . "' AND CC = '*****'";
                        $TERZI[$i]["Dati_Terzo"] = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"banca");

                        $nome_terzo = $TERZI[$i]["Dati_Terzo"]["Denominazione"];
                    }
                    else if($TERZI[$i]["Tipo_Terzi"]=="lavoro")
                    {
                        /*$query = "SELECT PRT.Cognome_Ditta,PRT.Nome
                        FROM `pignoramento_generale` AS `PG`
                        JOIN `v_partita` AS PRT ON `PRT`.`Partita_ID` = `PG`.`Partita_ID`
                        where ID = ".$ID;*/
                        $query = "SELECT * FROM v_utente WHERE Utente_ID = " . $TERZI[$i]["Terzo_ID"];
                        $TERZI[$i]["Dati_Terzo"] = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
                        $nome_terzo = $TERZI[$i]["Dati_Terzo"]["Cognome_Ditta"]. " ".$TERZI[$i]["Dati_Terzo"]["Nome"];
                    } else return array("terzi" => null, "terzi_pro_tempore" => null, "Arr_Terzi" => $TERZI, "ID_Debit" => $flagPecDebitore);

                    if ($i > 0) {
                        $nomi_terzi .= ", nonche' ";
                        $terzi_pro_tempore.= ", nonche' ";
                    }

                    $nomi_terzi .= strtoupper($nome_terzo);
                    $terzi_pro_tempore.= strtoupper($nome_terzo);

                    $terzi_pro_tempore.= ", in persona del legale rappresentante pro tempore";
                }

                return array("terzi" => $nomi_terzi, "terzi_pro_tempore" => $terzi_pro_tempore, "Arr_Terzi" => $TERZI, "ID_Debit" => $flagPecDebitore);

            }else return array("terzi" => null, "terzi_pro_tempore" => null, "Arr_Terzi" => array(), "ID_Debit" => $flagPecDebitore);
        }
        else return array("terzi" => null, "terzi_pro_tempore" => null, "Arr_Terzi" => array(), "ID_Debit" => array());

    }

    public function GetVehicle($array,$c){

        $return = "
      <table cellspacing='0' cellpadding='1' ><tbody>";

        $query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = ".$array["ID"]." AND CC = '".$c."'";
        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $return .= "<tr>";
        $return .= "<td><b>Tipo veicolo</b></td>";
        $return .= "<td><b>Marca veicolo</b></td>";
        $return .= "<td><b>Targa</b></td>";
        $return .= "</tr>";

        //$return.= "</tr><td colspan='3'><hr></td></tr>";
        $return.= "</table><hr><table cellspacing='0' cellpadding='1'>";
        for($i = 0; $i < count($result); $i++)
        {
            $return .= "<tr>";
            $return .= "<td>".$result[$i]["Tipo_Veicolo"]."</td>";
            $return .= "<td>".$result[$i]["Marca_Veicolo"]."</td>";
            $return .= "<td>".strtoupper($result[$i]["Targa_Veicolo"])."</td>";
            $return .= "</tr>";
        }
        $return .= "</tbody></table>";

        //var_dump($return);
        //die;

        return $return;

    }

    public function GetPecGestore($c)
    {
        $query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
        $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"enti_gestiti");

        if( $result->Gestore_ID != 0 ) {
            $query = "SELECT * FROM gestore WHERE ID = '" . $result->Gestore_ID . "'";
            $gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"gestore");
        }
        else {
            $query = "SELECT * FROM gestore WHERE ID = '" . $result->Info_ID . "'";
            $gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "gestore");
        }

        return $gestore->PEC;

    }

    public function testoUfficiale($val,$tribunale,$c)
    {
        $Ufficiale_Consegna = $val["Tipo_Ufficiale"];
        /*if($Ufficiale_Consegna == "riscossione")
        {
            $testo_ufficiale = "Ufficiale della Riscossione ( Atto di nomina n.___ del __/__/____ effettuato da _________________ )";
        }
        else */if($Ufficiale_Consegna == "giudiziario")
        {
            $comune = isset($tribunale->Comune)?ucfirst($tribunale->Comune):"";
            $testo_ufficiale = "Ufficiale Giudiziario addetto all'U.N.E.P. del Circondario del Tribunale di ".$comune;
        }
        else if($Ufficiale_Consegna == "riscossione")
        {
            $query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
            $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"enti_gestiti");

            if( $result->Gestore_ID != 0 ) {
                $query = "SELECT * FROM gestore WHERE ID = '" . $result->Gestore_ID . "'";
                $gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"gestore");
            }
            else {
                $query = "SELECT * FROM gestore WHERE ID = '" . $result->Info_ID . "'";
                $gestore = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query), "gestore");
            }

            if($gestore->Tipo == "Concessionario")
                $denom_gestore = $gestore->Tipo." ".$gestore->Denominazione;
            else
                $denom_gestore = $gestore->Denominazione;

            $testo_ufficiale = "Ufficiale della Riscossione, su delega del ".$denom_gestore;
        }
        else
            $testo_ufficiale = "";//$testo_ufficiale = "Ufficiale Giudiziario addetto U.N.E.P.";

        return $testo_ufficiale;
    }

    public function GetRiscossioneMinMax($ID)
    {
        $return = array();

        $query = "SELECT * FROM atto WHERE ID = ".$ID;
        $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto");

        $return["Riscossione_Max"] = $result["Diritto_Riscossione_Massimo"];
        $return["Riscossione_Min"] = $result["Diritto_Riscossione_Minimo"];

        return $return;
    }

    public function tutti_gli_atti_notificati($Partita_ID)
    {
        $query = "SELECT * FROM atto WHERE Partita_ID = ".$Partita_ID." AND (DocumentTypeId = 2 OR DocumentTypeId = 4)";
        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $atti_notificati = "";
        $aCapo = "";
        for($i=count($result)-1, $x=1 ; $i>=0; $i--, $x++)
        {
            $ing = $result[$i];

            $numeroatto = $ing["ID_Cronologico"]." DEL ".$ing["Anno_Cronologico"];
            if($ing["Data_Notifica"]!=null && $ing["Data_Notifica"]!='' && $ing["Data_Notifica"]!='0000-00-00')
            {
                if($i>0) $aCapo = "<br>";
                else $aCapo = "";
                $atti_notificati .= $x.") ".strtoupper($ing["Atto"]." N. ".$numeroatto." NOTIFICATO IL ".$this->date->Get_DateNewFormat($ing["Data_Notifica"],"DB")).$aCapo;
            }

            $query = "SELECT D.Description AS Descrizione_Pigno ,N.Data_Notifica ,P.* 
                        FROM pignoramento_generale as P 
                        JOIN document_type as D on D.Id = P.DocumentTypeId 
                        LEFT JOIN notifica_atto as N on P.ID = N.Atto_Notificato_ID
                        where P.Atto_ID = ".$ing["ID"];
            $resultPigno = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
            //var_dump($resultPigno);die;
            //echo $query;
            //die;
            $aCapo = "<br>";
            $countResult = count($resultPigno);
            for($j = 0; $j<$countResult; $j++){
                if($resultPigno[$j]["Data_Notifica"] != null && $resultPigno[$j]["Data_Notifica"] != "")
                    $atti_notificati .= $x.".".($j+1).") ".strtoupper($resultPigno[$j]["Descrizione_Pigno"]." N. ".$resultPigno[$j]["ID_Cronologico"]." DEL ".$resultPigno[$j]["Anno_Cronologico"]." NOTIFICATO IL ".$this->date->Get_DateNewFormat($resultPigno[$j]["Data_Notifica"],"DB")).$aCapo;
            }
        }

        return $atti_notificati;
    }

    public function getToponimo($el,$c)
    {
        if($el->Via_ID!=1)
        {
            $query = "SELECT * FROM toponimo WHERE ID = '".$el->Via_ID."' AND CC_Comune = '".$c."'";
            $el->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimo");
        }
        else if($el->Via_Cap_ID!=1)
        {
            $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$el->Via_Cap_ID."'";
            $el->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimi_cappati");
        }
        else
            $el->Toponimo = null;
        return $el;
    }

    function quinto_campo($el,$rata = 1)
    {
        $quinto_campo = "";

        //ID COMUNE 3 CIFRE
        $query = "SELECT * FROM enti_gestiti WHERE CC = '".$el->CC."'";
        $comune = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($el->CC);
        $id_comune = $comune->ID;
        //unset($comune);

        for($i=0; $i< 3-strlen($id_comune) ;$i++)
            $quinto_campo .= "0";
        $quinto_campo .= $id_comune;



        $perPigno = (int)$el->DocumentTypeId < 10 ? "0".$el->DocumentTypeId:$el->DocumentTypeId.""  ;

        //TIPO SERVIZIO + RISCOSSIONE 2 CIFRE
        switch($el->Atto)
        {
            case "Sollecito pre ingiunzione":				$quinto_campo.="11";	    break;
            case "Avviso di messa in mora":				    $quinto_campo.="12";	    break;
            case "Sollecito di pagamento":					$quinto_campo.="03";	    break;
            case "Ingiunzione":								$quinto_campo.="02";	    break;
            case "Avviso di intimazione ad adempiere":		$quinto_campo.="04";	    break;
            case "Sollecito avviso di intimazione":			$quinto_campo.="05";	    break;
            default:                                        $quinto_campo.=$perPigno;   break;
        }

        //NUMERO RATA 2 CIFRE
        for($i=0; $i< 2-strlen($rata) ;$i++)
            $quinto_campo .= "0";
        $quinto_campo .= $rata;

        //ANNO 2 CIFRE
        $cfr_anno = str_split($el->Anno_Cronologico);
        if(count($cfr_anno)>2)
            $anno = $cfr_anno[2].$cfr_anno[3];
        else
            $anno = "00";
        $quinto_campo .= $anno;

        //ATTO 7 CIFRE
        for($i=0; $i< 7-strlen($el->ID_Cronologico) ;$i++)
            $quinto_campo .= "0";
        $quinto_campo .= $el->ID_Cronologico;

        //COD POSTA 2 CIFRE
        $cod_posta = fmod($quinto_campo,93);
        for($i=0; $i< 2-strlen($cod_posta) ;$i++)
            $quinto_campo .= "0";
        $quinto_campo .= $cod_posta;

        return $quinto_campo;
    }

    public function testo_autorizzazione($selezione,$el)
    {
        if($selezione==1)
        {
            $data_auto = $this->date->Get_DateNewFormat($el->Data_Autorizzazione_1,"DB");
            $auto = $el->Autorizzazione_1;
            if($auto!="" && $data_auto!="")
            {
                return "AUT. N. ".$auto." DEL ".$data_auto;
            }
        }
        else if($selezione==2)
        {
            $data_auto = $this->date->Get_DateNewFormat($el->Data_Autorizzazione_2,"DB");
            $auto = $el->Autorizzazione_2;
            if($auto!="" && $data_auto!="")
            {
                return "AUT. N. ".$auto." DEL ".$data_auto;
            }
        }

        return false;
    }

    public function righe_indirizzo_utente($val)
    {
        if(isset($val->Recapito)) {
            if ($val->Recapito != null)
                $indirizzo = $val->Recapito;
        }
        else if(isset($val->Domicilio)) {
            if ($val->Domicilio != null)
                $indirizzo = $val->Domicilio;
        }
        else
            $indirizzo = $val->Residenza;

        //var_dump($indirizzo);

        if(strtoupper($indirizzo->Paese)=="ITALIA")
        {
            //$ind_1 = isset($indirizzo->Toponimo->Nome)?$indirizzo->Toponimo->Nome:$indirizzo->Toponimo->Odonimo;
            if(!isset($indirizzo->Toponimo->Nome))
                $ind_1 = isset($indirizzo->Toponimo->Odonimo)?$indirizzo->Toponimo->Odonimo:null;
            else
                $ind_1 = $indirizzo->Toponimo->Nome;

            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            if($indirizzo->Civico)
                $ind_1.= ", ".$indirizzo->Civico;
            if($indirizzo->Esponente)
                $ind_1.= " ".$indirizzo->Esponente;
            if($indirizzo->Interno)
                $ind_1.="/".$indirizzo->Interno;
            if($indirizzo->Dettagli)
                $ind_1.=", ".$indirizzo->Dettagli;

            $ind_3 = "";
        }
        else
        {
            $ind_1 = $indirizzo->Toponimo->Nome;
            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            $ind_3 = $indirizzo->Paese;
        }

        $ind_2 = $indirizzo->Cap." ".$indirizzo->Comune;
        $ind_2_senza_prov = $ind_2;
        if($indirizzo->Provincia!=null)
            $ind_2.= " ".$indirizzo->Provincia;

        $indirizzo_destinatario = array();
        $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if($lunghezza<50)
        {
            $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
            $indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
            $indirizzo_destinatario['Riga4'] = "";
        }
        else if($lunghezza<=100)
        {
            $pos = $lunghezza/2;
            //echo $pos;
            for( $i=0; $i<$pos; $i++)
            {
                $carattere = substr(strtoupper($ind_1), $pos-$i,1);
                //echo $carattere."*";
                if($carattere==" ")
                {
                    //echo $pos-$i;
                    $pos = $pos-$i;
                    break;
                }
            }

            $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0 , $pos);
            $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos+1);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
        }
        ///////////////////////

        $indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
        if($ind_3!="")
            $indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

        $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
        if($ind_3!="")
            $indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

        if($val->Genere == "D")
        {
            if(isset($val->Ditta)) $indirizzo_destinatario['Destinatario'] = $val->Ditta;
            else  $indirizzo_destinatario['Destinatario'] = $val->Cognome_Ditta;

            if(isset($val->Sigla_Forma_Giuridica))
                $indirizzo_destinatario['Destinatario'].= " ".$val->Sigla_Forma_Giuridica;
        }
        else
        {
            //$Cognome = isset($val->Cognome)?$val->Cognome:$val->Cognome_Ditta;
            if(isset($val->Cognome) && isset($val->Nome)) $indirizzo_destinatario['Destinatario'] = $val->Cognome." ".$val->Nome;
            else $indirizzo_destinatario['Destinatario'] = isset($val->Cognome)?$val->Cognome:$val->Cognome_Ditta;
        }

        if(isset($val->Recapito))
            if($val->Recapito->ID>0)
                $indirizzo_destinatario['Destinatario'].= " C/O ".strtoupper($val->Recapito->Presso);

        if(strlen($indirizzo_destinatario['Destinatario'])>45){
            $a_destinatario = array();
            $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
            $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0])+1, 40);
            $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
        }

        return $indirizzo_destinatario;
    }

    public function getDetailInstanment($importo,$scadenza)
    {
        $return = "<table style='width: 500px;'>";
        for($i=0;$i<count($importo);$i++)
        {
            $return .= "<tr>";
            $return .= "    <td style='width: 20%;'>- Rata ".($i+1)."</td>";
            $return .= "    <td style='width: 30%;'>".$importo[$i]." Euro</td>";
            $return .= "    <td style='width: 20%;'>Scadenza</td>";
            $return .= "    <td style='width: 30%;'>".$scadenza[$i]."</td>";
            $return .= "</tr>";
        }
        $return .= "</table>";

        return $return;
    }
    public function firme_responsabili($el)
    {
        $firma_path = "/archivio/Firme/".$el->CC."/";
        $percorso = FIRME."/".$el->CC."/";

        $firma = array();
        $firma['Funzionario'] = $firma_path.$el->Funzionario_Firma;
        $firma['Funzionario_Path'] = $percorso.$el->Funzionario_Firma;
        $firma['Funzionario_Nome'] = $el->Funzionario_Responsabile;
        $firma['Funzionario_Intestazione'] = "Il Funzionario responsabile";
        $firma['Funzionario_Testo'] = $el->Funzionario_Testo;

        if($el->Funzionario_Firma=="" && $el->Funzionario_Testo!="si")
        {
            $firma['Funzionario'] = "";
            $firma['Funzionario_Path'] = "";
            $firma['Funzionario_Nome'] = "";
            $firma['Funzionario_Intestazione'] = "";
            $firma['Funzionario_Testo'] = "";
        }

        $firma['Responsabile'] = $firma_path.$el->Responsabile_Firma;
        $firma['Responsabile_Path'] = $percorso.$el->Responsabile_Firma;
        $firma['Responsabile_Nome'] = $el->Responsabile_Procedimento;
        $firma['Responsabile_Intestazione'] = "Il Responsabile del procedimento";
        $firma['Responsabile_Testo'] = $el->Responsabile_Testo;

        if($el->Responsabile_Firma=="" && $el->Responsabile_Testo!="si")
        {
            $firma['Responsabile'] = "";
            $firma['Responsabile_Path'] = "";
            $firma['Responsabile_Nome'] = "";
            $firma['Responsabile_Intestazione'] = "";
            $firma['Responsabile_Testo'] = "";
        }


        $firma['Ufficiale'] = $firma_path.$el->Ufficiale_Firma;
        $firma['Ufficiale_Path'] = $percorso.$el->Ufficiale_Firma;
        $firma['Ufficiale_Nome'] = $el->Ufficiale_Riscossione;
        $firma['Ufficiale_Intestazione'] = "L'Ufficiale della riscossione";
        $firma['Ufficiale_Testo'] = $el->Ufficiale_Testo;
        if($el->Ufficiale_Firma=="" && $el->Ufficiale_Testo!="si")
        {
            $firma['Ufficiale'] = "";
            $firma['Ufficiale_Path'] = "";
            $firma['Ufficiale_Nome'] = "";
            $firma['Ufficiale_Intestazione'] = "";
            $firma['Ufficiale_Testo'] = "";
        }

        $firma['Responsabile_Richieste'] = $firma_path.$el->Responsabile_Richieste_Firma;
        $firma['Responsabile_Richieste_Path'] = $percorso.$el->Responsabile_Richieste_Firma;
        $firma['Responsabile_Richieste_Nome'] = $el->Responsabile_Richieste;
        $firma['Responsabile_Richieste_Intestazione'] = "Responsabile della richiesta";
        $firma['Responsabile_Richieste_Testo'] = $el->Responsabile_Richieste_Testo;
        if($el->Responsabile_Richieste_Firma=="" && $el->Responsabile_Richieste_Testo!="si")
        {
            $firma['Responsabile_Richieste'] = "";
            $firma['Responsabile_Richieste_Path'] = "";
            $firma['Responsabile_Richieste_Nome'] = "";
            $firma['Responsabile_Richieste_Intestazione'] = "";
            $firma['Responsabile_Richieste_Testo'] = "";
        }

        return $firma;
    }

    public function carica_firme($firma1, $firma2, $firma3, $firma4 = null, $el)
    {
        $firma = $this->firme_responsabili($el);

        if($firma1!="")
        {
            $temp[1]['intestazione'] = $firma[$firma1."_Intestazione"];
            $temp[1]['nome'] = $firma[$firma1."_Nome"];
            if($firma[$firma1."_Testo"]=="si")
                $temp[1]['firma'] = $el->Testo_Sostitutivo;
            else
                $temp[1]['firma'] = $firma[$firma1];
        }
        else
        {
            $temp[1]['intestazione'] = "";
            $temp[1]['nome'] = "";
            $temp[1]['firma'] = "";
        }

        if($firma2!="")
        {
            $temp[2]['intestazione'] = $firma[$firma2."_Intestazione"];
            $temp[2]['nome'] = $firma[$firma2."_Nome"];
            if($firma[$firma2."_Testo"]=="si")
                $temp[2]['firma'] = $el->Testo_Sostitutivo;
            else
                $temp[2]['firma'] = $firma[$firma2];
        }
        else
        {
            $temp[2]['intestazione'] = "";
            $temp[2]['nome'] = "";
            $temp[2]['firma'] = "";
        }

        if($firma3!="")
        {
            $temp[3]['intestazione'] = $firma[$firma3."_Intestazione"];
            $temp[3]['nome'] = $firma[$firma3."_Nome"];
            if($firma[$firma3."_Testo"]=="si")
                $temp[3]['firma'] = $el->Testo_Sostitutivo;
            else
                $temp[3]['firma'] = $firma[$firma3];
        }
        else
        {
            $temp[3]['intestazione'] = "";
            $temp[3]['nome'] = "";
            $temp[3]['firma'] = "";
        }

        if($firma4!="")
        {
            $temp[4]['intestazione'] = $firma[$firma4."_Intestazione"];
            $temp[4]['nome'] = $firma[$firma4."_Nome"];
            if($firma[$firma4."_Testo"]=="si")
                $temp[4]['firma'] = $el->Testo_Sostitutivo;
            else
                $temp[4]['firma'] = $firma[$firma4];
        }
        else
        {
            $temp[4]['intestazione'] = "";
            $temp[4]['nome'] = "";
            $temp[4]['firma'] = "";
        }

        return $temp;
    }

    public function righe_indirizzo_enti_esterni($el)
    {

        if($el->Paese=="Italia")
        {
            $ind_1 = $el->Toponimo;
            if($el->Frazione)
                $ind_1 = $el->Frazione.", ".$ind_1;

            if($el->Civico)
                $ind_1.= ", ".$el->Civico;
            if($el->Esponente)
                $ind_1.= $el->Esponente;
            if($el->Interno)
                $ind_1.="/".$el->Interno;
            if($el->Dettagli)
                $ind_1.=", ".$el->Dettagli;

            $ind_3 = "";
        }
        else
        {
            $ind_1 = $el->Toponimo;
            if($el->Frazione)
                $ind_1 = $el->Frazione.", ".$ind_1;

            $ind_3 = $el->Paese;
        }

        $ind_2 = $el->Cap." ".$el->Comune;
        $ind_2_senza_prov = $ind_2;
        if($el->Provincia!=null)
            $ind_2.= " ".$el->Provincia;

        $indirizzo = array();
        $indirizzo['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if($lunghezza<50)
        {
            $indirizzo['Riga1'] = strtoupper($ind_1);
            $indirizzo['Riga2'] = strtoupper($ind_2);
            $indirizzo['Riga3'] = strtoupper($ind_3);
            $indirizzo['Riga4'] = "";
        }
        else if($lunghezza<=100)
        {
            $pos = $lunghezza/2;
            //echo $pos;
            for( $i=0; $i<$pos; $i++)
            {
                $carattere = substr(strtoupper($ind_1), $pos-$i,1);
                //echo $carattere."*";
                if($carattere==" ")
                {
                    //echo $pos-$i;
                    $pos = $pos-$i;
                    break;
                }
            }

            $indirizzo['Riga1'] = substr(strtoupper($ind_1), 0 , $pos);
            $indirizzo['Riga2'] = substr(strtoupper($ind_1), $pos+1);
            $indirizzo['Riga3'] = strtoupper($ind_2);
            $indirizzo['Riga4'] = strtoupper($ind_3);
        }
        ///////////////////////

        $indirizzo['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
        if($ind_3!="")
            $indirizzo['Completo'].= ", ".strtoupper($ind_3);

        $indirizzo['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
        if($ind_3!="")
            $indirizzo['Senza_Provincia'].= ", ".strtoupper($ind_3);


        return $indirizzo;

    }

    public function righe_indirizzo($el,$flag="old")
    {
        if($flag == "st") {
            if ($el->Recapito != null)
                $indirizzo = $el->Recapito;
            else if ($el->Domicilio != null)
                $indirizzo = $el->Domicilio;
            else
                $indirizzo = $el->Residenza;
        }
        else $indirizzo = $el;

        if(strtoupper($indirizzo->Paese)=="ITALIA")
        {
            if(!isset($indirizzo->Toponimo->Nome))
                $ind_1 = isset($indirizzo->Toponimo->Odonimo)?$indirizzo->Toponimo->Odonimo:null;
            else
                $ind_1 = $indirizzo->Toponimo->Nome;

            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            if($indirizzo->Civico)
                $ind_1.= ", ". $indirizzo->Civico;
            if($indirizzo->Esponente)
                $ind_1.= $indirizzo->Esponente;
            if($indirizzo->Interno)
                $ind_1.="/".$indirizzo->Interno;
            if($indirizzo->Dettagli)
                $ind_1.=", ".$indirizzo->Dettagli;

            $ind_3 = "";
        }
        else
        {
            $ind_1 = $indirizzo->Toponimo;
            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            $ind_3 = $indirizzo->Paese;
        }


        $ind_2 = $indirizzo->Cap." ".$indirizzo->Comune;
        $ind_2_senza_prov = $ind_2;
        if($indirizzo->Provincia!=null)
            $ind_2.= " ".$indirizzo->Provincia;

        $fax = "FAX ".$indirizzo->Fax;
        if($indirizzo->Fax=="")
            $fax = "";

        $indirizzo_destinatario = array();
        $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if($lunghezza<50)
        {
            if($flag == "old") {
                $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
                $indirizzo_destinatario['Riga2'] = strtoupper($ind_2) . " " . strtoupper($ind_3);
                $indirizzo_destinatario['Riga3'] = $fax;
                $indirizzo_destinatario['Riga4'] = "";
            }
            else if($flag == "st"){
                $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
                $indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
                $indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
                $indirizzo_destinatario['Riga4'] = "";
            }
        }
        else if($lunghezza<=100)
        {
            $pos = $lunghezza/2;
            //echo $pos;
            for( $i=0; $i<$pos; $i++)
            {
                $carattere = substr(strtoupper($ind_1), $pos-$i,1);
                //echo $carattere."*";
                if($carattere==" ")
                {
                    //echo $pos-$i;
                    $pos = $pos-$i;
                    break;
                }
            }
            if($flag == "old") {
                $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0, $pos);
                $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos + 1);
                $indirizzo_destinatario['Riga3'] = strtoupper($ind_2) . " " . strtoupper($ind_3);
                $indirizzo_destinatario['Riga4'] = $fax;
            }else if($flag == "st"){
                $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0, $pos);
                $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos + 1);
                $indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
                $indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
            }
        }
        ///////////////////////

        $indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
        if($ind_3!="")
            $indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

        $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
        if($ind_3!="")
            $indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

        if($flag=="old") {
            $indirizzo_destinatario['Destinatario'] = $el->Denominazione;
        }
        else if($flag == "st"){

            if($el->Genere == "D")
            {
                $indirizzo_destinatario['Destinatario'] = $el->Ditta;
                if($el->Sigla_Forma_Giuridica!=null)
                    $indirizzo_destinatario['Destinatario'].= " ".$el->Sigla_Forma_Giuridica;
            }
            else
            {
                $indirizzo_destinatario['Destinatario'] = $el->Cognome." ".$el->Nome;
            }

            if(isset($el->Recapito))
                if($el->Recapito->ID>0)
                    $indirizzo_destinatario['Destinatario'].= " C/O ".strtoupper($el->Recapito->Presso);

            if(strlen($indirizzo_destinatario['Destinatario'])>45){
                $a_destinatario = array();
                $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
                $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0])+1, 40);
                $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
            }

        }
        return $indirizzo_destinatario;

    }

    public function InfoAtto($el, $IDCron, $annoCron, $atto, $protocollo, $dataNotifica)
    {
        $info_atto = "";
        if($IDCron>0){
            $info_atto.= "di ".strtoupper($atto)." N.".$IDCron;
            if($protocollo!="")
                $info_atto.= " ".$protocollo;
            $info_atto.= " / ".$annoCron;
        }

        if( $dataNotifica !="" && $dataNotifica != null)
            $info_atto.= ", notificata/o il ".$this->date->Get_DateNewFormat( $dataNotifica ,"DB").", ";

        $el["InfoAtto"] = $info_atto;

        return $el;
    }

    function where_stati_notifica ( $array_fields , $array_values )
    {
        $num = count($array_fields);
        $ctrl_num = count($array_values);

        if($num != $ctrl_num) return false;

        $query = "";
        for( $i=0; $i<$num; $i++ )
        {
            if($array_values[$i] != null && $array_values[$i] != "")
            {
                $valore = $array_values[$i];
                if($valore == "Nessuno" || $valore == "Nessuna") $valore = "0";

                if($valore == "Tutti" || $valore == "Tutte")
                    $query .= $array_fields[$i]." != '0' AND ";
                else
                    $query .= $array_fields[$i]." = '".$valore."' AND ";

            }
        }

        $query .= "1";

        return $query;

    }

    function where_date_vuote ($array_date)
    {
        $num = count($array_date);

        $query = "";
        for( $i=0; $i<$num; $i++ )
        {
            $query .= "( ".$array_date[$i]." = null OR ".$array_date[$i]." = '0000-00-00' ) AND ";
        }

        $query .= "1";

        return $query;
    }

    function where_giacenza ($giacenza, $ind_validato)
    {
        $query = "";
        if($giacenza == "Nessuno")
        {
            $query .= "atto.Stato_Notifica = 0 ";
            if($ind_validato=="attesa")	$query .= "AND atto.Indirizzo_Validato!='si'";
            else if($ind_validato=="Validato") $query .= "AND atto.Indirizzo_Validato='si'";
        }
        else if($giacenza == "Tutti")
        {
            $query .= "atto.Stato_Notifica != 0 ";
            if($ind_validato=="attesa")	$query .= "AND atto.Indirizzo_Validato!='si'";
            else if($ind_validato=="Validato") $query .= "AND atto.Indirizzo_Validato='si'";
        }
        else if($giacenza!="")
        {
            $query .= "atto.Stato_Notifica = '".$giacenza."' ";
            if($ind_validato=="attesa")	$query .= "AND atto.Indirizzo_Validato!='si'";
            else if($ind_validato=="Validato") $query .= "AND atto.Indirizzo_Validato='si'";
        }
        else if($giacenza=="")
        {
            if($ind_validato=="attesa")	$query .= "atto.Indirizzo_Validato!='si'";
            else if($ind_validato=="Validato") $query .= "atto.Indirizzo_Validato='si'";
        }

        return $query;
    }

    function da_a_data_array_order ( $c , $atto , $campo_data , $dadata , $adata , $where = null , $order = null, $where2 = null, $where3 = null)
    {
        $query = "SELECT DISTINCT atto.* FROM atto, tributo, partita_tributi ";

        if($order=="alfabetico")
        {

            $query.= ", ";
            $query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
            $query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
            $query.="UNION ";
            $query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
            $query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
            $query.="AS UNIONE_UTENTE ";

        }

        if($order=="tribunale")
        {
            $query.= ", utente, indirizzo, ufficio_giudiziario ";
        }

        $query.= "WHERE atto.CC = '".$c."' AND atto.Atto = '".$atto."' AND atto.Partita_ID = tributo.Partita_ID AND partita_tributi.ID = atto.Partita_ID ";

        if($order=="alfabetico")
        {
            $query.= " AND UNIONE_UTENTE.ID = partita_tributi.Utente_ID ";
        }

        if($order=="tribunale")
        {
            $query.= " AND utente.ID = partita_tributi.Utente_ID AND ";
            $query.= " indirizzo.Utente_ID = utente.ID AND ufficio_giudiziario.Tipo = 'tribunale' AND ufficio_giudiziario.CC = indirizzo.CC_Indirizzo ";
        }

        for( $i=0 ; $i<count($dadata) ; $i++ )
        {
            if( $dadata[$i] != null && $dadata[$i] != "" )
            {

                $query.= "AND ( atto.".$campo_data[$i]." >= '".$dadata[$i]."' AND atto.".$campo_data[$i]." <= '".$adata[$i]."' ) ";
            }
        }

        if($where != null)
        {
            $query.= "AND (".$where.") ";
        }

        if($where2 != null)
        {
            $query.= "AND (".$where2.") ";
        }

        if($where3 != null)
        {
            $query.= "AND (".$where3.") ";
        }

        $query.= "ORDER BY ";

        if($order=="verbale")
            $query.= "ABS(tributo.Titolo_Sanzione) ASC, ";
        if($order=="info")
            $query.= "atto.Info_Cartella ASC, ";
        if($order=="alfabetico")
            $query.= "UNIONE_UTENTE.NOME_UTENTE ASC , UNIONE_UTENTE.Nome ASC, ";
        if($order=="tribunale")
            $query.= "ufficio_giudiziario.CC_Ufficio ASC, ";

        $query.= "partita_tributi.Comune_ID ASC, atto.Anno_Cronologico ASC, atto.ID_Cronologico ASC";

        return $query;
    }

    public function ListaTipiPagamento ()
    {
        $queryTipi = "SELECT DISTINCT Tipo_Pagamento FROM pagamento ORDER BY Tipo_Pagamento";
        $resTipi = $this->cls_db->getResults($this->cls_db->ExecuteQuery($queryTipi));//mysql_query($queryTipi);
        $optionTipi = "<option value=''></option>\n";
        for ($i=0; $i < count($resTipi); $i++)
        {
            $optionTipi .= "<option value='" . $resTipi[$i]['Tipo_Pagamento'] . "'>" . $resTipi[$i]['Tipo_Pagamento'] . "</option>\n";
        }
        $optionTipi .= "<option value='TELEMATICO'>TELEMATICO</option>\n";
        return $optionTipi;
    }

    public function ListaModalitaPagamento ()
    {
        $queryModalita = "SELECT DISTINCT Modalita FROM pagamento ORDER BY Modalita";
        $resModalita = $this->cls_db->getResults($this->cls_db->ExecuteQuery($queryModalita));// mysql_query($queryModalita);
        $optionModalita = "<option value=''></option>";
        for ($i=0; $i < count($resModalita); $i++)
        {
            if($resModalita[$i]['Modalita']!="")
                $optionModalita .= "<option value='" . $resModalita[$i]['Modalita'] . "'>" . $resModalita[$i]['Modalita'] . "</option>";
        }
        return $optionModalita;
    }

    public function importiSplitPayment($el,$due=true){
        $arrayRitorno = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0, 13=>0, 14=>0, 15=>0, 16=>0);
        if($due===true){
            for ($i = 0; $i < count($el->Tributo); $i++)
                $arrayRitorno[$el->Tributo[$i]->Codice_Scorporo]+= $el->Tributo[$i]->Imposta;
        }

        return $arrayRitorno;
    }

    public function getSumCodiciTributo($a_categories){
        $sum = 0;
        for ($i = 0; $i < count($a_categories); $i++){
            if($i==0)
                $sum-= $a_categories[$i];
            else
                $sum+= $a_categories[$i];
        }

        return $sum;
    }

    public function cutSplitPaymentCat($a_splitCategoryDue, $splitCategoryNumber, &$a_splitCategoryPaid, &$totalPaid)
    {
        $splitDue = $a_splitCategoryDue[$splitCategoryNumber] - $a_splitCategoryPaid[$splitCategoryNumber];
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($totalPaid>$splitDue){
            $singleSplit = $splitDue;
            $a_splitCategoryPaid[$splitCategoryNumber] += $singleSplit;
            $totalPaid -= $singleSplit;
        }
        else{
            $singleSplit = $totalPaid;
            $a_splitCategoryPaid[$splitCategoryNumber] += $singleSplit;
            $totalPaid = 0;
        }

        return $singleSplit;
    }


    public function pagamenti_completi($ID, $Partita_ID){
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID <= ".$ID." AND Partita_ID = ".$Partita_ID." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";

        return $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query))['TOTALE_PAGAMENTI'];
    }

    public function getTotalAmountDue($atto)
    {
        $a_amount['tot'] = $atto->Totale_Dovuto;
        if($atto->Rate_Previste>0){
            if($atto->Tipo_Totale_Rate==1){
                $a_amount['tot'] += $atto->Diritto_Riscossione_Minimo;
                $a_amount['diritto'] = $atto->Diritto_Riscossione_Minimo;
            }
            else{
                $a_amount['tot'] += $atto->Diritto_Riscossione_Massimo;
                $a_amount['diritto'] = $atto->Diritto_Riscossione_Massimo;
            }
        }
        else{
            $data_not = new DateTime($atto->Data_Notifica);
            $data_not->modify("+2 months");
            if ($this->controlloDataPrimoPagamento($atto) > $data_not->format('Y-m-d')){
                $a_amount['tot'] += $atto->Diritto_Riscossione_Massimo;
                $a_amount['diritto'] = $atto->Diritto_Riscossione_Massimo;
            }
            else{
                $a_amount['tot'] += $atto->Diritto_Riscossione_Minimo;
                $a_amount['diritto'] = $atto->Diritto_Riscossione_Minimo;
            }
        }

        $a_amount['tot_residuo'] = $a_amount['tot'] - $this->pagamenti_completi($atto->ID,$atto->Partita_ID);

        return $a_amount;
    }

    public function controlloDataPrimoPagamento($atto)
    {
        if(isset($atto->Pagamento[0]))
        {
            return $atto->Pagamento[0]->Data_Pagamento;
        }
        else
            return null;
    }

    public function getTotalSplit($pagamenti)
    {

        $a_split = array();
        for($i=1;$i<16;$i++){
            $a_split[$i] = 0;
        }

        for($q=0;$q<count($pagamenti);$q++)
        {
            for($i=1;$i<16;$i++){
                $key = "Split_Payment".$i;
                $a_split[$i] += $pagamenti[$q]->$key;
            }
        }

        return $a_split;
    }

    public function cutPercentSplitPayment($a_splitDue, $splitNumber, &$a_splitPaid, &$totalPaid, $percent, $singleSplit=0)
    {
        $splitDue = ($a_splitDue[$splitNumber]) * $percent;
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($splitDue>$a_splitDue[$splitNumber]-$a_splitPaid[$splitNumber])
            $splitDue = $a_splitDue[$splitNumber]-$a_splitPaid[$splitNumber];

        if($splitDue<=0)
            return $singleSplit;

        if($totalPaid>$splitDue){
            $singleSplit+= $splitDue;
            $a_splitPaid[$splitNumber] += $splitDue;
            $totalPaid -= $splitDue;
        }
        else{
            $singleSplit+= $totalPaid;
            $a_splitPaid[$splitNumber] += $totalPaid;
            $totalPaid = 0;
        }

        return $singleSplit;
    }

    public function cutSplitPayment($a_splitDue, $splitNumber, &$a_splitPaid, &$totalPaid)
    {
        $splitDue = $a_splitDue[$splitNumber] - $a_splitPaid[$splitNumber];
        if($splitDue>0)
            $splitDue = number_format($splitDue,2,".","");

        if($totalPaid>$splitDue){
            $singleSplit = $splitDue;
            $a_splitPaid[$splitNumber] += $singleSplit;
            $totalPaid -= $singleSplit;
        }
        else{
            $singleSplit = $totalPaid;
            $a_splitPaid[$splitNumber] += $singleSplit;
            $totalPaid = 0;
        }

        return $singleSplit;
    }

    public function getTotalAmountDuePigno($el)
    {
        $el = $this->gestione_totali($el);
        $a_amount['tot'] = 0;
        $a_amount['spese_accessorie'] = 0;
        if($el->Rate_Previste>0){
            $a_amount['tot'] = (double) $el->Totali_Array[$el->Tipo_Totale_Rate];
            $a_amount['spese_accessorie'] = (double) $el->Parziali_Spese_Accessorie[$el->Tipo_Totale_Rate];
        }
        else{
            //var_dump($el->Totali_Array);
            $pagamento_pigno = $el->Pagamento[0]->Importo;

            if($pagamento_pigno == number_format($el->Totali_Array[3],2,",","."))
            {
                $a_amount['tot'] = (double) number_format($el->Totali_Array[3],2,",",".");
                $a_amount['spese_accessorie'] = (double) $el->Parziali_Spese_Accessorie[3];
            }
            else if($pagamento_pigno == number_format($el->Totali_Array[2],2,",","."))
            {
                $a_amount['tot'] = (double) number_format($el->Totali_Array[2],2,",",".");
                $a_amount['spese_accessorie'] = (double) $el->Parziali_Spese_Accessorie[2];
            }
            else
            {
                $a_amount['tot'] = (double) number_format($el->Totali_Array[1],2,",",".");
                $a_amount['spese_accessorie'] = (double) $el->Parziali_Spese_Accessorie[1];
            }
        }

        $a_amount['spese_pignoramento'] = (double) $el->Totale_Spese_Notifica;

        return $a_amount;
    }

    public function splitPayment( $miaPartita,  $mioAtto, $mioPigno, array $a_splitParams, $idParams, $CC,$pagamento)
    {
        if(!$miaPartita->Split_Parameters_ID>0){
            $miaPartita->Split_Parameters_ID = $idParams;

            if($this->cls_db->DbSave($this->utils->GetObjectQuery($miaPartita,"partita_tributi",array("ID" => $miaPartita->ID)))) $this->log->info("AGGIORNAMENTO PARTITA RIUSCITO");
            else $this->log->error("AGGIORNAMENTO PARTITA FALLITO");
            //$miaPartita->Update($miaPartita->ID);
        }

        //IMPORTI CATEGORIA DOVUTI [CODICI TRIBUTO]
        $a_splitCategoryDue = $this->importiSplitPayment($miaPartita);
        //IMPORTI CATEGORIA PAGATI [INIZIALIZZAZIONE]
        $a_splitCategoryPaid = $this->importiSplitPayment($miaPartita,false);

        $sumCodiciTributo = $this->getSumCodiciTributo($a_splitCategoryDue);


//  0       PAGAMENTI (Non in tabella)
//  1	    Imposta principale
//  2	    Spese accertamento
//  3	    Spese ingiunzione
//  4	    Spese avviso di intimazione
//  5	    Spese pignoramento
//  6	    Spese accessorie pignoramento
//  7	    Sanzione generica
//  8	    Sanzione dichiarazione
//  9	    Sanzione pagamento
//  10	    Interessi
//  11	    Altri diritti e accessori
//  12	    Spese ricerca
//  13	    Addizionale provinciale
//  14	    Oneri riscossione
//  15	    Eca
//  16	    Addizionale Comunale

        //SETTAGGIO SPESE DI RICERCA
        if(!$a_splitCategoryDue[12]>0){
            $query = "SELECT * FROM parametri_annuali WHERE CC = '".$CC."' AND Anno = '".$miaPartita->Anno_Riferimento."' AND Tipo_Riscossione = '*****'";
            $parametri_annuale = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"parametri_annuali");//new gestione_parametri_annuali($CC, $miaPartita->Anno_Riferimento, $miaPartita->Tipo);
            if ($parametri_annuale->ID != null){
                $a_splitCategoryDue[12]+= $parametri_annuale->Spese_Ricerca;
                $a_splitCategoryDue[2]-= $parametri_annuale->Spese_Ricerca;
            }
        }

        $a_splitDue = array();
        $a_splitPaid = array();

        //AGGREGO CATEGORIE IN PARAMETRI DI SCORPORO
        for($i=0;$i<count($a_splitParams);$i++){
            $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
            $a_splitPaid[$a_splitParams[$i]['split_number']] = 0;
            $a_splitRequest[$a_splitParams[$i]['split_number']] = 0;

            for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                if($a_splitParams[$i]['categories'][$y]>0)
                    $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
            }
        }

        //PRENDO I PAGAMENTI PRECEDENTI
        $query = "SELECT ID FROM pagamento WHERE Partita_ID = '".$miaPartita->ID."' AND CC='".$miaPartita->CC."' AND Tipo_Atto = 'Precedenti'";
        $id_pagamento_prec = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pagamento")["ID"];// single_answer_query($query);
        $query = "SELECT * FROM pagamento WHERE ID = '".$id_pagamento_prec."' AND CC = '".$miaPartita->CC."'";
        $pagamenti_precedenti = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pagamento");//new pagamento($id_pagamento_prec, $miaPartita->CC);

        $sum = 0;
        for($i=1;$i<=16;$i++){
            $key = "Split_Payment".$i;
            $sum+= $pagamenti_precedenti->$key;
        }
        if($sum>0){
            for($i=0;$i<count($a_splitParams);$i++){
                $key = "Split_Payment".$a_splitParams[$i]['split_number'];
                $a_splitPaid[$a_splitParams[$i]['split_number']] = $pagamenti_precedenti->$key;
            }
        }
        else if($miaPartita->Tipo == "CDS"){
            /**
             * SCORPORO PAGAMENTI DA GITCO VECCHIO
             */
            $maggiorazione_sollecito = 0;
            if($a_splitCategoryDue[1] > $sumCodiciTributo)
                $maggiorazione_sollecito = $a_splitCategoryDue[1] - $sumCodiciTributo;

            $totalPaid = $a_splitCategoryDue[0] - $maggiorazione_sollecito;
            for($i=0;$i<count($a_splitParams);$i++){
                $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
                for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                    if($a_splitParams[$i]['categories'][$y]>0)
                        $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
                }
            }

            if($totalPaid>0){
                $this->cutSplitPaymentCat($a_splitCategoryDue, 12, $a_splitCategoryPaid, $totalPaid);
                $this->cutSplitPaymentCat($a_splitCategoryDue, 2, $a_splitCategoryPaid, $totalPaid);
                $this->cutSplitPaymentCat($a_splitCategoryDue, 1, $a_splitCategoryPaid, $totalPaid);

                for($i=0;$i<count($a_splitParams);$i++){
                    for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                        if($a_splitParams[$i]['categories'][$y]>0)
                            $a_splitPaid[$a_splitParams[$i]['split_number']]+= $a_splitCategoryPaid[$a_splitParams[$i]['categories'][$y]];
                    }
                }

                for($i=1;$i<=16;$i++){
                    $key = "Split_Payment".$i;
                    if(isset($a_splitPaid[$i]))
                        $pagamenti_precedenti->$key = $a_splitPaid[$i];
                }

                if($id_pagamento_prec>0) {
                    if($this->cls_db->DbSave($this->utils->GetObjectQuery($pagamenti_precedenti,"pagamento",array("ID" => $id_pagamento_prec))))
                        $this->log->info("PAGAMENTO AGGIORNATO CORRETTAMENTE");
                    else
                        $this->log->error("IMPOSSIBILE AGGIORNARE PAGAMENTO");
                    //$pagamenti_precedenti->Update($id_pagamento_prec);
                }
                else{
                    $pagamenti_precedenti->Tipo_Atto = "Precedenti";
                    $pagamenti_precedenti->CC = $miaPartita->CC;
                    $pagamenti_precedenti->Partita_ID = $miaPartita->ID;
                    $pagamenti_precedenti->Data_Registrazione = date('Y-m-d');
                    if($this->cls_db->DbSave($this->utils->GetObjectQuery($pagamenti_precedenti,"pagamento")))
                        $this->log->info("PAGAMENTO INSERITO CORRETTAMENTE");
                    else
                        $this->log->error("IMPOSSIBILE INSERIRE PAGAMENTO");
                    //$pagamenti_precedenti->Insert();
                }
            }
        }

        for($i_atto=0;$i_atto<count($miaPartita->Atto);$i_atto++) {

            $attoID = $miaPartita->Atto[$i_atto]->ID;

            if($miaPartita->Atto[$i_atto]->Atto == "Avviso di intimazione ad adempiere")
                $a_splitCategoryDue[4] += $miaPartita->Atto[$i_atto]->Spese_Notifica + $miaPartita->Atto[$i_atto]->CAN + $miaPartita->Atto[$i_atto]->CAD;
            else
                $a_splitCategoryDue[3] += $miaPartita->Atto[$i_atto]->Spese_Notifica + $miaPartita->Atto[$i_atto]->CAN + $miaPartita->Atto[$i_atto]->CAD;


            $a_splitCategoryDue[10]+= $miaPartita->Atto[$i_atto]->Interessi;

            if($miaPartita->Atto[$i_atto]->ID==$mioAtto->ID && $mioPigno->ID == null){

                $a_amountsAtto = $this->getTotalAmountDue($miaPartita->Atto[$i_atto]);
                $totalDue = $a_amountsAtto['tot'];
                $a_splitCategoryDue[14] = $a_amountsAtto['diritto'];
                break;
            }

            if(isset($miaPartita->Atto[$i_atto]->Pagamento[0])){
                $a_amountsAtto = $this->getTotalAmountDue($miaPartita->Atto[$i_atto]);
                $totalDue = $a_amountsAtto['tot'];
                $a_splitCategoryDue[14] = $a_amountsAtto['diritto'];

                /**             QUESRA QUERY LA HO AGGIUNTA IO POTREBBE ESSERE SBAGLIATA           **/

                $query = "SELECT * FROM pagamento WHERE Atto_ID = ".$miaPartita->Atto[$i_atto]->ID." AND Partita_ID = ".$miaPartita->Atto[$i_atto]->Partita_ID." AND Tipo_Atto NOT LIKE 'Pignoramento%'";
                $pagamento_atti = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pagamento");

                $a_split_cur = $this->getTotalSplit($pagamento_atti);
                if(array_sum($a_split_cur)>0){
                    for($i=0;$i<count($a_splitParams);$i++)
                        $a_splitPaid[$a_splitParams[$i]['split_number']]+= $a_split_cur[$a_splitParams[$i]['split_number']];
                }
                else{

                    for($i=0;$i<count($a_splitParams);$i++){
                        $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
                        for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                            if($a_splitParams[$i]['categories'][$y]>0)
                                $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
                        }
                    }

                    $paymentSumCur = $this->totale_pagamenti($miaPartita->Atto[$i_atto]);
                    $percent = $paymentSumCur / $totalDue;

                    if($paymentSumCur>0){
                        for($i=0;$i<count($a_splitParams);$i++) {
                            if ($a_splitParams[$i]['type'] == 100){
                                $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent);
                                if($paymentSumCur<=0)
                                    break;
                            }
                        }

                        for($i=0;$i<count($a_splitParams);$i++){
                            if ($a_splitParams[$i]['type'] != 100){
                                $this->cutSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur);
                                if($paymentSumCur<=0)
                                    break;
                            }
                        }
                    }
                }
            }
        }

        $attoSplit = "atto";
        //SE PAGAMENTO RIFERITO A PIGNORAMENTO
        if(strpos($pagamento->Tipo_Atto,'Pignoramento')!==false){
            $attoSplit = "pignoramento";
            $a_amountsPigno = $this->getTotalAmountDuePigno($mioPigno);

            $totalDue = number_format($a_amountsPigno['tot'],2,",",".");

            $a_splitCategoryDue[5]+= $a_amountsPigno['spese_pignoramento'];
            $a_splitCategoryDue[6]+= $a_amountsPigno['spese_accessorie'];
        }

        for($i=0;$i<count($a_splitParams);$i++){
            $a_splitDue[$a_splitParams[$i]['split_number']] = 0;
            for($y=1;$y<=count($a_splitParams[$i]['categories']);$y++){
                if($a_splitParams[$i]['categories'][$y]>0)
                    $a_splitDue[$a_splitParams[$i]['split_number']]+= $a_splitCategoryDue[$a_splitParams[$i]['categories'][$y]];
            }
        }

        //SELEZIONO RATE RIFERITE ALL'ATTO/PIGNORAMENTO
        $queryRatePrec = "SELECT * FROM pagamento ";
        $queryRatePrec .= "WHERE Atto_ID = '" . $pagamento->Atto_ID . "' AND ";
        $queryRatePrec .= "Partita_ID = '" . $pagamento->Partita_ID . "' AND ";
        $queryRatePrec .= "Rata <= '" . $pagamento->Rata."' ";
        $queryRatePrec .= "ORDER BY Rata DESC, Data_Pagamento ASC";

        //$cls_db = new cls_db();
        $obj_rate = $this->cls_db->getResultsNull($this->cls_db->SelectQuery($queryRatePrec),"pagamento","object");

        $requestPayment = array_shift($obj_rate);

        if($attoSplit=="pignoramento")
            $requestAtto = $mioPigno;
        else
            $requestAtto = $mioAtto;

        $a_split_cur = $this->getTotalSplit($obj_rate);
        if(array_sum($a_split_cur)>0){
            for($i=0;$i<count($a_splitParams);$i++)
                $a_splitPaid[$a_splitParams[$i]['split_number']]+= $a_split_cur[$a_splitParams[$i]['split_number']];
        }
        else{
            $paymentSumCur = 0;
            for($i_rata=0;$i_rata<count($obj_rate);$i_rata++){
                $paymentSumCur+= $obj_rate[$i_rata]->Importo;
            }

            $percent = (double) $paymentSumCur /  (double) $totalDue;
            if($paymentSumCur>0){
                for($i=0;$i<count($a_splitParams);$i++) {
                    if ($a_splitParams[$i]['type'] == 100){
                        $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                for($i=0;$i<count($a_splitParams);$i++){
                    if ($a_splitParams[$i]['type'] != 100){
                        $this->cutSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur);
                        if($paymentSumCur<=0)
                            break;
                    }
                }
            }
        }

        $sum = 0;
        for($i=1;$i<=16;$i++){
            $key = "Split_Payment".$i;
            $sum+= $pagamento->$key;
        }
        $requestPaymentSum = $sum;//$this->getSplitSum();

        if($requestPaymentSum>0){
            for($i=0;$i<count($a_splitParams);$i++){
                $key = "Split_Payment".$a_splitParams[$i]['split_number'];
                $a_splitRequest[$a_splitParams[$i]['split_number']]+= $pagamento->$key;
            }
        }
        else{

            $paymentSumCur = $requestPayment->Importo;

            $percent = (double) $paymentSumCur / (double) $totalDue;

            if($paymentSumCur>0){
                for($i=0;$i<count($a_splitParams);$i++) {
                    if ($a_splitParams[$i]['type'] == 100){
                        $a_splitRequest[$a_splitParams[$i]['split_number']] = $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                for($i=0;$i<count($a_splitParams);$i++){
                    if ($a_splitParams[$i]['type'] != 100){
                        $a_splitRequest[$a_splitParams[$i]['split_number']] = $this->cutSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                for($i=0;$i<count($a_splitParams);$i++) {
                    if ($a_splitParams[$i]['type'] == 100){
                        $a_splitRequest[$a_splitParams[$i]['split_number']] = $this->cutPercentSplitPayment($a_splitDue, $a_splitParams[$i]['split_number'], $a_splitPaid, $paymentSumCur, $percent, $a_splitRequest[$a_splitParams[$i]['split_number']]);
                        if($paymentSumCur<=0)
                            break;
                    }
                }

                if($paymentSumCur>0){
                    $a_splitRequest[12]+= $paymentSumCur;
                }
            }

//            print_r($a_splitDue);
//            echo "<br><br>";
//            print_r($a_splitPaid);
//            echo "<br><br>";
//            print_r($a_splitRequest);
//            die;

            for($i=1;$i<=16;$i++){
                $key = "Split_Payment".$i;
                if(isset($a_splitRequest[$i]))
                    $pagamento->$key = $a_splitRequest[$i];
            }

            if(number_format(array_sum($a_splitRequest),2,".","")==$pagamento->Importo) {
                if($this->cls_db->DbSave($this->utils->GetObjectQuery($pagamento,"pagamento",array("ID"=>$pagamento->ID))))
                    $this->log->info("DATI PAGAMENTO AGGIORNATI CORRETTAMENTE");
                else
                    $this->log->error("IMPOSSIBILE AGGIORNARE DATI PAGAMENTO");
                //$this->Update($pagamento->ID);
            }
            else{
                for($i=1;$i<=16;$i++)
                    $a_splitRequest[$i] = 0;
            }
        }

        return $a_splitRequest;
    }

    public function estraiTitoloTributo($el)
    {
        for ($i = 0; $i < count($el->Tributo); $i++)
        {
            $el->Tributo[$i];

            if ($el->Tributo[$i]->Tipo_Codice == "IMPORTO")
            {
                switch($el->Tributo[$i]->Tipo_Info)
                {
                    case "S": return $el->Tributo[$i]->Titolo_Sanzione; 	break;
                    case "E": return $el->Tributo[$i]->Titolo_Entrata; 	break;
                    case "M": return $el->Tributo[$i]->Matricola;	 		break;
                }

            }
        }

        return null;
    }

    public function getDataPartita($progr , $c , $a = null ){

        $query = "SELECT * FROM partita_tributi WHERE ID = '".$progr."' AND CC = '".$c."'";
        if($a!=null)	$query.=" AND Anno_Riferimento = '".$a."'";

        $res = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"partita_tributi");

        $query = "SELECT * FROM utente WHERE ID = '".$res->Utente_ID."' AND CC_Comune = '".$c."'";
        $res->Utente = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"utente");

        $coo = utf8_decode($res->Coo_ID);
        if($coo!="")
        {
            $coo = explode("*", $coo);

            for($i=1;$i<count($coo);$i++)
            {
                $res->Coo_ID[$i-1] = $coo[$i];
            }
        }
        else
        {
            $res->Coo_ID = null;
        }

        $contaTributi = 0;
        $codice_tributo = "";

        $query = "SELECT * FROM tributo WHERE Partita_ID = '".$res->ID."' ORDER BY Codice_Tributo ASC";
        $res->Tributo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");


        for( $i=0; $i<count($res->Tributo); $i++)
        {
            $query = "SELECT Descrizione, Tipo_Codice, Testo_Codice, Codice_Scorporo FROM codice_tributo WHERE Codice_Tributo = '".$res->Tributo[$i]->Codice_Tributo."'";
            $resultCT = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"codice_tributo");
            $res->Tributo[$i]->Tipo_Tributo = $resultCT["Descrizione"];
            $res->Tributo[$i]->Tipo_Codice = $resultCT["Tipo_Codice"];
            $res->Tributo[$i]->Testo_Codice = $resultCT["Testo_Codice"];
            $res->Tributo[$i]->Codice_Scorporo = $resultCT["Codice_Scorporo"];
            $res->Data_Inizio_Interessi = null;

            $array_tributo = $res->Tributo[$i];//new tributo( $tributo_id[$i]['ID'] , $c );

            if($array_tributo->Tipo_Codice=="IMPORTO" && $array_tributo->Data_Decorrenza_Interessi!="" && $array_tributo->Data_Decorrenza_Interessi!=null){
                if($res->Data_Inizio_Interessi=="" || $res->Data_Inizio_Interessi==null)
                    $res->Data_Inizio_Interessi = $array_tributo->Data_Decorrenza_Interessi;

                $data_decorrenza = $array_tributo->Data_Decorrenza_Interessi;
            }

            if($codice_tributo!=$array_tributo->Codice_Tributo){
                if($i>0)
                    $contaTributi++;
                $res->a_tributi[$contaTributi] = $array_tributo;
            }
            else{
                $res->a_tributi[$contaTributi]->Imposta+= $array_tributo->Imposta;
            }

            $codice_tributo = $array_tributo->Codice_Tributo;
        }

        $temp = "";
        for($i=0;$i<count($res->a_tributi);$i++){
            if($res->a_tributi[$i]->Codice_Tributo=="5243"){
                $temp = $res->a_tributi[$i];
                unset($res->a_tributi[$i]);
                $res->a_tributi = array_values($res->a_tributi);
            }
        }
        if($temp!="")
            $res->a_tributi[]=$temp;

        $testoSemestri = "";
        $countGiri = 0;
        $dataInizioInteressi = $res->Data_Inizio_Interessi;

        $query = "SELECT * FROM atto WHERE Partita_ID = '".$res->ID."'";
        $res->Atto = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

        $res->Somma_Spese_Notifica = 0;

        for( $i=0; $i<count($res->Atto); $i++)
        {
            //$this->Atto[$i] = new atto( $atto_id[$i]['ID'] , $c );

            $res->Somma_Spese_Notifica += $res->Atto[$i]->Spese_Notifica;
            $res->Somma_Spese_Notifica += $res->Atto[$i]->CAN;
            $res->Somma_Spese_Notifica += $res->Atto[$i]->CAD;

            $res->Atto[$i]->Semestri = "";
            if($res->Atto[$i]->Atto == "Ingiunzione" || $res->Atto[$i]->Atto == "Avviso di intimazione ad adempiere" || $res->Atto[$i]->Atto == "Avviso di messa in mora")
            {
                $res->ultimo_atto = $res->Atto[$i]->ID;


                $data1 = new DateTime($dataInizioInteressi);
                $data2 = new DateTime($res->Atto[$i]->Data_Calcolo_Interessi);
                $interval = $data1->diff($data2);
                $res->Atto[$i]->Data_Inizio_Calcolo = $dataInizioInteressi;
                $semestri = floor($interval->format('%a')/182.5);
                if($res->Atto[$i]->Interessi>0){
                    if($res->Tipo == "CDS") {
                        if ($semestri <= 1)
                            $semestri = "1 semestre calcolato";
                        else
                            $semestri .= " semestri calcolati";
                    }
                    else{
                        $semestri = "Interesse calcolato";
                    }

                    if($countGiri>0)
                        $testoSemestri.= " + ";

                    $testoSemestri.= $semestri." dal ".$this->date->Get_DateNewFormat($res->Atto[$i]->Data_Inizio_Calcolo,"DB")." al ".$this->date->Get_DateNewFormat($res->Atto[$i]->Data_Calcolo_Interessi,"DB");

                    $countGiri++;
                }

                $res->Atto[$i]->Semestri = $testoSemestri;

                $dataInizioInteressi = $res->Atto[$i]->Data_Calcolo_Interessi;


                if($res->Atto[$i]->Atto == "Ingiunzione")
                {
                    if( $res->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" )) )
                        $res->ultimo_atto_scaduto = $res->Atto[$i]->ID;
                }
                else
                {
                    $res->ultimo_avviso = $res->Atto[$i]->ID;

                    if( $res->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-180 days" )) )
                        $res->ultimo_atto_scaduto = $res->Atto[$i]->ID;
                }
            }

            $res->Atto_Not = $i+1;


            if($res->Atto[$i]->Stato != "Annullata")
            {
                $res->Atto_Calc = $i+1;
            }
        }

        $query = "SELECT * FROM ricorso_generale WHERE Partita_ID = '".$res->ID."'";
        $res->Ricorso = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

        $query = "SELECT * FROM pignoramento_generale WHERE Partita_ID = '".$res->ID."'";
        $res->Pignoramento = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");


        /*for( $i=0; $i<count($ricorso_id); $i++)
        {
            $this->Ricorso[$i] = new ricorso_generale( $ricorso_id[$i]['ID'] , $c );
        }

        for( $i=0; $i<count($pignoramento_id); $i++)
        {
            $this->Pignoramento[$i] = new pignoramento( $pignoramento_id[$i]['ID'] , $c );
        }*/
        return $res;
    }

    function where_campi ( $array_fields , $array_values )
    {
        $num = count($array_fields);
        $ctrl_num = count($array_values);

        if($num != $ctrl_num) return false;

        $query = "";
        for( $i=0; $i<$num; $i++ )
        {
            if($array_values[$i] != null && $array_values[$i] != "")
            {
                $query .= $array_fields[$i]." = '".$array_values[$i]."' AND ";
            }
        }

        $query .= "1";

        return $query;

    }

    public function query_pignoramenti_docId( $c, $documentTypeId = null, $order = null, $where = null )
    {
        /**
        Query selezione pignoramenti

        @param $c(string) = Codice catastale;
        @param $tipo_pigno(string) = Tipo di pignoramento;
        @param $where(array) = lista di where (facoltativo);
         */

        $query = "SELECT * ";
        $query.= "FROM v_list_pignoramento AS PIG_GEN ";//, tributo AS TR, partita_tributi AS PAR ";

        if($order=="tribunale")
        {
            $query.= "LEFT JOIN ufficio_giudiziario AS TRIB ON TRIB.Tipo = 'tribunale' AND TRIB.CC = PIG_GEN.Res_CC ";
            //$query.= "GROUP BY PIG_GEN.Pignoramento_ID ";
        }

        $query.= "WHERE PIG_GEN.CC = '".$c."' ";

        if(!empty($documentTypeId))
            $query.= " AND PIG_GEN.DocumentTypeId = ".$documentTypeId." ";

            //var_dump($where);
        if($where != null)
            for($i=0;$i<count($where);$i++)
                if($where[$i]!=null)
                    $query.= " AND (".$where[$i].")";
        
        if($order=="tribunale")
        {
            //$query.= "LEFT JOIN ufficio_giudiziario AS TRIB ON TRIB.Tipo = 'tribunale' AND TRIB.CC = PIG_GEN.Res_CC ";
            $query.= "GROUP BY PIG_GEN.Pignoramento_ID ";
        }

        $query.= " ORDER BY ";
        if($order=="alfabetico")
            $query.= "PIG_GEN.Cognome_Ditta ASC , PIG_GEN.Nome ASC, ";
        if($order=="tribunale")
            $query.= "TRIB.CC_Ufficio ASC, ";

        $query.= "PIG_GEN.Anno_Cronologico ASC, PIG_GEN.ID_Cronologico ASC";

        return $query;
    }

    public function query_selezione_pignoramenti( $c, $tipo_pigno = null, $tipo_terzi = null, $order = null, $where = null )
    {
        /**
        Query selezione pignoramenti

        @param $c(string) = Codice catastale;
        @param $tipo_pigno(string) = Tipo di pignoramento;
        @param $where(array) = lista di where (facoltativo);
         */

        $query = "SELECT DISTINCT PIG_GEN.ID, PIG_GEN.Partita_ID ";
        $query.= "FROM v_pignoramento AS PIG_GEN, tributo AS TR, partita_tributi AS PAR ";

        if($order=="alfabetico")
        {

            $query.= ", ";
            $query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
            $query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
            $query.="UNION ";
            $query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
            $query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
            $query.="AS UTENTE ";

        }

        if($order=="tribunale")
        {
            $query.= ", utente AS UTENTE, indirizzo AS IND, ufficio_giudiziario AS TRIB ";
        }

        $query.= "WHERE PAR.ID = PIG_GEN.partita_ID AND PIG_GEN.CC = '".$c."' AND TR.Partita_ID = PIG_GEN.Partita_ID";

        if($tipo_pigno!=null)
            $query.= " AND PIG_GEN.Tipo = '".$tipo_pigno."' ";

        if($tipo_pigno=="terzi" && $tipo_terzi!=null)
        {
            $query.= " AND PIG_GEN.Tipo_Terzi = '".$tipo_terzi."' ";
        }

        if($order=="alfabetico")
            $query.= " AND UTENTE.ID = PAR.Utente_ID ";

        if($order=="tribunale")
        {
            $query.= " AND UTENTE.ID = PAR.Utente_ID AND";
            $query.= " IND.Utente_ID = UTENTE.ID AND TRIB.Tipo = 'tribunale' AND TRIB.CC = IND.CC_Indirizzo";
        }

        if($where != null)
            for($i=0;$i<count($where);$i++)
                if($where[$i]!=null)
                    $query.= " AND (".$where[$i].")";

        $query.= " ORDER BY ";

        if($order=="verbale")
            $query.= "ABS(tributo.Titolo_Sanzione) ASC, ";
        if($order=="info")
            $query.= "atto.Info_Cartella ASC, ";
        if($order=="alfabetico")
            $query.= "UTENTE.NOME_UTENTE ASC , UTENTE.Nome ASC, ";
        if($order=="tribunale")
            $query.= "TRIB.CC_Ufficio ASC, ";

        $query.= "PAR.Comune_ID ASC, PIG_GEN.Anno_Cronologico ASC, PIG_GEN.ID_Cronologico ASC, PIG_GEN.Comune_ID ASC";

        return $query;
    }

    public function tutti_gli_atti_notificati_pigno($partita)
    {
        $this->date->changeFormat("IT");
        $atti_notificati = array();
        $tot = 0.00;
        for($i=count($partita->Atto)-1; $i>=0; $i--)
        {
            $ing = $partita->Atto[$i];

            if($ing->Atto == "Ingiunzione" || $ing->Atto == "Avviso di intimazione ad adempiere")
            {
                if($ing->Atto == "Ingiunzione")
                    $partita->Ingiunzione = $ing;

                $numeroatto = $ing->ID_Cronologico." DEL ".$ing->Anno_Cronologico;
                if($ing->Data_Notifica!=null && $ing->Data_Notifica!='0000-00-00')
                {
                    $atti_notificati[] = strtoupper($ing->Atto." N. ".$numeroatto." NOTIFICATO IL ".$this->date->Get_DateNewFormat($ing->Data_Notifica,"DB"));
                }
                else
                {
                    if($ing->Motivo_Notifica!=0)
                    {
// 						$query = "SELECT Descrizione FROM parametri_notifica WHERE ID = ".$ing->Motivo_Notifica;
// 						$anomalia = single_query($query);

// 						$atti_notificati[] = strtoupper($ing->Atto." N. ".$numeroatto." - ".strtoupper($anomalia));
                    }
                    else
                    {
                        if($ing->Rielabora_Flag!="si")
                            $atti_notificati[] = strtoupper($ing->Atto." N. ".$numeroatto." IN ATTESA DI VERIFICA");
                    }
                }
            }
        }

        return $atti_notificati;
    }
}