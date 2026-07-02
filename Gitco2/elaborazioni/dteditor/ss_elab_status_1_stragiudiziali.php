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
Editor::inst( $db, 'v_pre_elab_acts_stragiudiziali','Partita_ID')
    ->fields(
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Comune_ID' ),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_stragiudiziali')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_stragiudiziali.Procedure_Id', $_POST['Procedure_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.CC' ),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Info_Cartella' ),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Anomalia_ATTO' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_stragiudiziali')->value( 'Anomalia_ATTO' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_stragiudiziali.Procedure_Id', $_POST['Procedure_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Position_Status' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts_stragiudiziali')->value( 'Position_Status' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts_stragiudiziali.Procedure_Id', $_POST['Procedure_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Partita_ID' ),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Flag_Elaboration' ),
        Field::inst( 'v_pre_elab_acts_stragiudiziali.Position_Status_Id' ),
    )
    ->where("v_pre_elab_acts_stragiudiziali.Procedure_Id", $_POST['Procedure_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();
