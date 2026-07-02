<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_InserimentoManualeNotifica.php";
include_once CLS . "/cls_paramUtils.php";


$cls_db = new cls_db();
$cls_help = new cls_help();


$p = $cls_help->getVar('p');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$partita_ID = $cls_help->getVar('partita');
$atto_ID = $cls_help->getVar('atto_corrente');
$stato = $cls_help->getVar('stato');
$tipo_atto = $cls_help->getVar('tipo_atto');
$tipo_file = $cls_help->getVar('type_file');


?>
<script>
	function fileExists(url) {
		if(url){
			var req = new XMLHttpRequest();
			req.open('HEAD', url, false);
			req.send();
			return req.status==200;
		} else {
			return false;
		}
    }
	function cambia_tipo(){
		var pathFronte = $('#immagine_fronte').val();
 		var filenameFronte = pathFronte.replace(/^.*\\/, "");
		if(fileExists(filenameFronte)){
			$("#immagine_fronte").removeClass("validateCustom vld_Custom_r");
		}
		else{
			$("#immagine_fronte").addClass("validateCustom vld_Custom_r");
		};
		var pathRetro = $('#immagine_retro').val();
 		var filenameRetro = pathRetro.replace(/^.*\\/, "");
		if(fileExists(filenameRetro)){
			$("#immagine_retro").removeClass("validateCustom vld_Custom_r");
		}
		else{
			$("#immagine_retro").addClass("validateCustom vld_Custom_r");
		};
		
	}
	function Salva()
	{
		cambia_tipo();
    	if(validateForm())
		{	
	    	$("#btnSub").trigger("click");
		}
		else return false;
	}

	
</script>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Caricamento - Notifiche</title>
	
	<link rel=StyleSheet href="<?=CSS;?>/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="<?=CSS;?>/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="<?= WEB_ROOT ?>/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= WEB_ROOT ?>librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="<?= WEB_ROOT ?>/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="<?= WEB_ROOT ?>/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="<?= WEB_ROOT ?>/librerie/js/datepicker.js" ></script>
	<!-- JS SWEETALERT  START -->

	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

	<!-- JS sweetalert    END -->
	<script src="<?= JS ?>/image_magnifier.js" type="text/javascript"></script>

<body class="sfondo_new_gitco" >  
<style>  
		.container{  
	    position : relative;
		text-align: center;  
		width: 50px;  
		height: 25px;  
		left: 375px;
		top:55px;
		}  
		.btn_class{  
		font-size: 20px;  
		}  
		.thumb{
		 width:100px;
		 height:100px;
		}
	</style>  
<?php if ($stato==0) {
		
	?>
	<script>
	$('document').ready(function () {
		$("#immagine_fronte").change(function () {
			if (this.files && this.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$('#imgFronte').attr('src', e.target.result);
					console.log(e);
					alert( URL.createObjectURL(e.target.result));
					$('#linkFronte').attr('href', e.target.result);
				}
				reader.readAsDataURL(this.files[0]);
			}
		});
		$("#immagine_retro").change(function () {
			if (this.files && this.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$('#imgRetro').attr('src', e.target.result);
				}
				reader.readAsDataURL(this.files[0]);
			}
		});
	});
	</script>
	<form id=form_caricanotifica name=form_caricanotifica action="carica_notifica.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>&atto_corrente=<?php echo $atto_ID; ?>&stato=1&tipo_atto=<?php echo $tipo_atto?>&type_file=<?php echo $tipo_file?>" method=post style="z-index: 1;" onsubmit="Salva()" enctype="multipart/form-data">
	<table  class="table table-striped table-bordered display" width="100%" >
		<tbody>
			<tr height="100px">
				<td width = "30%">
				<div class="col-lg-5">
					IMMAGINE FRONTE
				</div>
				</td>
				<td width = "30%">
				<div class="col-lg-5">
					<input class="resize form-control" style="width: 100%; background-color: rgb(153, 204, 255);" type="file" name="immagine_fronte" id="immagine_fronte" value="Carica immagine" accept="image/png, image/jpeg">
				</div>
				<td width="40%"></div><div class="thumb" id="tbFronte">
				<a id="linkFronte" href="#"><img id="imgFronte" class="thumb"></img></a>
				</div></td>
			</tr>
			<tr height="100px">
				<td>
				<div class="col-lg-5">
					IMMAGINE RETRO
				</div>
				</td>
				<td>
				<div class="col-lg-5">
					<input class="resize form-control" style="width: 100%; background-color: rgb(153, 204, 255);" type="file" name="immagine_retro" id="immagine_retro" value="Carica immagine" accept="image/png, image/jpeg" >
				<td></div><div class="thumb" id="tbRetro">
					<a id="linkRetro" href="#"><img id="imgRetro" class="thumb" ></img></a>
				</div></td>
				
			</tr>
		</tbody>
	</table>
	
	<button type="submit" id="btnSub" class="btn btn-primary pull-right" style="margin-bottom:5px;margin-right:10px;display:none" name="btnSub"  value="Salva"></button>

	</form>
	<div class="container" >
		<button id="btn" class="btn_class" onclick="Salva()" >Salva</button>
	</div>
<?php } else {
	
		if(isset($_FILES['immagine_fronte']) && $_FILES['immagine_fronte']['size'] > 0)
		if(isset($_FILES['immagine_retro']) && $_FILES['immagine_retro']['size'] > 0)
		try{
			$fai_nome = function($file_name,$atto_id,$suffix){
				$ext = pathinfo($file_name , PATHINFO_EXTENSION);
				//$file_name = pathinfo($file_name , PATHINFO_BASENAME);
    			return $atto_id.$suffix.".".$ext;
			};

			if($tipo_file == "cad") $suff_partial = "_CAD";
			else $suff_partial = "_NOT";

			$Immagine_Fronte = $fai_nome($_FILES['immagine_fronte']['name'],$atto_ID,$suff_partial."_F");
			$Immagine_Retro = $fai_nome($_FILES['immagine_retro']['name'],$atto_ID,$suff_partial."_R");
			$Nome_File = $cls_help->getVar('nome_file');
			$Data_Importazione = date("Y-m-d");
			$Operatore = $_SESSION['username'];
			

			switch ($tipo_atto) {
				case "Sollecito pre ingiunzione":
					$tipo_atto = "SOLL_PRE";
					break;
				case "Ingiunzione":
					$tipo_atto = "INGIUNZIONE";
					break;
				case "Sollecito di pagamento":
					$tipo_atto = "SOLLECITOINGIUNZIONE";
					break;
				case "Avviso di intimazione ad adempiere":
					$tipo_atto = "AVVISOINTIMAZIONE";
					break;
				case "Avviso di messa in mora":
					$tipo_atto = "AV_MORA";
					break;
				default :$tipo_atto = null;
			}

			$inserimento = new InserimentoManualeNotifica($cls_db);
			
			if($tipo_file == "cad") $path = IMMAGINI_CAD."/";
			else $path = IMMAGINI_NOTIFICHE."/";
			$inserimento
			->Set("Tipo_File",$tipo_file)
			->Set("Atto_Id",$atto_ID)
			->Set("Immagine_Fronte",$Immagine_Fronte)
			->Set("Immagine_Retro",$Immagine_Retro)
			->Set("Data_Importazione",$Data_Importazione)
			->Set("Operatore",$Operatore)
			->Set("Tipo_Atto",$tipo_atto)
			->PreparaRiga()
			->InserimentoDati()
			->Set("path_fronte",$_FILES['immagine_fronte']['tmp_name'])
			->Set("path_retro",$_FILES['immagine_retro']['tmp_name'])
			->Set("cls_param",new cls_paramNotifiche())
			->Set("cls_help",$cls_help)
			->SalvaImmagini($path);
	

		}
		catch(Exception $e) {

			$errmsg = "Alla riga " . $e->getLine() . ".\nCodice: " . $e->getCode() . ".\nErrore: " . $e->getMessage();
			echo json_encode(['esito' => 'KO','message'=>'L\'OPERAZIONE DI AGGIORNAMENTO NON È ANDATA A BUON FINE : '.$errmsg]);
			?>
			<div class="container" >
				<button class="btn_class" onclick="javascript:window.close()" >Chiudi</button>
			</div>
			<?php
			return;
		}
	?>

	<script>
		swal({
					title: 'OPERAZIONE RIUSCITA',
					text: "INSERIMENTO ANDATO A BUON FINE!",
					icon: 'success',
					timer: 5000,
					height:70,
					buttons: false
				}).then((result) => {

					window.opener.location.reload(false);
					window.close();
			})
	</script>
<?php }?>

<?php include(INC."/footer.php"); ?>