<?php
include_once CLS . "/cls_paramUtils.php";


  class cls_paramNotifiche extends cls_param
  {
    function SaveImageNotifica($percorso,$file_name, $c, $path){

    	
    	$im = new Imagick( $percorso );
    	$im->setImageCompression(Imagick::COMPRESSION_JPEG);
    	$im->setImageCompressionQuality(100);
		$path = $path.$c;
		//echo $path;
		if( is_dir( $path ) == false )
		{
			$folder = explode("/", $path);

            $control_path = $folder[0];

            for ($l = 1; $l < count($folder); $l++) {
                $control_path .= "/" . $folder[$l];
                if (is_dir($control_path) == false) {
                    mkdir($control_path);
                }
            }
			//mkdir( $path );
		}
    	$im->writeImage($path."/".$file_name );

    	return $file_name;
    }

	function SavePdf($percorso,$filename, $c, $path)
	{
		if(file_exists($percorso))
		{
			$path = $path.$c;
			if( is_dir( $path ) == false )
			{
				mkdir( $path );
			}
			copy(
				$percorso,
				$path."/".$filename
			);
		}
		return $this;
   }
}
?>
