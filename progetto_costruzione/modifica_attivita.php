<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $ruolo = $_SESSION['ruolo'];
    $id_azienda = $_SESSION['id_azienda']; 
    $id_utente = $_SESSION['id_utente'];
    $link = $_SERVER['REQUEST_URI'];

    $attivita=[];
    $attivita_responsabili=[];
    //Select per avere i parametri che occorrono
    if($_SESSION['ruolo']=='Responsabile'){
        $sql_attivita_responsabile="SELECT a.*,u.id_utente,u.nome AS nome_responsabile,u.cognome AS cognome_responsabile,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN utenti AS u ON a.id_responsabile = u.id_utente JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto WHERE pr.id_responsabile=? OR a.id_responsabile = ?";
        $stmt_attivita_responsabile=$conn->prepare($sql_attivita_responsabile);
        $stmt_attivita_responsabile->bind_param("ii",$id_utente,$id_utente);
        $stmt_attivita_responsabile->execute();
        $result=$stmt_attivita_responsabile->get_result();
        while($row = $result->fetch_assoc()){
            $attivita_responsabili[]=$row;
        }
    }

    //Select per ottenere i responsabili dell'azienda
    if($ruolo=='Responsabile'){
        $sql = "SELECT id_utente, nome, cognome FROM utenti WHERE id_azienda = ? AND ruolo = 'Responsabile'";
        $sql_selezione = $conn->prepare($sql);
        $sql_selezione->bind_param("i", $id_azienda);
        $sql_selezione->execute();
        $result = $sql_selezione->get_result();
    
        $responsabili = [];
        while ($row = $result->fetch_assoc()) {
            $responsabili[] = $row; // Memorizza i responsabili
            }   
        }
    if(isset($_POST['elimina'])){
        $id_attivita = $_POST['id_attivita'];

    // Recupera prima l'id_responsabile prima di eliminare
    $sql_get_responsabile = "SELECT id_responsabile FROM attivita WHERE id_attivita=?";
    $stmt_get_responsabile = $conn->prepare($sql_get_responsabile);
    $stmt_get_responsabile->bind_param("i", $id_attivita);
    $stmt_get_responsabile->execute();
    $result_responsabile = $stmt_get_responsabile->get_result();
    $row_responsabile = $result_responsabile->fetch_assoc();
    $id_responsabile = $row_responsabile['id_responsabile']; // Adesso è definito correttamente

    //eliminaazione dell'attivita
    $sql_elimina = "DELETE FROM attivita WHERE id_attivita=?";
    $stmt_elimina = $conn->prepare($sql_elimina);
    $stmt_elimina->bind_param("i", $id_attivita);
    if($stmt_elimina->execute()){
        if($_SESSION['ruolo'] == 'Responsabile' && !empty($id_responsabile)){
            inviaNotifica($conn, $id_responsabile, "Attività Eliminata", "L'attività alla quale eri stato assegnato è stata eliminata", "gestione_attivita.php");
        }
        echo "<script>alert('Eliminazione dell\'attività avvenuta con successo!'); window.location.href = 'modifica_attivita.php';</script>";
        }
    }

    //Modifica dell'attività
    if(isset($_POST['modifica'])){
        $id_attivita = $_POST['id_attivita'];    
        $old_responsabile_id = null;
        $sql_old_responsabile = "SELECT id_responsabile FROM attivita WHERE id_attivita=? ";
        $stmt_old_responsabile = $conn->prepare ($sql_old_responsabile);
        $stmt_old_responsabile ->bind_param("i",$id_attivita);
        $stmt_old_responsabile->execute();
        $result_old_responsabile = $stmt_old_responsabile->get_result();
        if ($row_old_responsabile = $result_old_responsabile->fetch_assoc()) {
            $old_responsabile_id = $row_old_responsabile['id_responsabile'];
        }
        $stato=$_POST['stato'];
        $costo_effettivo=$_POST['costo_effettivo'];
        // Verifica se l'utente è un Responsabile e sta modificando l'attività
        if ($_SESSION['ruolo'] == 'Responsabile' && $old_responsabile_id != $_POST['id_responsabile']) {
            $id_responsabile = $_POST['id_responsabile']; // Nuovo responsabile dell'attività

            // Aggiorna l'attività con il nuovo responsabile
            $sql_modifica = "UPDATE attivita SET stato=?,costo_effettivo=?, 
            id_responsabile=? WHERE id_attivita=?";
            $stmt_modifica = $conn->prepare($sql_modifica);
            $stmt_modifica->bind_param("sdii", $stato,$costo_effettivo, $id_responsabile, $id_attivita);
        } else {
            // Se l'utente non è un Responsabile, non cambiamo il responsabile
            $id_responsabile = $old_responsabile_id; // Se non viene cambiato
            $sql_modifica = "UPDATE attivita SET  stato=?, costo_effettivo=? WHERE id_attivita=?";
            $stmt_modifica = $conn->prepare($sql_modifica);
            $stmt_modifica->bind_param("sdi", $stato,$costo_effettivo, $id_attivita);
        }

        // Esegue la query
        if ($stmt_modifica->execute()) {
            // Se l'attività viene completata, aggiorniamo il costo_effettivo del progetto
        if ($stato == 'Conclusa') {
            // Recuperiamo il progetto a cui appartiene l'attività
            $sql_get_progetto = "SELECT id_progetto FROM attivita WHERE id_attivita=?";
            $stmt_get_progetto = $conn->prepare($sql_get_progetto);
            $stmt_get_progetto->bind_param("i", $id_attivita);
            $stmt_get_progetto->execute();
            $stmt_get_progetto->bind_result($id_progetto);
            $stmt_get_progetto->fetch();
            $stmt_get_progetto->close();

            // Aggiorna il costo_effettivo del progetto sommando il costo dell'attività
            $sql_update_progetto = "UPDATE progetti SET costo_effettivo = costo_effettivo + ? WHERE id_progetto = ?";
            $stmt_update_progetto = $conn->prepare($sql_update_progetto);
            $stmt_update_progetto->bind_param("di", $costo_effettivo, $id_progetto);
            $stmt_update_progetto->execute();
            $stmt_update_progetto->close();
        }
            // Invia notifiche se è stato modificato il responsabile
            if ($_SESSION['ruolo'] == 'Responsabile' && $old_responsabile_id != $_POST['id_responsabile']) {
                inviaNotifica($conn, $id_responsabile, "Nuova Attività", "Sei stato assegnato come Responsabile di attività di un
                progetto", "gestione_attivita.php");
                inviaNotifica($conn, $old_responsabile_id, "Rimozione Attività", "Sei stato rimosso come Responsabile di attività di 
                un progetto", "gestione_attivita.php");
            }else{
                inviaNotifica($conn, $id_responsabile, "Modifica Attività", "Sono stati modificati alcuni parametri della tua attività",
                 "gestione_attivita.php");
            }
            echo "<script>alert('Modifica dell\'attività avvenuta con successo!'); window.location.href = 'modifica_attivita.php';</script>";

        }
    } 

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'progettiCSS.php'?>
        <title>Modifca Attività</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!--Intestazione con logo -->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Modifica Attività</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
        <!--Parte del Responsabile con lista dell'attività e informazioni legate ad essa-->
        <div class="lista_attivita" id="create">
        <h1>Elenco Attività</h1>
        <?php 
        $trovate = false;
        foreach($attivita_responsabili as $a):
                if(!$trovate): $trovate = true; ?>
                <table>
                    <tr>
                        <th>Nome Progetto</th>
                        <th>Nome Attività</th>
                        <th>Descrizione</th>
                        <th>Responsabile Attività</th>
                        <th>Stato</th>
                        <th>Costo Effettivo</th>
                        <th>Azioni</th>
                    </tr>
                <?php endif; ?>

                <form action="modifica_attivita.php" method="POST">
                <tr>
                    <td><?php echo $a['nome_progetto']; ?></td>
                    <td><?php echo $a['nome_attivita']; ?></td>
                    <td><?php echo $a['descrizione']; ?></td>
                    <td>
                        <select id="responsabile" name="id_responsabile">
                        <option value="<?php echo $a['id_responsabile']; ?>">
                                <?php echo $a['nome_responsabile'] . " " . $a['cognome_responsabile']; ?>
                            </option> 
                            <?php foreach($responsabili as $responsabile): ?>
                                <?php if($responsabile['id_utente'] != $a['id_responsabile']): ?>
                                    <option value="<?php echo $responsabile['id_utente']; ?>">
                                        <?php echo $responsabile['nome'] . " " . $responsabile['cognome']; ?>
                                    </option>   
                                <?php endif; ?>             
                            <?php endforeach; ?>
                        </select>
                        <td>
                        <select id="stato" name="stato" onchange="showMotivoSospensione(this)"> 
                        <option value="<?php echo $a['stato']; ?>"><?php echo $a['stato']; ?></option> 
                        <option value="Non Iniziata">Non Iniziata</option>  
                        <option value="In Corso">In Corso</option>  
                        <option value="Sospesa">Sospesa</option> 
                        <option value="Conclusa">Conclusa</option>  
                        </select>
                    <td><input type="number" value="<?php echo $a['costo_effettivo']; ?>" name="costo_effettivo"></td>
                    <td>
                        <input type="hidden" name="id_attivita" value="<?php echo $a['id_attivita']; ?>">
                        <button type="submit" onclick="return confirm('Sei sicuro di voler modificare questa attività?');" name="modifica">Modifica</button>
                        <button type="submit" onclick="return confirm('Sei sicuro di voler eliminare questa attività?');" name="elimina">Elimina</button>
                    </td>
                </tr>
            </form>

        <?php endforeach; ?>

        <?php if($trovate): ?>
            </table>
        <?php else: ?>
            <p><strong>Nessuna attività.</strong></p>
        <?php endif; ?>
    </div>
    </body>
</html>