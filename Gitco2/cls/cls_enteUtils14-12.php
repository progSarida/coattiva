<?php
include_once CLS . "/cls_db.php";
//include_once CLS . "/cls_DateTimeInLine.php";

class cls_ente
{
  public function controlloParametri( $c, $data , $tipo ,$ID)
  {
    $cls_db = new cls_db();

    if($ID ==	null || $ID = "")
    {
      $query = "SELECT * FROM parametri_annuali WHERE CC = '".$c."' AND Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
      $result = $cls_db->ExecuteQuery($query);//safe_query($query);
      if($cls_db->getNumberRow($result)>0 /*mysql_num_rows($result)>0*/)
        $val = $cls_db->getArrayLine($result);// mysql_fetch_array($result);
      else
      {
        $query = "SELECT * FROM parametri_annuali WHERE Tipo_Riscossione = '".$tipo."' ORDER BY Anno DESC LIMIT 1";
        $result = $cls_db->ExecuteQuery($query);//safe_query($query);
        if($cls_db->getNumberRow($result)>0/*mysql_num_rows($result)>0*/)
          $val = $cls_db->getArrayLine($result);//mysql_fetch_array($result);
        else
          return false;
      }

      $a_paramsEnti = array(
    			'table' => 'parametri_annuali',
    			'fields'=> array(
    					array(  'name' => 'CC',                             'type' => 'string', 'value' =>  $c),
    					array(  'name' => 'Anno',                           'type' => 'int', 'value' => substr($data, 0,4)),
    					array(  'name' => 'Tipo_Riscossione',               'type' => 'string', 'value' => $tipo),
    					array(  'name' => 'Maggiorazione_Preavviso',        'type' => 'string', 'value' => utf8_decode($val['Maggiorazione_Preavviso'])),
    					array(  'name' => 'Maggiorazione_Ingiunzione',      'type' => 'string', 'value' => utf8_decode($val['Maggiorazione_Ingiunzione'])),
    					array(  'name' => 'Diritto_Riscossione_Minimo',     'type' => 'int', 'value' => utf8_decode($val['Diritto_Riscossione_Minimo'])),
    					array(  'name' => 'Diritto_Riscossione_Massimo',    'type' => 'int', 'value' => utf8_decode($val['Diritto_Riscossione_Massimo'])),
    					array(  'name' => 'Giorni_Diritto',                 'type' => 'int', 'value' => utf8_decode($val['Giorni_Diritto'])),
    					array(  'name' => 'Importo_Minimo',                 'type' => 'int', 'value' => utf8_decode($val['Importo_Minimo']))
    			)
    	);
      /*$arrayData["Maggiorazione_Preavviso"] = utf8_decode($val['Maggiorazione_Preavviso']);
      $arrayData["Maggiorazione_Ingiunzione"] = utf8_decode($val['Maggiorazione_Ingiunzione']);

      $arrayData["Diritto_Riscossione_Minimo"] = utf8_decode($val['Diritto_Riscossione_Minimo']);
      $arrayData["Diritto_Riscossione_Massimo"] = utf8_decode($val['Diritto_Riscossione_Massimo']);
      $arrayData["Giorni_Diritto"] = utf8_decode($val['Giorni_Diritto']);

      $arrayData["Importo_Minimo"] = utf8_decode($val['Importo_Minimo']);*/

      //$arrayData["CC"] = $c;
      //$arrayData["Anno"] = substr($data, 0,4);
      //$arrayData["Tipo_Riscossione"] = $tipo;

      //$arrayData["Spese_Notifica"] = utf8_decode($val['Spese_Notifica']);
      if( $data >= $val['Spese_Notifica_Data'] && $val['Spese_Notifica_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Notifica_New'])));
      //  $arrayData["Spese_Notifica"] = utf8_decode($val['Spese_Notifica_New']);
    }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Notifica'])));

      //$arrayData["Spese_Notifica_Pignoramento"] = utf8_decode($val['Spese_Notifica_Pignoramento']);
      if( $data >= $val['Spese_Notifica_Pignoramento_Data'] && $val['Spese_Notifica_Pignoramento_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica_Pignoramento', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Notifica_Pignoramento_New'])));
        //$arrayData["Spese_Notifica_Pignoramento"] = utf8_decode($val['Spese_Notifica_Pignoramento_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Notifica_Pignoramento', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Notifica_Pignoramento'])));

      //$arrayData["Spese_Ricerca"] = utf8_decode($val['Spese_Ricerca']);
      if( $data >= $val['Spese_Ricerca_Data'] && $val['Spese_Ricerca_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Ricerca', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Ricerca_New'])));
        //$arrayData["Spese_Ricerca"] = utf8_decode($val['Spese_Ricerca_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Ricerca', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Ricerca'])));

      //$arrayData["Spese_Postali"] = utf8_decode($val['Spese_Postali']);
      if( $data >= $val['Spese_Postali_Data'] && $val['Spese_Postali_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Postali_New'])));
        //$arrayData["Spese_Postali"] = utf8_decode($val['Spese_Postali_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Postali'])));

      //$arrayData["Spese_Raccomandata"] = utf8_decode($val['Spese_Raccomandata']);
      if( $data >= $val['Spese_Raccomandata_Data'] && $val['Spese_Raccomandata_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Raccomandata', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Raccomandata_New'])));
          //$arrayData["Spese_Raccomandata"] = utf8_decode($val['Spese_Raccomandata_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Raccomandata', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Raccomandata'])));

      //$arrayData["Spese_Postali_AG"] = utf8_decode($val['Spese_Postali_AG']);
      if( $data >= $val['Spese_Postali_AG_Data'] && $val['Spese_Postali_AG_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali_AG', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Postali_AG_New'])));
        //$arrayData["Spese_Postali_AG"] = utf8_decode($val['Spese_Postali_AG_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'Spese_Postali_AG', 'type' => 'int', 'value' =>  utf8_decode($val['Spese_Postali_AG'])));

      //$arrayData["CAN"] = utf8_decode($val['CAN']);
      if( $data >= $val['CAN_Data'] && $val['CAN_Data'])
      {
        array_push($a_paramsEnti["fields"],array('name' => 'CAN', 'type' => 'int', 'value' =>  utf8_decode($val['CAN_New'])));
        //$arrayData["CAN"] = utf8_decode($val['CAN_New'] != null );
      }else array_push($a_paramsEnti["fields"],array('name' => 'CAN', 'type' => 'int', 'value' =>  utf8_decode($val['CAN'])));

      //$arrayData["CAD"] = utf8_decode($val['CAD']);
      if( $data >= $val['CAD_Data'] && $val['CAD_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'CAD', 'type' => 'int', 'value' =>  utf8_decode($val['CAD_New'])));
        //$arrayData["CAD"] = utf8_decode($val['CAD_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'CAD', 'type' => 'int', 'value' =>  utf8_decode($val['CAD'])));

      //$arrayData["A_Mani"] = utf8_decode($val['A_Mani']);
      if( $data >= $val['A_Mani_Data'] && $val['A_Mani_Data'] != null )
      {
        //$arrayData["A_Mani"] = utf8_decode($val['A_Mani_New']);
        array_push($a_paramsEnti["fields"],array('name' => 'A_Mani', 'type' => 'int', 'value' =>  utf8_decode($val['A_Mani_New'])));
      }else array_push($a_paramsEnti["fields"],array('name' => 'A_Mani', 'type' => 'int', 'value' =>  utf8_decode($val['A_Mani'])));

      //$arrayData["A_Mani_Pignoramento"] = utf8_decode($val['A_Mani_Pignoramento']);
      if( $data >= $val['A_Mani_Pignoramento_Data'] && $val['A_Mani_Pignoramento_Data'] != null )
      {
        //$arrayData["A_Mani_Pignoramento"] = utf8_decode($val['A_Mani_Pignoramento_New']);
        array_push($a_paramsEnti["fields"],array('name' => 'A_Mani_Pignoramento', 'type' => 'int', 'value' =>  utf8_decode($val['A_Mani_Pignoramento_New'])));
      }else array_push($a_paramsEnti["fields"],array('name' => 'A_Mani_Pignoramento', 'type' => 'int', 'value' =>  utf8_decode($val['A_Mani_Pignoramento'])));

      //$arrayData["IVA"] = utf8_decode($val['IVA']);
      if( $data >= $val['IVA_Data'] && $val['IVA_Data'] != null )
      {
        array_push($a_paramsEnti["fields"],array('name' => 'IVA', 'type' => 'int', 'value' =>  utf8_decode($val['IVA_New'])));
        //$arrayData["IVA"] = utf8_decode($val['IVA_New']);
      }else array_push($a_paramsEnti["fields"],array('name' => 'IVA', 'type' => 'int', 'value' =>  utf8_decode($val['IVA'])));


      $cls_db->Start_Transaction();
      $cls_db->Begin_Transaction();

      if(!$cls_db->DbSave($a_paramsEnti))
      {
        $cls_db->Rollback();
        return false;
      }

      $cls_db->End_Transaction();
      
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
