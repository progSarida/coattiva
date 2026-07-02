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
Editor::inst( $db, 'v_estrazioni_elaborabili','Partita_ID')
    ->fields(
        Field::inst( 'v_estrazioni_elaborabili.Comune_ID' ),
        Field::inst( 'v_estrazioni_elaborabili.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_estrazioni_elaborabili')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_estrazioni_elaborabili.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_estrazioni_elaborabili.CC' ),
        Field::inst( 'v_estrazioni_elaborabili.Info_Cartella' ),
        Field::inst( 'v_estrazioni_elaborabili.Position_Status' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_estrazioni_elaborabili')->value( 'Position_Status' )
        ->where( function ($q) { $q->where( 'v_estrazioni_elaborabili.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_estrazioni_elaborabili.Partita_ID' ),
        Field::inst( 'v_estrazioni_elaborabili.flag_elaboration' ),
        Field::inst( 'v_estrazioni_elaborabili.Position_Status_Id' ),
    )
    ->where("v_estrazioni_elaborabili.Elaboration_Id", $_POST['Elaboration_Id'])
    ->where("v_estrazioni_elaborabili.Flag_Elaboration", 1)
    ->debug(true)
    ->process($_POST)
    ->json();
