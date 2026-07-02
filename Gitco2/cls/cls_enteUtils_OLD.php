<?php
include_once CLS . "/cls_db.php";
//include_once CLS . "/cls_DateTimeInLine.php";

class cls_enteUtils
{
  public $cls_db;
  public function __construct()
  {
    $this->cls_db = new cls_db();
  }


  function controlloParametri( $c, $data , $tipo ,$ID)
  {

    if($ID ==	null || $ID = "")
    {
      $query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
      $result = $this->cls_db->ExecuteQuery($query);//safe_query($query);
      if($this->cls_db->getNumberRow($result)>0 /*mysql_num_rows($result)>0*/)
        $val = $this->cls_db->getArrayLine($result);// mysql_fetch_array($result);
      else
      {
        $query = "SELECT * FROM parametri_annuali WHERE Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
        $result = $this->cls_db->ExecuteQuery($query);//safe_query($query);
        if($this->cls_db->getNumberRow($result)>0/*mysql_num_rows($result)>0*/)
          $val = $this->cls_db->getArrayLine($result);//mysql_fetch_array($result);
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
}

 ?>
