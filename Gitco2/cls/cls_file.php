<?php

class cls_file{

    function getExtension($fileType){
        switch(strtolower($fileType)){
            case "pdf":
                $extension = "pdf";
                break;
            case "excel":
            case "xls":
                $extension = "xls";
                break;
            default:
                $extension = false;
        }
        return $extension;
    }

    function iconExtensionType($extension){
        switch(strtolower($extension)){
            case "jpg":
            case "png":
            case "jpeg":
            case "gif":
                $fileIcon = "icon_img.png";
                break;
            case "pdf":
                $fileIcon = "icon_pdf.png";
                break;
            case "txt":
                $fileIcon = "icon_txt.png";
                break;
            case "rar":
            case "zip":
                $fileIcon = "icon_rar.png";
                break;
            case "xls":
            case "xlsx":
                $fileIcon = "icon_excel.png";
                break;
            case "csv":
                $fileIcon = "icon_csv.png";
                break;
            case "doc":
            case "docx":
                $fileIcon = "icon_doc.png";
                break;
            case "xml":
                $fileIcon = "icon_xml.png";
                break;
            default:
                $fileIcon = "icon_unknown.png";
        }

        return $fileIcon;
    }

    function fileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    function folderCreation( $path )
    {
        $folder = explode("/",$path);

        $control_path = $folder[0];

        for($l=1;$l<count($folder);$l++)
        {
            $control_path .= "/".$folder[$l];
            if( is_dir( $control_path ) === false )
            {
                mkdir( $control_path, 0775 );
                chmod( $control_path, 0775 );
            }
        }

        return $path;
    }

    function getWebPath ( $path )
    {
        return substr( $path , strpos( $path , "/archivio/" ));
    }

    function encryptIt( $q ) {
        $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
        $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
        return( $qEncoded );
    }

    function decryptIt( $q ) {
        $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
        $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
        return( $qDecoded );
    }

    function setImgSize($imgPath, $maxWidth, $maxHeight, $setMaxDim = false)
    {
        $a_size = array(0,0);
        if (file_exists($imgPath)) {
            $dim = getimagesize($imgPath);

            if($dim[0]<$dim[1])
                $a_img = array("format"=>"v","w"=>$dim[1],"h"=>$dim[0]);
            else
                $a_img = array("format"=>"h","w"=>$dim[0],"h"=>$dim[1]);

            if($a_img['w']>$maxWidth){
                $width = $maxWidth;
                $height = $a_img['h']*($width/$a_img['w']);

                if($height>$maxHeight){
                    $height = $maxHeight;
                    $width = $a_img['w']*($height/$a_img['h']);
                }
            }
            else if($a_img['h']>$maxHeight){
                $height = $maxHeight;
                $width = $a_img['w']*($height/$a_img['h']);

                if($width>$maxWidth){
                    $width = $maxWidth;
                    $height = $a_img['h']*($width/$a_img['w']);
                }
            }

            if($setMaxDim===true){
                if($width<$maxWidth){
                    $width = $maxWidth;
                    $height = $a_img['h']*($width/$a_img['w']);

                    if($height>$maxHeight){
                        $height = $maxHeight;
                        $width = $a_img['w']*($height/$a_img['h']);
                    }
                }
                else if($height<$maxHeight){
                    $height = $maxHeight;
                    $width = $a_img['w']*($height/$a_img['h']);

                    if($width>$maxWidth){
                        $width = $maxWidth;
                        $height = $a_img['h']*($width/$a_img['w']);
                    }
                }
            }

            if($a_img['format']=="h"){
                $a_size = array(
                    0=>$width,
                    1=>$height
                );
            }
            else{
                $a_size = array(
                    0=>$height,
                    1=>$width
                );
            }
        }
        return $a_size;
    }

    function imageSize($imgPath, $maxWidth, $maxHeight, $setMaxDim = false)
    {
        $a_size = array();
        if (is_file($imgPath)){
            $dim = getimagesize($imgPath);

            if($dim[0] > $dim[1]){
                $width = $maxWidth;
                $height = $dim[1]*($width/$dim[0]);

                if($height>$maxHeight){
                    $height = $maxHeight;
                    $width = $dim[0]*($height/$dim[1]);
                }
            }
            else if($dim[0] < $dim[1]){
                $height = $maxHeight;
                $width = $dim[0]*($height/$dim[1]);

                if($width>$maxWidth){
                    $width = $maxWidth;
                    $height = $dim[1]*($width/$dim[0]);
                }
            }
            else if($dim[0] == $dim[1]){
                if($maxWidth<$maxHeight){
                    $width = $maxWidth;
                    $height = $maxWidth;
                }
                else{
                    $width = $maxHeight;
                    $height = $maxHeight;
                }
            }

            if($setMaxDim){
                if($width>$height){
                    if($width<$maxWidth){
                        $width = $maxWidth;
                        $height = $dim[1]*($width/$dim[0]);
                    }
                }
                else{
                    if($width<$maxWidth){
                        $width = $maxWidth;
                        $height = $dim[1]*($width/$dim[0]);
                    }
                }
            }

            $a_size = array(
                0=>$width,
                1=>$height
            );

        }
        else{
            //$cls_help = new cls_help();
            //$cls_help->alert("Il file ".$imgPath." non esiste (/Gitco2/cls/cls_file.php)");
            $a_size[0] = 0.1;
            $a_size[1] = 0.1;
            
        }
        return $a_size;
    }

    /**
     * Cancella i file creati da un determinato numero di giorni
     * @param path string
     * percorso file
     * @param days int
     * giorni passati dall'ultima modifica del file
     */
    function removeFiles ($path, $days){
        $handle = opendir($path);

        while (($file = readdir($handle)) != false){
            if($file!="." && $file!=".."){
                $data_modifica = date('Y-m-d',filemtime($path."/".$file));
                $differenzaDate = ( strtotime (date('Y-m-d')) - strtotime ($data_modifica) ) / (60 * 60 * 24);

                if ($differenzaDate >= $days)
                    unlink ($path."/".$file);
            }
        }

        closedir($handle);
    }

    public function createArchive ($archiveFile, $fileToArchive, $a_attachments=null){
        $rarPath = $this->checkRarExe();

        if($rarPath!==false){
            $archiveFile = str_replace("Program Files (x86)", "Progra~2", $archiveFile);
            $archiveFile = str_replace("Programmi", "Progra~1", $archiveFile);
            $fileToArchive = str_replace("Program Files (x86)", "Progra~2", $fileToArchive);
            $fileToArchive = str_replace("Programmi", "Progra~1", $fileToArchive);

            $expFile = explode ("/", $fileToArchive);
            $fileName = $expFile[count($expFile)-1];
            $filePath = substr($fileToArchive, 0, -strlen($fileName));

            if(is_file($archiveFile))
                unlink($archiveFile);

            $cwd = getcwd();

            $str_zip = $rarPath . "/rar.exe a ";
            $str_zip.= $archiveFile .  " " . $fileName;

            chdir($filePath);
            exec ($str_zip);

            if(is_array($a_attachments)){
                for($i=0;$i<count($a_attachments);$i++){
                    $expFile = explode ("/", $a_attachments[$i]);
                    $fileName = $expFile[count($expFile)-1];
                    $filePath = substr($a_attachments[$i], 0, -strlen($fileName));

                    $str_zip = $rarPath . "/rar.exe a ";
                    $str_zip.= $archiveFile .  " " . $fileName;

                    chdir($filePath);
                    exec ($str_zip);

                }
            }

            chdir($cwd);

            return $fileToArchive;
        }
    }

    public function checkRarExe(){
        if (is_dir("C:/Progra~1/WinRAR"))
            return "C:/Progra~1/WinRAR";
        else if (is_dir("C:/Progra~2/WinRAR"))
            return "C:/Progra~2/WinRAR";
        else{
            $cls_help = new cls_help();
            $testo_alert = "Nel server non c'e' il programma WINRAR!!!";

            $cls_help->alert( $testo_alert );

            return false;
        }
    }

    public function getFilesFromPath($path, $webPath = null){
        $files = scandir($path,1);
        $count = 0;
        $a_file = array();
        if(count($files)>0){
            for($i=count($files)-1;$i>=0;$i--) {
                if($files[$i]!="." && $files[$i]!=".."){
                    $fileExp = explode(".", $files[$i]);

                    $a_file[$count]['icon'] = IMG."/".$this->iconExtensionType($fileExp[count($fileExp)-1]);
                    $a_file[$count]['fileName'] = $files[$i];
                    $a_file[$count]['file'] = $path."/".$files[$i];
                    if(is_null($webPath))
                        $a_file[$count]['fileWeb'] = $this->getWebPath($path)."/".$files[$i];
                    else
                        $a_file[$count]['fileWeb'] = $webPath."/".$files[$i];
                    $count++;
                }

            }
        }
        return $a_file;
    }

    public function multipartFile($path){
        $a_file = array();
        if (! is_readable ( $path )) {
            throw new \Exception ( "Il file non e' leggibile!" );
        }

        $a_file['path'] = $path;
        $a_file['content_type'] = mime_content_type( $path );
        $a_file['name'] = basename( $path );

        $expPath = explode(".",$path);
        $a_file['extension'] = $expPath[count($expPath)-1];
        unset($expPath[count($expPath)-1]);
        $a_file['nameNoExt'] = implode(".",$expPath);

        return $a_file;
    }


    public function uploadFiles($destinationPath, $params = array()){
        $defParams = array("allowedExt"=>array(),"filekey"=>"file","addDateInFilename"=>false);
        foreach ($defParams as $key=>$value){
            if(!isset($params[$key]))
                $params[$key] = $value;
        }

        if(isset($_FILES[$params['filekey']]) && !empty($params['filekey'])){
            if (!is_dir($destinationPath))
                mkdir($destinationPath);
            chmod($destinationPath, 0777);

            $filesNumber = count($_FILES[$params['filekey']]['name']);
            $a_files = array();
            for( $i=0 ; $i < $filesNumber ; $i++ ) {
                $extension = strtolower(pathinfo($_FILES[$params['filekey']]['name'][$i], PATHINFO_EXTENSION));
                $filename = pathinfo($_FILES[$params['filekey']]['name'][$i], PATHINFO_FILENAME);
                //Se ho una limitazione dei tipi di file allora controllo che l'estensione del file sia nell'array
                //Problemi per la gestione dei punti all'interno del nome file
                if(count($params['allowedExt'])==0 || (count($params['allowedExt'])>0 && array_search($extension, $params['allowedExt'])!==false))
                    $extensionCheck = true;
                else
                    $extensionCheck = false;

                if($extensionCheck){
                    if ($_FILES[$params['filekey']]['tmp_name'][$i] != "") {
                        $date = date('Y-m-d');
                        $time = date('H-i-s');
                        if($params['addDateInFilename'])
                            $destFilename = $filename."_".date('Y-m-d_H-i-s').".".$extension;
                        else
                            $destFilename = $filename.".".$extension;
                        if (!move_uploaded_file($_FILES[$params['filekey']]['tmp_name'][$i], $destinationPath . "/" . $destFilename)){
                            $a_files['error'][] = array(
                                "msg" => "ERRORE UPLOAD: FILE ". $_FILES[$params['filekey']]['name'][$i]." NON CARICATO!",
                                "original_filename" => $_FILES[$params['filekey']]['name'][$i],
                                "filename" => $destFilename,
                                "extension" => $extension,
                                "date" => $date,
                                "time" => $time
                            );
                        }
                        else{
                            $a_files['uploaded'][] = array(
                                "msg" => "",
                                "original_filename" => $_FILES[$params['filekey']]['name'][$i],
                                "filename" => $destFilename,
                                "extension" => $extension,
                                "date" => $date,
                                "time" => str_replace("-",":",$time)
                            );
                        }
                    }
                    else{
                        $a_files['error'][] = array(
                            "msg" => "ERRORE UPLOAD: FILE TMP ".$_FILES[$params['filekey']]['name'][$i]." NON TROVATO!",
                            "original_filename" => $_FILES[$params['filekey']]['name'][$i],
                            "filename" => "",
                            "extension" => $extension,
                            "date" => "",
                            "time" => ""
                        );
                    }
                }
                else{
                    $a_files['error'][] = array(
                        "msg" => "ESTENSIONE NON PERMESSA ".$extension." PER IL FILE ".$_FILES[$params['filekey']]['name'][$i],
                        "original_name" => $_FILES[$params['filekey']]['name'][$i],
                        "new_name" => "",
                        "extension" => $extension,
                        "date" => "",
                        "time" => ""
                    );
                }
            }
            return $a_files;
        }
    }


}


?>