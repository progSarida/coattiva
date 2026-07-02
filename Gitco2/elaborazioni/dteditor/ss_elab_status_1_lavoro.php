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
Editor::inst( $db, 'v_pre_elab_acts_lavoro','Partita_ID')
    ->fields(
        Field::inst( 'v_pre_elab_acts_lavoro.Comune_ID' ),
        Field::inst( 'v_pre_elab_acts_lavoro.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_lavoro')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_lavoro.CC' ),
        Field::inst( 'v_pre_elab_acts_lavoro.Info_Cartella' ),
        Field::inst( 'v_pre_elab_acts_lavoro.Anomalia_ATTO' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_lavoro')->value( 'Anomalia_ATTO' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_lavoro.Position_Status' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_lavoro')->value( 'Position_Status' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_lavoro.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_lavoro.Partita_ID' ),
        Field::inst( 'v_pre_elab_acts_lavoro.flag_elaboration' ),
        Field::inst( 'v_pre_elab_acts_lavoro.Position_Status_Id' ),
    )
    ->where("v_pre_elab_acts_lavoro.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();
