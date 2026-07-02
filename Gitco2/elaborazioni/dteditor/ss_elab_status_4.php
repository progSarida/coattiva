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
Editor::inst( $db, 'v_manage_acts','Partita_ID')
    ->fields(
        Field::inst( 'v_manage_acts.Comune_ID' ),
        Field::inst( 'v_manage_acts.ID_Cronologico' ),
        Field::inst( 'v_manage_acts.Anno_Cronologico' ),
        Field::inst( 'v_manage_acts.Info_Cartella' ),
        Field::inst( 'v_manage_acts.Partita_ID' ),
        Field::inst( 'v_manage_acts.Data_Stampa' ),
    )
    ->where("v_manage_acts.Elaboration_List_Id", $_POST['Elaboration_List_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

