<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    $id_utente = $_SESSION['id_utente'];

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);

    //SELEZIONE DEI COMPITI DELL'OPERAIO
     $sql_compiti_operaio =  "SELECT c.*,a.nome_attivita,a.id_attivita,a.id_progetto,pr.nome_progetto,pr.id_progetto,u.nome AS nome_operaio, 
     u.cognome AS cognome_operaio,u.id_utente,urp.nome AS nome_responsabile_progetto,urp.cognome AS cognome_responsabile_progetto,
     ura.nome AS nome_responsabile_attivita,ura.cognome AS cognome_responsabile_attivita FROM compiti AS c
    JOIN attivita AS a ON c.id_attivita = a.id_attivita 
    JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
    JOIN utenti AS u ON c.id_operaio = u.id_utente 
    LEFT JOIN utenti AS urp ON pr.id_responsabile = urp.id_utente 
    LEFT JOIN utenti AS ura ON a.id_responsabile = ura.id_utente 
    WHERE c.id_operaio = ? AND c.stato != 'Completato'";
     $compiti=[];
        $stmt_compiti_operaio = $conn->prepare($sql_compiti_operaio);
        $stmt_compiti_operaio->bind_param("i", $id_utente);
        if ($stmt_compiti_operaio->execute()) {
            $result = $stmt_compiti_operaio->get_result();
            while ($row = $result->fetch_assoc()) {
                $compiti[] = $row;
            }
        }

    //AGGIUNTA DELLE ORE LAVORATE E NOTIFICA E MESSAGGIO AL RESPONSABILE DELL'ATTIVITA'
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ore_lavorate = $_POST['ore_lavorate'];
        $id_compito = $_POST['id_compito'];
        $messaggio = $_POST['messaggio'];
        $id_mittente = $_SESSION['id_utente'];
    
        // Prendo info sull'attività e il responsabile
        $sql_info = "SELECT c.id_attivita, a.id_responsabile, a.nome_attivita FROM compiti AS c 
                     JOIN attivita AS a ON c.id_attivita = a.id_attivita  WHERE c.id_compito = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $id_compito);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        $row = $result_info->fetch_assoc();
    
        $id_destinatario = $row['id_responsabile'];
        $nome_attivita = $row['nome_attivita'];
    
        // Aggiorno le ore
        $sql_ore = "UPDATE compiti SET ore_lavorate = ? WHERE id_compito = ?";
        $stmt_ore = $conn->prepare($sql_ore);
        $stmt_ore->bind_param("di", $ore_lavorate, $id_compito);
    
        if ($stmt_ore->execute()) {
            // Messaggio
            $oggetto = "Aggiornamento ore lavorate - Compito #{$id_compito}";
            $sql_msg = "INSERT INTO messaggi (id_mittente, id_destinatario, oggetto, contenuto, data_invio, letto) 
                        VALUES (?, ?, ?, ?, NOW(), 0)";
            $stmt_msg = $conn->prepare($sql_msg);
            $stmt_msg->bind_param("iiss", $id_mittente, $id_destinatario, $oggetto, $messaggio);
            $stmt_msg->execute();
    
            // Notifica
            inviaNotifica($conn, $id_destinatario, "Ore Lavorate", "Sono state inserite nuove ore lavorate per il compito dell'attività 
            \"{$nome_attivita}\".", "gestione_compiti.php");
    
            echo "<script>alert('Ore lavorate inviate con successo.'); window.location.href='ore_lavorate.php';</script>";
            exit;
        } else {
            echo "<script>alert('Errore nell\'invio dei dati.');</script>";
        }
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'attivitaCSS.php'?>
        <title>Ore Lavorate</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
        <!--Intestazione con logo-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Ore Lavorate</h1>
                <div class="div_button">
                <button onclick="location.href='<?php echo $menu; ?>'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>
                </div>
            </div>
            <!--Lista dei compiti-->
            <h2>Lista dei Compiti</h2>
            <div class="container">
                <div class="box_form">
                    <?php $trovate = false; ?>
                    <?php foreach($compiti as $c): ?>
                        <?php if(!$trovate): $trovate = true; ?>
                        <table>
                            <tr>
                                <th>Nome Progetto</th>
                                <th>Responsabile di Progetto</th>
                                <th>Nome Attività</th>
                                <th>Responsabile di Attività</th>
                                <th>Descrizione</th>
                                <th>Stato</th>
                                <th>Ore Lavorate</th>
                                <th>Note</th>
                                <th>Azioni</th>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><?php echo $c['nome_progetto']; ?></td>
                            <td><?php echo $c['nome_responsabile_progetto']." ".$c['cognome_responsabile_progetto']; ?></td>
                            <td><?php echo $c['nome_attivita']; ?></td>
                            <td><?php echo $c['nome_responsabile_attivita']." ".$c['cognome_responsabile_attivita']; ?></td>
                            <td><?php echo $c['descrizione']; ?></td>
                            <td><?php echo $c['stato']; ?></td>
                            <td>
                                <form action="ore_lavorate.php" method="POST">
                                    <input type="hidden" name="id_compito" value="<?php echo $c['id_compito']; ?>">
                                    <div class="parametri">
                                        <label for="ore_lavorate">Ore Lavorate:</label>
                                        <input type="number" name="ore_lavorate" id="ore_lavorate" required>
                                    </div></td> 
                                    <td>
                                    <div class="parametri">    
                                        <label for="messaggio">Note:</label>
                                        <input type="text" id="messaggio" name="messaggio" required>
                                    </div>
                            </td>
                            <td>
                            <!--Bottoni di invio-->
                                <div class="last">
                                    <button type="submit" class="invia"><i class="fas fa-plus"></i>&nbsp;Invia</button>
                                    <a href="javascript:history.back()" class="back2"><button><i class="fas fa-times"></i>&nbsp;Annulla
                                    </button></a>
                                </div>
                            </td>  
                            </form>
                        </tr>
                    <?php endforeach; ?>
                    <?php if($trovate): ?>
                        </table>
                    <?php else: ?>
                        <p><strong>Nessun Compito presente nella lista.</strong></p>
                    <?php endif; ?>
                </div>
            </div>

    </body>
</html>
