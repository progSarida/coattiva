
<?php
if($_SESSION['aut_tipo']==20){
    ?>
        <script>
            if(checkMenuImg("F3")==1){
                switchMenuImg("F3");
                F3_button = function(){ return false;   }
            }

            F3_button = function(){ return false;   }
            if(checkMenuImg("F4")==1){
                switchMenuImg("F4");
                F4_button = function(){ return false;   }
            }


            if(checkMenuImg("F6")==1){
                switchMenuImg("F6");
                F6_button = function(){ return false;   }
            }
            elimina_tributo = function(){ return false;   }
            gestione_ruolo = function(){ return false;   }
            mostra_nuovo = function(){ return false;   }
            elabora_nuovo = function(){ return false;   }
            elabora_nuovo_atto = function(){ return false;   }
            stampa_richiesta = function(){ return false;   }
            archivia = function(){ return false;   }
            lista_mail = function(){ return false;   }
            visura = function(){ return false;   }
        </script>

    <?php
}
?>
