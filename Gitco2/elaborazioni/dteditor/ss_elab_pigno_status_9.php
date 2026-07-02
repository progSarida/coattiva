<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

// DataTables PHP library
include( DT_EDITOR."/lib/DataTables.php" );
set_time_limit(-1);
// Alias Editor classes so they are easy to use
use
    DataTables\Editor,
    DataTables\Editor\Field,
    DataTables\Editor\SearchPaneOptions;

// Build our Editor instance and process the data coming from _POST
Editor::inst( $db, 'v_elab_pignoramenti','Partita_ID')
    ->fields(
        Field::inst( 'v_elab_pignoramenti.CC' ),
        Field::inst( 'v_elab_pignoramenti.Pignoramento_ID' ),
        Field::inst( 'v_elab_pignoramenti.Pignoramento_Veicolo_ID' ),
        Field::inst( 'v_elab_pignoramenti.Partita_ID' ),
        Field::inst( 'v_elab_pignoramenti.Comune_ID' ),
        Field::inst( 'v_elab_pignoramenti.Tipo_Riscossione' ),
        Field::inst( 'v_elab_pignoramenti.Info_Cartella' ),
        Field::inst( 'v_elab_pignoramenti.Veicolo_ID' ),
        Field::inst( 'v_elab_pignoramenti.ID_Veicoli' ),
        Field::inst( 'v_elab_pignoramenti.Targhe_Veicoli' ),
        // Field::inst( 'v_elab_pignoramenti.Fabbriche_Veicoli' ),
        Field::inst( 'v_elab_pignoramenti.Modelli_Veicoli' ),
        // Field::inst( 'v_elab_pignoramenti.Classe_Veicolo' ),
        Field::inst( 'v_elab_pignoramenti.Data_Immatricolazione' ),
        Field::inst( 'v_elab_pignoramenti.Tribunale' ),
        Field::inst( 'v_elab_pignoramenti.IVG' ),
        Field::inst( 'v_elab_pignoramenti.Comune_Residenza' ),
        Field::inst( 'v_elab_pignoramenti.Utente_ID' ),
    )
    ->where("v_elab_pignoramenti.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();
