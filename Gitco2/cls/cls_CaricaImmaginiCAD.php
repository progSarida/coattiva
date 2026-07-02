<?php

class CaricamentoImmaginiCAD
{
    private $a_immagini_path=array();
    private $workpath;
    private $webpath;
    private $file_attuale;
    
    public function GetNumber()
    {
        return count($this->a_immagini_path);
    }
    public function Set($variabile,$valore)
    {
        $this->{$variabile} = $valore;
        return $this;
    }
    public function CaricaImmagini()
    {
        $this->a_immagini_path = array();
        $extension = function($filename)
        {
            return in_array(
                pathinfo($filename, PATHINFO_EXTENSION),
                array("jpeg","jpg","pdf","png")
            );

        };
        $prefix = function($filename)
        {
            $cont=0;
            $tofind= array("lavorato_","scartato_","nonprocessato_");
            array_map(function($i) use(&$cont,$filename){
                $cont+=count(explode($i,$filename))>1 ? 1 : 0;
            },$tofind);
            return $cont==0;
        };
        $single = function($arrayIn,&$arrayOut,&$arrayFind,$index) use(&$single)
        {
            if(count($arrayIn)>$index)
            {
                $filename = $arrayIn[$index];
                $tmp_name =pathinfo($filename, PATHINFO_FILENAME);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $right2=substr($tmp_name,strlen($tmp_name)-2);
                $name_without_ext = strrev(substr(strrev($tmp_name),2));
                if(!in_array($name_without_ext,$arrayFind))
                {
                    $arrayFind[] = $name_without_ext;
                    $a_condition = array("_R","_r","_F","_f");
                    if (in_array($right2,$a_condition))
                    {
                        $flag = true;
                        $name = $name_without_ext;
                    }
                    else
                    {
                        $flag = false;
                        $name = $tmp_name;
                    }
                    $arrayOut[] = array(
                        "flag"=>$flag,
                        "name"=>$name,
                        "filename" =>$filename,
                        "filenameR" =>$name_without_ext."_R.".$ext,
                        "filenameF" =>$name_without_ext."_F.".$ext,
                        "isPDF"=>(strtolower($ext)=="pdf")
                    );
                }
                $single($arrayIn,$arrayOut,$arrayFind,$index+1);
            }
            return $arrayOut;
        };

        if (is_dir($this->workpath))
        {
            $a_temp = scandir($this->workpath);
            $a_temp_filename= array_values(
                array_filter($a_temp,function($i)use($extension,$prefix){
                    return $extension($i) && $prefix($i);
                }));
            $arrayFind = array();
            $single($a_temp_filename,$this->a_immagini_path,$arrayFind,0);

        }
        return $this;
    }

    public function Immagine()
    {
        $a_result = null;
        
        if(count($this->a_immagini_path)>0)
        {
            $nome = $this->a_immagini_path[0]["filename"];
            $flag = $this->a_immagini_path[0]["flag"];
            $nomeR = $this->a_immagini_path[0]["filenameR"];
            $nomeF = $this->a_immagini_path[0]["filenameF"];
            $isPdf = $this->a_immagini_path[0]["isPDF"];
            $a_result = array(
                "filename" =>$nome,
                "filenameR" =>$nomeR,
                "filenameF" =>$nomeF,
                "path" =>$this->workpath,
                "pathweb" =>$this->webpath,
                "pathcompleto" =>$this->workpath."/".$nome,
                "webpathcompleto" =>$this->webpath."/".$nome,
                "pathcompletoR" =>$this->workpath."/".$nomeR,
                "webpathcompletoR" =>$this->webpath."/".$nomeR,
                "pathcompletoF" =>$this->workpath."/".$nomeF,
                "webpathcompletoF" =>$this->webpath."/".$nomeF,
                "flag" =>$flag,
                "isPDF"=>$isPdf
            );
            $this->file_attuale = $a_result;
        }
        
        return $a_result;
    }

    public static function RenameFile($path,$filename,$prefix)
    {
        if(file_exists($path."/".$filename))
        {
            rename(
                $path."/".$filename,
                $path."/".$prefix."_".$filename
            );
        }
    }
    public static function MoveFile($path,$filename,$destpath)
    {
        if(file_exists($path."/".$filename))
        {
            rename(
                $path."/".$filename,
                $path."/".$destpath."/".$filename
            );
        }
    }

    public static function SetDuplicato($path,$filename)
    {
        self::MoveFile($path,$filename,"Duplicati");
    }
    public static function SetNonProcessato($path,$filename)
    {
        self::MoveFile($path,$filename,"NonProcessati");
    }
    public static function SetScartato($path,$filename)
    {
        self::MoveFile($path,$filename,"Scartati");
    }
    public static function SetCDS($path,$filename)
    {
        self::MoveFile($path,$filename,"CDS");
    }   
    public static function SetLavorato($path,$filename)
    {
        self::MoveFile($path,$filename,"Lavorati");
    }

    public static function CreaPath($root)
    {
        $cicla_path = function ($array,$index,&$path) use (&$cicla_path)
        {   
            if(count($array)>$index)
            {
                $path=$path.$array[$index]."/";
                if(!is_dir($path))
                    mkdir($path);
                $cicla_path($array,$index+1,$path);
            }
        };

        $crea_path = function($path) use ($cicla_path)
        {
            $array = explode("/",$path);
            $p="";
            $cicla_path($array,0,$p);
        };
       

        $a_path = array("CDS","Duplicati","NonProcessati","Scartati","Lavorati");
        $crea =function ($index) use ($a_path,$root,&$crea,$crea_path)
        {   
            if(count($a_path)>$index)
            {
                $new_path = $root."/".$a_path[$index];
                $crea_path($new_path);
                return $crea($index+1);
            }
        };
        $crea(0);
    }
};