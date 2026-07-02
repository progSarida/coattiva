
<?php if($bool)
{
?>
    <script>
        swal({
            title: 'ERRORE',
            text: '<?= $msg_error?>',
            icon: 'danger',
            timer: 5000,
            buttons: false
        }).then((result) => {
            location.href ="<?= ELAB_STRAGIUDIZIALI_WEB ?>/start_stragiudiziali.php?c="+c+"&a="+a+"&pr="+p;
        });
    </script>
<?php
    include(INC . "/footer.php");
    return;
}