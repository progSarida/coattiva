<?php
if (!session_id()) session_start();

use
    DataTables\Database,
    DataTables\Editor,
    DataTables\Editor\Field,
    DataTables\Editor\SearchPaneOptions,
    DataTables\Editor\Options,
    DataTables\Editor\SearchBuilderOptions;


include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_elaboration.php");
include_once(CLS."/cls_DateTime.php");
include_once(SUPER_CLS."/datatables.net/editor-php/DataTables.php");

$cls_help = new cls_help();
$cls_db = new cls_db();

$c = $cls_help->getVar("c");

//var_dump($c);die;


try {

    $query = Editor::inst( $db, 'V_stragiudiziali' )
        ->field(
            Field::inst( 'V_stragiudiziali.Partita_ID' ),
            Field::inst( 'V_stragiudiziali.CC' ),//->searchPaneOptions( SearchPaneOptions::inst() ),
            Field::inst( 'V_stragiudiziali.Denominazione' ),//->searchPaneOptions( SearchPaneOptions::inst() ),
            Field::inst( 'V_stragiudiziali.Comune_ID' ),//->searchPaneOptions( SearchPaneOptions::inst() ),
            Field::inst( 'V_stragiudiziali.Cognome_Ditta' ),
            Field::inst( 'V_stragiudiziali.Nome' ),//->searchPaneOptions( SearchPaneOptions::inst() ),
            Field::inst( 'V_stragiudiziali.CF_PI' ),//->searchPaneOptions( SearchPaneOptions::inst() ),
            Field::inst( 'V_stragiudiziali.ESITO' )->searchPaneOptions( SearchPaneOptions::inst()->table( 'V_stragiudiziali')->value( 'ESITO' )->where( function ($q) use ($c) { $q->where( 'V_stragiudiziali.CC', $c, '=' );} )),
            Field::inst( 'V_stragiudiziali.STATO' )->searchPaneOptions( SearchPaneOptions::inst()->table( 'V_stragiudiziali')->value( 'STATO' )->where( function ($q) use ($c) { $q->where( 'V_stragiudiziali.CC', $c, '=' );} )),
            Field::inst( 'V_stragiudiziali.Utente_ID' ),
            Field::inst( 'V_stragiudiziali.Data_Notifica_Atto' ),
            Field::inst( 'V_stragiudiziali.Atto_ID' ),
            Field::inst( 'V_stragiudiziali.Data_Notifica_60' ),
            Field::inst( 'V_stragiudiziali.Data_Notifica_5' ),
            Field::inst( 'V_stragiudiziali.Data_Notifica_1' ),
            Field::inst( 'V_stragiudiziali.Data_Notifica_Atto_Pigno_5' ),
            Field::inst( 'V_stragiudiziali.Tipo_Riscossione' ),
            Field::inst( 'V_stragiudiziali.user_check' )->searchPaneOptions( SearchPaneOptions::inst()->table( 'V_stragiudiziali')->value( 'user_check' )->where( function ($q) use ($c) { $q->where( 'V_stragiudiziali.CC', $c, '=' );} ))
        )
        ->where("V_stragiudiziali.CC ", $c,"=")
        ->process($_POST)
        //->debug(true);
        ->write( false )
        ->json();
    //$db->debug(true);
    //var_dump($debug);
        //->write( false )
        //->json()

        //->write( false )
        //->json();
}
catch(Exception $ex){

}
