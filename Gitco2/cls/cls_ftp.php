<?php


class cls_ftp
{
    private $connectionId;
    private $loginOk = false;
    private $messageArray = array();

    public function __construct($server, $ftpUser, $ftpPassword, $isPassive = false) {
        // *** Set up basic connection
        $this->connectionId = ftp_connect($server);

        if($this->connectionId===false) throw new Exception("Connessione FTP con $server e $ftpUser");
        // *** Login with username and password
        $loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);

        // *** Sets passive mode on/off (default off)
        ftp_pasv($this->connectionId, $isPassive);

        // *** Check connection
        if ((!$this->connectionId) || (!$loginResult)) {
            $this->logMessage('FTP connection has failed!');
            $this->logMessage('Attempted to connect to ' . $server . ' for user ' . $ftpUser, true);
            return false;
        } else {
            $this->logMessage('Connected to ' . $server . ', for user ' . $ftpUser);
            $this->loginOk = true;
            return true;
        }
    }

    public function __deconstruct()
    {
        if ($this->connectionId) {
            ftp_close($this->connectionId);
        }
    }

    public function makeDir($directory)
    {
        // *** If creating a directory is successful...
        if (ftp_mkdir($this->connectionId, $directory)) {

            $this->logMessage('Directory "' . $directory . '" created successfully');
            return true;

        } else {

            // *** ...Else, FAIL.
            $this->logMessage('Failed creating directory "' . $directory . '"');
            return false;
        }
    }

    public function changeDir($directory)
    {
        if (ftp_chdir($this->connectionId, $directory)) {
            $this->logMessage('Current directory is now: ' . ftp_pwd($this->connectionId));
            return true;
        } else {
            $this->logMessage('Couldn\'t change directory');
            return false;
        }
    }

    public function getDirListing($directory = '.')
    {
        // get contents of the current directory
        $contentsArray = ftp_nlist($this->connectionId,   $directory);

        return $contentsArray;
    }

    public function downloadFile ($fileFrom, $fileTo, $includeBySubstring=null)
    {

        // *** Set the transfer mode
        $asciiArray = array('txt', 'csv');
        $expFile = explode('.', $fileFrom);
        if (in_array($expFile[count($expFile)-1], $asciiArray)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }

        if($includeBySubstring==null || strpos(strtoupper($fileFrom),strtoupper($includeBySubstring))===false){
            // try to download $remote_file and save it to $handle
            if (ftp_get($this->connectionId, $fileTo, $fileFrom, $mode, 0)) {

                return true;
                $this->logMessage(' file "' . $fileTo . '" successfully downloaded');
            } else {

                return false;
                $this->logMessage('There was an error downloading file "' . $fileFrom . '" to "' . $fileTo . '"');
            }
        }

    }

    public function downloadFolder($pathToSave, $includeBySubstring=null){
        $a_files = $this->getDirListing();
        foreach($a_files as $file){
            $this->downloadFile($file,$pathToSave.$file,$includeBySubstring);
        }

    }

    public function moveTo($name, $newName){
        if (ftp_rename($this->connectionId, $name, $newName)) {
            return true;
            $this->logMessage(' file/directory "' . $name . '" successfully moved to '. $newName);
        } else {
            return false;
            $this->logMessage('There was an error moving file/directory "' . $name . '" to "' . $newName . '"');
        }
    }

    public function moveFiles($root_folder, $original_folder, $new_folder, $substring_mismatch=null){
        $this->makeDir($new_folder);
        $this->changeDir($root_folder.$original_folder);
        $a_files = $this->getDirListing();
        $this->changeDir($root_folder);
        foreach($a_files as $file){
            if($substring_mismatch==null)
                $this->moveTo($original_folder."/".$file,$new_folder."/".$file);
            else if(strpos($file,$substring_mismatch)===false)
                $this->moveTo($original_folder."/".$file,$new_folder."/".$file);
        }
    }

    public function deleteFile($file){
        if (ftp_delete($this->connectionId, $file)) {
            return true;
            $this->logMessage(' file "' . $name . '" successfully deleted to '. $newName);
        } else {
            return false;
            $this->logMessage('There was an error deleting file "' . $name . '" to "' . $newName . '"');
        }
    }

    public function deleteDirectory($directory){
        if($this->is_dir($directory)){
            if (ftp_rmdir($this->connectionId, $directory)) {
                return true;
                $this->logMessage(' directory "' . $name . '" successfully deleted to '. $newName);
            } else {
                return false;
                $this->logMessage('There was an error deleting directory "' . $name . '" to "' . $newName . '"');
            }
        }
        else
            return false;
    }

    function is_dir($directory)
    {
        // Get the current working directory
        $origin = ftp_pwd($this->connectionId);
        // Attempt to change directory, suppress errors
        if (@ftp_chdir($this->connectionId, $directory))
        {
            // If the directory exists, set back to origin
            ftp_chdir($this->connectionId, $origin);
            return true;
        }
        // Directory does not exist
        return false;
    }

    public function loadFile($fileToLoad, $remoteFile, $mode = FTP_BINARY){
        if (ftp_put($this->connectionId, $remoteFile,$fileToLoad,$mode)) {
            return true;
            $this->logMessage(' file "' . $fileToLoad . '" successfully loaded to '. $remoteFile);
        } else {
            return false;
            $this->logMessage('There was an error uploading file "' . $fileToLoad . '" to "' . $remoteFile . '"');
        }


    }

    private function logMessage($message)
    {
        $this->messageArray[] = $message;
    }

    public function getMessages()
    {
        return $this->messageArray;
    }
}