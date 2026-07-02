<?php

//use Carbon\Carbon;

include_once CLS . "/cls_Utils.php";

class storico{

    private $name;
    private $user;
    private $user_id;
    private $path;
    private $CompletePath;

    public function __construct($name="storico", $type=null,$path=null)
    {
        //Set data azione
        date_default_timezone_set("Europe/Rome");                               //set timezone
        //Nome file di log
        $this->name = date("Y")."_".$type."_".$name.".csv";
        //Set user e user_id
        $this->user = $_SESSION['username'];
        $this->user_id = $_SESSION['aut_progr'];
        //Controllo cartella di destinazione
        if($path == null){
            $util = new cls_Utils();
            $this->path = $util->crea_dir(ARCHIVIO."/storico");
        }
        //Percorso completo file di log
        $this->CompletePath = $this->path."/".$this->name;
        //Controllo esistenza file di log
        if(!file_exists($this->CompletePath)){
            $file = fopen($this->CompletePath, "w");
            fputcsv($file, ['Type_id', 'Type', 'User','User_Id','Page', 'Action', 'Time'], ';' );
            fclose($file);
        }
    }

    public function insRow($type, $message){
        try {
            //Apertura file di log
            $file = fopen($this->CompletePath, "a") or die("Unable to open file!");
            //$time = new Carbon(date("d-m-Y H:i:s"));
            $time = date("d-m-Y H:i:s");
            //Costruzione messaggio in 'Type'
            switch($type){
                case "I"://Inserisci
                    $type_text = 'Inserimento';
                    break;
                case "U"://Modifica
                    $type_text = 'Modifica';
                    break;
                case "D"://Elimina
                    $type_text = 'Cancellazione';
                    break;
                case "P"://Importa
                    $type_text = 'Importazione';
                    break;
                case "X"://Esporta
                    $type_text = 'Esportazione';
                    break;
                case "S"://Stampa
                    $type_text = 'Stampa';
                    break;
                case "C"://Calcola
                    $type_text = 'Calcolo';
                    break;
                case "E"://Elabora
                    $type_text = 'Elaborazione';
                    break;
                case "L"://Carica
                    $type_text = 'Caricamento';
                    break;
                default://Generico
                    $type_text = 'Altro';
                    break;
            }
            //Debug     
            $debug = "Page -> ".$_SERVER['SCRIPT_FILENAME'];
            if(isset(debug_backtrace()[1]['class']))                                      // 
                $debug .= ' Class->'.debug_backtrace()[1]['class'].' - ';                 // eliminati perchè vuoti:
            if(isset(debug_backtrace()[1]['function']))                                   // le azioni non chiamano metodi di classi ma pagine php
                $debug .= ' Function->'.debug_backtrace()[1]['function'];                 // 
            //Scrittura su file
            fputcsv($file, [$type, $type_text, $this->user,$this->user_id, $debug, $message, $time], ';');
            //Chiusura file
            fclose($file);
        }
        catch (Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}