<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
?>
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" 
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/1.2.2/css/searchPanes.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.1/css/select.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap.min.css"> -->

<?php
include_once(INC . "/menu.php");
include_once CLS . "/cls_help.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$last_el_id =  $cls_help->getVar('el');

$functionEcho = function($str)
{
    var_dump($str);echo"<br>";

};

$valid_username = function()
{
    return in_array($_SESSION['username'],array("fabrizio","mirkop","robertop"));
};

if ($_SESSION['username'] == NULL) {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$auth =  $_SESSION['aut_tipo'];

?>
<link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
<script type="text/javascript" src="<?= DATATABLE ?>/datatables.min.js"></script>

<style>
    td {
        text-align: center;
    }

    th {
        text-align: center;
    }
</style>

<div class="col col-md-auto text_center">
    <span class="titolo font16 under_decor">Lista Stragiudiziali</span>
</div>
<div class="container">

    <table id="example" class="table table-striped table-bordered wrap" style="border:3px solid #6D95D5; width:100%;  margin-left:3px; ">
        <thead>
            <tr>
                <th>Ente</th>
                <th>Descrizione</th>
                <th>Stato</th>
                <th>Data Creazione</th>
                <th>Operatore</th>
                <th>Elaborazione</th>

            </tr>
        </thead>
        <tbody>
            <?php
            $query_stragiudiziali =  "SELECT P.*,E.Denominazione as Comune,  auth.User AS OPERATORE, PS.name
            FROM   procedures AS P  
            JOIN autenticazione as auth on auth.ID = P.User_Id 
            JOIN enti_gestiti as E on E.CC = P.CC
            LEFT JOIN procedure_status as PS on PS.id = P.Procedure_Status_Id 
            WHERE P.Procedure_Type_Id in (1) ORDER BY P.DateTime DESC";

            if (intval($auth) !== 1) {
                $query_stragiudiziali .= " AND CC = '" . $c . "' ";
            }

            $results = $cls_db->ExecuteQuery($query_stragiudiziali);

            if (isset($results)) {
                $procedure_list = $cls_db->getResults($results);

                $scegli_tipo = function($id) use($cls_db){
                    $query = "SELECT distinct if(Banca_Id is NULL,'Previdenziali','Banca') as Tipo
                    FROM gitco2.stragiudiziali where Procedure_Id = $id";
                    $res = $cls_db->getResults($cls_db->ExecuteQuery($query));
                    return $res[0]["Tipo"];
                };

                
                foreach ($procedure_list as $procedura) {   
                    
                    if (($procedura['Procedure_Status_Id']<=40) && $valid_username())
                        $hiddenDelete = " ";
                    else
                        $hiddenDelete = "none";
            ?>
                    <tr>
                        <td><?php echo $procedura['Comune']; ?></td>
                        <td><?php echo $procedura['Description']; ?></td>
                        <td><?php echo $procedura['name']; ?></td>
                        <td><?php echo date("d/m/Y H:i:s", strtotime($procedura['Datetime'])); ?></td>
                        <td><?php echo $procedura['OPERATORE']; ?></td>
                        <td colspan="2">
                            <div style="margin: 0 auto; width: 200px; text-align: center;">
                                <button type="button" class="btn btn-primary showProc" id="strag_<?= $procedura['Id'] ?>" name="strag_<?= $procedura['CC'] ?>_<?= $scegli_tipo($procedura['Id']) ?>">Visualizza</button>
                                <button type="button" style="display:<?php  echo $hiddenDelete?>  " class="btn btn-danger deleteProc" id="<?= (string)$procedura['CC'] ?>_<?= $procedura['Id'] ?>" name= "elab_<?= $procedura['Document_Type_Id'] ?>">Elimina</button>
                            </div>
                        </td>
                    </tr>
                <?php
                }
            } // if (isset($results))
            else {
                ?>
                <tr>
                    <td colspan="6">
                        Non Sono presenti dati
                    </td>
                </tr>
        </tbody>
    </table>

<?php
                return;
            }
?>
</tbody>
</table>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/searchpanes/1.2.2/js/dataTables.searchPanes.min.js"></script>
<script src="https://cdn.datatables.net/searchpanes/1.2.2/js/searchPanes.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> -->

<script>
    var table;
    $(document).ready(function() {
        $('#example').DataTable({
            "order": [
                [0, "desc"]
            ], //or asc 
        });
    });

    var c = '<?= $c; ?>';
    var a = '<?= $a; ?>';

    $(document).ready(function() {


        $('#example').on('click', '.showProc', function() {
            var par_String = this.id;
            console.log(par_String);
            var id_pro = par_String.split('_')[1];
            var proc_id = id_pro.replace(/[^0-9.]/g, "");
            var codcat = $('#strag_' + id_pro).attr("name");
            var cod_cat = codcat.split('_')[1];
            var tipo = codcat.split('_')[2];
            console.log(cod_cat);


            window.location.href = "<?= WEB_ROOT ?>/elaborazioni/stragiudiziali/mgmt_stragiudiziali.php?c=" + cod_cat + "&a=" + a + "&pr=" + proc_id +"&tipo=" + tipo;

        });

        $('#example').on('click', '.deleteProc', function() {
    
            var par_String =  this.id;
            var proc_id = par_String.split('_')[1];

            swal({
                title: "SEI SICURO?",
                text: "Una volta eliminata la procedura non può più essere recuperata!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
            if (willDelete) {
                
                $.ajax({
                    type: "POST",
                    url: "ajax/ajax_delete_procedure.php",
                    data: { "proc_id" : proc_id,},
                    cache: false,
                    success: function(response){        
                    var response = JSON.parse(response);
        
                    if(response.esito == "OK")
                    {
                        swal({
                                title: "SUCCESS!",
                                text:  response.message,
                                icon: "success",
                                timer: 25000,
                                buttons: false
                            });
                            window.location.href ="<?= WEB_ROOT ?>/controlli/lista_procedure.php?&p=&c="+c+"&a="+a;
                    }
                    else{
                            
                        swal({
                                title: "ERROR!",
                                text:  response.message,
                                icon: "danger",
                                timer: 5000,
                                buttons: false
                            });
                        
                    }
        
                    },
                    error: function(error){
                        console.log(error)
                    }        
                });
            } else {
                swal("La tua elaborazione è salva!");
            }
             });
        });
    });    
</script>
<?php
include(INC . "/footer.php");
?>