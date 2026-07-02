<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente/partita'" href="#" onClick="RicercheDaId('utente',0);" style="text-decoration: none;">
			<img src="/gitco2/immagini/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=15% class="text_center"><font class="titolo font18">PARTITA</font><font class="titolo font14"><br> Pag 1/7</font></td>
    	<td colspan=5 width=55% align=center>
            <em style="background-color:rgb(251,255,208);font-style : normal ;">
            <?php if($genere_utente!='D'){echo $cognome_utente." ".$nome_utente;}else{ echo $ditta; } ?>
            </em>
        	<td class="text_left"><input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" title="Gestione Ruolo" onclick="ruolo('<?php echo $utente_ID; ?>');">
        </td>
		<td width=22% class="text_right">
		<form id=cerca_id method=post action=modali/ricerca_partita.php>
			<input type=hidden name=old_cod_contr value='<?php echo $ID_Partita; ?>'>
           	<input name=c type=hidden value='<?php echo $c; ?>'>
            <input name=a type=hidden value='<?php echo $a; ?>'>

			Partita ID &nbsp;

			<input id=id_cerca tabindex=1 onblur="campo_successivo();" class="valign_center text_right" type=text name=ric_cod_contr value='<?php echo $ID_Partita; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;</form>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td class="width20"><font class="titoletto font16 under_decor">Codici tributo</font></td>
		<td class="width20"><a href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Ingiunzione</font></a></td>
		<td class="width20"><a href="pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Pagamenti</font></a></td>
		<td class="width20"><a href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Scorpori</font></a></td>
		<td class="width20"><a href="appeal_list.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Ricorsi</font></a></td>
		<td class="width20"><a href="coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" style="text-decoration: none;"><font class="titolo font15"><i>Coazione</i></font> <img alt="" src="/gitco2/immagini/forward.png" style="width:12px; height:12px; border:0;"></a></td>
	</tr>
</table>
