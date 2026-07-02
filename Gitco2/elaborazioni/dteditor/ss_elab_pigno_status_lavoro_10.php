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
Editor::inst( $db, 'v_elab_acts_pignoramenti_lavoro','Partita_ID')
    ->fields(
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Comune_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Pignoramento_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Notifica_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_lavoro')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.CC' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Info_Cartella' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Tipo_Notifica' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Printer' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_lavoro')->value( 'Printer' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.PrintType' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_lavoro')->value( 'PrintType' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Tipo_Ufficiale' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_lavoro')->value( 'Tipo_Ufficiale' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.Partita_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.PEC' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.InipecLoaded' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.REC_PRESSO' ),
        Field::inst( 'v_elab_acts_pignoramenti_lavoro.CF_PI' )
    )
    ->where("v_elab_acts_pignoramenti_lavoro.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

