<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";


$cls_db = new cls_db();
$cls_help = new cls_help();


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$servizio = $cls_help->getVar('servizio');
$Select_Tax = $cls_help->getVar('Select_Tax');

$invia = $cls_help->getVar('invia_submit');//QUESTO FORSE SI PUò ELIMINARE
$info_id = $cls_help->getVar('info_id');//NON SO DI CHE TABELLA SIA



if($invia == "Salva")
{
  $a_params = array(
              'table'=>'gestore',
              'fields'=> array (
                              array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('CC')),
                              array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune')),
                              array(  'name' => 'Codice_Fiscale', 'type' => 'string', 'value' => $cls_help->getVar('CF')),
                              array(  'name' => 'Partita_Iva',    'type' => 'string', 'value' => $cls_help->getVar('PI')),
                              array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),
                              array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),
                              array(  'name' => 'Fax',            'type' => 'string', 'value' => $cls_help->getVar('fax')),
                              array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),
                              array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),
                              array(  'name' => 'Toponimo',       'type' => 'string', 'value' => $cls_help->getVar('via')),
                              array(  'name' => 'Civico',         'type' => 'int',    'value' => $cls_help->getVar('civico')),
                              array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente')),
                              array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),
                              array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),
                              array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('Pro_Sigla')),
                              array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),
                              array(  'name' => 'Tipo',           'type' => 'string', 'value' => 'Comune'),
                              array(  'name' => 'Denominazione',  'type' => 'string', 'value' => 'Comune di '.$cls_help->getVar('comune')),
                              array(  'name' => 'Paese',          'type' => 'string', 'value' => 'Italia'),
                              array(  'name' => 'Orario',          'type' => 'string', 'value' => '')
                            )
          );
  if($info_id == 0)
  {
    //LANCIO LA INSERT
    $id = $cls_db->DbInsert($a_params);
    //echo "<h1>id = ".mysqli_insert_id($cls_db->conn)." ---- ".$cls_help->getVar('codice_290')." ----- ".$cls_help->getVar('Select_Tax')." </h1>";

    $a_params = array(
                'table'=>'enti_gestiti',
                'fields'=> array (
                                array(  'name'=>'Info_ID',         'type'=>'int',       'value'=>$id),
                                array(  'name'=>'Codice_290',      'type'=>'int',       'value'=>$cls_help->getVar('codice_290')),
                                array(  'name'=>'Select_Tax',      'type'=>'int',       'value'=>$cls_help->getVar('Select_Tax'))
                              ),
                'updateField'=>array(   'name'=>'CC',              'type'=>'string',    'value'=>$cls_help->getVar('CC'))
            );

      $cls_db->DbUpdate($a_params);
    }
    else {

      $a_params = array(
                  'table'=>'gestore',
                  'fields'=> array (
                                  array(  'name' => 'CC',             'type' => 'string', 'value' => $cls_help->getVar('CC')),
                                  array(  'name' => 'Comune',         'type' => 'string', 'value' => $cls_help->getVar('comune')),
                                  array(  'name' => 'Codice_Fiscale', 'type' => 'string', 'value' => $cls_help->getVar('CF')),
                                  array(  'name' => 'Partita_Iva',    'type' => 'string', 'value' => $cls_help->getVar('PI')),
                                  array(  'name' => 'Mail',           'type' => 'string', 'value' => $cls_help->getVar('email')),
                                  array(  'name' => 'Telefono',       'type' => 'string', 'value' => $cls_help->getVar('tel')),
                                  array(  'name' => 'Fax',            'type' => 'string', 'value' => $cls_help->getVar('fax')),
                                  array(  'name' => 'PEC',            'type' => 'string', 'value' => $cls_help->getVar('PEC')),
                                  array(  'name' => 'Sito',           'type' => 'string', 'value' => $cls_help->getVar('sito')),
                                  array(  'name' => 'Toponimo',       'type' => 'string', 'value' => $cls_help->getVar('via')),
                                  array(  'name' => 'Civico',         'type' => 'int',    'value' => $cls_help->getVar('civico')),
                                  array(  'name' => 'Esponente',      'type' => 'string', 'value' => $cls_help->getVar('esponente')),
                                  array(  'name' => 'Interno',        'type' => 'int',    'value' => $cls_help->getVar('interno')),
                                  array(  'name' => 'Dettagli',       'type' => 'string', 'value' => $cls_help->getVar('dettagli')),
                                  array(  'name' => 'Provincia',      'type' => 'string', 'value' => $cls_help->getVar('Pro_Sigla')),
                                  array(  'name' => 'Cap',            'type' => 'string', 'value' => $cls_help->getVar('cap')),
                                  array(  'name' => 'Tipo',           'type' => 'string', 'value' => 'Comune'),
                                  array(  'name' => 'Denominazione',  'type' => 'string', 'value' => 'Comune di '.$cls_help->getVar('comune')),
                                  array(  'name' => 'Paese',          'type' => 'string', 'value' => 'Italia'),
                                  array(  'name' => 'Orario',          'type' => 'string', 'value' => '')
                                ),
                  'updateField'=> array(  'name'=>'ID',               'type' => 'int',    'value'=> $info_id)
              );

        $cls_db->DbUpdate($a_params);

        $a_params = array(
                    'table'=>'enti_gestiti',
                    'fields'=> array (
                                    array(  'name'=>'Codice_290',      'type'=>'int',       'value'=>$cls_help->getVar('codice_290')),
                                    array(  'name'=>'Select_Tax',      'type'=>'int',       'value'=>$cls_help->getVar('Select_Tax'))
                                  ),
                    'updateField'=>array(   'name'=>'CC',              'type'=>'string',    'value'=>$cls_help->getVar('CC'))
                );

          $cls_db->DbUpdate($a_params);

    }
}

header("Location: dati_ente.php?c=".$c."&a=".$a);

?>
