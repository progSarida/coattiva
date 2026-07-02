<?php

class cls_zip{
    /* creates a compressed zip file */
    function create_zip($files = array(),$destination = '',$overwrite = false) {
        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination) && !$overwrite)
        {
            echo "Il file zip esiste gia e non è sovrascrivibile!<br><br>";
            return false;
        }
        //vars
        $valid_files = array();
        //if files were passed in...
        if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                //make sure the file exists
                if(file_exists($file)) {
                    $valid_files[] = $file;
                }
                else{
                    echo $file." NON ESISTE<br><br>";
                }
            }
        }
        //if we have good files...
        if(count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                echo "PROBLEMA SALVATAGGIO<br><br>";
                return false;
            }
            //add the files
            foreach($valid_files as $file) {
                $checkFile = $zip->addFile($file,basename($file));
                if($checkFile!==true){
                    echo "PROBLEMA AGGIUNTA FILE $file<br><br>";
                    $zip->close();
                    return false;
                }
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

            //close the zip -- done!
            $zip->close();

            //check to make sure the file exists
            return file_exists($destination);
        }
        else
        {
            echo "Non ho trovato file!<br><br>";
            return false;
        }
    }

    function readZip($fileName){
        $zip = new ZipArchive();

        $zip->open($fileName);

        $a_fileNames = array();
        for( $i = 0; $i < $zip->numFiles; $i++ ){
            $stat = $zip->statIndex( $i );
            $a_fileNames[] =  basename( $stat['name'] ) . PHP_EOL;
        }

        return $a_fileNames;
    }

    function extractZip($file, $path){
        $zip = new ZipArchive();

        if ($zip->open($file)){
            if($zip->extractTo($path)){
                $zip->close();

                return true;
            }
            else
                return false;
        }
        else
            return false;
    }
}


?>