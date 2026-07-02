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
Editor::inst( $db, 'v_previdenziali_stragiudiziali')
    ->fields(
        Field::inst( 'v_previdenziali_stragiudiziali.ID' ),
        Field::inst( 'v_previdenziali_stragiudiziali.Denominazione' ),
        Field::inst( 'v_previdenziali_stragiudiziali.PEC' ),
        Field::inst( 'v_previdenziali_stragiudiziali.InipecLoaded' ),
        Field::inst( 'v_previdenziali_stragiudiziali.REC_PRESSO' ),
        Field::inst( 'v_previdenziali_stragiudiziali.CF_PI' )
    )
    //->where("v_previdenziali_stragiudiziali.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

