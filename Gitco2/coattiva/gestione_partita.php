<?php
    if (!session_id()) session_start();

	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include_once(INC."/header.php");
	include_once(INC."/menu.php");
	include_once(CLS."/cls_GestionePartita.php");
	include_once(CLS."/cls_DateTimeInLine.php");
    //include_once(ROOT."/search_modal/startAjax.php");
    //include_once CLS . "/cls_storico.php";

    //$storico = new storico('storicoRuolo','3');
	$cls_partita = new cls_GP();
	$cls_date = new cls_DateTimeI("IT",false);
$pageCalled = '<p style="font-weight: bold;display: inline;">Vai a pagina Elenco Partite</p>';

    $note_interne = "";

	$partita_ID = $cls_help->getVar('partita');

	$layout = "<script>";

	//$anni_gestiti = new anni_gestiti($c, null);

	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $cls_partita->Options_Anni_Veloci($c, "COATTIVA", "gestione_partita");

		if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";
	}

	$layout.= "</script>";

	if($partita_ID == "NO")
		$partita_ID = null;

	$partita = $cls_partita->getDataPartita($partita_ID, $c, $a); //$cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    $docTypePartita = "";
    if(!empty($partita['DocumentTypeId'])){
        $a_docType = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM document_type WHERE ID=".$partita['DocumentTypeId']));
        $docTypePartita = "DOCUMENTO IMPORTATO: ".$a_docType['Description'];
    }
    
//var_dump($partita);
    $note_interne = $partita['Note_Interne'];
   
    if(!isset($partita['Import_Id']))
        $partita['Import_Id'] = null;
	$path =  ATTI ."/". $c . "/Documenti/";


    if($partita["Data_Attivazione_Flag_Blocco_Coazione"] != null) $data_att_blocco = "( ".$cls_date->Get_DateNewFormat($partita["Data_Attivazione_Flag_Blocco_Coazione"],"DB")." )";
    else $data_att_blocco = "";

    $parametri_notifica = $cls_partita->array_notifica();// new parametri_notifica(null);

    $options_blocco = $cls_partita->options_select_array($parametri_notifica["BloccoCoattiva"]);


	$path_file_1 = substr( $path.$partita["File_1"] , strpos( $path.$partita["File_1"] , "/archivio/" ));//mostra_file_path($path.$partita->File_1);
	$path_file_2 = substr( $path.$partita["File_2"] , strpos( $path.$partita["File_2"] , "/archivio/" ));//mostra_file_path($path.$partita->File_2);

    $path_file_1 = SUPER_WEB_ROOT.$path_file_1;
    $path_file_2 = SUPER_WEB_ROOT.$path_file_2;
    
	if($partita["File_1"]!="")
		$check_file = "<input type=checkbox name=del_file_1 value='no' checked title=\"Per cancellare il file e' necessario deselezionarlo e cliccare il tasto salva\">";
	else
		$check_file = "<input type=hidden name=del_file_1 value='no'>";

	if($partita["File_2"]!="")
		$check_file_2 = "<input type=checkbox name=del_file_2 value='no' checked title=\"Per cancellare il file e' necessario deselezionarlo e cliccare il tasto salva\">";
	else
		$check_file_2 = "<input type=hidden name=del_file_2 value='no'>";

	$query = "SELECT * FROM ruolo WHERE ID = '".$partita["Ruolo_ID"]."' AND CC = '".$c."'";
	$ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo");

	//$ruolo = new ruolo($partita["Ruolo_ID"], $c, null, false);

	$layout.="<script>$('#tipo_partita').val('".$partita["Tipo"]."');</script>";

	$sottotipi = $cls_partita->sottotipi_option();
	if(isset($sottotipi[$partita["Tipo"]]))
	{
		if($sottotipi[$partita["Tipo"]]=="")
			$layout.="<script>$('#sottotipo_partita').hide();</script>";
	}
	else
		$layout.="<script>$('#sottotipo_partita').hide();</script>";

	if($partita["Sottotipo"]!="")
		$layout.="<script>$('#sottotipo_partita').val('".$partita["Sottotipo"]."');</script>";

	$query = "SELECT ID FROM atto WHERE Partita_ID = '".$partita['ID']."'";
	$atto_id = $cls_db->getResults($cls_db->ExecuteQuery($query));// select_mysql_array("ID", "atto","Partita_ID = '".$this->ID."'");

	$num_atti = count($atto_id);

	$control_modifiche = 1;

	if($num_atti>0)
		$data_notifica_ing = $cls_date->Get_DateNewFormat($partita["Atto"][0]["Data_Notifica"],"DB");
	else{
		$data_notifica_ing = "";
		$control_modifiche = 0;
	}

	if($num_atti==1)
	{
		if($partita["Atto"][0]["Rettifica_Flag"] == "si"){
			$control_modifiche = 0;
			$data_notifica_ing = "";
		}
	}
//echo $num_atti." ".$control_modifiche;
	$ID_Partita = $partita["ID"];

	$anno_riferimento = $partita["Anno_Riferimento"];

	$utente_ID = $partita["Utente_ID"];
	$query = "SELECT * FROM utente WHERE ID = '".$utente_ID."' AND CC_Comune = '".$c."' LOCK IN SHARE MODE";
	$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($utente_ID,$c);

	$query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente['Forma_Giuridica']."' AND CC = '".$c."'";
	$utente["Forma_Giuridica_Oggetto"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"forma_giuridica_societa");

	//$id_utente 				= 	$utente["ID"]; CONTROLLARE
	$genere_utente 			= 	isset($utente["Genere"])?$utente["Genere"]:"";
	$comune_id 				=	isset($utente["Comune_ID"])?$utente["Comune_ID"]:"";
	$cognome_utente 		=	isset($utente["Cognome"])?$utente["Cognome"]:"";
	$nome_utente 			=	isset($utente["Nome"])?$utente["Nome"]:"";
	$ditta					=	isset($utente["Ditta"])?$utente["Ditta"]:"";

	if(!isset($utente["Genere"])) $utente["Genere"] = "";
	if($utente["Genere"]=="")
		$utente_e_codice = "";
	else if($utente["Genere"]!="D")
		$utente_e_codice = "(".$utente["Comune_ID"].") ".$utente["Cognome"]." ".$utente["Nome"];
	else if($utente["Genere"]=="D")
	{
		$utente_e_codice = "(".$utente["Comune_ID"].") ".$utente["Ditta"]." ".$utente["Forma_Giuridica_Oggetto"]["Sigla"];
	}

	$coo_ID = $partita["Coo_ID"];
	$coo_tipo = $partita["Coo_Tipo"];
	$tipo = $partita["Tipo"];

	$prev = $partita["prev"];
	$next = $partita["next"];

	$data_interessi = "";
	$tipo_info_gen = "";
	$info_cart = "";
	$titolo_ent = "";
	$descrizione_ent = "";
	$tipo_sanz = "";
	$titolo_sanz = "";
	$targa_sanz = "";
	$data_sanz = "";
	$matri = "";


	$tributo = isset($partita["Tributo"])?$partita["Tributo"]:null;

	$readonly = "";
	if($tributo!=null)
	{
		$readonly = "readonly";
		$data_interessi = $cls_date->Get_DateNewFormat($tributo[0]["Data_Decorrenza_Interessi"],"DB");
		$tipo_info_gen = $tributo[0]["Tipo_Info"];

		$info_cart = $tributo[0]["Info_Cartella"];

		$titolo_ent = $tributo[0]["Titolo_Entrata"];
		$descrizione_ent = $tributo[0]["Descrizione_Entrata"];

		$titolo_sanz = $tributo[0]["Titolo_Sanzione"];
		$targa_sanz = $tributo[0]["Targa_Sanzione"];
		$data_sanz = $cls_date->Get_DateNewFormat($tributo[0]["Data_Sanzione"],"DB");

		$matri = $tributo[0]["Matricola"];

		for($y=0;$y<count($tributo);$y++)
		{
			$id_tributo[$y] = $tributo[$y]["ID"];
			$codice_tributo[$y] = $tributo[$y]["Codice_Tributo"];
			$anno_tributo[$y] = $tributo[$y]["Anno_Tributo"];
			$tipo_tributo[$y] = $tributo[$y]["Tipo_Tributo"];

			$info_cartella[$y] = $tributo[$y]["Info_Cartella"];
			$tipo_info[$y] = $tributo[$y]["Tipo_Info"];
			$imposta[$y] = number_format($tributo[$y]["Imposta"],2,",","");

			//print_r($tributo);

			if($tipo_info[$y]=="S")
			{

				$tipo_sanzione[$y] = $tributo[$y]["Tipo_Sanzione"];
				$titolo_sanzione[$y] = $tributo[$y]["Titolo_Sanzione"];
				$targa_sanzione[$y] = $tributo[$y]["Targa_Sanzione"];
				$data_sanzione[$y] = $tributo[$y]["Data_Sanzione"];

			}
			else if($tipo_info[$y]=="E")
			{
				$titolo_entrata[$y] = $tributo[$y]["Titolo_Entrata"];
				$descrizione_entrata[$y] = $tributo[$y]["Descrizione_Entrata"];
			}
			else if($tipo_info[$y]=="M")
			{
				$matricola[$y] = $tributo[$y]["Matricola"];
			}

		}
	}
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

var numero_atti = parseInt("<?php echo $control_modifiche; ?>");
var data_notifica_ing = "<?php echo $data_notifica_ing; ?>";
var numero_tributi = "<?php  echo $tributo!=null?count($tributo):null; ?>";
var readonly = "<?php echo $readonly; ?>";
var operatore = "<?php echo $_SESSION['username']; ?>";

//F3
switchMenuImg("F3");
F3_button = function()
{
	if( numero_atti > 0 && (operatore!="emanuela" && operatore!="andrea" && operatore!="mirkop"))
	{
		alert("Sono gia' stati elaborati atti successivi. Impossibile effettuare modifiche ai codici tributo.");
		return false;
	}
	else
	{
		if($('#tipo_partita').val()=="" || $('#tipo_partita').val()==undefined)
		{
			alert("Selezionare il campo Tipo entrata.");
			return false;
		}

		if($('#ruolo').val()=="" || $('#ruolo').val()==undefined)
		{
			alert("Selezionare un ruolo da associare alla partita contabile.");
			return false;
		}

		if($('#utente').val()=="" || $('#utente').val()==undefined)
		{
			alert("Selezionare un utente da associare alla partita contabile.");
			return false;
		}

		if($('#anno_rif').val()=="" || $('#anno_rif').val()==undefined)
		{
			alert("Compilare il campo Anno rif. con l'anno dell'accertamento.");
			return false;
		}

		if($('#info_cartella').val()=="" || $('#info_cartella').val()==undefined)
		{
			alert("Compilare il campo Rif. Accertamento con le informazioni della cartella!");
			return false;
		}

		if($('#data_interessi').val()=="" || $('#data_interessi').val()==undefined)
		{
			alert_data = "Attenzione il campo Data di decorrenza interessi non e' stato compilato!";
			alert_data += "\nIn questo modo non verranno calcolati interessi sulla prima ingiunzione elaborata.";
			alert(alert_data);
		}

		if($('#select_info').val()=="" || $('#select_info').val()==undefined)
		{
			alert("Selezionare il campo tipo!");
			return false;
		}

		if($('#select_info').val()=="S"){
			if(($('#titolo_sanz').val()=="" || $('#titolo_sanz').val()==undefined) )
			{
				alert("Compilare il campo rif. atto con il numero dell'accertamento.");
				return false;
			}
			if($('#titolo_sanz').val()!="" && ( $('#data_sanz').val()=="" || $('#data_sanz').val()==undefined))
			{
				alert("Compilare il campo Data sanzione con la data dell'accertamento.");
				return false;
			}
		}
		else if($('#select_info').val()=="E"){
			if($('#titolo_ent').val()=="" || $('#titolo_ent').val()==undefined)
			{
				alert("Attenzione! Il campo rif. atto non e' stato compilato con il numero di accertamento.");
			}
		}

		if( 0 > parseInt("<?php echo $tributo!=null?count($tributo):0; ?>") && ($('#cod_tributo_new').val()=="" || $('#cod_tributo_new').val()==undefined))
		{
			alert("Inserire il codice tributo prima di salvare!");
			return false;
		}

        if($('#flag_blocco').is(":checked"))
        {
            $("#note_blocco").addClass("validateCustom vld_Custom_r");
            $("#motivo_blocco").addClass("validateCustom vld_Custom_r");
        }
        else {
            $("#note_blocco").removeClass("validateCustom vld_Custom_r");
            $("#motivo_blocco").removeClass("validateCustom vld_Custom_r");
        }


		if(data_notifica_ing=="" || operatore=="emanuela" || operatore=="andrea" || operatore=="mirkop")
		{
			control = submit_buttons('Update');
			//alert(validateForm());
			if(control && validateForm())
   				$("#btnSub").trigger("click");
		}
		else
			alert("Nell'Ingiunzione e' presente la data di notifica del "+data_notifica_ing+". Impossibile effettuare modifiche ai codici tributo.");
	}
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	if( numero_atti > 0 )
	{
		alert("Sono gia' stati elaborati degli atti. Impossibile eliminare i codici tributo.");
		return false;
	}
	control = submit_buttons('Delete');
		if(control)
		$("#btnSub").trigger("click");
}


//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="gestione_partita.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
switchMenuImg("F6");
F6_button = function()
{
	if( modifica == 0 )
	{
		crea_partita();
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


//F7
//switchMenuImg("F7");
F7_button = function()
{
	if( modifica == 0 )
	{
		value = "<?php echo $prev; ?>";
		location.href="gestione_partita.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F8
//switchMenuImg("F8");
F8_button = function()
{
	if( modifica == 0 )
	{
		value = "<?php echo $next; ?>";
		location.href="gestione_partita.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


//PAG GIU
//switchMenuImg("pagedown");
pagedown_button = function(){

	if( modifica == 0 )
	{
		location.href="pagamento_pignoramento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
//switchMenuImg("pageup");
pageup_button = function(){

	if( modifica == 0 )
	{
		location.href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


//F9
function ricerca_F9()
{
	if( modifica == 0 )
	{
		RicercheDaId('utente',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function ruolo (value)
{
	location.href="gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function crea_partita()
{
	top.location.href = "gestione_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

<!-- ********** VARIABILI ********** -->
<script>
var info = "";
</script>

<!-- ********** CALENDARIO ********** -->
<script>
$(function() {

	 $( "input#data_interessi" ).datepicker();

	 });

$(function() {

	 $( "input#data_sanz" ).datepicker();

	 });
</script>

<!-- ********** TIPO PARTITA ********** -->
<script>
function cambia_tipo()
{
    $temp = $('#select_info_count').val();
    $('#select_info_count').val($temp+1);
    /**<div class="col col-lg-12">
     <p class='titolo font16 under_decor' id=tipo_trib_<?php echo $y; ?>><?php echo $tipo_tributo[$y]; ?></p>
     </div> */
    info = "	<div class='col col-lg-12'>";
    info += "	<p class='titolo font16 under_decor' id=tipo_trib_new></p>";
    info += "	<div>";
    info += "	<div class='col col-lg-3'>";
    info += "	  <div class='form-group'>";
    info += "	    <label class='col-lg-7 control-label' style='text-align: left;'>Codice Tributo *</label>";
    info += "	    <div class='col-lg-5'>";
    info += "	      <input id=cod_tributo_new class='form-control resize validateCustom vld_Custom_r' style='background-color: rgb(153, 204, 255); border: 2px solid black; width: 60px;' readonly name=cod_tributo_new type=text value='' size=4>";
    info += "	    </div>";
    info += "	  </div>";
    info += "	</div>";
    info += "	<div class='col col-lg-2'>";
    info += "	  <div class='form-group'>";
    info += "	    <label class='col-lg-6 control-label' style='text-align: left;'>Anno *</label>";
    info += "	    <div class='col-lg-6'>";
    info += "	      <input id=anno_tributo_new class='form-control resize' style='border: 2px solid black; width: 60px;' name=anno_tributo_new type=text value='' size=4>";
    info += "	    </div>";
    info += "	  </div>";
    info += "	</div>";
    if($('#select_info').val()=="S"){

        info += "	<div class='col col-lg-4'>";
        info += "	  <div class='form-group'>";
        info += "	    <label class='col-lg-4 control-label resize' style='text-align: center;'>Atto</label>";
        info += "	    <div class='col-lg-8'>";
        info += "			  <select id=select_atto_new name=select_atto_new  onchange='cambia_atto();' class='pwidth150 form-control resize'>";
        info += "			  	<option value=VE >Verbale</option>";
        info += "			  	<option value=OR >Ordinanza</option>";
        info += "			  	<option value=IN >Ingiunzione</option>";
        info += "			  	<option value=DM >Decreto Ministeriale</option>";
        info += "			  </select>";
        info += "	    </div>";
        info += "	  </div>";
        info += "  </div>";
    }
    else{
        info += "	<div class='col col-lg-4'>";
        info += "  </div>";
    }

    info += "	<div class='col col-lg-2'>";
    info += "	  <div class='form-group'>";
    info += "	    <label class='col-lg-6 control-label resize' style='text-align: left;'>Importo *</label>";
    info += "	    <div class='col-lg-6'>";
    info += "	      <input id=importo_new class='form-control resize corrige_numero validateCustom vld_Custom_d vld_Custom_r' style='width: 90px;text-align: right;' name=importo_new type=text value='' size=6>";
    info += "	    </div>";
    info += "	  </div>";
    info += "	</div>";
    info += "	<div class='col col-lg-1'>";
    info += "</div>";

	if(readonly=="readonly")
	{
		$('#select_info').val('<?php echo $tipo_info_gen; ?>');
	}

	$('.sanzione').hide();
	$('.entrata').hide();
	$('.matricola').hide();

	switch($('#select_info').val())
	{
		case "S":   $('.sanzione').show();		 	break;
		case "E":   $('.entrata').show();		    break;
		case "M":	$('.matricola').show();		    break;
	}
    if($('#select_info_count').val() > 1)
        $('#select_info_f').val('t');                                                           // segna modifica
	console.log("info: "+$('#select_info').val());
}

function cambio_anno_rif()
{
	if(numero_atti>0)
	{
		$('#anno_rif').val("<?php echo $partita["Anno_Riferimento"]; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#anno_rif_f').val('t');
}

var sottotipo = "";
function cambio_tipo()
{
	if(numero_atti>0)
	{
		$('#tipo_partita').val("<?php echo $partita["Tipo"]; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}

	var tipo = "_"+$('#tipo_partita').val();

	switch(tipo)
	{
		case "_RIFIUTI":
			sottotipo = "<?php echo $sottotipi['RIFIUTI']; ?>";
			break;
		case "_IMMOBILI":
			sottotipo = "<?php echo $sottotipi['IMMOBILI']; ?>";
			break;
		case "_CDS":
			sottotipo = "<?php echo $sottotipi['CDS']; ?>";
			break;
		case "_IRPEF":
			sottotipo = "<?php echo $sottotipi['IRPEF']; ?>";
			break;
		case "_PATRIMONIALE":
			sottotipo = "<?php echo $sottotipi['PATRIMONIALE']; ?>";
			break;
		case "_OSAP":
			sottotipo = "<?php echo $sottotipi['OSAP']; ?>";
			break;
		case "_PUBBLICITA":
			sottotipo = "<?php echo $sottotipi['PUBBLICITA']; ?>";
			break;

		default:
			sottotipo = "";
			break;
	}

	if(sottotipo!="")
	{
		$('.aggiunta_option').remove();
		$('#sottotipo_partita').append(sottotipo);
		$('#sottotipo_partita').show();
	}
	else
	{
		$('.aggiunta_option').remove();
		$('#sottotipo_partita').hide();
	}
    $('#tipo_partita_f').val("t");
}
</script>

<!-- ********** NUOVO CODICE TRIBUTO ********** -->
<script>
function mostra_nuovo()
{
    if( numero_atti > 0 )
    {
        alert("Sono gia' stati elaborati degli atti.");
        return false;
    }
	if($('#tipo_partita').val()=="")
	{
		alert("Selezionare il tipo tributo per effettuare la ricerca e l'inserimento del codice tributo.");
		return false;
	}

	if($('#select_info').val()=="")
	{
		alert("Selezionare il campo Tipo per inserire un nuovo codice tributo!");
		return false;
	}

	if($('#cod_tributo_new').val()!="" && $('#cod_tributo_new').val()!=undefined)
	{
		alert("Codice tributo gia inserito! Salvare la partita per inserire un codice tributo aggiuntivo.");
		return false;
	}

    cambia_tipo();

    $temp = parseInt($('#nuovo_tributo_f').val())
    $('#nuovo_tributo_f').val($temp+1);
    $('#scrivi_nuovo').html(info);
	$('#cod_tributo_new').attr('ondblclick',"/*RicercheDaId('codice','new');*/openOfcanvas('code','new');");
	//InizializzaAttributi();
}

function gestione_ruolo()
{
	link = "<?= WEB_ROOT; ?>/coattiva/inserimento_ruolo.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	location.href= link;
}

function gestione_importazione()
{
    link = "<?= WEB_ROOT; ?>/290/mgmt_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&Import_Id=<?= $partita['Import_Id']; ?>";
    location.href= link;
}

function elabora_nuovo()
{
	if( numero_atti == "0" && numero_tributi!="0")
	{
		link = "<?= WEB_ROOT; ?>/elaborazioni/elabora_atto.php?richiesta_singola=si&tipo_atto=Ingiunzione";
		link+= "&partita=<?php echo $ID_Partita; ?>&anno_rif=<?php echo $partita["Anno_Riferimento"]; ?>&p=<?php echo $utente_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        //$('#elab_ing_f').val('t');
        //$storico->insRow('E',"Elaborata ingiunzione partita ".$partita_ID);
        location.href= link;
	}
	else if(numero_atti > 0)
	{
		alert("Ingiunzione esistente! Impossibile effettuare l'elaborazione.");
	}
	else if(numero_tributi == "0")
	{
		alert("Codici tributo inesistenti! Impossibile effettuare l'elaborazione.");
	}
}

</script>

<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

	return setupPagina;
}
function callParent(valorediritorno) {
    //alert("callParent");
    if(valorediritorno!=null){
        switch(selectParent){
            case "utente":
                if(typeof valorediritorno !== 'string')
                    reopen('obj',valorediritorno);
                else
                    reopen('str',valorediritorno);

                break;
            case "codice":

                //alert("codice undef - "+selectRif);
                console.log(valorediritorno);

                if(selectRif!="new")
                {
                    //alert(selectRif);
                    $('#cod_tributo_'+selectRif).val(valorediritorno.Codice);
                    $('#tipo_trib_'+selectRif).text(valorediritorno.Descrizione);
										//document.getElementById('#cod_tributo_'+selectRif).dispatchEvent(new Event("change"));

                }
                else
                {
                    $('#tipo_trib_new').text(valorediritorno.Descrizione);
                    $('#cod_tributo_new').val(valorediritorno.Codice);
										//document.getElementById("cod_tributo_new").dispatchEvent(new Event("change"));
                }

                break;

            case "intestatario":
                //Questa chiamata serve per creare la stinga da inserire nel form '(num) nome'
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "<?= WEB_ROOT; ?>/coattiva/ajax/ajax_partita.php?c=<?php echo $c; ?>",
                    data: {
                        ajax: "nome",
                        ID: valorediritorno.p,
                    },

                    success: function(value) {
                        nome = value;
                    }
                });

                $('#utente_nome').val(nome);
                $('#utente').val(valorediritorno.p);
								document.getElementById("utente_nome").dispatchEvent(new Event("change"));
                break;

            case "ruolo":

                $('#ruolo').val(valorediritorno.ID);
                $('#ruolo_desc').val(valorediritorno.Descrizione);
								document.getElementById("ruolo_desc").dispatchEvent(new Event("change"));

                break;
        }
    }
}

function reopen(type, value){
    if(type == 'obj')
        top.location.href="../gestione_partita.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
    else if(type == 'str')
        top.location.href="../gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

var selectParent = "";
var selectRif = "";
function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
    DimensionPos = undefined;

    switch(value)
    {
        case "utente":
            DimensionPos = {
            width: 800,
            height: 400,
            left: (screen.width/2-400),
            top: (screen.height/2-200)
        };
            var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

            break;

        case "intestatario":
            if(numero_atti>0)
            {
                alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
                return false;
            }
						DimensionPos = {
							width: 600,
							height: 300,
							left: (screen.width/2-300),
							top: (screen.height/2-150)
						};
            var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            break;

        case "codice":
            DimensionPos = {
                width: 800,
                height: 500,
                left: (screen.width/2-400),
                top: (screen.height/2-250)
            };
            tipo_partita = $('#tipo_partita').val();
            sottotipo_partita = $('#sottotipo_partita').val();
            if(sottotipo_partita==null)
                sottotipo_partita = "";
            var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=codice&c=<?php echo $c; ?>&a=<?php echo $a; ?>&tipo="+tipo_partita+"&sottotipo="+sottotipo_partita;

            break;

        case "lista":
            DimensionPos = {
                width: 900,
                height: 750,
                left: (screen.width/2-450),
                top: (screen.height/2-375)
            };
            var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=listaCodice&posted=true&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

            break;

        case "ruolo":
            if(numero_atti>0)
            {
                alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
                return false;
            }

            DimensionPos = {
							width: 600,
							height: 300,
							left: (screen.width/2-300),
							top: (screen.height/2-150)
						};
            var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=gen_ruolo&c=<?php echo $c; ?>&a=<?php echo $a; ?>";

            break;

        default:
            alert('Ricerca sconosciuta!');
            return false;

            break;
    }

    openWindowSearch(stringa,DimensionPos);
}

</script>
<!-- ********** GESTIONE MODALI OFFCANVAS ********** -->
<script>

    //Variabili
    //var role_S = "";                                                        // Tipo ricerca ruolo
    //var owner_S = "";                                                       // Tipo ricerca intestatario
    var all_city = 0;                                                       // Ricerca su tutti i comuni
    //var code_S ="";
    //var user_entry_S = "";

    // Apertura modale
    function openOfcanvas(type,rif){
        // Reset campi input
        $('#desc').val("");
        $('#year').val("");
        $('#name').val("");
        $('#cf').val("");
        $('#ricDesc').val("");
        $('#ricCode').val("");
        $('.user_entry').val("");

        // Reset spazi tabella
        $('#appendTableRole').empty();
        $('#appendTableOwner').empty();
        $('#appendTableCode').empty();
        $('#appendTableUserEntry').empty();

        selectRif = rif;
        switch (type){
            case 'role':
                if(numero_atti>0)
                {
                    alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
                    return false;
                }
                //role_S = "desc";
                $("#ins_year").hide();
                $("#ins_desc").show();
                document.getElementById('check_desc').checked = true;
                document.getElementById('check_year').checked = false;
                $('#roleSearchModal').modal('show');
                break;
            case 'owner':
                //owner_S = "name";
                $("#ins_cf").hide();
                $("#ins_name").show();
                document.getElementById('check_name').checked = true;
                document.getElementById('check_cf').checked = false;
                $('#ownerSearchModal').modal('show');
                break;
            case 'list':
                $('#ListModal').modal('show');
                startAjax('list');
                break;
            case 'code':
                //code_S = "c_desc";
                $("#ins_code").hide();
                $("#ins_desc_c").show();
                document.getElementById('check_desc_code').checked = true;
                document.getElementById('check_code').checked = false;
                $('#codeSearchModal').modal('show');
                break;
            case 'user_entry':
                // Setta stato checkbox iniziale
                document.getElementById('check_u_n').checked = true;
                document.getElementById('check_u_c').checked = false;
                document.getElementById('check_e_cA').checked = false;
                document.getElementById('check_e_cP').checked = false;
                document.getElementById('check_e_i').checked = false;
                // Setta titolo modale iniziale
                $("#userEntrySearchModalLabel_u").show();
                $("#userEntrySearchModalLabel_e").hide();
                // Setta campo input iniziale
                $("#ins_u_n").show();
                $("#ins_u_c").hide();
                $("#ins_e_cA").hide();
                $("#ins_e_cP").hide();
                $("#ins_e_i").hide();
                // Setta tipop di ricerca iniziale
                //user_entry_S = "user_n";
                // Apre modale
                $('#userEntrySearchModal').modal('show');
                break;
        }
    }
    // Iserimento dati da modale a pagine
    function initialId(type,val){
        switch (type){
            // Inserimento Ruolo
            case "role_d":
            case "role_y":
                $('#ruolo').val(val["ID"]);
                $('#ruolo_desc').val(val["Descrizione"]);
                document.getElementById("ruolo_desc").dispatchEvent(new Event("change"));
                break;
            // Inserimento Intestatario
            case "owner_n":
            case "owner_cf":
                $('#utente').val(val["ID"]);
                $('#utente_nome').val(val["Ins"]);
                document.getElementById("utente_nome").dispatchEvent(new Event("change"));
                //Sostituzione comune (ricarica pagina) se ricerca su titti i comuni: ELIMINATA
                /*
                }
                else {
                    console.log(val);
                    if(val["P_ID"] !== null && val["Anno"] !== null)
                       top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_partita.php?partita="+val["P_ID"]+"&c="+val["CC_Comune"]+"&a="+val["Anno"];
                    else
                        alert("Partita non presente per questo utente");
                }
                */
                break;
            // Inserimento Codice Tributo
            case "code_d":
            case "code_n":
                //console.log(val);
                if(selectRif!="new")
                {
                    //alert(selectRif);
                    $('#cod_tributo_'+selectRif).val(val["Codice_Tributo"]);
                    $('#tipo_trib_'+selectRif).text(val["Descrizione"]);
                   // document.getElementById('#cod_tributo_'+selectRif).dispatchEvent(new Event("change"));
                   // document.getElementById('#cod_tributo_'+selectRif).dispatchEvent(new Event("change"));
                }
                else
                {
                    $('#tipo_trib_new').text(val["Descrizione"]);
                    $('#cod_tributo_new').val(val["Codice_Tributo"]);
                    //document.getElementById("cod_tributo_new").dispatchEvent(new Event("change"));
                }
                break;
            // Inserimento dati utente in 'Gitco2/coattiva/gestione_ruolo.php'
            case "user":
            case "cf":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                break;
            // Inserimento dati partita in 'Gitco2/coattiva/gestione_partita.php'
            case "info":
            case "entry":
            case "fore":
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_partita.php?mode=consulta&partita="+val['ID']+"&c=<?php echo $c; ?>&a="+val['Anno_Riferimento'];
                break;

            default: alert("Ricerca non trovata!"); break;
        }
    }
</script>



<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>
$(document).ready(function(){
    if('<?=$partita["Flag_Blocco_Coazione"]?>'=="si")
    {
        $('#flag_blocco').prop('checked',true);
        $('#motivo_blocco').val('<?=$partita["Motivo_Blocco"]?>');
    }
});

	/*$('#cerca_id').ajaxForm(

	        function(value) {
	            var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='NO')
		{
			alert('Codice partita non trovato!');
            annulla();
		}
		else
		{
			top.location.href = "gestione_partita.php?partita="+array_ritorno[0]+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
		}
	});

$('#form_codice_tributo').ajaxForm(

        function(value) {

            var array_ritorno = value.split(' ');
	if(array_ritorno[0]=='OK')
	{
		alert('Salvataggio effettuato correttamente!');
		top.location.href = "gestione_partita.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a="+array_ritorno[2];
	}
	else if(array_ritorno[0]=='DELETED')
	{
		alert('Cancellazione Codici Tributo completata!');
		top.location.href = "gestione_partita.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else if(array_ritorno[0]=='ERROR_DELETED')
	{
		alert('Errore nella cancellazione dei codici tributo! '+value);
		top.location.href = "gestione_partita.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
	{
		alert('Errore nel salvataggio del Codice Tributo: '+value);
		//top.location.href = "gestione_partita.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
});

$("#submit_click").click( salva_form );

$("#delete_click").click( cancella_form );

});*/

function elimina_tributo (value_ID)
{
	if( numero_atti > 0 && (operatore!="mirkop"))
	{
		alert("Sono gia' stati elaborati atti successivi. Impossibile effettuare modifiche ai codici tributo.");
		return false;
	}

	if( numero_tributi > 1 || numero_atti == 0 || (operatore=="emanuela" || operatore=="andrea" || operatore=="mirkop"))
	{

	if(data_notifica_ing=="" || operatore=="mirkop")
	{
	ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
	if(ritorno)
	{
			$.post("ajax/ajax_partita.php?c=<?php echo $c; ?>",

	    	   	{ 'ajax': 'elimina_tributo' ,
	   			  'ID_tributo': value_ID 	,
	   			  'ID_partita': '<?php echo $partita_ID; ?>'	},

   			function (value) {
				if (value == "OK")
				{
					alert("Tributo eliminato correttamente.");
                    $('#elimina_f').val('t');
                    location.href = "gestione_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>";

				}
				else if (value == "ERROR")
				{
					alert("Eliminazione fallita");
					location.href = "gestione_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&partita=<?php echo $partita_ID; ?>";
				}
				else
				{
					alert(value);
				}
			}
		);

	}
	else
		{return	false;}
	}
	else
		alert("Nell'Ingiunzione e' presente la data di notifica del "+data_notifica_ing+". Impossibile effettuare modifiche ai codici tributo.");
	}
	else if( numero_atti == 1 )
	{
		alert("Non e' possibile eliminare tutti i codici tributo. Eliminare prima l'Ingiunzione.")
	}

}

function cambio_info()
{
	if(numero_atti>0)
	{
		$('#info_cartella').val("<?php echo $info_cart; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}

	info_cart = $('#info_cartella').val();
    $('#info_cartella_f').val('t');
    if(info_cart.length>=1000)
	{
		alert("La lunghezza del testo non puo' essere superiore ai 1000 caratteri!");
		$('#info_cartella').val(info_cart.substring(0, 1000));
	}
}

function cambia_data_interessi()
{
	if(numero_atti>0)
	{
		$('#data_interessi').val("<?php echo $data_interessi; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#data_interessi_f').val('t');
}

function cambia_titolo_sanzione()
{
	if(numero_atti>0)
	{
		$('#titolo_sanz').val("<?php echo $titolo_sanz; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#titolo_sanz_f').val('t');
}

function cambia_data_sanzione()
{
	if(numero_atti>0)
	{
		$('#data_sanz').val("<?php echo $data_sanz; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#data_sanz_f').val('t');
}

function cambia_targa()
{
	if(numero_atti>0)
	{
		$('#targa_sanz').val("<?php echo $targa_sanz; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#targa_sanz_f').val('t');
}

function cambia_titolo_entrata()
{
	if(numero_atti>0)
	{
		$('#titolo_ent').val("<?php echo $titolo_ent; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#titolo_ent_f').val('t');
}

function cambia_descrizione()
{
	if(numero_atti>0)
	{
		$('#desc_ent').val("<?php echo $descrizione_ent; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#desc_ent_f').val('t');
}

function cambia_matricola()
{
	if(numero_atti>0)
	{
		$('#matri').val("<?php echo $matri; ?>");
		alert("Ingiunzione esistente! Impossibile effettuare modifiche alla partita.");
		return false;
	}
    $('#matri_f').val('t');
}

function campo_successivo()
{
	$('#tipo_partita').focus();
}
// Funzioni che tengono traccia di cambiamenti in campi già non controllati
function cambio_ruolo()
{
    $('#ruolo_desc_f').val('t');
}

function cambio_utente()
{
    $('#utente_nome_f').val('t');
}

function cambio_cod_tributo()
{
    $('#cod_tributo_f').val('t');
}

function cambio_anno_tributo()
{
    $('#annotributo_f').val('t');
}

function cambio_select_atto()
{
    $('#select_atto_f').val('t');
}

function cambio_importo()
{
    $('#importo_f').val('t');
}

</script>

	<?php
	//print_r($partita);
        $ultimoAtto = 0;
		$submenuPageNo = 1;
	 include_once(INC."/submenu_partita.php");
    include_once(INC . "/pages_authorization.php");
    ?>


<form id=form_codice_tributo name=form_codice_tributo class="form-horizontal validate" action="gestione_partita_salva.php" method=post enctype="multipart/form-data">
<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
<input type=hidden id=ruolo name=ruolo value="<?php echo $partita["Ruolo_ID"]; ?>" >
<input type=hidden id=utente name=utente value="<?php echo $utente_ID; ?>" >
<input type=hidden id=partita name=partita value="<?php echo $partita_ID; ?>" >
<input type=hidden id=NumAtti name=NumAtti value="<?php echo isset($partita["Atto"])?count($partita["Atto"]):0; ?>" >
<input type=hidden id=File_1 name=File_1 value="<?php echo $partita["File_1"]; ?>" >
<input type=hidden id=File_2 name=File_2 value="<?php echo $partita["File_2"]; ?>" >
<input type=hidden id=ID_PT name=ID_PT value="<?php echo	$partita["ID"]; ?>" >
<input type=hidden name=ultimoAtto id=ultimoAtto value="0" >
<input type=hidden name=nomePagina id=nomePagina value="codice_tributo" >
<input name=invia_submit 	id=invia_submit	type=hidden	value="" >

    
<div style="margin-left: 75px;margin-right: 75px;">
    <!--<div class="row" style="margin-top: 2%;">
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-7 control-label resize" style="text-align: left;">Data Importazione Ruolo</label>
                <div class="col-lg-5">
                    <span id=numero_flusso class="font_bold resize"><?php echo $cls_date->Get_DateNewFormat(isset($partita["Data_Fornitura"])?$partita["Data_Fornitura"]:null,"DB"); ?></span>
                </div>
            </div>
        </div>
    </div>-->
    <div class="row" style="margin-top: 1rem;margin-bottom: 1rem;">
        <div class="col col-lg-12 text-center">
            <b style="color: red;"><?= $docTypePartita; ?></b>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-4 ">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo entrata *</label>
                <div class="col-lg-8">
                    <select id=tipo_partita name=tipo_partita class="form-control resize vld_req" onchange="cambio_tipo();">
                        <option></option>
                        <option value="CDS">CDS/AMMINISTRATIVA</option>
                        <option>IMMOBILI</option>
                        <option>IRPEF</option>
                        <option>OSAP</option>
                        <option>PATRIMONIALE</option>
                        <option value="PUBBLICITA">PUBBLICITA'</option>
                        <option>RIFIUTI</option>
                    </select>
                </div>
                <input type="hidden" id="tipo_partita_f" name="tipo_partita_f" value="f">
            </div>
        </div>
        <div class="col col-lg-3">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;"></label>
                <div class="col-lg-8">
                    <select id=sottotipo_partita name=sottotipo_partita class="form-control resize">
                        <?php echo $sottotipi[$partita["Tipo"]]; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group provincia_rec_dati_sogg">

                <label class="col-lg-4 control-label resize " style="text-align: left;">
                    <div class="col-lg-12">
                        <a onMouseover="title='Gestione ruolo'" href="#" onClick="gestione_ruolo();" style="text-decoration: none">
                            <img src="<?= IMMAGINIWEB; ?>/gestione.png" width=15 height=15 border=0>
                        </a>

                        <?php
                        if($partita['Import_Id']>0){
                            ?>
                            <a onMouseover="title='Gestione importazione'" href="#" onClick="gestione_importazione();" style="text-decoration: none">
                                <img src="<?= IMMAGINIWEB; ?>/file-import.png" width=17 height=17 border=0>
                            </a>
                            <?php
                        }
                        ?>


                        Ruolo *
                    </div>
                </label>
                <div class="col-lg-8">
                    <input type=text onchange="cambio_ruolo();" ondblclick="/*RicercheDaId('ruolo',1);*/openOfcanvas('role',1);" class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" id=ruolo_desc name=ruolo_desc value="<?php echo $ruolo["Descrizione"]; ?>" readonly />
                </div>
                <input type="hidden" id="ruolo_desc_f" name="ruolo_desc_f" value="f">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-7 ">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Intestatario *</label>
                <div class="col-lg-10">
                    <input class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text id=utente_nome name=utente_nome value="<?php echo $utente_e_codice; ?>" onchange="cambio_utente();" ondblclick="/*RicercheDaId('intestatario',1);*/openOfcanvas('owner',1);" readonly />
                    <input type="hidden" id="utente_nome_f" name="utente_nome_f" value="f">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: center;">Anno rif. *</label>
                <div class="col-lg-8">
                    <input type=text onchange="cambio_anno_rif();" class="form-control resize vld_yReq corrige_numero" style="width: 40%;" id=anno_rif name=anno_rif value="<?php echo $anno_riferimento; ?>" size=6>
                    <input type="hidden" id="anno_rif_f" name="anno_rif_f" value="f">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-12 ">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Rif. accertamento / Informazioni cartella *</label>
                <div class="col-lg-10">
                    <textarea class="form-control resize vld_req" rows=2% id=info_cartella style="max-width: 100%;" name=info_cartella onchange="cambio_info();"><?php echo $info_cart; ?></textarea>
                    <input type="hidden" id="info_cartella_f" name="info_cartella_f" value="f">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-8 ">
            <div class="form-group">
                <label class="col-lg-3 control-label resize" style="text-align: left;">Data decor. interessi *</label>
                <div class="col-lg-2">
                    <input id=data_interessi onchange="cambia_data_interessi();" class="form-control resize vld_dateReq text_center" name=data_interessi type=text value='<?php echo $data_interessi; ?>' size=9 >
                    <input type="hidden" id="data_interessi_f" name="data_interessi_f" value="f">
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo *</label>
                <div class="col-lg-8">
                    <select id=select_info name=select_info onchange="cambia_tipo();" class="form-control resize vld_req" >
                        <option></option>
                        <optgroup label="Seleziona un'opzione">
                            <option id=s value=S>Sanzione Amministrativa</option>
                            <option id=e value=E>Entrata Patrimoniale / Tributaria</option>
                            <option id=m value=M>Matricola</option>
                        </optgroup>
                        <!-- <input type="hidden" id="select_info_f" name="select_info_f" value="f"> non necessario perchè tipo sanzione può essere modificata solo all'inserimento -->
                        <input type="hidden" id="select_info_count" name="select_info_count" value=0>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row sanzione" style="display: none;">
        <div class="col col-lg-4 ">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Rif. atto</label>
                <div class="col-lg-8">
                    <input id=titolo_sanz class="form-control resize vld_req" onchange="cambia_titolo_sanzione();" name=titolo_sanz type=text value='<?php echo $titolo_sanz; ?>' size=5>
                    <input type="hidden" id="titolo_sanz_f" name="titolo_sanz_f" value="f">
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <div class="form-group">
                <label class="col-lg-5 control-label resize" style="text-align: left;">Data Sanzione</label>
                <div class="col-lg-4">
                    <input id=data_sanz class="form-control resize vld_dateReq text_center" onchange="cambia_data_sanzione();" name=data_sanz type=text value='<?php echo $data_sanz; ?>' size=9>
                    <input type="hidden" id="data_sanz_f" name="data_sanz_f" value="f">
                </div>
            </div>
        </div>
        <div class="col col-lg-4 ">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Targa</label>
                <div class="col-lg-8">
                    <input id=targa_sanz onchange="cambia_targa();" class="form-control resize" name=targa_sanz type=text value='<?php echo $targa_sanz; ?>' size=7>
                </div>
                <input type="hidden" id="targa_sanz_f" name="targa_sanz_f" value="f">
            </div>
        </div>
    </div>

    <div class="row entrata" style="display: none;">
        <div class="col col-lg-4 ">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Rif. atto *</label>
                <div class="col-lg-8">
                    <input id=titolo_ent class="form-control resize vld_req" onchange="cambia_titolo_entrata();" name=titolo_ent type=text value='<?php echo $titolo_ent; ?>' size=5>
                </div>
                <input type="hidden" id="titolo_ent_f" name="titolo_ent_f" value="f">
            </div>
        </div>
        <div class="col col-lg-8">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Descrizione</label>
                <div class="col-lg-8">
                    <input id=desc_ent class="form-control resize" onchange="cambia_descrizione();" name=desc_ent type=text value='<?php echo $descrizione_ent; ?>' size=20>
                </div>
                <input type="hidden" id="desc_ent_f" name="desc_ent_f" value="f">
            </div>
        </div>
    </div>

    <div class="row matricola" style="display: none;">
        <div class="col col-lg-4 ">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Matricola</label>
                <div class="col-lg-8">
                    <input id=matri class="form-control resize" onchange="cambia_matricola();" name=matri type=text value='<?php echo $matri; ?>' size=5>
                </div>
                <input type="hidden" id="matri_f" name="matri_f" value="f">
            </div>
        </div>
    </div>

    <div class="row sanzione" style="margin-bottom: 0; display: none;">
        <div class="col col-lg-5 ">
            <div class="form-group">
                <div class="col-lg-2 resize" style="text-align: left;"><?php echo $check_file; ?></div>
                <div class="col-lg-10">
                    <a onMouseover="title='Scarica il file'" href="<?php echo $path_file_1; ?>" target="_blank" style="text-decoration: none;">
                        <span class="font14 color_titolo"><?php echo $partita["File_1"]; ?></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <div class="col-lg-2 resize" style="text-align: left;"><?php echo $check_file_2; ?></div>
                <div class="col-lg-10">
                    <a onMouseover="title='Scarica il file'" href="<?php echo $path_file_2; ?>" target="_blank" style="text-decoration: none;">
                        <span class="font14 color_titolo"><?php echo $partita["File_2"]; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-bottom: 0;">
        <div class="col col-lg-5 ">
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="form-control resize" type="file" name="img_1" style="width: 100%; background-color: rgb(153, 204, 255);">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="form-control resize" type="file" name="img_2" style="width: 100%; background-color: rgb(153, 204, 255);">
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-4 resize">
                    <a onMouseover="title='Nuovo Codice Tributo'" href="#" onClick="mostra_nuovo();" style="text-decoration: none">
                        <img src="<?= IMMAGINIWEB; ?>/Plus.png" width=25 height=25 border=0>
                    </a>
                </div>
                <input type="hidden" id="nuovo_tributo_f" name="nuovo_tributo_f" value=0>
                <div class="col-lg-4 resize">
                    <a onMouseover="title='Lista Codici Tributo'" href="#" onClick="/*RicercheDaId('lista','0');*/openOfcanvas('list',0)" style="text-decoration: none">
                        <img src="<?= IMMAGINIWEB; ?>/lista.png" width=25 height=25 border=0 >
                    </a>
                </div>
                <div class="col-lg-4 resize">
                    <a onMouseover="title='Elabora Nuova Ingiunzione'" href="#" onClick="elabora_nuovo();" style="text-decoration: none">
                        <img src="<?= IMMAGINIWEB; ?>/elabora.png" width=30 height=30 border=0 >
                    </a>
                </div>
                <!-- <input type="hidden" id="elab_f" name="elab_ing_f" value="f">  non necessario fare log perchè manda solo a pagina inserimento ingiunzione -->
            </div>
        </div>
    </div>
</div>


<?php

if(!$tributo && $utente_ID != 0)
	echo "<div style='margin-left: 75px;margin-right: 75px;'><br><span class='color_red'>IMPORTANTE!!!<br><br>INSERIRE PER PRIMO IL CODICE TRIBUTO DELL'IMPORTO PRINCIPALE<br><br></span></div>";

//var_dump($tributo);

if($tributo){


    for($y=0;$y<count($tributo);$y++)
    {
        if(empty($tipo_info_gen))
            $tipo_info_gen = "E";
        //TODO aggiornare db con tipo tributo dove manca

        if($tipo_info_gen == "S")
	    {
?>

<div style="margin-left: 75px;margin-right: 75px;">
    <div class="col col-lg-12">
        <p class="titolo font16 under_decor" id=tipo_trib_<?php echo $y; ?>><?php echo $tipo_tributo[$y]; ?></p>
    </div>
    <div class="col col-lg-3">
        <div class="form-group">
            <label class="col-lg-7 control-label resize" style="text-align: left;">Codice Tributo *</label>
            <div class="col-lg-5">
                <input onchange="cambio_cod_tributo();" id=cod_tributo_<?php echo $y; ?> class="form-control resize validateCustom vld_Custom_r" style="text-align:right;background-color: rgb(153, 204, 255); border: 2px solid black; width:60px;" readonly name=cod_tributo[<?php echo $y; ?>] type=text value='<?php echo $codice_tributo[$y]; ?>' size=4 ondblclick="/*RicercheDaId('codice','<?php echo $y; ?>');*/openOfcanvas('code','<?php echo $y; ?>')" >
            </div>
        </div>
        <input type="hidden" id="cod_tributo_f" name="cod_tributo_f" value="f">
    </div>
    <div class="col col-lg-2">
        <div class="form-group">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno *</label>
            <div class="col-lg-6">
                <input onchange="cambio_anno_tributo();" id=anno_tributo_<?php echo $y; ?> class="form-control" style="text-align:right;border: 2px solid black; width:60px;" name=anno_tributo[<?php echo $y; ?>] type=text value='<?php echo $anno_tributo[$y]; ?>' size=4 ondblclick="/*RicercheDaId('codice','<?php echo $y; ?>');*/" >       <!-- Ricerca ELIMINATA -->
            </div>
        </div>
        <input type="hidden" id="anno_tributo_f" name="anno_tributo_f" value="f">
    </div>

    <div class="col col-lg-4">
        <div class="form-group">
            <label class="col-lg-4 control-label resize" style="text-align: center;">Atto</label>
            <div class="col-lg-8">
                <select id="select_atto_<?php echo ($y+1); ?>" name="select_atto[]"  onchange="/*cambia_atto();*/cambio_select_atto();" class="pwidth150 form-control resize">
                    <option value=VE>Verbale</option>
                    <option value=AC>Accertamento</option>
                    <option value=OR>Ordinanza</option>
                    <option value=IN>Ingiunzione</option>
                    <option value=DM>Decreto Ministeriale</option>
                </select>
            </div>
            <input type="hidden" id="select_atto_f" name="select_atto_f" value="f">
        </div>
    </div>
    <div class="col col-lg-2">
        <div class="form-group">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Importo *</label>
            <div class="col-lg-6">
                <input onchange="cambio_importo();" id="importo_<?php echo $y; ?>" class="form-control resize validateCustom vld_Custom_d vld_Custom_r corrige_numero" style="width: 90px;text-align:right;" name=importo[<?php echo $y; ?>] type=text value='<?php echo $imposta[$y]; ?>' size=6>
            </div>
            <input type="hidden" id="importo_f" name="importo_f" value="f">
        </div>
    </div>
    <div class="col col-lg-1">
        <div class="form-group">
            <div class="col-lg-12">
                <input type=button class="form-control resize btn-danger" name=elimina value="Elimina" onclick="elimina_tributo('<?php echo $id_tributo[$y]; ?>');">
            </div>
            <input type="hidden" id="elimina_f" name="elimina_f" value="f">
        </div>
    </div>
</div>

<input type=hidden id=progr_tributo_<?php echo $y; ?> name=progr_tributo[<?php echo $y; ?>] value=<?php echo $id_tributo[$y]; ?>>

<script>$('#select_atto_<?php echo $y; ?>').val("<?php echo $tipo_sanzione[$y]; ?>")</script>
<?php

	}
	else if($tipo_info_gen == "E" || $tipo_info_gen == "M")
	{?>

        <div style="margin-left: 75px;margin-right: 75px;">
            <div class="col col-lg-12">
                <p class="titolo font16 under_decor" id=tipo_trib_<?php echo $y; ?>><?php echo $tipo_tributo[$y]; ?></p>
            </div>
            <div class="col col-lg-3">
                <div class="form-group">
                    <label class="col-lg-7 control-label resize" style="text-align: left;">Codice Tributo *</label>
                    <div class="col-lg-5">
                        <input id=cod_tributo_<?php echo $y; ?> class="form-control resize validateCustom vld_Custom_r" style="text-align:right;background-color: rgb(153, 204, 255); border: 2px solid black; width:60px;" readonly name=cod_tributo[<?php echo $y; ?>] type=text value='<?php echo $codice_tributo[$y]; ?>' size=4 ondblclick="/*RicercheDaId('codice','<?php echo $y; ?>');*/openOfcanvas('code','<?php echo $y; ?>')" >
                    </div>
                </div>
            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize" style="text-align: left;">Anno *</label>
                    <div class="col-lg-6">
                        <input id=anno_tributo_<?php echo $y; ?> class="form-control" style="text-align:right;border: 2px solid black; width:60px;" name=anno_tributo[<?php echo $y; ?>] type=text value='<?php echo $anno_tributo[$y]; ?>' size=4 ondblclick="/*RicercheDaId('codice','<?php echo $y; ?>');*/" >       <!-- Ricerca ELIMINATA -->
                    </div>
                </div>
            </div>
            <div class="col col-lg-4">

            </div>
            <div class="col col-lg-2">
                <div class="form-group">
                    <label class="col-lg-6 control-label resize" style="text-align: left;">Importo *</label>
                    <div class="col-lg-6">
                        <input id="importo_<?php echo $y; ?>" class="form-control resize validateCustom vld_Custom_d vld_Custom_r corrige_numero" style="width: 90px;text-align:right;" name=importo[<?php echo $y; ?>] type=text value='<?php echo $imposta[$y]; ?>' size=6>
                    </div>
                </div>
            </div>
            <div class="col col-lg-1">
                <div class="form-group">
                    <div class="col-lg-12">
                        <input type=button class="form-control resize btn-danger" name=elimina value="Elimina" onclick="elimina_tributo('<?php echo $id_tributo[$y]; ?>');">
                    </div>
                </div>
            </div>
        </div>


<input type=hidden id=progr_tributo_<?php echo $y; ?> name=progr_tributo[<?php echo $y; ?>] value=<?php echo $id_tributo[$y]; ?>>
<input type=hidden name=select_atto[<?php echo $y; ?>] value="">


<?php }
}
}
echo $layout;
?>

<div id=scrivi_nuovo style="margin-left: 75px; margin-right: 75px"></div>

    <div class="row"></div>
    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>
    <div class="row">
        <div class="col-lg-offset-1 col-lg-10" style="text-align: center;color:red;font-weight: bold;">
            <p>Attenzione, per attivare il blocco coazione andare sulla pagina dell'ingiunzione!</p>
        </div>
    </div>

    <div class="row" style="margin-top: 2%;">
        <div class=" col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="control-label resize">
                    <input disabled type="checkbox" name=flag_blocco id=flag_blocco value="si">
                    <b>ARCHIVIAZIONE/Blocco coazione</b><?php echo $data_att_blocco; ?>
                </label>
            </div>
        </div>
        <div class=" col-lg-5">
            <div class="form-group">
                <label class="col-lg-4 control-label resize" style="text-align: left;">Motivi archiviazione</label>
                <div class="col-lg-8">
                    <select disabled id=motivo_blocco name=motivo_blocco class="form-control resize" onchange="cambia_title('motivo_blocco');">
                        <option value=""></option>
                        <?php echo $options_blocco; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Motivazione</label>
                <div class="col-lg-10">
                    <input readonly class="form-control resize" name="note_blocco" id="note_blocco" value="<?php echo isset($partita["Note_Blocco"])?$partita["Note_Blocco"]:""; ?>" >
                </div>
            </div>
        </div>
    </div>
    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;margin-top: 1%;"></div>

        <div class="row" style="margin-top: 1%;">
            <div class="col col-lg-10 col-lg-offset-1">
                <div class="form-group">
                    <label class="col-lg-2 control-label resize" style="text-align: left;">Note interne</label>
                    <div class="col-lg-10">
                        <textarea style="max-width: 100%;" class="form-control resize" name="note_interne" id="note_interne" ><?php echo $note_interne; ?></textarea>
                    </div>
                </div>
            </div>
        </div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<!-- Inclusione modale per ricerca ruolo-->
<?php include_once(ROOT . "/search_modal/offcanvas/role_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca intestatario -->
<?php include_once(ROOT . "/search_modal/offcanvas/owner_offcanvas.php"); ?>
<!-- Inclusione modale per lista codici tributo -->
<?php include_once(ROOT . "/search_modal/offcanvas/list_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca codice tributo -->
<?php include_once(ROOT . "/search_modal/offcanvas/code_offcanvas.php"); ?>
<!-- Inclusione modale per ricerca utente-partita -->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>

<script>
    cambia_tipo();
</script>

<?php

echo $layout;
include(INC."/footer.php");

?>
