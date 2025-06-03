<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $id_utente = $_SESSION['id_utente'];
    $ruolo = $_SESSION['ruolo'];
    $tipo_utente = $_SESSION['tipo_utente'];
    $id_azienda = $_SESSION['id_azienda'];

    $notifiche = [];
    // Notifica per i dipendenti in attesa di approvazione (per Amministratore Aziendale)
    if ($ruolo === 'Amministratore Aziendale') {
        $sql = "SELECT id_utente, nome, cognome FROM utenti WHERE stato='In Attesa' AND id_azienda=?";
        $sql_not_dip = $conn->prepare($sql);
        $sql_not_dip->bind_param("i", $id_azienda);
        $sql_not_dip->execute();
        $result = $sql_not_dip->get_result();
        while ($row = $result->fetch_assoc()) {
            $data_notifica = date('Y-m-d H:i:s');
            $tipo = "Nuova richiesta Utente";
            $messaggio = "Nuovo dipendente da approvare (" . $row['nome'] . " " . $row['cognome'] . ")";
            $link = "gestione_utenti.php";
    
            // Verifica se la notifica è già presente nel database
            $check_sql = "SELECT * FROM notifiche WHERE id_utente=? AND tipo_notifica=? AND messaggio=?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iss", $id_utente, $tipo, $messaggio);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
    
            if ($check_result->num_rows == 0) {
                // Inserisci la notifica solo se non esiste già
                $sql_n = "INSERT INTO notifiche (id_utente, tipo_notifica, messaggio, link, data_notifica) VALUES (?, ?, ?, ?, ?)";
                $sql_insert_notifica = $conn->prepare($sql_n);
                $sql_insert_notifica->bind_param("issss", $id_utente, $tipo, $messaggio, $link, $data_notifica);
                $sql_insert_notifica->execute();
            }
        }
    }

    //Segnate come lette tutte le notifiche visualizzate nella pagina
    $update_letta = $conn->prepare("UPDATE notifiche SET letto = 1 WHERE id_utente=?");
    $update_letta -> bind_param("i",$id_utente);
    $update_letta -> execute();

    $sql_notifiche = "SELECT * FROM notifiche WHERE id_utente=? ORDER BY data_notifica DESC";
    $stmt_notifiche = $conn->prepare($sql_notifiche);
    $stmt_notifiche->bind_param("i", $id_utente);
    $stmt_notifiche->execute();
    $result_notifiche = $stmt_notifiche->get_result();
    while ($row = $result_notifiche->fetch_assoc()) {
        $notifiche[] = $row;
    }    
    $conn->close();
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifiche</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <?php include "notificheCSS.php" ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>

        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Notifiche</h1>
            <div class="div_button">
            <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>    
            </div>
        </div>
            <?php //Descrizione delle notifiche
            foreach ($notifiche as $n): ?>
                <div class="notifica_box">
                    <div class="info_notifica">
                        <span class="data"><?php echo "(" . $n['data_notifica'] . ")"; ?></span>
                        <span class="notifica_tipo"><?php echo ($n['tipo_notifica']); ?>:</span>
                        <span class="notifica_messaggio"><?php echo ($n['messaggio']);?></span>
                    </div>
                    <div class="link_notifica">
                        <a href="<?php echo ($n['link']);?>"> Vai alla Gestione</a>
            </div>
                </div>
            <?php endforeach; ?>    
    </body>
</html>