<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

// include_once(CLS."/cls_db.php");
// include_once(CLS."/cls_help.php");
// include_once(CLS."/cls_DateTimeInLine.php");
// include_once(CLS."/DataTable_Serverside/ssp_DataTable.php");


// $cls_help = new cls_help();

// $c = $cls_help->getVar("c");
// $a = $cls_help->getVar("a");
// $last_el_id = $cls_help->getVar("last_el_id");

// DataTables PHP library
include( DT_EDITOR."/lib/DataTables.php" );
 
// Alias Editor classes so they are easy to use
use
    DataTables\Editor,
    DataTables\Editor\Field,
    // DataTables\Editor\Format,
    // DataTables\Editor\Mjoin,
    // DataTables\Editor\Options,
    // DataTables\Editor\Upload,
    // DataTables\Editor\Validate,
    // DataTables\Editor\ValidateOptions,
    DataTables\Editor\SearchPaneOptions;

// Build our Editor instance and process the data coming from _POST
Editor::inst( $db, 'v_pre_elab_acts','Partita_ID')
    ->fields(
        Field::inst( 'v_pre_elab_acts.Comune_ID' ),
        Field::inst( 'v_pre_elab_acts.Tipo_Riscossione' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts')->value( 'Tipo_Riscossione' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts.CC' ),
        Field::inst( 'v_pre_elab_acts.Info_Cartella' ),
        Field::inst( 'v_pre_elab_acts.Anomalia_ATTO' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts')->value( 'Anomalia_ATTO' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts.Position_Status' )
        ->searchPaneOptions( SearchPaneOptions::inst()->table( 'v_pre_elab_acts')->value( 'Position_Status' )
        ->where( function ($q) { $q->where( 'v_pre_elab_acts.Elaboration_Id', $_POST['Elaboration_Id'], '=' );} )),
        Field::inst( 'v_pre_elab_acts.Partita_ID' ),
        Field::inst( 'v_pre_elab_acts.flag_elaboration' ),
        Field::inst( 'v_pre_elab_acts.Position_Status_Id' ),
    )
    ->where("v_pre_elab_acts.Elaboration_Id", $_POST['Elaboration_Id'])
    ->debug(true)
    ->process($_POST)
    ->json();


//     include_once(CLS."/DataTable_Serverside/ssp_DataTable.php");

//     // DB table to use
// $table = 'v_pre_elab';
 
// // Table's primary key
// $primaryKey = 'Partita_ID';

// $columns = array(
//     array( 'db' => 'Comune_ID', 'dt' => 0 ),
//     array( 'db' => 'Tipo_Riscossione',  'dt' => 1 ),
//     array( 'db' => 'CC',   'dt' => 2 ),
//     array( 'db' => 'Info_Cartella',     'dt' => 3 ),
//     array( 'db' => 'Anomalia_ATTO',   'dt' => 4 ),
//     array( 'db' => 'Position_Status',     'dt' => 5 ),
// );

// $sql_details = array(
//     'user' => 'root',
//     'pass' => 'Lum1k0',
//     'db'   => 'gitco2',
//     'host' => 'localhost'
// );

// echo json_encode(
//     SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, null, "Elaboration_Id=176" )
// );