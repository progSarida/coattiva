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
Editor::inst( $db, 'v_elab_acts_pignoramenti','Partita_ID')
    ->fields(
        Field::inst( 'v_elab_acts_pignoramenti.Comune_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti.Pignoramento_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti.Notifica_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti.CC' ),
        Field::inst( 'v_elab_acts_pignoramenti.Info_Cartella' ),
        Field::inst( 'v_elab_acts_pignoramenti.Tipo_Notifica' ),
        Field::inst( 'v_elab_acts_pignoramenti.Printer' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti')->value( 'Printer' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti.PrintType' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti')->value( 'PrintType' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti.Tipo_Ufficiale' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti')->value( 'Tipo_Ufficiale' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti.Partita_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti.PEC' ),
        Field::inst( 'v_elab_acts_pignoramenti.InipecLoaded' ),
        Field::inst( 'v_elab_acts_pignoramenti.REC_PRESSO' ),
        Field::inst( 'v_elab_acts_pignoramenti.CF_PI' )
    )
    ->where("v_elab_acts_pignoramenti.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

