<?php
    require 'db.php';
    session_start();
    include 'funzioni.php';
    $id_utente = $_SESSION['id_utente'];
    $nome = $_SESSION['nome'];
    $cognome = $_SESSION['cognome'];

    // Rimuove tutte le variabili di sessione
    session_unset();

    // Controllo dei cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params(); //ottiene i parametri dei cookie usati per la sessione
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    // Distrugge la sessione
    session_destroy();
    // Reindirizza alla pagina di login o alla home
    header("Location: index.php");
    exit();
?>

