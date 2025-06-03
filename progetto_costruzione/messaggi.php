<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    $link = $_SERVER['REQUEST_URI'];

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    rimuoviNotifiche($conn,$_SESSION['id_utente'],$link);

    // Recupero dell'id del destinatario nel caso il mittente avesse cliccato su rispondi 
    if(isset($_GET['id_destinatario'])){
        $id_destinatario = $_GET['id_destinatario'];
    } else {
        $id_destinatario = null;
    }

    //Si marcano i messaggi visti nella pagina come letti e si segna la data di lettura
    $sql_mark_letto = "UPDATE messaggi SET letto = TRUE, data_letto = NOW() WHERE id_destinatario = ? AND letto = FALSE";
    $stmt_mark_letto = $conn->prepare($sql_mark_letto);
    $stmt_mark_letto->bind_param("i", $_SESSION['id_utente']);
    $stmt_mark_letto->execute();

    //Select per recuperari i messaggi
    $messaggi=[];
    $sql_messaggi="SELECT m.*,u.id_utente,u.nome AS nome_mittente,u.cognome AS cognome_mittente 
    FROM messaggi m JOIN utenti u ON m.id_mittente = u.id_utente WHERE id_destinatario=? ORDER BY data_invio DESC";
    $stmt_messaggi = $conn->prepare($sql_messaggi);
    $stmt_messaggi->bind_param("i",$_SESSION['id_utente']);
    if($stmt_messaggi->execute()){
        $result = $stmt_messaggi->get_result();
        while($row=$result->fetch_assoc()){
            $messaggi[]=$row;
        }
    }

    //Eliminazione dei messaggi letti dopo due ore
    $sql_elimina_messaggi = "DELETE FROM messaggi WHERE letto=TRUE AND TIMESTAMPDIFF(HOUR,data_letto,NOW())>2";
    $stmt_elimina_messaggi = $conn->prepare($sql_elimina_messaggi);
    $stmt_elimina_messaggi->execute();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Messaggi</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <?php include 'progettiCSS.php'?>
        <?php include 'messaggiCSS.php'?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>

        <!--Intestazione con logo-->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Messaggi</h1>
            <div class="div_button">
            <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>    
            </div>
        </div>
        <div class="button">
                <button class="add_button2" onclick='location.href="invia_messaggio.php"'>Invia Messaggio</button>
        </div>
        <!--Elenco dei messaggi-->
            <h1>Elenco Messaggi</h1>
            <?php foreach ($messaggi as $messaggio): ?>
                <div class="messaggi_box">
                    <div class="messaggio">
                        <div class="inizio">
                        <span class="data"><?php echo "(" . $messaggio['data_invio'] . ")"; ?></span>
                        <span class="mittente"><?php echo $messaggio['nome_mittente']." ".$messaggio['cognome_mittente']; ?>:</span>
                        <span class="oggetto_messaggio"><?php echo "(" .($messaggio['oggetto']).")";?>:</span>
                        <span class="contenuto_messaggio"><?php echo ($messaggio['contenuto']);?></span>
                        <?php if($messaggio['letto']):?>
                            <span class="stato_letto"><i class="fas fa-check-double" title="Letto" style="color:green;"></i></span></div>
                        <?php else:?>
                            <a class="btn_modifica" 
                               href="modifica_messaggio.php?id=<?php echo $messaggio['id_messaggio']; ?>">
                                Modifica Messaggio</a>
                        <?php endif; ?>
                        <div class="risposta">
                    <a class="rispondi" href="invia_messaggio.php?id_destinatario=<?php echo $messaggio['id_mittente']; ?>">Rispondi</a>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?> 

    </body>
</html>