<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_DateTimeInLine.php");

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();


$c = $cls_help->getVar('c');

$ajax = $cls_help->getVar('ajax');

switch($ajax)
{
	case "nome":
		
		$ID = $cls_help->getVar('ID');
        $query = "SELECT * FROM utente WHERE ID = '".$ID."' AND CC_Comune = '".$c."'";
		$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");// new utente($ID, $c);

        $query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente->Forma_Giuridica."' AND CC = '".$c."'";
        $utente->Sigla_Forma_Giuridica = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa")["Sigla"];


        $query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente->Forma_Giuridica."' AND CC = '".$c."'";
        $utente->Forma_Giuridica_Oggetto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");
	
		if($utente->Genere!="D")
			$ritorno = $utente->Cognome."*".$utente->Nome."*".$utente->Genere;
		else
			$ritorno = $utente->Ditta." ".$utente->Forma_Giuridica_Oggetto->Sigla."*D";
			
		echo $ritorno;
		
		break;
		
	case "info_visura":
	
		$ID_Partita = $cls_help->getVar('ID_Partita');
		$Anno_Partita = $cls_help->getVar('Anno_Partita');
		$ID_Atto = $cls_help->getVar('ID_Atto');

        $query = "SELECT * FROM partita_tributi WHERE ID = '".$ID_Partita."' AND CC = '".$c."'";
		$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");//new partita($ID_Partita, $c);

        $query = "SELECT * FROM atto WHERE ID = ".$ID_Atto." AND CC = '".$c."'";
		$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($ID_Atto, $c);
		$tipo_atto = $atto->Atto;
		$cronologico = $atto->ID_Cronologico;
		$anno = $atto->Anno_Cronologico;
		
		$info_atto = strtoupper($tipo_atto." n.".$cronologico." del ".$anno);

        $query = "SELECT * FROM utente WHERE ID = '".$partita->Utente_ID."' AND CC_Comune = '".$c."'";
		$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($partita->Utente_ID, $c);

        $query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente->Forma_Giuridica."' AND CC = '".$c."'";
        $utente->Sigla_Forma_Giuridica = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa")["Sigla"];

        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'res'";
        $utente->Residenza = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if($utente->Residenza != null)
            if($utente->Residenza->Via_ID!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente->Residenza->Via_ID."' AND CC_Comune = '".$c."'";
                $utente->Residenza->Toponimo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"toponimo");
            }
            else if($utente->Residenza->Via_Cap_ID!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente->Residenza->Via_Cap_ID."'";
                $utente->Residenza->Toponimo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
            }
            else
                $utente->Residenza->Toponimo = null;


        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'dom'";
        $utente->Domicilio = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if($utente->Domicilio != null)
            if($utente->Domicilio->Via_ID!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente->Domicilio->Via_ID."' AND CC_Comune = '".$c."'";
                $utente->Domicilio->Toponimo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"toponimo");
            }
            else if($utente->Domicilio->Via_Cap_ID!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente->Domicilio->Via_Cap_ID."'";
                $utente->Domicilio->Toponimo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
            }
            else
                $utente->Domicilio->Toponimo = null;


        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'rec'";
        $utente->Recapito = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if($utente->Recapito != null)
            if($utente->Recapito->Via_ID!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente->Recapito->Via_ID."' AND CC_Comune = '".$c."'";
                $utente->Recapito->Toponimo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"toponimo");
            }
            else if($utente->Recapito->Via_Cap_ID!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente->Recapito->Via_Cap_ID."'";
                $utente->Recapito->Toponimo = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
            }
            else
                $utente->Recapito->Toponimo = null;



		$informazioni = info_utente($utente);
		
		$ritorno = $info_atto."*".$atto->ID."*".$informazioni['riga1']."*".$informazioni['riga2']."*".$informazioni['riga3']."*".$informazioni['riga4']."*".$informazioni['riga5']."*".$utente->ID;
			
		echo $ritorno;
	
		break;
}

function info_utente($utente)
{
    $cls_date = new cls_DateTimeI("IT",false);
    $righe_indirizzo = righe_indirizzo($utente);
    if($utente->Genere=="D")
    {
        $informazioni['riga1'] = $utente->Ditta." ".$utente->Sigla_Forma_Giuridica;
        $informazioni['riga2'] = "Partita Iva: ".$utente->Partita_Iva;
        $informazioni['riga3'] = "Codice INPS: ".$utente->Azienda;
        $informazioni['riga4'] = "";
        $informazioni['riga5'] = "Indirizzo: ".$righe_indirizzo['Completo'];
    }
    else
    {
        $informazioni['riga1'] = $utente->Cognome." ".$utente->Nome;
        $informazioni['riga2'] = "Codice fiscale: ".$utente->Codice_Fiscale;
        $informazioni['riga3'] = "Comune di nascita: ".$utente->Comune_Nascita." (".$utente->Provincia_Nascita.") ".$utente->Paese_Nascita;
        $informazioni['riga4'] = "Data di nascita: ".$cls_date->Get_DateNewFormat($utente->Data_Nascita,"DB");
        $informazioni['riga5'] = "Indirizzo: ".$righe_indirizzo['Completo'];
    }

    return $informazioni;

}

function righe_indirizzo($utente)
{
    if($utente->Recapito!=null)
        $indirizzo = $utente->Recapito;
    else if($utente->Domicilio!=null)
        $indirizzo = $utente->Domicilio;
    else
        $indirizzo = $utente->Residenza;

    if(strtoupper($indirizzo->Paese)=="ITALIA")
    {
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

    if($utente->Genere == "D")
    {
        $indirizzo_destinatario['Destinatario'] = $utente->Ditta;
        if($utente->Sigla_Forma_Giuridica!=null)
            $indirizzo_destinatario['Destinatario'].= " ".$utente->Sigla_Forma_Giuridica;
    }
    else
    {
        $indirizzo_destinatario['Destinatario'] = $utente->Cognome." ".$utente->Nome;
    }

    if(isset($utente->Recapito))
        if($utente->Recapito->ID>0)
            $indirizzo_destinatario['Destinatario'].= " C/O ".strtoupper($utente->Recapito->Presso);

    if(strlen($indirizzo_destinatario['Destinatario'])>45){
        $a_destinatario = array();
        $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
        $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0])+1, 40);
        $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
    }

    return $indirizzo_destinatario;
}

?>