<?php

include_once FPDI."/src/autoload.php";

use setasign\Fpdi\Tcpdf\Fpdi;


class cls_merge extends Fpdi{

    var $files = array();
    function setFiles($files)
    {
        $this->files = $files;
    }

    function concatFiles($progress_bar = false)
    {
        $this->setPrintHeader(false);
        $numero_files = count($this->files);
        $i_file = 1;
        foreach($this->files AS $file)
        {
            set_time_limit(30);
            if($progress_bar===true)
            {
                $value = ceil($i_file*100/$numero_files);

                echo "<script>updateMerge(".$value.");</script>";
                flush();
                ob_flush();
                flush();
                ob_flush();
            }

            $pagecount = $this->setSourceFile($file);
            for ($i = 1; $i <= $pagecount; $i++)
            {
                $tplidx = $this->ImportPage($i);
                $s = $this->getTemplatesize($tplidx);
                if($s['width'] > $s['height'])
                {
                    $format = 'L';
                }
                if($s['width'] < $s['height'])
                {
                    $format = 'P';
                }
                //var_dump($s);

                $this->AddPage($format, array($s['width'], $s['height']),false);
                $this->useTemplate($tplidx);
            }
            $i_file++;
        }
    }
}