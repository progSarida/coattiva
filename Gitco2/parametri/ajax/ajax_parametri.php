<?php
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
		
	include CLASSI . "/comuni.php";
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/290.php";
	include CLASSI . "/ruolo.php";

if (!session_id()) session_start();

$c = get_var('c');

$ajax = get_var('ajax');

switch($ajax)
{
	case "ufficio":
		
		$ID = get_var('ID');
	
		$ufficio_comune = new ufficio_comune($ID);
			
		$CC_ente = $ufficio_comune->CC;
		$comune_ente = new comune($CC_ente);
		$nome_ente = $comune_ente->Nome;
		$nome_ente_temp = addslashes($comune_ente->Nome);
		$com_nome_temp = addslashes($ufficio_comune->Comune);
		$com_indirizzo_temp = addslashes($ufficio_comune->Toponimo);
		 	
		$ritorno = $ufficio_comune->CC."**".$nome_ente_temp."**".$ufficio_comune->Denominazione."**".$ufficio_comune->CC_Comune."**".$com_nome_temp."**".$ufficio_comune->Provincia."**";
		$ritorno.= $ufficio_comune->Cap."**".$com_indirizzo_temp."**".$ufficio_comune->Civico."**".$ufficio_comune->Esponente."**".$ufficio_comune->Interno."**";
		$ritorno.= $ufficio_comune->Dettagli."**".$ufficio_comune->Partita_Iva."**".$ufficio_comune->Telefono."**".$ufficio_comune->Fax."**".$ufficio_comune->Mail."**";
		$ritorno.= $ufficio_comune->PEC."**".$ufficio_comune->Sito."**".$ufficio_comune->Orario."**".$ufficio_comune->ID."**".$ufficio_comune->Modalita_Invio;
	
		echo $ritorno;
	
	break;
	
	case "uff_giudiziario":
	
		$CC = get_var('CC_ufficio');
		$tipo_ufficio = get_var('tipo_ufficio');
		$ufficio_giudiziario = new ufficio_giudiziario($CC,$tipo_ufficio);
	
		$comune_ente = new comune($CC);
		$nome_ente = $comune_ente->Nome;
		$nome_ente_temp = addslashes($comune_ente->Nome);
		$com_nome_temp = addslashes($ufficio_giudiziario->Comune);
		$com_indirizzo_temp = addslashes($ufficio_giudiziario->Toponimo);
		 
		$ritorno = $CC."**".$nome_ente_temp."**".$ufficio_giudiziario->Sezione."**".$ufficio_giudiziario->CC_Ufficio."**".$com_nome_temp."**".$ufficio_giudiziario->Provincia."**";
		$ritorno.= $ufficio_giudiziario->Cap."**".$com_indirizzo_temp."**".$ufficio_giudiziario->Civico."**".$ufficio_giudiziario->Esponente."**".$ufficio_giudiziario->Interno."**";
		$ritorno.= $ufficio_giudiziario->Dettagli."**".$ufficio_giudiziario->Telefono."**".$ufficio_giudiziario->Fax."**".$ufficio_giudiziario->Mail."**";
		$ritorno.= $ufficio_giudiziario->PEC."**".$ufficio_giudiziario->Sito."**".$ufficio_giudiziario->ID."**";
		$ritorno.= $ufficio_giudiziario->Denominazione."**".$ufficio_giudiziario->Forma_Giuridica;

		echo $ritorno;

	break;
	
	case "uff_giudiziario_esistente":
	
		$CC = get_var('CC_ufficio');
		$tipo_ufficio = get_var('tipo_ufficio');
		$ufficio_giudiziario = new ufficio_giudiziario($CC,$tipo_ufficio,"ufficio");	
	
		$comune_ente = new comune($CC);
		$nome_ente = $comune_ente->Nome;
		$nome_ente_temp = addslashes($comune_ente->Nome);
		$com_nome_temp = addslashes($ufficio_giudiziario->Comune);
		$com_indirizzo_temp = addslashes($ufficio_giudiziario->Toponimo);
	
		$ritorno = $CC."**".$nome_ente_temp."**".$ufficio_giudiziario->Sezione."**".$ufficio_giudiziario->CC_Ufficio."**".$com_nome_temp."**".$ufficio_giudiziario->Provincia."**";
		$ritorno.= $ufficio_giudiziario->Cap."**".$com_indirizzo_temp."**".$ufficio_giudiziario->Civico."**".$ufficio_giudiziario->Esponente."**".$ufficio_giudiziario->Interno."**";
		$ritorno.= $ufficio_giudiziario->Dettagli."**".$ufficio_giudiziario->Telefono."**".$ufficio_giudiziario->Fax."**".$ufficio_giudiziario->Mail."**";
		$ritorno.= $ufficio_giudiziario->PEC."**".$ufficio_giudiziario->Sito."**".$ufficio_giudiziario->ID;
		$ritorno.= $ufficio_giudiziario->Denominazione."**".$ufficio_giudiziario->Forma_Giuridica;
	
		echo $ritorno;
	
	break;
		
}


?>