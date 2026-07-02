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
Editor::inst( $db, 'v_pre_elab_acts_preav_fermo','Partita_ID')
    ->fields(
        Field::inst( 'v_pre_elab_acts_preav_fermo.Comune_ID' ),
        Field::inst( 'v_pre_elab_acts_preav_fermo.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_preav_fermo')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_preav_fermo.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_preav_fermo.CC' ),
        Field::inst( 'v_pre_elab_acts_preav_fermo.Info_Cartella' ),
        Field::inst( 'v_pre_elab_acts_preav_fermo.Anomalia_ATTO' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_preav_fermo')->value( 'Anomalia_ATTO' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_preav_fermo.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_preav_fermo.Position_Status' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_preav_fermo')->value( 'Position_Status' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_preav_fermo.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_preav_fermo.Partita_ID' ),
        Field::inst( 'v_pre_elab_acts_preav_fermo.flag_elaboration' ),
        Field::inst( 'v_pre_elab_acts_preav_fermo.Position_Status_Id' ),
    )
    ->where("v_pre_elab_acts_preav_fermo.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();
