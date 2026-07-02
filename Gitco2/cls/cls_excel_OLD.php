<?php

require_once PHPEXCEL;

class cls_excel{

    public $a_sheet = array();
    private $a_params = array(  'creator'=>'sarida',
                                'lastModifiedBy'=>'sarida',
                                'title'=>'Elenco',
                                'subject'=>'Elenco',
                                'description'=>'Elenco',
                                'sheetTitle'=>'Elenco'
        );

    private $a_headerStyle = array(
        'font' => array('bold' => true),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    );

    public $xls;
    public $sheet;

    public function getArrayFromFile($file){

        $obj_excel = PHPExcel_IOFactory::load($file);
        $i = 0;

        foreach ($obj_excel->getWorksheetIterator() as $worksheet) {

            $a_header = array();
            for ($row = 1; $row <= $worksheet->getHighestRow(); ++ $row) {

                $checkValue = $worksheet->getCellByColumnAndRow(0, $row)->getCalculatedValue();
                if(trim($checkValue)!=""){
                    $lastColumn = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
                    for ($col = 0; $col < $lastColumn; ++ $col) {

                        $cell = $worksheet->getCellByColumnAndRow($col, $row);

                        if($row==1)	{
                            $a_header[$col] = $cell->getCalculatedValue();
                        }
                        else if($a_header[$col]!="") {

                            $valore = $cell->getCalculatedValue();
                            if(PHPExcel_Shared_Date::isDateTime($cell) && $valore!="") {
                                $valore = date('d/m/Y', PHPExcel_Shared_Date::ExcelToPHP($valore));
                            }

                            $this->a_sheet[$i][$row-2][$a_header[$col]] = trim($valore);
                        }
                    }
                }
            }
            break;
        }
    }

    public function setParameters($a_parameters){
        foreach ($a_parameters as $key=>$value){
            $this->a_params[$key] = $value;
        }
    }

    public function createFile($a_header=null){
        $this->xls = new PHPExcel();
        $this->xls->getProperties()
            ->setCreator($this->a_params['creator'])
            ->setLastModifiedBy($this->a_params['lastModifiedBy'])
            ->setTitle($this->a_params['title'])
            ->setSubject($this->a_params['subject'])
            ->setDescription($this->a_params['description']);

        $this->sheet = $this->xls->setActiveSheetIndex(0);
        $this->sheet->setTitle = $this->a_params['sheetTitle'];

        if($a_header!=null){
            for($i=0;$i<count($a_header);$i++)
                $this->sheet->setCellValueByColumnAndRow($i,1,$a_header[$i]);

            $colString = PHPExcel_Cell::stringFromColumnIndex(count($a_header));
            $this->sheet->getStyle('a1:'.$colString.'1')->applyFromArray($this->a_headerStyle);
        }

    }

    public function addHeader($a_header, $row){
        for($i=0;$i<count($a_header);$i++)
            $this->sheet->setCellValueByColumnAndRow($i, $row, $a_header[$i]);

        $colString = PHPExcel_Cell::stringFromColumnIndex(count($a_header));
        $this->sheet->getStyle('a'.$row.':'.$colString.$row)->applyFromArray($this->a_headerStyle);
        $this->sheet->setTitle = $this->a_params['sheetTitle'];
    }

    public function addRow($a_value, $row){
        for($i=0;$i<count($a_value);$i++){
            if(substr($a_value[$i],0,1=="0") && strlen($a_value[$i])==11){
                $colString = PHPExcel_Cell::stringFromColumnIndex($i);
                $this->getActiveSheet()->setCellValueExplicit($colString.$row, $a_value[$i], PHPExcel_Cell_DataType::TYPE_STRING);
            }
            else
                $this->sheet->setCellValueByColumnAndRow($i, $row, $a_value[$i]);

        }


    }

    public function saveFile($fileName){
        $writer = PHPExcel_IOFactory::createWriter($this->xls, 'Excel5');
        $writer->save($fileName);
    }

}


?>