<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin(); 
    $compiti = [];
    $nome_responsabile = $_SESSION['nome'];
    $cognome_responsabile = $_SESSION['cognome'];

        //Select dei compiti del responsabile
        $sql_compiti_resp_attivita = "SELECT c.id_compito, c.descrizione, c.id_attivita, a.id_responsabile
                                    FROM compiti AS c
                                    JOIN attivita AS a ON c.id_attivita = a.id_attivita
                                    WHERE a.id_responsabile = ?";
        $stmt_compiti_resp_attivita = $conn->prepare($sql_compiti_resp_attivita);
        $stmt_compiti_resp_attivita->bind_param("i", $_SESSION['id_utente']);
        if ($stmt_compiti_resp_attivita->execute()) {
            $result = $stmt_compiti_resp_attivita->get_result();
            while ($row = $result->fetch_assoc()) {
                $compiti[] = $row;
            }
        }

        //Select per i magazzinieri
        $magazzinieri = [];
        $sql_magazziniere = "SELECT id_utente, nome, cognome FROM utenti WHERE ruolo = 'Magazziniere'";
        $stmt_magazziniere = $conn->query($sql_magazziniere);
        while($row = $stmt_magazziniere->fetch_assoc()) {
            $magazzinieri[] = $row;
        }

        //Select per i materiali restitubili per il responsabile
        $materiali_restituibili = [];
        $sql_uscite = "SELECT  m.id_movimento, m.id_risorsa, m.quantita AS quantita_utilizzata,
                      mat.nome, mat.unita_misura,c.id_compito,c.descrizione
               FROM movimenti_materiali AS m
               JOIN materiali_attrezzature AS mat ON m.id_risorsa = mat.id
               JOIN compiti AS c ON m.id_compito = c.id_compito
               WHERE m.tipo = 'Uscita' AND m.id_mittente = ?
               AND m.quantita > 0";


        $stmt_uscite = $conn->prepare($sql_uscite);
        $stmt_uscite->bind_param("i", $_SESSION['id_utente']);
        if ($stmt_uscite->execute()) {
            $result = $stmt_uscite->get_result();
            while ($row = $result->fetch_assoc()) {
                $materiali_restituibili[] = $row;
            }
        }

        // Gestione restituzione/scarto
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_risorsa = $_POST['id_risorsa'];
            $id_mittente = $_SESSION['id_utente'];
            $id_destinatario = $_POST['id_magazziniere'];
            $id_compito = $_POST['id_compito'];
            $tipo = $_POST['tipo'];
            $quantita = $_POST['quantita'];
            $note = $_POST['note'];
            $stato = 'In Attesa';

            $sql_movimento = "INSERT INTO movimenti_materiali (id_risorsa,id_mittente,id_destinatario,id_compito, quantita, tipo, stato, note)
                            VALUES (?, ?, ?, ?, ?, ?,?,?)";
            $stmt_movimento = $conn->prepare($sql_movimento);
            $stmt_movimento->bind_param("iiiidsss", $id_risorsa, $id_mittente,$id_destinatario,$id_compito, $quantita, $tipo, $stato, $note);

            if ($stmt_movimento->execute()) {
                echo "Richiesta registrata";
                if ($tipo == 'Reso') {
                    inviaNotifica($conn, $id_destinatario, "Richiesta di Reso", "È stata ricevuta una richiesta di reso dal Responsabile " . $nome_responsabile . " " . $cognome_responsabile,"gestione_mat_e_attr.php");
                } else {
                    inviaNotifica($conn, $id_destinatario, "Richiesta di Scarto", "È stata ricevuta una richiesta di scarto dal Responsabile " . $nome_responsabile . " " . $cognome_responsabile,"gestione_mat_e_attr.php");
                }
            }
        }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestione Restituzioni e Scarti</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <?php include "materialiCSS.php" ?>
        <?php include 'progettiCSS.php'?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>

        <!--Intestazione con logo-->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Gestione Restituzioni e Scarti</h1>
            <div class="div_button">
            <button onclick="location.href='richieste_mat.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
            </div>
        </div>
        <!--Sezione form con i campi per la restituzione/scarto-->
            <div class="container">
            <div class="box_form">
                <form action="restituzione_mat.php" method="POST">
                    <div class="parametri">
                    <label for="id_compito">Seleziona Compito:</label>
                    <select name="id_compito" id="id_compito" required>
                        <option value="" selected disabled>-- Seleziona --</option>
                        <?php foreach ($compiti as $compito): ?>
                            <option value="<?php echo $compito['id_compito']; ?>">
                                <?php echo $compito['descrizione'] . " ( ID:" .$compito['id_compito'].")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="parametri">
                    <label for="materiale">Materiale/Attrezzatura:</label>
                    <select id="materiale" name="id_risorsa" required>
                        <option value="" selected disabled></option>
                        <?php foreach ($materiali_restituibili as $mat): ?>
                            <option value="<?php echo $mat['id_risorsa']; ?>">
                                <?php echo $mat['nome']; ?> (Usato: <?php echo $mat['quantita_utilizzata']." ".$mat['unita_misura'] ." in compito ID: ".$compito['id_compito']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                <div class="parametri">
                    <label for="magazziniere">Magazziniere:</label>
                    <select id="magazziniere" name="id_magazziniere" required>
                        <?php foreach ($magazzinieri as $mag): ?>
                            <option value="<?php echo $mag['id_utente']; ?>">
                                <?php echo $mag['nome'] . ' ' . $mag['cognome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="parametri">
                    <label for="tipo">Tipo:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Reso">Reso</option>
                        <option value="Scarto">Scarto</option>
                    </select>
                </div>
                <div class="parametri">
                    <label for="quantita">Quantità:</label>
                    <input id="quantita" type="number" name="quantita" min="1" required>
                </div>
                <div class="parametri">
                    <label for="note">Note:</label>
                    <textarea id="note" name="note" required></textarea>
                </div>

                <!--Parte finale con bottoni-->
                     <div class="last">
                        <button type="submit" class="crea"onclick="return confirm('Sei sicuro di voler inviare questa richiesta?');" ><i class="fas fa-plus"></i>&nbsp;Invia</button>
                        <a href="javascript:history.back()" class="back2"><i class="fas fa-times"></i>&nbsp;Annulla</a>
                    </form> 
                </div> 
            </div>
        </div>

    </body>
</html>