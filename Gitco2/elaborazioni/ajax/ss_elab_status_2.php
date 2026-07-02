<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

// DataTables PHP library
include( DT_EDITOR."/lib/DataTables.php" );
 
// Alias Editor classes so they are easy to use
use
    DataTables\Editor,
    DataTables\Editor\Field,
    DataTables\Editor\SearchPaneOptions;

// Build our Editor instance and process the data coming from _POST
Editor::inst( $db, 'v_elab_acts','Partita_ID')
    ->fields(
        Field::inst( 'v_elab_acts.Comune_ID' ),
        Field::inst( 'v_elab_acts.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts.CC' ),
        Field::inst( 'v_elab_acts.Info_Cartella' ),
        Field::inst( 'v_elab_acts.Printer' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts')->value( 'Printer' )
        ->where( function ($q) { $q->where( 'v_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts.PrintType' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts')->value( 'PrintType' )
        ->where( function ($q) { $q->where( 'v_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts.Tipo_Ufficiale' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts')->value( 'Tipo_Ufficiale' )
        ->where( function ($q) { $q->where( 'v_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts.Partita_ID' ),
        Field::inst( 'v_elab_acts.PEC' ),
        Field::inst( 'v_elab_acts.InipecLoaded' ),
        Field::inst( 'v_elab_acts.REC_PRESSO' ),
        Field::inst( 'v_elab_acts.CF_PI' )
    )
    ->where("v_elab_acts.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

