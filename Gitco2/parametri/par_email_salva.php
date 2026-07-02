<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_crypt.php";


$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_crypt = new cls_crypt();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

$invia = $cls_help->getVar('invia_submit');

$error = 0;
$msg = "";

$Autenticazione_Uscita_PEC = $cls_help->getVar('Autenticazione_Uscita_PEC');
$Nome_Utente_Uscita_PEC = "";
$Password_Uscita_PEC = "";

if($Autenticazione_Uscita_PEC=="si")
{
	$Nome_Utente_Uscita_PEC = $cls_help->getVar('Nome_Utente_Uscita_PEC');
	$Password_Uscita_PEC = $cls_crypt->encryptIt($cls_help->getVar('Password_Uscita_PEC'));
}
else $Autenticazione_Uscita_PEC = "no";


$Autenticazione_Uscita_email = $cls_help->getVar('Autenticazione_Uscita_email');
$Nome_Utente_Uscita_email = "";
$Password_Uscita_email = "";

if($Autenticazione_Uscita_email == "si")
{
	$Nome_Utente_Uscita_email = $cls_help->getVar('Nome_Utente_Uscita_email');
	$Password_Uscita_email = $cls_crypt->encryptIt($cls_help->getVar('Password_Uscita_email'));
}
else $Autenticazione_Uscita_email = "no";

if($invia == "Salva")
{


	$a_paramsParEMail = array(
	    'table' => 'parametri_email',
	    'fields'=> array(
	        array(  'name' => 'CC',                           'type' => 'string', 'value' => $cls_help->getVar('c')),
	        array(  'name' => 'Tipo_Riscossione',             'type' => 'string', 'value' => $cls_help->getVar('tipo_riscossione') ),
	        array(  'name' => 'Tipo_Email',                   'type' => 'string', 'value' => 'email'),
	        array(  'name' => 'Indirizzo_Email',              'type' => 'string', 'value' => $cls_help->getVar('Indirizzo_Email_email')),
	        array(  'name' => 'Nome_Utente',                  'type' => 'string', 'value' => $cls_help->getVar('Nome_Utente_email')),
	        array(  'name' => 'Password',                     'type' => 'string', 'value' => $cls_crypt->encryptIt($cls_help->getVar('Password_email'))),
					array(  'name' => 'Nome_Visualizzato',            'type' => 'string', 'value' => $cls_help->getVar('Nome_Visualizzato_email')),
					array(  'name' => 'Sicurezza_Connessione',        'type' => 'string', 'value' => $cls_help->getVar('Sicurezza_Connessione_email')),
					array(  'name' => 'Server_Posta_Arrivo',          'type' => 'string', 'value' => $cls_help->getVar('Server_Posta_Arrivo_email')),
					array(  'name' => 'Protocollo_Arrivo',            'type' => 'string', 'value' => $cls_help->getVar('Protocollo_Arrivo_email')),
					array(  'name' => 'Porta_Arrivo',                 'type' => 'string', 'value' => $cls_help->getVar('Porta_Arrivo_email')),
					array(  'name' => 'Server_Posta_Uscita',          'type' => 'string', 'value' => $cls_help->getVar('Server_Posta_Uscita_email')),
					array(  'name' => 'Protocollo_Uscita',            'type' => 'string', 'value' => $cls_help->getVar('Protocollo_Uscita_email')),
					array(  'name' => 'Porta_Uscita',                 'type' => 'string', 'value' => $cls_help->getVar('Porta_Uscita_email')),
					array(  'name' => 'Autenticazione_Uscita',        'type' => 'string', 'value' => $Autenticazione_Uscita_email),
					array(  'name' => 'Nome_Utente_Uscita',           'type' => 'string', 'value' => $Nome_Utente_Uscita_email),
					array(  'name' => 'Password_Uscita',              'type' => 'string', 'value' => $Password_Uscita_email)
	    )
	);

	$a_paramsParPEC = array(
	    'table' => 'parametri_email',
	    'fields'=> array(
	        array(  'name' => 'CC',                           'type' => 'string', 'value' => $cls_help->getVar('c')),
	        array(  'name' => 'Tipo_Riscossione',             'type' => 'string', 'value' => $cls_help->getVar('tipo_riscossione') ),
	        array(  'name' => 'Tipo_Email',                   'type' => 'string', 'value' => 'PEC'),
	        array(  'name' => 'Indirizzo_Email',              'type' => 'string', 'value' => $cls_help->getVar('Indirizzo_Email_PEC')),
	        array(  'name' => 'Nome_Utente',                  'type' => 'string', 'value' => $cls_help->getVar('Nome_Utente_PEC')),
	        array(  'name' => 'Password',                     'type' => 'string', 'value' => $cls_crypt->encryptIt($cls_help->getVar('Password_PEC'))),
					array(  'name' => 'Nome_Visualizzato',            'type' => 'string', 'value' => $cls_help->getVar('Nome_Visualizzato_PEC')),
					array(  'name' => 'Sicurezza_Connessione',        'type' => 'string', 'value' => $cls_help->getVar('Sicurezza_Connessione_PEC')),
					array(  'name' => 'Server_Posta_Arrivo',          'type' => 'string', 'value' => $cls_help->getVar('Server_Posta_Arrivo_PEC')),
					array(  'name' => 'Protocollo_Arrivo',            'type' => 'string', 'value' => $cls_help->getVar('Protocollo_Arrivo_PEC')),
					array(  'name' => 'Porta_Arrivo',                 'type' => 'string', 'value' => $cls_help->getVar('Porta_Arrivo_PEC')),
					array(  'name' => 'Server_Posta_Uscita',          'type' => 'string', 'value' => $cls_help->getVar('Server_Posta_Uscita_PEC')),
					array(  'name' => 'Protocollo_Uscita',            'type' => 'string', 'value' => $cls_help->getVar('Protocollo_Uscita_PEC')),
					array(  'name' => 'Porta_Uscita',                 'type' => 'string', 'value' => $cls_help->getVar('Porta_Uscita_PEC')),
					array(  'name' => 'Autenticazione_Uscita',        'type' => 'string', 'value' => $Autenticazione_Uscita_PEC),
					array(  'name' => 'Nome_Utente_Uscita',           'type' => 'string', 'value' => $Nome_Utente_Uscita_PEC),
					array(  'name' => 'Password_Uscita',              'type' => 'string', 'value' => $Password_Uscita_PEC)
	    )
	);

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();
	$msgErr1 = "";
	$flagPEC = 0;
	$flagMail = 0;

	if($cls_help->getVar('ID_PEC') == 0)
	{
		if(!$cls_db->DbSave($a_paramsParPEC))
		{
			$cls_db->Rollback();
			$error = 1;
			$flagPEC = 1;
		}
	}
	else {
		$a_paramsParPEC['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $cls_help->getVar('ID_PEC'));
		if(!$cls_db->DbSave($a_paramsParPEC))
		{
			$cls_db->Rollback();
			$error = 1;
			$flagPEC = 2;
		}
	}

	if($cls_help->getVar('ID_Mail') == 0)
	{
		if(!$cls_db->DbSave($a_paramsParEMail))
		{
			$cls_db->Rollback();
			$error = 1;
			$flagMail = 1;
		}
	}
	else {
		$a_paramsParEMail['updateField'] = array(   'name'=>'ID',  'type'=>'int',    'value'=> $cls_help->getVar('ID_Mail'));
		if(!$cls_db->DbSave($a_paramsParEMail))
		{
			$cls_db->Rollback();
			$error = 1;
			$flagMail = 2;
		}
	}

	$cls_db->End_Transaction();

	if($error == 0)
	{
		$msg = "Dati inseriti correttamente";
	}
	else {
		$msg = "Errore impossibile ";
		if($flagPEC > 0 && $flagMail > 0)
		{
			if($flagPEC == 1) $msg .= "inserire dati relativa alla PEC e alla E-Mail";
			else $msg .= " aggiornare dati relativi alla PEC e alla E-Mail";
		}
		else if($flagPEC > 0)
		{
			if($flagPEC == 1) $msg .= "inserire dati relativa alla PEC";
			else $msg .= " aggiornare dati relativi alla PEC";
		}
		else {
			if($flagMail == 1) $msg .= "inserire dati relativa alla E-Mail";
			else $msg .= " aggiornare dati relativi alla E-Mail";
		}

	}

}
else if( $invia == "Delete" )
{

	$cls_db->Start_Transaction();
	$cls_db->Begin_Transaction();

	if(!$cls_db->Delete("parametri_email" , "CC = '".$c."' AND Tipo_Riscossione = '".$tipo_riscossione."'"))
	{
		$cls_db->Rollback();
		$error = 1;
		$msg = "Errore, impossibile eliminare i dati";
	}
	else $msg = "Dati eliminati correttamente";

	$cls_db->End_Transaction();

}

header("Location: par_email.php?tipo_riscossione={$cls_help->getVar('tipo_riscossione')}&a={$a}&c={$c}&error={$error}&msg={$msg}");
?>
