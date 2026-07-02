<style>
   
  .navbar-nav.navbar-right {
    position: absolute;
    left: 95%;
    transform: translatex(20%);
  
}
</style>

<script>
    $(document).ready(function(){

        $('.dropdown-submenu a').on("click", function(e){

            //alert("scd");
            /* This is to hide all dropdown-menu children if the parent(dropdown-submenu) in the element have been clicked */
            $(this).next('ul').find('.dropdown-menu').each(function(){
                $(this).hide();
            });

            /* This is to find another dropdown-menu have has been opened and hide its submenu */
            var xw = $(this);
            $(this).closest(".dropdown-menu").find('.dropdown-submenu a').not(xw).each(function(){
                if($(this).next("ul").is(":visible")){
                    $(this).next("ul").hide();
                }
            });

            //$(this).next('ul').toggle();
            e.stopPropagation();
            e.preventDefault();
        });
    });
</script>

<nav class="navbar custom-nav">
    <div class="container-fluid">
        <div class="navbar-content-holder">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a onclick="menuClick('10000');" class="navbar-brand" href="#">Gitco 2</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <div class="navbar-left custom-nav-links">
                    <ul class="nav navbar-nav">
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Gestione Ente <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#" onclick="menuClick('29');">Selezione Ente/Anno</a></li>
                                <?php if ($_SESSION['aut_tipo'] == 1) {
                                    ?>
                                    <li><a onclick="menuClick('13');" href="#">Creazione ente</a></li>
                                <?php }
                                ?>
                                <li><a onclick="menuClick('14');" href="#">Creazione anno</a></li>
                                <li><a onclick="menuClick('16');" href="#">Cancellazione anno</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="list-item" onclick="menuClick('1');" href="#">Anagrafe</a></li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Ruolo <span class="caret"></span></a>
                            <ul class="dropdown-menu multi-level">
                                <li><a onclick="menuClick('104');" href="#">Gestione ruoli</a></li>
                                <li role="separator" class="divider"></li>
                                <li class="dropdown-submenu">
                                    <a href="#" ondblclick="menuClick('7');" class=" submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Gestione partite &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-submenu">
                                            <a href="#" ondblclick="menuClick('9');" class="dropdown-toggle submenu" data-toggle="dropdown">Iter ingiuntivo &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('9');" href="#">Codice tributo</a></li>
                                                <li><a onclick="menuClick('10');" href="#">Ingiunzione</a></li>
                                                <li><a onclick="menuClick('11');" href="#">Pagamenti</a></li>
                                                <li><a onclick="menuClick('12');" href="#">Ricorsi</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" ondblclick="menuClick('50');" class="dropdown-toggle submenu" data-toggle="dropdown">Iter coattivo &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('50');" href="#">Pignoramento</a></li>
                                                <li><a onclick="menuClick('90');" href="#">Pagamenti pignoramento</a></li>
                                                <li><a onclick="menuClick('91');" href="#">Ricorsi pignoramento</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li><a onclick="menuClick('45');" href="#">Ispezioni</a></li>
                                <li><a onclick="menuClick('44');" href="#">Lista Codici Tributo</a></li>
                                <li><a onclick="menuClick('51');" href="#">Lista Tariffe Coazione</a></li>
                            </ul>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Importazione <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a onclick="menuClick('62');" href="#">Importazione Notifiche</a></li>
                                <li><a onclick="menuClick('70');" href="#">Importazioni Pagamenti</a></li>
                                <li><a onclick="menuClick('8');" href="#">Importazioni 290</a></li>
                                <li><a onclick="menuClick('7001');" href="#">Importazioni CAD</a></li>
                                <li><a onclick="menuClick('3006');" href="#">Reimportazione atti cancellati</a></li>
                                <li role="separator" class="divider"></li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Passaggio Verso Gitco2 &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('63');" href="#">Flussi</a></li>
                                        <li><a onclick="menuClick('73');" href="#">Pagamenti</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Elaborazioni <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a onclick="menuClick('3003');" href="#">Atti</a></li>
                                <li><a onclick="menuClick('3005');" href="#">Pignoramenti</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a onclick="menuClick('114');" href="#">iniPEC</a></li>
                                <li><a onclick="menuClick('3004');" href="#">Elaborazione procedure stragiudiziali Art. 75 bis D.P.R. 602/1973</a></li>
                                <!-- <li><a onclick="menuClick('3006');" href="#">Stragiudiziali ist. previdenziali</a></li>-->
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Bonifiche &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('71');" href="#">Pagamenti Non telematici</a></li>
                                        <li><a onclick="menuClick('72');" href="#">Pagamenti telematici</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">TRAFAT &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-submenu">
                                            <li><a onclick="menuClick('112');" href="#">Crea File</a></li>
                                            <li><a onclick="menuClick('113');" href="#">Leggi File</a></li>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Visura ACI &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-submenu">
                                        <li><a onclick="menuClick('88');" href="#">Visura massiva</a></li>
                                        <li><a onclick="menuClick('92');" href="#">Elenco veicoli</a></li>
                                        </li>
                                    </ul>
                                </li>
                                <li><a onclick="menuClick('200');" href="#">Discarichi</a></li>
                                <!-- <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Discarichi &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-submenu">
                                            <li><a onclick="menuClick('200');" href="#">Elaborazione massiva</a></li>
<!--                                            <li><a onclick="menuClick('256');" href="#">Report discarico automatico</a></li>
<!--                                            <li><a onclick="menuClick('255');" href="#">Visualizza file discarichi/annullamenti</a></li>
                                        </li>
                                    </ul>
                                </li> -->
                                <!-- LEGACY MODULE: voce "Discarico per legge" nascosta 2026-05-10
                                     dopo rename UI Art.19 (commit 1d10c9a) per evitare confusione con la voce
                                     "Discarichi / Annullamenti" del nuovo modulo sgravi/Art.19.
                                     File PHP collegati ancora presenti (elab_discharge.php, elab_extraction.php,
                                     elenco_discarico.php) e raggiungibili via URL diretto se necessario.
                                     Dismissione completa = task futuro separato.
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Discarico per legge &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-submenu">
                                            <li><a onclick="menuClick('3011');" href="#">Elaborazione</a></li>
                                            <li><a onclick="menuClick('3012');" href="#">Estrazione</a></li>
                                            <li><a onclick="menuClick('4009');" href="#">Elenco discarico</a></li>
                                        </li>
                                    </ul>
                                </li>
                                -->

                            </ul>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Controlli di gestione <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <!-- GV - 31/05/2022 START -->
                                <li><a onclick="menuClick('301');" href="#">Anagrafica</a></li>
                                <!-- GV - 31/05/2022   END -->
                                <!-- GV - 08/07/2022 START -->
                                <li><a onclick="menuClick('302');" href="#">Lista Elaborazioni</a></li>
                                <li><a onclick="menuClick('303');" href="#">Lista Stragiudiziali</a></li>
                                <!-- GV - 08/07/2022   END -->
                                <li><a onclick="menuClick('300');" href="#">Ricevute PEC</a></li>

                                <li><a onclick="menuClick('6003');" href="#">Gestione Procedure</a></li>
                                <li><a onclick="menuClick('6001');" href="#">Gestione Flussi</a></li>
                                <li><a onclick="menuClick('6004');" href="#">Aggiornamento date Flussi</a></li>
                            </ul>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Stampe <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Elenco &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        
                                        
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Atti &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('4001');" href="#">Sollecito pre ingiunzione</a></li>
                                                <li><a onclick="menuClick('4002');" href="#">Avviso di messa in mora</a></li>
                                                <li><a onclick="menuClick('4006');" href="#">Ingiunzione</a></li>
                                                <li><a onclick="menuClick('26');" href="#">Distinta ingiunzione</a></li>
                                                <li><a onclick="menuClick('4010');" href="#">Dettaglio ingiunzione</a></li>
                                                <li><a onclick="menuClick('4011');" href="#">Sollecito post ingiunzione</a></li>
                                                <li><a onclick="menuClick('4007');" href="#">Avviso di intimazione</a></li>
                                            </ul>
                                        </li>
                                        <li><a onclick="menuClick('82');" href="#">Pignoramenti</a></li>
                                        <li><a onclick="menuClick('4022');" href="#">Ultimi atti</a></li>
                                        <li role="separator" class="divider"></li>
                                        <li><a onclick="menuClick('4005');" href="#">Esiti notifiche</a></li>
                                        <li><a onclick="menuClick('4012');" href="#">Cartoline notifiche</a></li>
                                        <li><a onclick="menuClick('80');" href="#">Pagamenti</a></li>
                                        
                                        <li><a onclick="menuClick('4004');" href="#">Posizioni</a></li>
                                        <li><a onclick="menuClick('4003');" href="#">Udienze</a></li>
                                        <li><a onclick="menuClick('4014');" href="#">Tariffe</a></li>
                                        <li><a onclick="menuClick('4015');" href="#">Coefficiente</a></li>
                                        <li><a onclick="menuClick('4008');" href="#">Stampe guidate</a></li>
                                        <li><a onclick="menuClick('4025');" href="#">Elenco Excel</a></li>
                                        <li><a onclick="menuClick('4026');" href="#">Esportazione posizioni</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Stampe &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('1301');" href="#">Richiesta codici INPS</a></li>
                                        <li><a onclick="menuClick('1302');" href="#">Richiesta validazione notifica</a></li>
                                        <li><a onclick="menuClick('204');" href="#">Annullamenti</a></li>
                                        <li><a onclick="menuClick('1501');" href="#">Discarichi Art. 19</a></li>
                                        <li><a onclick="menuClick('4013');" href="#">Rimborso spese Art.17</a></li>
                                        <li><a onclick="menuClick('4016');" href="#">Excel spese Art.17</a></li>
                                        <li><a onclick="menuClick('4018');" href="#">Rendiconto della gestione</a></li>
                                        <li><a onclick="menuClick('4024');" href="#">Minuta di ruolo</a></li>
                                        <li><a onclick="menuClick('4019');" href="#">Agente contabile</a></li>
                                        <li><a onclick="menuClick('4017');" href="#">Conto Giudiziale</a></li>
                                        <li><a onclick="menuClick('4020');" href="#">Resoconto INIPEC</a></li>
                                        <li><a onclick="menuClick('4021');" href="#">Resoconto Visure ACI</a></li>
                                        <li><a onclick="menuClick('4023');" href="#">Storico Azioni</a></li>
                                        <!--<li><a href="#" onclick="menuClick('1101');" data-toggle="dropdown">Atti</a></li>
                                        
                                        <li role="separator" class="divider"></li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Atti &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('1101');" href="#">Ingiunzione</a></li>
                                                <li><a onclick="menuClick('1102');" href="#">Avviso di intimazione</a></li>
                                                <li><a onclick="menuClick('1103');" href="#">Avviso di messa in mora</a></li>
                                                <li><a onclick="menuClick('1104');" href="#">Sollecito post ingiunzione</a></li>
                                                <li><a onclick="menuClick('1105');" href="#">Sollecito pre ingiunzione</a></li>
                                            </ul>
                                        </li>-->
                                        <!--<li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Pignoramento &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('1201');" href="#">Pignoramento veicolo</a></li>
                                                <li><a onclick="menuClick('1202');" href="#">Presso datore di lavoro</a></li>
                                                <li><a onclick="menuClick('1203');" href="#">Presso banca</a></li>
                                                <li><a onclick="menuClick('1205');" href="#">Preavviso fermo</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Altri documenti &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('1301');" href="#">Richiesta codici INPS</a></li>
                                                <li><a onclick="menuClick('1302');" href="#">Richiesta validazione notifica</a></li>
                                                <li><a onclick="menuClick('204');" href="#">Discarichi / annullamenti</a></li>
                                                <li><a onclick="menuClick('4013');" href="#">Rimborso spese Art.17</a></li>
                                                <li><a onclick="menuClick('4016');" href="#">Excel spese Art.17</a></li>
                                            </ul>
                                        </li> -->
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Testi <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Html &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('2000');" href="#">Testi</a></li>
                                        <li><a onclick="menuClick('2003');" href="#">Sottotesti</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Atti &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('2001');" href="#">Sollecito pre ingiunzione</a></li>
                                        <li><a onclick="menuClick('2002');" href="#">Avviso di messa in mora</a></li>
                                        <li><a onclick="menuClick('28');" href="#">Ingiunzione</a></li>
                                        <li><a onclick="menuClick('43');" href="#">Sollecito di pagamento</a></li>
                                        <li><a onclick="menuClick('501');" href="#">Avviso di intimazione</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Pignoramenti &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('54');" href="#">Presso datore di lavoro</a></li>
                                        <li><a onclick="menuClick('74');" href="#">Presso banca</a></li>
                                        <li><a onclick="menuClick('83');" href="#">Beni mobili registrati</a></li>
                                        <li><a onclick="menuClick('108');" href="#">Sollecito beni mobili registrati</a></li>
                                        <li><a onclick="menuClick('86');" href="#">Preavviso fermo amministrativo</a></li>
                                        <li><a onclick="menuClick('87');" href="#">Fermo amministrativo</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Altri documenti &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('111');" href="#">Archiviazione atto</a></li>
                                        <li><a onclick="menuClick('57');" href="#">Richiesta rateizzazione</a></li>
                                        <li><a onclick="menuClick('58');" href="#">Esito rateizzazione</a></li>
                                        <li><a onclick="menuClick('61');" href="#">Richiesta matricole</a></li>
                                        <li><a onclick="menuClick('66');" href="#">Richiesta indirizzo</a></li>
                                        <li><a onclick="menuClick('67');" href="#">Richiesta certificato di decesso</a></li>
                                        <li><a onclick="menuClick('68');" href="#">Richiesta duplicato AR</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Parametri <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Ente &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('55');" href="#">Stemma</a></li>
                                        <li><a onclick="menuClick('17');" href="#">Dati ente</a></li>
                                        <li><a onclick="menuClick('18');" href="#">Gestore</a></li>
                                        <li><a onclick="menuClick('30');" href="#">Ufficio</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Autorità &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('35');" href="#">Tribunale</a></li>
                                        <li><a onclick="menuClick('36');" href="#">Giudice di pace</a></li>
                                        <li><a onclick="menuClick('37');" href="#">Corte d'appello</a></li>
                                        <li><a onclick="menuClick('38');" href="#">Corte di giustizia tributaria di I grado</a></li>
                                        <li><a onclick="menuClick('39');" href="#">Commissione tributaria regionale</a></li>
                                        <li><a onclick="menuClick('40');" href="#">Corte di cassazione</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Enti esterni &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('47');" href="#">Sedi banche</a></li>
                                        <li><a onclick="menuClick('48');" href="#">Filiali banche</a></li>
                                        <li><a onclick="menuClick('60');" href="#">Istituti previdenziali</a></li>
                                        <li><a onclick="menuClick('85');" href="#">Tribunali/Ist. vendite giudiziarie</a></li>
                                        <li><a onclick="menuClick('64');" href="#">Uffici anagrafici</a></li>
                                        <li><a onclick="menuClick('5001');" href="#">Uffici giudiziari</a></li>
                                        <li><a onclick="menuClick('65');" href="#">Uffici postali</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Generali &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('20');" href="#">Parametri annuali</a></li>
                                        <li><a onclick="menuClick('106');" href="#">Interessi tributi</a></li>
                                        <li><a onclick="menuClick('110');" href="#">Periodo blocco</a></li>
                                        <li><a onclick="menuClick('56');" href="#">Scorporo pagamenti</a></li>
                                        <li><a onclick="menuClick('89');" href="#">Ricorsi</a></li>
                                        <li><a onclick="menuClick('211');" href="#">EMail/PEC</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Entrate &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">CDS / AMMINISTRATIVA &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('400');" href="#">Generali</a></li>
                                                <li><a onclick="menuClick('53');" href="#">Pagamento</a></li>
                                                <li><a onclick="menuClick('19');" href="#">Responsabili</a></li>
                                                <li><a onclick="menuClick('2100');" href="#">Ufficio</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">IMMOBILI &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('401');" href="#">Generali</a></li>
                                                <li><a onclick="menuClick('93');" href="#">Pagamento</a></li>
                                                <li><a onclick="menuClick('94');" href="#">Responsabili</a></li>
                                                <li><a onclick="menuClick('2101');" href="#">Ufficio</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">RIFIUTI &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('402');" href="#">Generali</a></li>
                                                <li><a onclick="menuClick('97');" href="#">Pagamento</a></li>
                                                <li><a onclick="menuClick('98');" href="#">Responsabili</a></li>
                                                <li><a onclick="menuClick('2102');" href="#">Ufficio</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">PATRIMONIALE &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('403');" href="#">Generali</a></li>
                                                <li><a onclick="menuClick('101');" href="#">Pagamento</a></li>
                                                <li><a onclick="menuClick('102');" href="#">Responsabili</a></li>
                                                <li><a onclick="menuClick('2103');" href="#">Ufficio</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">OSAP &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('404');" href="#">Generali</a></li>
                                                <li><a onclick="menuClick('201');" href="#">Pagamento</a></li>
                                                <li><a onclick="menuClick('202');" href="#">Responsabili</a></li>
                                                <li><a onclick="menuClick('2104');" href="#">Ufficio</a></li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">PUBBLICITA &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="menuClick('405');" href="#">Generali</a></li>
                                                <li><a onclick="menuClick('205');" href="#">Pagamento</a></li>
                                                <li><a onclick="menuClick('206');" href="#">Responsabili</a></li>
                                                <li><a onclick="menuClick('2105');" href="#">Ufficio</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="submenu dropdown-item dropdown-toggle" data-toggle="dropdown">Tariffe &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i></a>
                                    <ul class="dropdown-menu">
                                        <li><a onclick="menuClick('52');" href="#">Pignoramento</a></li>
                                        <li><a onclick="menuClick('59');" href="#">Coefficiente</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Comunicazioni Ente<span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a onclick="menuClick('6006');" href="#">Comunicazioni</a></li>
                                <li><a onclick="menuClick('6005');" href="#">Riversamenti</a></li>
                            </ul>
                        </li>
                        <ul class="nav navbar-nav navbar-right" >
                            <li class="dropdown nav-item">
                                <a href="#" class="dropdown-toggle list-item" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class = "fa fa-bars"></i><span class="caret"></span></a>
                                <ul class="dropdown-menu">                               
                                    <?php if ($_SESSION['aut_tipo'] == 1) {
                                        ?>
                                        <li><a onclick="menuClick('10001');" href="#">Crea Utente</a></li>
                                        <li><a onclick="menuClick('10002');" href="#">Reset Password</a></li>
                                        <li><a onclick="menuClick('10003');" href="#">Lista Notifiche</a></li>
                                        <li><a onclick="menuClick('10004');" href="#">Gestione emails</a></li>
                                    <?php }
                                        else if($_SESSION['aut_tipo'] == 3){
                                            ?>
                                            <li><a onclick="menuClick('10004');" href="#">Gestione emails</a></li>
                                        <?php
                                        }
                                    ?>                                
                                </ul>
                            </li>
                        </ul>
                    </ul>
                </div>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.navbar-content-holder -->
    </div><!-- /.container-fluid -->
</nav>

<!--<div class="mainmenu width100" style="margin-bottom: 0;padding-bottom: 0;">
    <ul class="text_left">
        <li class="li_hc"><a href="#" target="_self">Gestione Ente</a>
            <ul class="ul_ch">
                <li class="li_nc"><a onclick="menuClick('29');" href="#">Selezione Ente/Anno</a></li>
                <?php if ($_SESSION['aut_tipo'] == 1) {
                    ?>
                    <li class="li_nc"><a onclick="menuClick('13');" href="#">Creazione ente</a></li>
                    <li class="li_nc"><a onclick="menuClick('15');" href="#">Cancellazione ente</a></li>
                <?php }
                ?>
                <li class="li_nc"><a onclick="menuClick('14');" href="#">Creazione anno</a></li>
                <li class="li_nc"><a onclick="menuClick('16');" href="#">Cancellazione anno</a></li>
            </ul>
        </li>
        <li class="li_hc"><a onclick="menuClick('1');" href="#">Anagrafe</a></li>
        <li class="li_hc"><a onclick="menuClick('110');" href="#">Periodi blocco interesse</a></li>
        <li class="li_hc"><a href="#" target="_self">Ruolo</a>
            <ul class="ul_ch">
                <li class="li_nc"><a onclick="menuClick('104');" href="#">Gestione ruoli</a></li>
                <li><a href="#" onclick="menuClick('7');">Gestione partite</a>
                    <ul class="ul_ch">
                        <li><a href="#" onclick="menuClick('9');">Iter ingiuntivo</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('9');" href="#">Codice Tributo</a></li>
                                <li class="li_nc"><a onclick="menuClick('10');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('11');" href="#">Pagamenti</a></li>
                                <li class="li_nc"><a onclick="menuClick('12');" href="#">Ricorsi</a></li>
                            </ul>
                        </li>
                        <li><a href="#" onclick="menuClick('50');">Iter coattivo</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('50');" href="#">Pignoramento</a></li>
                                <li class="li_nc"><a onclick="menuClick('90');" href="#">Pagamenti pignoramento</a></li>
                                <li class="li_nc"><a onclick="menuClick('91');" href="#">Ricorsi pignoramento</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="menuClick('45');" href="#">Ispezioni</a></li>
                <li class="li_nc"><a onclick="menuClick('44');" href="#">Lista Codici Tributo</a></li>
                <li class="li_nc"><a onclick="menuClick('51');" href="#">Lista Tariffe Coazione</a></li>
                <li><a onclick="menuClick('8');" href="#">Importazioni 290</a></li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Importazioni</a>
            <ul class="ul_ch">
                <li class="li_nc"><a onclick="menuClick('62');" href="#">Importazione Notifiche</a></li>
                <li class="li_nc"><a onclick="" href="#">Passaggio Verso Gitco2</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('63');" href="#">Flussi</a></li>
                        <li class="li_nc"><a onclick="menuClick('73');" href="#">Pagamenti</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="menuClick('70');" href="#">Importazioni Pagamenti</a></li>
                <li><a onclick="menuClick('8');" href="#">Importazioni 290</a></li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Elaborazioni</a>
            <ul class="ul_ch">
                <li><a href="#" onclick="menuClick('3001');" target="_self">Atti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('3001');" href="#">Sollecito pre ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('3002');" href="#">Avviso di messa in mora</a></li>
                        <li class="li_nc"><a onclick="menuClick('22');" href="#">Ingiunzione / Avviso di intimazione</a></li>
                        <li class="li_nc"><a onclick="menuClick('32');" href="#">Sollecito di pagamento</a></li>
                        <li class="li_nc"><a onclick="menuClick('23');" href="#">Avviso di intimazione</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Pignoramenti</a>
                    <ul class="ul_ch">
                        <li><a href="#" target="_self">Sollecito di pagamento</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('107');" href="#">Beni mobili registrati</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Importazioni</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('62');" href="#">Importazione Notifiche</a></li>
                        <li class="li_nc"><a onclick="" href="#">Passaggio Verso Gitco2</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('63');" href="#">Flussi</a></li>
                                <li class="li_nc"><a onclick="menuClick('73');" href="#">Pagamenti</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('70');" href="#">Importazioni Pagamenti</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Bonifiche</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="" href="#">Bonifiche Pagamenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('71');" href="#">Non Telematici</a></li>
                                <li class="li_nc"><a onclick="menuClick('72');" href="#">Telematici</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="" href="#">TRAFAT</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('112');" href="#">Crea file</a></li>
                        <li class="li_nc"><a onclick="menuClick('113');" href="#">Leggi file</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="" href="#">Visura ACI</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('88');" href="#">Visura Massiva</a></li>
                        <li class="li_nc"><a onclick="menuClick('92');" href="#">Elenco veicoli</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Discarichi / Annullamenti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('200');" href="#">Crea discarico automatico</a></li>
                        <?php /* DEAD LINK: menuClick('256') non e' definito in menu_script.php; voce nascosta 2026-05-10
                        <li class="li_nc"><a onclick="menuClick('256');" href="#">Report discarico automatico</a></li>
                        */ ?>
                        <li class="li_nc"><a onclick="menuClick('204');" href="#">Stampe discarichi/annullamenti</a></li>
                        <?php /* DEAD LINK: menuClick('255') non e' definito in menu_script.php; voce nascosta 2026-05-10
                        <li class="li_nc"><a onclick="menuClick('255');" href="#">Visualizza file discarichi/annull</a></li>
                        */ ?>

                    </ul>
                </li>
                <?php if ($_SESSION['CC_User'] == '****' || $_SESSION['CC_User'] == '***+') {
                    ?>
                    <?php /* LEGACY MODULE: voce "Discarico" nascosta 2026-05-10 dopo rename UI Art.19
                         (commit 1d10c9a) per evitare confusione con "Discarichi / Annullamenti".
                         File PHP collegati (elab_discharge.php, elab_extraction.php) ancora presenti. */ ?>
                <?php }
                ?>

                <li class="li_nc"><a onclick="menuClick('81');" href="#">Visura</a></li>

            </ul>
        </li>

        <li class="li_hc"><a href="#" target="_self">Controlli di gestione</a>
            <ul class="ul_ch">
                <li class="li_nc"><a target="_self" href="#">Flussi</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('6001');" href="#">Gestione</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="menuClick('300');" href="#">Ricevute PEC</a></li>
            </ul>
        </li>

        <li class="li_hc"><a href="#" target="_self">Stampe</a>
            <ul class="ul_ch">
                <li class="li_nc"><a target="_self" href="#">Elenco</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a target="_self" href="#">Atti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('4001');" href="#">Sollecito pre ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4002');" href="#">Avviso di messa in mora</a></li>
                                <li class="li_nc"><a onclick="menuClick('4006');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('26');" href="#">Distinta Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4010');" href="#">Dettaglio ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4011');" href="#">Sollecito post ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4007');" href="#">Avviso di intimazione</a></li>
                                                                <li class="li_nc"><a onclick="menuClick('27');" href="#">Avviso di intimazione</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('4005');" href="#">Esiti notifiche</a></li>
                        <li class="li_nc"><a onclick="menuClick('4012');" href="#">Cartoline notifiche</a></li>
                        <li class="li_nc"><a onclick="menuClick('80');" href="#">Pagamenti</a></li>
                        <li class="li_nc"><a onclick="menuClick('82');" href="#">Pignoramenti</a></li>
                        <li class="li_nc"><a onclick="menuClick('4004');" href="#">Posizioni</a></li>
                        <li class="li_nc"><a onclick="menuClick('4003');" href="#">Udienze</a></li>
                        <li class="li_nc"><a onclick="menuClick('4008');" href="#">Confronto</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a target="_self" href="#">Stampa</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a target="_self" href="#">Atti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1001');" href="#">Sollecito pre ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1002');" href="#">Avviso di messa in mora</a></li>
                                <li class="li_nc"><a onclick="menuClick('24');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('42');" href="#">Sollecito post ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('25');" href="#">Avviso di intimazione</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a target="_self" href="#">Pignoramenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('75');" href="#">Presso datore di lavoro</a></li>
                                <li class="li_nc"><a onclick="menuClick('76');" href="#">Presso banca</a></li>
                                <li class="li_nc"><a onclick="menuClick('84');" href="#">Beni mobili registrati</a></li>
                                <li class="li_nc"><a onclick="menuClick('109');" href="#">Sollecito beni mobili registrati</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a href="#" target="_self">Altri documenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('59');" href="#">Richiesta Codici INPS</a></li>
                                <li class="li_nc"><a onclick="menuClick('69');" href="#">Richieste validazione notifica</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="li_nc"><a target="_self" href="#">Stampe</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a target="_self" href="#">Atti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1101');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1102');" href="#">Avviso di intimazione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1103');" href="#">Avviso di messa in mora</a></li>
                                <li class="li_nc"><a onclick="menuClick('1104');" href="#">Sollecito post ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1105');" href="#">Sollecito pre ingiunzione</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a target="_self" href="#">Pignoramento</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1201');" href="#">Pignoramento veicolo</a></li>
                                <li class="li_nc"><a onclick="menuClick('1202');" href="#">Presso datore di lavoro</a></li>
                                <li class="li_nc"><a onclick="menuClick('1203');" href="#">Presso banca</a></li>
                                <li class="li_nc"><a onclick="menuClick('1205');" href="#">Preavviso fermo</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a target="_self" href="#">Altri documenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1301');" href="#">Richiesta Codici INPS</a></li>
                                <li class="li_nc"><a onclick="menuClick('1302');" href="#">Richieste validazione notifica</a></li>
                                <li class="li_nc"><a onclick="menuClick('204');" href="#">Stampe discarichi/annullamenti</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="li_hc"><a href="#" target="_self">Testi</a>
            <ul class="ul_ch">
                <li><a href="#" target="_self">Html</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('2000');" href="#">Testi</a></li>
                        <li class="li_nc"><a onclick="menuClick('2003');" href="#">Sottotesti</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Atti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('2001');" href="#">Sollecito Pre Ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('2002');" href="#">Avviso di messa in mora</a></li>
                        <li class="li_nc"><a onclick="menuClick('28');" href="#">Ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('43');" href="#">Sollecito di pagamento</a></li>
                        <li class="li_nc"><a onclick="menuClick('501');" href="#">Avviso di intimazione</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Pignoramenti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('54');" href="#">Presso datore di lavoro</a></li>
                        <li class="li_nc"><a onclick="menuClick('74');" href="#">Presso banca</a></li>
                        <li class="li_nc"><a onclick="menuClick('83');" href="#">Beni mobili registrati</a></li>
                        <li class="li_nc"><a onclick="menuClick('108');" href="#">Sollecito Beni mobili registrati</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('86');" href="#">Preavviso fermo amministrativo</a></li>
                        <li class="li_nc"><a onclick="menuClick('87');" href="#">Fermo amministrativo</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Altri documenti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('111');" href="#">Archiviazione atto</a></li>
                        <li class="li_nc"><a onclick="menuClick('57');" href="#">Richiesta rateizzazione</a></li>
                        <li class="li_nc"><a onclick="menuClick('58');" href="#">Esito rateizzazione</a></li>
                        <li class="li_nc"><a onclick="menuClick('61');" href="#">Richiesta matricole</a></li>
                        <li class="li_nc"><a onclick="menuClick('66');" href="#">Richiesta indirizzo</a></li>
                        <li class="li_nc"><a onclick="menuClick('67');" href="#">Richiesta certificato di decesso</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('68');" href="#">Richiesta duplicato AR</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Parametri</a>
            <ul class="ul_ch">
                <li><a href="#" target="_self">Ente</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('55');" href="#">Stemma</a></li>
                        <li class="li_nc"><a onclick="menuClick('17');" href="#">Dati Ente</a></li>
                        <li class="li_nc"><a onclick="menuClick('18');" href="#">Gestore</a></li>
                        <li class="li_nc"><a onclick="menuClick('30');" href="#">Ufficio</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Autorita'</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('35');" href="#">Tribunale</a></li>
                        <li class="li_nc"><a onclick="menuClick('36');" href="#">Giudice di Pace</a></li>
                        <li class="li_nc"><a onclick="menuClick('37');" href="#">Corte d'appello</a></li>
                        <li class="li_nc"><a onclick="menuClick('38');" href="#">Commissione tributaria provinciale</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('39');" href="#">Commissione tributaria regionale</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('40');" href="#">Corte di cassazione</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Enti esterni</a>
                    <ul class="ul_ch">
                       <li class="li_nc"><a onclick="menuClick('46');" href="#">Avvocati</a></li>
                        <li class="li_nc"><a onclick="menuClick('47');" href="#">Sedi banche</a></li>
                        <li class="li_nc"><a onclick="menuClick('48');" href="#">Filiali banche</a></li>

                        <li class="li_nc"><a onclick="menuClick('60');" href="#">INPS</a></li>
                        <li class="li_nc"><a onclick="menuClick('85');" href="#">Tribunali / Ist. vendite
                                giudiziarie</a></li>
                        <li class="li_nc"><a onclick="menuClick('64');" href="#">Uffici anagrafici</a></li>
                        <li class="li_nc"><a onclick="menuClick('5001');" href="#">Uffici giudiziari</a></li>
                        <li class="li_nc"><a onclick="menuClick('65');" href="#">Uffici postali</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Generali</a>
                    <ul class="ul_ch">
                        <li><a onclick="menuClick('20');" href="#">Parametri annuali</a></li>
                        <li><a onclick="menuClick('106');" href="#">Interessi tributi</a></li>
                        <li><a onclick="menuClick('56');" href="#">Scorpori pagamenti</a></li>
                        <li><a onclick="menuClick('89');" href="#">Ricorsi</a></li>
                        <li><a onclick="menuClick('211');" href="#">Email/PEC</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Entrate</a>
                    <ul class="ul_ch">
                        <li><a href="#" target="_self">CDS / AMMINISTRATIVA</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('400');" href="#">Generali</a></li>
                                <li class="li_nc"><a onclick="menuClick('77');" href="#">Email</a></li>
                                <li class="li_nc"><a onclick="menuClick('53');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('19');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">IMMOBILI</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('401');" href="#">Generali</a></li>
                                <li class="li_nc"><a onclick="menuClick('92');" href="#">Email</a></li>
                                <li class="li_nc"><a onclick="menuClick('93');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('94');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">RIFIUTI</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('402');" href="#">Generali</a></li>
                                <li class="li_nc"><a onclick="menuClick('96');" href="#">Email</a></li>
                                <li class="li_nc"><a onclick="menuClick('97');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('98');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">PATRIMONIALE</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('403');" href="#">Generali</a></li>
                                <li class="li_nc"><a onclick="menuClick('100');" href="#">Email</a></li>
                                <li class="li_nc"><a onclick="menuClick('101');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('102');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">OSAP</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('404');" href="#">Generali</a></li>
                                <li class="li_nc"><a onclick="menuClick('200');" href="#">Email</a></li>
                                <li class="li_nc"><a onclick="menuClick('201');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('202');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">PUBBLICITA'</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('405');" href="#">Generali</a></li>
                                <li class="li_nc"><a onclick="menuClick('204');" href="#">Email</a></li>
                                <li class="li_nc"><a onclick="menuClick('205');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('206');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Tariffe</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('52');" href="#">Pignoramento</a></li>
                    </ul>
                </li>

            </ul>
        </li>

    </ul>
</div>-->
