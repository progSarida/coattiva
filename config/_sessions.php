<?php

if (session_status() == PHP_SESSION_NONE)
    session_start();

if(!empty($_SESSION['s_expire'])){
    $currentTime = time();
    if($currentTime > $_SESSION['s_expire']) {

        $secondi = $currentTime-$_SESSION['s_start'];
        if($secondi<60)
            $minuti = $secondi." secondi di inattività!";
        else
            $minuti = (int)( $secondi / 60 )." minuti di inattività!";

        session_unset();
        session_destroy();
        echo "<script>alert('SESSIONE SCADUTA: ".$minuti."');</script>";
        echo "<script>location.href = '".SUPER_WEB_ROOT."';</script>";
        die;
    }
    else{
        $_SESSION['s_start'] = $currentTime;
        $_SESSION['s_expire'] = $currentTime + ($_SESSION['s_minutes'] * 60);
    }
}
else if(empty($_SESSION['start_auth'])){
    echo "<script>alert('SESSIONE NON TROVATA!');</script>";
    echo "<script>location.href = '".SUPER_WEB_ROOT."';</script>";
    die;
}


