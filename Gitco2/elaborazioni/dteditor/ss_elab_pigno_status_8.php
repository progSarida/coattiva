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
Editor::inst( $db, 'v_pre_elab_pignoramenti','Partita_ID')
    ->fields(
        Field::inst( 'v_pre_elab_pignoramenti.Comune_ID' ),
        Field::inst( 'v_pre_elab_pignoramenti.Tipo_Riscossione' ),
        Field::inst( 'v_pre_elab_pignoramenti.Info_Cartella' ),
        Field::inst( 'v_pre_elab_pignoramenti.Data_Acquisizione' ),
        Field::inst( 'v_pre_elab_pignoramenti.Stato_Veicolo' ),
        Field::inst( 'v_pre_elab_pignoramenti.Partita_ID' ),
        Field::inst( 'v_pre_elab_pignoramenti.CC' )
    )
    ->where("v_pre_elab_pignoramenti.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();
