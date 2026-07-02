<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once INC."/headerAjax.php";
//require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
//include LIBRERIE . "/funzioni.php";



if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

  $file = $cls_help->getVar('file');
  $filename = $cls_help->getVar('filename');
  
  // verifico che il file esista
  if (!file_exists($file))
  {
  	// se non esiste stampo un errore
  	echo "Il file non esiste!";
  }else{
  	// Se il file esiste...
  	// Imposto gli header della pagina per forzare il download del file
//      echo $file."<br><br>";
//      echo $filename;
//  	header("Cache-Control: public");
//  	header("Content-Description: File Transfer");
//  	header("Content-Disposition: attachment; filename= " . $filename);
//      header('Content-Length: ' . filesize($file));
//
//      header("Content-Transfer-Encoding: binary");
//  	// Leggo il contenuto del file
//  	readfile($file);

      if (file_exists($file)) {
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename=' . basename($file));
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize($file));
          ob_clean();
          flush();
          readfile($file);
          exit;
      }
  }
  
  
?>