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
Editor::inst( $db, 'v_pre_elab_acts','Partita_ID')
    ->fields(
        Field::inst( 'v_pre_elab_acts.Comune_ID' ),
        Field::inst( 'v_pre_elab_acts.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts.CC' ),
        Field::inst( 'v_pre_elab_acts.Info_Cartella' ),
        Field::inst( 'v_pre_elab_acts.Anomalia_ATTO' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts')->value( 'Anomalia_ATTO' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts.Position_Status' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts')->value( 'Position_Status' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts.Partita_ID' ),
        Field::inst( 'v_pre_elab_acts.flag_elaboration' ),
        Field::inst( 'v_pre_elab_acts.Position_Status_Id' ),
    )
    ->where("v_pre_elab_acts.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();
