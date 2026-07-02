<?php
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_Utils.php";
//include_once CLS . "/cls_DateTimeInLine.php";

class cls_enteUtils
{
  private $cls_db;
  private $cls_utils;
  public function __construct()
  {
    $this->cls_db = new cls_db();
    $this->cls_utils = new cls_Utils();
  }


  function controlloParametri( $c, $data , $tipo ,$ID)
  {

    if($ID == null || $ID == "")
    {
      $query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
      //echo $query;die;
      $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//safe_query($query);
      if(count($result)>0 /*mysql_num_rows($result)>0*/)
        $val = $result[0];// mysql_fetch_array($result);
      else
      {
        $query = "SELECT * FROM parametri_annuali WHERE Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));//safe_query($query);
        if(count($result)>0/*mysql_num_rows($result)>0*/)
          $val = $result[0];//mysql_fetch_array($result);
        else
          return false;
      }

      $a_paramsEnti = array(
    			'table' => 'parametri_annuali',
    			'fields'=> array(
    					array(  'name' => 'CC',                             'type' => 'string', 'value' =>  $c),
    					array(  'name' => 'Anno',                           'type' => 'int', 'value' => substr($data, 0,4)),
    					array(  'name' => 'Tipo_Riscossione',               'type' => 'string', 'value' => $tipo),
    					array(  'name' => 'Maggiorazione_Preavviso',        'type' => 'string', 'value' => $val['Maggiorazione_Preavviso']),
    					array(  'name' => 'Maggiorazione_Ingiunzione',      'type' => 'string', 'value' => $val['Maggiorazione_Ingiunzione']),
    					array(  'name' => 'Diritto_Riscossione_Minimo',     'type' => 'int', 'value' => $val['Diritto_Riscossione_Minimo']),
    					array(  'name' => 'Diritto_Riscossione_Massimo',    'type' => 'int', 'value' => $val['Diritto_Riscossione_Massimo']),
    					array(  'name' => 'Giorni_Diritto',                 'type' => 'int', 'value' => $val['Giorni_Diritto']),
    					array(  'name' => 'Importo_Minimo',                 'type' => 'int', 'value' => $val['Importo_Minimo'])
    			)
    	);



      if( $data >= $val['Spese_Notifica_Data'] && $val['Spese_Notifica_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica', 'type' => 'int', 'value' =>  $val['Spese_Notifica_New']));
    }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica', 'type' => 'int', 'value' =>  $val['Spese_Notifica']));


      if( $data >= $val['Spese_Notifica_Pignoramento_Data'] && $val['Spese_Notifica_Pignoramento_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica_Pignoramento', 'type' => 'int', 'value' =>  $val['Spese_Notifica_Pignoramento_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica_Pignoramento', 'type' => 'int', 'value' =>  $val['Spese_Notifica_Pignoramento']));


      if( $data >= $val['Spese_Ricerca_Data'] && $val['Spese_Ricerca_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Ricerca', 'type' => 'int', 'value' =>  $val['Spese_Ricerca_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Ricerca', 'type' => 'int', 'value' =>  $val['Spese_Ricerca']));

      if( $data >= $val['Spese_Postali_Data'] && $val['Spese_Postali_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali', 'type' => 'int', 'value' =>  $val['Spese_Postali_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali', 'type' => 'int', 'value' =>  $val['Spese_Postali']));

      if( $data >= $val['Spese_Raccomandata_Data'] && $val['Spese_Raccomandata_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Raccomandata', 'type' => 'int', 'value' =>  $val['Spese_Raccomandata_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Raccomandata', 'type' => 'int', 'value' =>  $val['Spese_Raccomandata']));

      if( $data >= $val['Spese_Postali_AG_Data'] && $val['Spese_Postali_AG_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali_AG', 'type' => 'int', 'value' =>  $val['Spese_Postali_AG_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali_AG', 'type' => 'int', 'value' =>  $val['Spese_Postali_AG']));

      if( $data >= $val['CAN_Data'] && $val['CAN_Data'])
      {
        array_push($a_paramsEnti["fields"],array('name' => 'CAN', 'type' => 'int', 'value' =>  $val['CAN_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'CAN', 'type' => 'int', 'value' =>  $val['CAN']));

      if( $data >= $val['CAD_Data'] && $val['CAD_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'CAD', 'type' => 'int', 'value' =>  $val['CAD_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'CAD', 'type' => 'int', 'value' =>  $val['CAD']));

      if( $data >= $val['A_Mani_Data'] && $val['A_Mani_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'A_Mani', 'type' => 'int', 'value' =>  $val['A_Mani_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'A_Mani', 'type' => 'int', 'value' =>  $val['A_Mani']));

      if( $data >= $val['A_Mani_Pignoramento_Data'] && $val['A_Mani_Pignoramento_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'A_Mani_Pignoramento', 'type' => 'int', 'value' =>  $val['A_Mani_Pignoramento_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'A_Mani_Pignoramento', 'type' => 'int', 'value' =>  $val['A_Mani_Pignoramento']));

      if( $data >= $val['IVA_Data'] && $val['IVA_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'IVA', 'type' => 'int', 'value' =>  $val['IVA_New']));
      }else array_push($a_paramsEnti["fields"],array('name' => 'IVA', 'type' => 'int', 'value' =>  $val['IVA']));


      $this->cls_db->Start_Transaction();
      $this->cls_db->Begin_Transaction();

      if(!$this->cls_db->DbSave($a_paramsEnti))
      {
        $this->cls_db->Rollback();
        return false;
      }

      $this->cls_db->End_Transaction();

      return true;
      /*$controlInsert = $this->Insert();
      if($controlInsert)
        return "NEW";
      else
        return "ERROR";*/

    }
    else
    {
      return "OK";
    }
  }

  public function controlloParametriRicorso( $c ,$ID)
  {
    if($ID ==	null)
    {
      $query = "SELECT * FROM parametri_ricorso ORDER BY ID DESC LIMIT 1";
      $res = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

      if($res == null)
        return false;

      $save = new stdClass();
      $save->CC = $c;
      $save->Termini_Commissione_Tributaria_Provinciale = utf8_decode($res['Termini_Commissione_Tributaria_Provinciale']);
      $save->Termini_Giustizia_Ordinaria = utf8_decode($res['Termini_Giustizia_Ordinaria']);

      if($this->cls_db->DbSave($this->cls_utils->GetObjectQuery($save,"parametri_ricorso"))) return true;
      else return false;
    }
    else
    {
      return true;
    }
  }

  public function crea_tariffe_base($c)
  {
    $query = "SELECT * FROM tariffe_coazione WHERE CC = '".$c."'";
    //$result = safe_query($query);
    if($this->cls_db->getNumberRow($this->cls_db->ExecuteQuery($query))>0)
      return true;

    $query = "SELECT * FROM tariffe_coazione WHERE CC = '*****'";
    $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));// safe_query($query);

    //$controlInsert = false;
    for ($i=0; $i < count($result); $i++) {

      $save = new stdClass();

      $save->CC = $c;
      $save->Data_Inizio = utf8_decode($result[$i]['Data_Inizio']);
      $save->Tipo = utf8_decode($result[$i]['Tipo']);
      $save->Descrizione = utf8_decode($result[$i]['Descrizione']);
      $save->Importo = utf8_decode($result[$i]['Importo']);
      $save->Note = utf8_decode($result[$i]['Note']);
      $save->Deposito_Portata = utf8_decode($result[$i]['Deposito_Portata']);
      $save->Importo_Fisso = utf8_decode($result[$i]['Importo_Fisso']);
      $save->Km_Giorni_Importo_Fisso = utf8_decode($result[$i]['Km_Giorni_Importo_Fisso']);
      $save->Coefficiente = utf8_decode($result[$i]['Coefficiente']);
      $save->Pignoramenti = utf8_decode($result[$i]['Pignoramenti']);

      if(!$this->cls_db->DbSave($this->cls_utils->GetObjectQuery($save,"tariffe_coazione")))
        return false;
      /*$controlInsert = $this->Insert();
      if($controlInsert)
      {

      }
      else
        return "ERROR";*/

    }

    return true;
    /*if($controlInsert)
      return "NEW";
    else
      return "ERROR";*/


  }
}

 ?>
