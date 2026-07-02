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
Editor::inst( $db, 'v_elab_acts_pignoramenti_banca','Partita_ID')
    ->fields(
        Field::inst( 'v_elab_acts_pignoramenti_banca.Comune_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Pignoramento_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Notifica_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_banca')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_banca.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_banca.CC' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Info_Cartella' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Tipo_Notifica' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Printer' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_banca')->value( 'Printer' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_banca.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_banca.PrintType' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_banca')->value( 'PrintType' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_banca.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Tipo_Ufficiale' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_elab_acts_pignoramenti_banca')->value( 'Tipo_Ufficiale' )
        ->where( function ($q) { $q->where( 'v_elab_acts_pignoramenti_banca.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_elab_acts_pignoramenti_banca.Partita_ID' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.PEC' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.InipecLoaded' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.REC_PRESSO' ),
        Field::inst( 'v_elab_acts_pignoramenti_banca.CF_PI' )
    )
    ->where("v_elab_acts_pignoramenti_banca.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

