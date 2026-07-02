<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

// DataTables PHP library
include( DT_EDITOR."/lib/DataTables.php" );


// Alias Editor classes so they are easy to use
use
    DataTables\Editor,
    DataTables\Editor\Field,
    DataTables\Editor\SearchPaneOptions;

    Editor::inst( $db, 'v_scelta_atto_per_inserimento_multipli','Partita_ID')
    ->fields(
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Partita_ID' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.CC' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Comune_ID' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Anno_Cronologico' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.ID_Cronologico' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Atto' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Atto_ID' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Info_Cartella' ),
        Field::inst( 'v_scelta_atto_per_inserimento_multipli.Raccomandata' )
    )
    ->where("v_scelta_atto_per_inserimento_multipli.CC", $_POST['CC'])
    ->where("v_scelta_atto_per_inserimento_multipli.Utente_ID", $_POST['Utente_ID'])
    ->debug(true)
    ->process($_POST)
    ->json();
