<script>
    function menu_script(value){
        $elab_atti = "<?=WEB_ROOT?>/elaborazioni/";
        $stampa_atti = "<?=WEB_ROOT?>/stampe/stampa_atto.php?" + stringaMODE;
        $elenco_atti = "<?=WEB_ROOT?>/stampe/elenco_atto.php?" + stringaMODE;
        $stampe = "<?=WEB_ROOT?>/stampe/";
        $stampa_pignoramenti = "<?=WEB_ROOT?>/stampe/stampa_pignoramento.php?" + stringaMODE;
        $stampa_solleciti_pignoramenti = "<?=WEB_ROOT?>/stampe/stampa_solleciti_pignoramento.php?" + stringaMODE;
        $elenco_pignoramenti = "<?=WEB_ROOT?>/stampe/gestione_elenco_pignoramenti.php?" + stringaMODE;
        $procedure_controllo = "<?=WEB_ROOT?>/stampe/procedure_controllo.php?" + stringaMODE;
        $autorita = "<?=WEB_ROOT?>/parametri/ufficio_giudiziario.php?" + stringaMODE;
        $ente_esterno_menu = "<?=WEB_ROOT?>/parametri/ente_esterno.php?" + stringaMODE;
        $banca = "<?=WEB_ROOT?>/parametri/banca.php?" + stringaMODE;
        $avvocato = "<?=WEB_ROOT?>/parametri/avvocato.php?" + stringaMODE;
        $ufficio_comune_link = "<?=WEB_ROOT?>/parametri/ufficio_comune.php?" + stringaMODE;
        $filiale = "<?=WEB_ROOT?>/parametri/filiale.php?" + stringaMODE;
        $amministrazione = "<?=WEB_ROOT?>/amministrazione/";

        menulink = "";
        switch (value) {          

            case '10004':
                menulink = "<?=WEB_ROOT?>/parametri/par_user_emails.php?"+ stringaMODE;
                break;
            case '10003':
                menulink = $amministrazione + "lista_notifiche.php?"+ stringaMODE;
                break;
            case '10002':
                menulink = $amministrazione + "reset_pswd.php?"+ stringaMODE;
                break;
            case '10001':
                menulink = $amministrazione + "crea_utente.php?"+ stringaMODE;
                break;
            case '10000':
                menulink = "<?=WEB_ROOT?>/home.php?"+ stringaMODE;
                break;
            //STAMPE [1000]
            /*case '1001':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=print&docType=SOLL_PRE"+ stringaMODE;
                break;
            case '1002':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=print&docType=AV_MORA"+ stringaMODE;
                break;*/
            //STAMPE HTML[1100]
            case '1101':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=ING"+ stringaMODE;
                break;
            case '1102':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=AV_INT"+ stringaMODE;
                break;
            case '1103':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=AV_MORA"+ stringaMODE;
                break;
            case '1104':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=SOLL_POST"+ stringaMODE;
                break;
            case '1105':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=SOLL_PRE"+ stringaMODE;
                break;
            case '1201':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=veicolo"+ stringaMODE;
                break;
            case '1202':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=lavoro"+ stringaMODE;
                break;
            case '1203':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=banca"+ stringaMODE;
                break;
            case '1205':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=html&docType=preav_fermo"+ stringaMODE;
                break;

            //PARAMETRI TESTO [2000]
            case '2000':
                menulink = "<?=WEB_ROOT?>/parametri/textParameters.php?" + stringaMODE;
                break;
            case '2003':
                menulink = "<?=WEB_ROOT?>/parametri/subtextParameters.php?" + stringaMODE;
                break;

            case '2001':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/text_parameters.php?docType=SOLL_PRE" + stringaMODE;
                break;
            case '2002':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/text_parameters.php?docType=AV_MORA" + stringaMODE;
                break;

            //ELABORAZIONI [3000]
            case '3001':
                menulink = $elab_atti + "elabora_atto.php?tipo_atto=generico" + stringaMODE;
                break;

            /** GV 28/06/2022  START */    
            case '3003':
                menulink = "<?=ELAB_ATTI_WEB?>/start_elaboration.php?tipo_atto=generico" + stringaMODE;
                break;
            /** GV 28/06/2022    END */ 
            
            case '3004':
                menulink = "<?=ELAB_STRAGIUDIZIALI_WEB?>/start_stragiudiziali.php?tipo_atto=generico" + stringaMODE;
                break;
            // case '3006':
            //     menulink = $elab_atti + "start_stragiudiziale_previdenziale.php?tipo_atto=generico" + stringaMODE;
            //     break;

            case '3005':
                menulink = "<?=ELAB_PIGNORAMENTI_WEB?>/start_pignoramento.php?" + stringaMODE;
                break;
            /*case '3002':
                menulink = $elab_atti + "elabora_atto.php?tipo_atto=avviso_mora" + stringaMODE;
                break;*/
            case '3011':
                menulink = $elab_atti + "gestione_elaborazioni.php?docType=discharge" + stringaMODE;
                break;
            case '3012':
                menulink = $elab_atti + "gestione_elaborazioni.php?docType=extraction" + stringaMODE;
                break;
                

            //ELENCHI [4000]
            case '4001':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=SOLL_PRE" + stringaMODE;
                break;
            case '4002':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=AV_MORA" + stringaMODE;
                break;
            case '4003':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=court_hearing" + stringaMODE;
                break;
            case '4004':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=positions" + stringaMODE;
                break;
            case '4008':
                menulink = $stampe + "stampe_guidate.php?printType=list&docType=positions" + stringaMODE;
                break;
            case '4025':
                menulink = $stampe + "excel_posizioni.php?" + stringaMODE;
                break;
            case '4026':
                menulink = $stampe + "export_mgmt.php?" + stringaMODE;
                break;

                

            case '4005':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=esiti" + stringaMODE;
                break;

            case '4006':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=ING" + stringaMODE;
                break;
            case '4007':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=AV_INT" + stringaMODE;
                break;
            /**   GV - 08/06/2022 - START */
            case '4009':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=discharge" + stringaMODE;
                break;
            /**   GV - 08/06/2022 - END  */     
            
            case '4010':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=SCORPORO_ING" + stringaMODE;
                break;

            case '4011':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=SOLL_POST" + stringaMODE;
                break;
            case '4012':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=notifiche" + stringaMODE;
                break;

            case '4013':
                menulink = $elab_atti + "rimborso_spese.php?" + stringaMODE;
                break;

            case '4014':
                menulink = $stampe + "filtri_stampa_tariffe.php?" + stringaMODE;
                break;

            case '4015':
                menulink = $stampe + "filtri_stampa_maggiorazioni.php?" + stringaMODE;
                break;
            case '4016':
                menulink = $elab_atti + "rimborso_spese_excel.php?" + stringaMODE;
                break;
            case '4017':
                menulink = $stampe + "filtri_conto_giudiziale.php?" + stringaMODE;
                break;
            case '4018':
                menulink = $elab_atti + "rendiconto_gestione.php?" + stringaMODE;
                break;
            case '4019':
                menulink = $elab_atti + "stampa_agente_contabile.php?" + stringaMODE;
                break;
            case '4020':
                menulink = $stampe + "resoconto_inipec.php?" + stringaMODE;
                break;
            case '4021':
                menulink = $stampe + "resoconto_visure_aci.php?" + stringaMODE;
                break;

            case '4022':
                menulink = $stampe + "gestione_stampe.php?printType=list&docType=last_acts" + stringaMODE;
                break;
            case '4023':
                menulink = $stampe + "storico_azioni.php?" + stringaMODE;
                break;
            case '4024':
                menulink = $stampe + "importazioni_290.php?" + stringaMODE;
                break;
            //ENTI ESTERNI (5000)
            case '5001':
                menulink = "<?=WEB_ROOT?>/parametri/authorityOffice.php?" + stringaMODE;
                break;

            //CONTROLLI GESTIONE (6000)
            case '6001':
                menulink = "<?=WEB_ROOT?>/coattiva/flow_mgmt.php?" + stringaMODE;
                break;
            case '6002':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_stampe.php?printType=list&docType=flow" + stringaMODE;
                break;
            case '6003':
                menulink = $elab_atti + "procedure.php?" + stringaMODE;
                break;
            case '6004':
            menulink = "<?=WEB_ROOT?>/coattiva/update_date_flows.php?" + stringaMODE;
            break;
            case '6005':
                menulink = $elab_atti + "riversamenti.php?" + stringaMODE;
                break;
            case '6006':
                menulink = $elab_atti + "comunicazioni.php?" + stringaMODE;
                break;
            //IMPORTAZIONI (7000)
            case '7001':
                menulink = "<?=WEB_ROOT?>/coattiva/inserimento_multiplo_cad.php?" + stringaMODE;
                break;
            case '3006':
                menulink = "<?=WEB_ROOT?>/coattiva/carica_excel_reimportazione_atti.php?" + stringaMODE;
                break;
            
            //ALTRO
            case '1':
                menulink = "<?=WEB_ROOT?>/anagrafe/dati_soggetto.php?" + stringaMODE;
                break;
            /*case '2':
                menulink = "<?=WEB_ROOT?>/anagrafe/annotazioni.php?" + stringaMODE;
                break;
            case '3':
                menulink = "<?=WEB_ROOT?>/anagrafe/recapito.php?" + stringaMODE;
                break;
            case '4':
                menulink = "<?=WEB_ROOT?>/anagrafe/domicilio.php?" + stringaMODE;
                break;
            case '5':
                menulink = "<?=WEB_ROOT?>/anagrafe/dettagli.php?" + stringaMODE;
                break;
            case '6':
                menulink = "<?=WEB_ROOT?>/anagrafe/cambia_residenza.php?" + stringaMODE;
                break;
            case '207':
                menulink = "<?=WEB_ROOT?>/anagrafe/Veicoli.php?" + stringaMODE;
                break;*/

            case '7':
                menulink = "<?=WEB_ROOT?>/coattiva/gestione_ruolo.php?" + stringaMODE;
                break;

            case '8':
                menulink = "<?=WEB_ROOT?>/290/upload_290.php?" + stringaMODE;
                break;
            case '79':
                menulink = "<?=WEB_ROOT?>/290/modelli_290.php?" + stringaMODE;
                break;
            case '105':
                menulink = "<?=WEB_ROOT?>/290/imp_excel290.php?" + stringaMODE;
                break;

            case '44':
                menulink = "<?=WEB_ROOT?>/coattiva/lista_codici_tributo.php?" + stringaMODE;
                break;
            case '51':
                menulink = "<?=WEB_ROOT?>/coattiva/lista_tariffe.php?" + stringaMODE;
                break;
            case '104':
                menulink = "<?=WEB_ROOT?>/coattiva/inserimento_ruolo.php?" + stringaMODE;
                break;
            case '9':
                menulink = "<?=WEB_ROOT?>/coattiva/gestione_partita.php?" + stringaMODE;
                break;

            case '10':
                menulink = "<?=WEB_ROOT?>/coattiva/ingiunzione.php?" + stringaMODE;
                break;
            case '11':
                menulink = "<?=WEB_ROOT?>/coattiva/pagamento.php?" + stringaMODE;
                break;
            case '12':
                menulink = "<?=WEB_ROOT?>/coattiva/appeal_list.php?" + stringaMODE;
                break;
            case '50':
                menulink = "<?=WEB_ROOT?>/coattiva/coazione.php?" + stringaMODE;
                break;
            case '90':
                menulink = "<?=WEB_ROOT?>/coattiva/pagamento_pignoramento.php?" + stringaMODE;
                break;
            case '91':
                menulink = "<?=WEB_ROOT?>/coattiva/ricorso_pignoramento.php?" + stringaMODE;
                break;

            case '45':
                menulink = "<?=WEB_ROOT?>/ispezioni/ricerche_ispezioni.php?" + stringaMODE;
                break;

            case '13':
                menulink = "<?=WEB_ROOT?>/gestione/crea_comune.php?" + stringaMODE;
                break;
            case '14':
                menulink = "<?=WEB_ROOT?>/gestione/crea_anno.php?" + stringaMODE;
                break;
            //case '15':
            //    menulink = "<?//=WEB_ROOT?>///gestione/elimina_comune.php?" + stringaMODE;
            //    break;
            case '16':
                menulink = "<?=WEB_ROOT?>/gestione/elimina_anno.php?" + stringaMODE;
                break;

            case '17':
                menulink = "<?=WEB_ROOT?>/parametri/dati_ente.php?" + stringaMODE;
                break;
            case '18':
                menulink = "<?=WEB_ROOT?>/parametri/gestore.php?" + stringaMODE;
                break;
            case '30':
                menulink = "<?=WEB_ROOT?>/parametri/ufficio.php?" + stringaMODE;
                break;
            case '55':
                menulink = "<?=WEB_ROOT?>/parametri/stemma.php?" + stringaMODE;
                break;


            case '35':
                menulink = $autorita + "&tipo_ufficio=tribunale";
                break;
            case '36':
                menulink = $autorita + "&tipo_ufficio=giudice";
                break;
            case '37':
                menulink = $autorita + "&tipo_ufficio=appello";
                break;
            case '38':
                menulink = $autorita + "&tipo_ufficio=cort_giust_trib";
                break;
            case '39':
                menulink = $autorita + "&tipo_ufficio=comm_trib_reg";
                break;
            case '40':
                menulink = $autorita + "&tipo_ufficio=cassazione";
                break;

            // case '46':
            //     menulink = $avvocato;
            //     break;
            case '47':
                menulink = $banca;
                break;
            case '48':
                menulink = $filiale + "&tipo_sede=banca";
                break;
            case '60':
                menulink = $ente_esterno_menu + "&tipo_ente=previdenza";
                break;
            case '85':
                menulink = "<?=WEB_ROOT?>/parametri/tribunale_esterno.php?" + stringaMODE;
                break;
            case '64':
                menulink = $ufficio_comune_link + "&tipo=uff_anagrafico";
                break;
            case '65':
                menulink = $ufficio_comune_link + "&tipo=uff_postale";
                break;

            case '56':
                menulink = "<?=WEB_ROOT?>/parametri/par_scorpori.php?" + stringaMODE;
                break;
            case '89':
                menulink = "<?=WEB_ROOT?>/parametri/par_ricorso.php?" + stringaMODE;
                break;
            case '211':
                menulink = "<?=WEB_ROOT?>/parametri/par_email_PEC.php?" + stringaMODE;
                break;

            case '20':
                menulink = "<?=WEB_ROOT?>/parametri/par_annuali.php?tipo_riscossione=*****" + stringaMODE;
                break;
            case '106':
                menulink = "<?=WEB_ROOT?>/parametri/gestione_interessi_tributi.php?" + stringaMODE;
                break;

            case '400':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=CDS" + stringaMODE;
                break;
            case '401':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=IMMOBILI" + stringaMODE;
                break;
            case '402':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=RIFIUTI" + stringaMODE;
                break;
            case '403':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=PATRIMONIALE" + stringaMODE;
                break;
            case '404':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=OSAP" + stringaMODE;
                break;
            case '405':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=PUBBLICITA" + stringaMODE;
                break;
            case '406':
                menulink = "<?=WEB_ROOT?>/parametri/par_generali.php?tipo_riscossione=TUTTE" + stringaMODE;
                break;


            case '19':
                menulink = "<?=WEB_ROOT?>/parametri/par_responsabili.php?tipo_riscossione=CDS" + stringaMODE;
                break;
            case '53':
                menulink = "<?=WEB_ROOT?>/parametri/par_pagamento.php?tipo_riscossione=CDS" + stringaMODE;
                break;
            /*case '77':
                menulink = "<?=WEB_ROOT?>/parametri/par_email.php?tipo_riscossione=CDS" + stringaMODE;
                break;*/
            case '78':
                menulink = "<?=WEB_ROOT?>/parametri/par_spedizione.php?tipo_riscossione=CDS" + stringaMODE;
                break;
            case '2100':
                menulink = "<?=WEB_ROOT?>/parametri/par_ufficio.php?tipo_riscossione=CDS" + stringaMODE;
                break;

            /*case '92':
                menulink = "<?=WEB_ROOT?>/parametri/par_email.php?tipo_riscossione=IMMOBILI" + stringaMODE;
                break;*/
            case '93':
                menulink = "<?=WEB_ROOT?>/parametri/par_pagamento.php?tipo_riscossione=IMMOBILI" + stringaMODE;
                break;
            case '94':
                menulink = "<?=WEB_ROOT?>/parametri/par_responsabili.php?tipo_riscossione=IMMOBILI" + stringaMODE;
                break;
            case '95':
                menulink = "<?=WEB_ROOT?>/parametri/par_spedizione.php?tipo_riscossione=IMMOBILI" + stringaMODE;
                break;
            case '2101':
                menulink = "<?=WEB_ROOT?>/parametri/par_ufficio.php?tipo_riscossione=IMMOBILI" + stringaMODE;
                break;

            /*case '96':
                menulink = "<?=WEB_ROOT?>/parametri/par_email.php?tipo_riscossione=RIFIUTI" + stringaMODE;
                break;*/
            case '97':
                menulink = "<?=WEB_ROOT?>/parametri/par_pagamento.php?tipo_riscossione=RIFIUTI" + stringaMODE;
                break;
            case '98':
                menulink = "<?=WEB_ROOT?>/parametri/par_responsabili.php?tipo_riscossione=RIFIUTI" + stringaMODE;
                break;
            case '99':
                menulink = "<?=WEB_ROOT?>/parametri/par_spedizione.php?tipo_riscossione=RIFIUTI" + stringaMODE;
                break;
            case '2102':
                menulink = "<?=WEB_ROOT?>/parametri/par_ufficio.php?tipo_riscossione=RIFIUTI" + stringaMODE;
                break;

            /*case '100':
                menulink = "<?=WEB_ROOT?>/parametri/par_email.php?tipo_riscossione=PATRIMONIALE" + stringaMODE;
                break;*/
            case '101':
                menulink = "<?=WEB_ROOT?>/parametri/par_pagamento.php?tipo_riscossione=PATRIMONIALE" + stringaMODE;
                break;
            case '102':
                menulink = "<?=WEB_ROOT?>/parametri/par_responsabili.php?tipo_riscossione=PATRIMONIALE" + stringaMODE;
                break;
            case '103':
                menulink = "<?=WEB_ROOT?>/parametri/par_spedizione.php?tipo_riscossione=PATRIMONIALE" + stringaMODE;
                break;
            case '2103':
                menulink = "<?=WEB_ROOT?>/parametri/par_ufficio.php?tipo_riscossione=PATRIMONIALE" + stringaMODE;
                break;

            /*case '200':
                menulink = "<?=WEB_ROOT?>/parametri/par_email.php?tipo_riscossione=OSAP" + stringaMODE;
                break;*/
            case '201':
                menulink = "<?=WEB_ROOT?>/parametri/par_pagamento.php?tipo_riscossione=OSAP" + stringaMODE;
                break;
            case '202':
                menulink = "<?=WEB_ROOT?>/parametri/par_responsabili.php?tipo_riscossione=OSAP" + stringaMODE;
                break;
            case '203':
                menulink = "<?=WEB_ROOT?>/parametri/par_spedizione.php?tipo_riscossione=OSAP" + stringaMODE;
                break;
            case '2104':
                menulink = "<?=WEB_ROOT?>/parametri/par_ufficio.php?tipo_riscossione=OSAP" + stringaMODE;
                break;

            /*case '204':
                menulink = "<?=WEB_ROOT?>/parametri/par_email.php?tipo_riscossione=PUBBLICITA" + stringaMODE;
                break;*/
            case '205':
                menulink = "<?=WEB_ROOT?>/parametri/par_pagamento.php?tipo_riscossione=PUBBLICITA" + stringaMODE;
                break;
            case '206':
                menulink = "<?=WEB_ROOT?>/parametri/par_responsabili.php?tipo_riscossione=PUBBLICITA" + stringaMODE;
                break;
            case '207':
                menulink = "<?=WEB_ROOT?>/parametri/par_spedizione.php?tipo_riscossione=PUBBLICITA" + stringaMODE;
                break;
            case '2105':
                menulink = "<?=WEB_ROOT?>/parametri/par_ufficio.php?tipo_riscossione=PUBBLICITA" + stringaMODE;
                break;
            

            case '501':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_avviso_intimazione.php?" + stringaMODE;
                break;
            case '28':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_ingiunzione.php?" + stringaMODE;
                break;
            case '43':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_sollecito_ingiunzione.php?" + stringaMODE;
                break;

            case '54':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_pignoramento_presso_lavoro.php?" + stringaMODE;
                break;
            case '74':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_pignoramento_presso_banca.php?" + stringaMODE;
                break;
            case '83':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_pignoramento_veicolo.php?" + stringaMODE;
                break;
            case '86':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_preavviso_fermo.php?" + stringaMODE;
                break;
            case '87':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_fermo_amministrativo.php?" + stringaMODE;
                break;

            case '108':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_sollecito_pignoramento_veicolo.php?" + stringaMODE;
                break;

            case '57':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_richiesta_rateizzazione.php?" + stringaMODE;
                break;
            case '58':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_esito_rateizzazione.php?" + stringaMODE;
                break;
            case '61':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_richiesta_matricole.php?" + stringaMODE;
                break;
            case '66':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_richiesta_indirizzo.php?" + stringaMODE;
                break;
            case '67':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_richiesta_decesso.php?" + stringaMODE;
                break;
            case '68':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_richiesta_duplicato_AR.php?" + stringaMODE;
                break;
            case '111':
                menulink = "<?=WEB_ROOT?>/parametri/stampe/testo_archiviazione_atto.php?" + stringaMODE;
                break;

            case '52':
                menulink = "<?=WEB_ROOT?>/parametri/lista_tariffe_generali.php?" + stringaMODE;
                break;

            case '59':
                menulink = "<?=WEB_ROOT?>/parametri/lista_maggiorazioni_generali.php?" + stringaMODE;
                break;

           /* case '22':
                menulink = $elab_atti + "elabora_atto.php?tipo_atto=avint_ing" + stringaMODE;
                break;
            case '32':
                menulink = $elab_atti + "elabora_atto.php?tipo_atto=sollecito" + stringaMODE;
                break;*/
            /*case '23':
                menulink = $elab_atti + "elabora_atto.php?tipo_atto=avint_ing" + stringaMODE;
                break;*/
            case '62':
                menulink = "<?=WEB_ROOT?>/coattiva/importazione_notifiche.php?provenienza=COATTIVA" + stringaMODE;
                break;
            case '63':
                menulink = "<?=WEB_ROOT?>/targheestere/elaborazioni/travaso_flussi_su_gitco.php?provenienza=COATTIVA" + stringaMODE;
                break;
            case '73':
                menulink = "<?=WEB_ROOT?>/targheestere/elaborazioni/travaso_pagamenti_su_gitco.php?provenienza=COATTIVA" + stringaMODE;
                break;
            case '81':
                menulink = $elab_atti + "utente_visura.php?" + stringaMODE;
                break;
            case '114':
                menulink = "<?=WEB_ROOT?>/elaborazioni/ws_inipec.php?" + stringaMODE;
                break;
            case '88':
                menulink = $elab_atti + "visura_massiva.php?page=visura&printType=procedure" + stringaMODE;
                break;
            case '92':
                  menulink = $elab_atti + "elenco_visura_massiva.php?" + stringaMODE;
                  break;
            case '200':
                menulink = "<?=WEB_ROOT?>/sgravi/elaborazione_sgravi.php?" + stringaMODE;
                break;
            case '204':
                menulink = $elab_atti + "elenco_sgravi_annull.php?" + stringaMODE;
                break;

            case '1501':
                menulink = "<?=WEB_ROOT?>/sgravi/gestione_stampa_sgravi.php?" + stringaMODE;
                break;

            case '112':
                menulink = $elab_atti + "stampa_commercialista.php?" + stringaMODE;
                break;
            case '113':
                menulink = $elab_atti + "leggi_trafat.php?" + stringaMODE;
                break;

            /*case '107':
                menulink = $elab_atti + "elabora_pignoramento.php?tipo_elabo=sollecito&tipo_pigno=veicolo" + stringaMODE;
                break;*/

            case '70':
                menulink = "<?=WEB_ROOT?>/coattiva/importazione_pagamenti.php?provenienza=COATTIVA" + stringaMODE;
                break;
            case '71':
                menulink = "<?=WEB_ROOT?>/coattiva/pagamento_da_bonificare.php?provenienza=COATTIVA&telematico=NO" + stringaMODE;
                break;
            case '72':
                menulink = "<?=WEB_ROOT?>/coattiva/pagamento_da_bonificare.php?provenienza=COATTIVA&telematico=SI" + stringaMODE;
                break;

            /*case '24':
                menulink = $stampa_atti + "&tipo_atto=Ingiunzione";
                break;
            case '42':
                menulink = $stampa_atti + "&tipo_atto=sollecito";
                break;
            case '25':
                menulink = $stampa_atti + "&tipo_atto=avv_intimazione";
                break;*/

            case '33':
                menulink = $elenco_atti + "&tipo_atto=preavviso_ing";
                break;
            case '26':
                menulink = $elenco_atti + "&tipo_atto=Ingiunzione";
                break;
            case '41':
                menulink = $elenco_atti + "&tipo_atto=sollecito";
                break;
            case '27':
                menulink = $elenco_atti + "&tipo_atto=avv_intimazione";
                break;

            case '88':
                menulink = $stampe + "gestione_elenco_posizioni.php?" + stringaMODE;
                break;


            case '82':
                menulink = $elenco_pignoramenti;
                break;
            /*case '75':
                menulink = $stampa_pignoramenti + "&tipo_pignoramento=lavoro";
                break;
            case '76':
                menulink = $stampa_pignoramenti + "&tipo_pignoramento=banca";
                break;
            case '84':
                menulink = $stampa_pignoramenti + "&tipo_pignoramento=veicolo";
                break;

            case '109':
                menulink = $stampa_solleciti_pignoramenti + "&tipo_pignoramento=veicolo";
                break;*/

            /*case '59':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_richiesta_inps.php?" + stringaMODE;
                break;
            case '69':
                menulink = "<?=WEB_ROOT?>/stampe/richiesta_validazione_notifica.php?" + stringaMODE;
                break;*/
            case '80':
                menulink = "<?=WEB_ROOT?>/stampe/pagina_stampa_pagamenti.php?tipo=ELENCO" + stringaMODE;
                break;

            case '300':
                menulink = "<?=WEB_ROOT?>/controlli/gestione_PEC.php?" + stringaMODE;
                break;

            // GV - 31/05/2022 - START 
            case '301':
                menulink = "<?=WEB_ROOT?>/controlli/anagrafe.php?" + stringaMODE;
                break;
             // GV - 31/05/2022 -  END  

             // GV - 31/05/2022 - START 
            case '302':
                menulink = "<?=WEB_ROOT?>/controlli/lista_elaborazioni.php?" + stringaMODE;
                break;
             // GV - 31/05/2022 -  END

            case '303':
                menulink = "<?=WEB_ROOT?>/controlli/lista_procedure.php?" + stringaMODE;
                break; 

            case '29':
                menulink = "<?=WEB_ROOT?>/selectCityYear.php?" + stringaMODE;
                break;

            case '1301':
                menulink = "<?=WEB_ROOT?>/stampe/gestione_richiesta_inps.php?" + stringaMODE;
                break;

            case '1302':
                menulink = "<?=WEB_ROOT?>/stampe/richiesta_validazione_notifica.php?" + stringaMODE;
                break;

            case '110':
                menulink = "<?=WEB_ROOT?>/interessi/interessi.php?" + stringaMODE;
                break;

            default:
                alert("Errore nella scelta del menu");
                return false;
                break;
        }

        return menulink;
    }


</script>
