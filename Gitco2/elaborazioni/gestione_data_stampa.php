<?php

function checkHoliday($date){
    
    if(date('l', strtotime($date)) == 'Saturday'){
      return true;
    }else if(date('l', strtotime($date)) == 'Sunday'){
      return true;
    }else{
      $receivedDate = date('d M', strtotime($date));
  	  //$pasqua = date('d M',easter_date($date));
      //$pasquetta = date('d M', strtotime($pasqua. ' + 1 days'));
      $holiday = array(
        '01 Jan' => 'New Year Day',
        '06 Jan' => 'Epifania',
        '25 Apr' => 'Festa Liberazione',
        '02 Jun' => 'Festa Repubblica',
        '15 Aug' => 'Ferragosto',
        '01 Nov' => 'Santi',
        '08 Dec' => 'Immacolata',
        '25 Dec' => 'Christmas Day',
        '26 Dec' => 'Santo Stefano',
        '31 Dec' => 'New Year Eve',
        // $pasqua => 'Pasqua',
         //$pasquetta =>'Pasquetta'
      );
  
      foreach($holiday as $key => $value){
        if($receivedDate == $key){
          return true;
        }
      }
    }
    return false;
  }
  
function GetWorkingDay($date)
{
    $i = 0;
      While(checkHoliday($date) && ($i<10))
      {

        $date = date("Y/m/d", strtotime($date. ' - 1 days'));
          $i++;
        }
      return $date;
}


$data_stampa = $cls_help->getVar('data_stampa');
if ($data_stampa==null)
{
    $anno = $a+1;
    $data_stampa_ita = $cls_help->toItalianDate(GetWorkingDay($anno."/06/30"));
    $data_stampa = $cls_help->toDbDate($data_stampa_ita);
}
else
{
    $data_stampa_ita =$cls_help->toItalianDate($data_stampa);
}
?>