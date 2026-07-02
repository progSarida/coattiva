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
Editor::inst( $db, 'v_manage_acts_pignoramenti','Partita_ID')
    ->fields(
        Field::inst( 'v_manage_acts_pignoramenti.ID' ),
        Field::inst( 'v_manage_acts_pignoramenti.Comune_ID' ),
        Field::inst( 'v_manage_acts_pignoramenti.ID_Cronologico' ),
        Field::inst( 'v_manage_acts_pignoramenti.Anno_Cronologico' ),
        Field::inst( 'v_manage_acts_pignoramenti.Info_Cartella' ),
        Field::inst( 'v_manage_acts_pignoramenti.Partita_ID' ),
        Field::inst( 'v_manage_acts_pignoramenti.Data_Stampa' ),
        Field::inst( 'v_manage_acts_pignoramenti.Tipo_Notifica' ),
        Field::inst( 'v_manage_acts_pignoramenti.CC' ),
        Field::inst( 'v_manage_acts_pignoramenti.PignoID' ),
        Field::inst( 'v_manage_acts_pignoramenti.PrefixName' ),
    )
    ->where("v_manage_acts_pignoramenti.Elaboration_List_Id", $_POST['Elaboration_List_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();

