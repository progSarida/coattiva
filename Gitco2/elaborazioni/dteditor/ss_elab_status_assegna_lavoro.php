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
Editor::inst( $db, 'v_assegna_terzo_lavoro')
    ->fields(
        Field::inst( 'v_assegna_terzo_lavoro.Utente_ID' ),
        Field::inst( 'v_assegna_terzo_lavoro.Denominazione' ),
        Field::inst( 'v_assegna_terzo_lavoro.CF_PI' ),
        Field::inst( 'v_assegna_terzo_lavoro.Flag_Terzo' )
    )
    ->where("v_assegna_terzo_lavoro.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

