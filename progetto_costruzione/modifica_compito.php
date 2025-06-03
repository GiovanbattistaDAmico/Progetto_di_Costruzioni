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
    
    $compiti=[];
    //Select per avere i parametri che occorrono dei compiti
    if($_SESSION['ruolo']=='Responsabile'){
        $sql_compiti_resp_attivita = "SELECT c.*, a. nome_attivita, a.id_attivita, a.id_progetto, 
                                    pr.nome_progetto, pr.id_progetto, u.nome AS nome_operaio, u.cognome AS cognome_operaio, u.id_utente
                                      FROM compiti AS c 
                                      JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                                      JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                                      JOIN utenti AS u ON c.id_operaio = u.id_utente 
                                      WHERE  a.id_responsabile = ?";
        $stmt_compiti_resp_attivita = $conn->prepare($sql_compiti_resp_attivita);
        $stmt_compiti_resp_attivita->bind_param("i" , $_SESSION['id_utente']);
        // Esegui la query per il responsabile dell'attività
        if ($stmt_compiti_resp_attivita->execute()) {
            $result = $stmt_compiti_resp_attivita->get_result();
            while ($row = $result->fetch_assoc()) {
                $compiti[] = $row;
            }
        }
    }

    //Modifica dei compiti
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifica'])){
        $id_compito = $_POST ['id_compito'];
        $old_operaio_id = null;
        $sql_old_operaio = "SELECT id_operaio FROM compiti WHERE id_compito=? ";
        $stmt_old_operaio = $conn->prepare ($sql_old_operaio);
        $stmt_old_operaio ->bind_param("i",$id_compito);
        $stmt_old_operaio->execute();
        $result_old_operaio = $stmt_old_operaio->get_result();
        if ($row_old_operaio = $result_old_operaio->fetch_assoc()) {
            $old_operaio_id = $row_old_operaio['id_operaio'];
        }
        $id_operaio = $_POST['operaio_id'];
        $stato = $_POST['stato'];
        $costo_effettivo = $_POST['costo_effettivo'];
           
        $sql_modifica = "UPDATE compiti  SET id_operaio=? , stato =? ,costo_effettivo=? WHERE id_compito=?";
        $stmt_modifica = $conn -> prepare($sql_modifica);
        $stmt_modifica->bind_param("isdi", $id_operaio,$stato,$costo_effettivo,$id_compito);
        if($stmt_modifica->execute()){
            // Se il compito viene completato, aggiorniamo il costo_effettivo dell'attività
            if ($stato == 'Completato') {
                // Recuperiamo l'attività a cui appartiene il compito
                $sql_get_attivita = "SELECT id_attivita FROM compiti WHERE id_compito=?";
                $stmt_get_attivita = $conn->prepare($sql_get_attivita);
                $stmt_get_attivita->bind_param("i", $id_compito);
                $stmt_get_attivita->execute();
                $stmt_get_attivita->bind_result($id_attivita);
                $stmt_get_attivita->fetch();
                $stmt_get_attivita->close();

                // Aggiorniamo il costo_effettivo dell'attività sommando il costo del compito
                $sql_update_attivita = "UPDATE attivita SET costo_effettivo = costo_effettivo + ? WHERE id_attivita = ?";
                $stmt_update_attivita = $conn->prepare($sql_update_attivita);
                $stmt_update_attivita->bind_param("di", $costo_effettivo, $id_attivita);
                $stmt_update_attivita->execute();
                $stmt_update_attivita->close();
            }
            echo "<script>alert('Modifica del compito avvenuta con successo!'); window.location.href = 'modifica_compito.php';</script>";
            if($id_operaio==$old_operaio_id){
                inviaNotifica($conn,$id_operaio,"Modifica Compito","Sono stati modificati alcuni parametri del tuo compito", "gestione_compiti.php");
            }else{
                inviaNotifica($conn,$id_operaio,"Nuovo Compito ","Sei stato assegnato al compito di un'attività","gestione_compiti.php");
                inviaNotifica($conn,$old_operaio_id,"Rimozione Compito","Sei stato rimosso come Responsabile di progetto di un'attività", "gestione_compiti.php");

            }
        }   
        //Eliminazione dei compiti
    }elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['elimina'])){
        $id_compito=$_POST['id_compito'];
        $sql_get_operaio = "SELECT id_operaio FROM compiti WHERE id_compito = ?";
        $stmt_get_operaio = $conn->prepare($sql_get_operaio);
        $stmt_get_operaio->bind_param("i", $id_compito);
        $stmt_get_operaio->execute();
        $result_get_operaio = $stmt_get_operaio->get_result();
        if ($row = $result_get_operaio->fetch_assoc()) {
            $id_operaio = $row['id_operaio'];
        }
        $sql_elimina="DELETE FROM compiti WHERE id_compito=?";
        $stmt_elimina=$conn->prepare($sql_elimina);
        $stmt_elimina->bind_param("i",$id_compito);
        if($stmt_elimina->execute()){
            echo "<script>alert('Eliminazione del compito avvenuta con successo!'); window.location.href = 'modifica_compito.php';</script>";
            inviaNotifica($conn,$id_operaio,"Compito Eliminato","E' stato eliminato il compito al quale eri stato assegnato","gestione_compiti.php");

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
        <title>Modifca Compiti</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!--Intestazione con logo-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Modifica Compiti</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
        <!--Parte del Responsabile con lista dei compiti creati-->
        <div class="lista_attivita" id="create">
    <h1>Elenco Compiti In Corso</h1>
    <?php 
    $trovate = false;
    foreach($compiti as $compito):
        if (!$trovate): 
            $trovate = true; ?>
            <table>
                <tr>
                    <th>Nome Progetto</th>
                    <th>Nome Attività</th>
                    <th>Descrizione Compito</th>
                    <th>Operaio</th>
                    <th>Stato</th>
                    <th>Costo Effettivo</th>
                    <th>Azioni</th>
                </tr>
        <?php endif; ?>

        <form action="modifica_compito.php" method="POST">
            <tr>
                <td><?php echo $compito['nome_progetto']; ?></td>
                <td><?php echo $compito['nome_attivita']; ?></td>
                <td><?php echo $compito['descrizione']; ?></td>
                <td>
                    <select id="operaio" name="operaio_id">
                        <option value="<?php echo $compito['id_utente']; ?>">
                            <?php echo $compito['nome_operaio'] . " " . $compito['cognome_operaio']; ?>
                        </option> 
                        <?php foreach($responsabili as $responsabile): ?>
                            <?php if ($responsabile['id_utente'] != $compito['id_responsabile']): ?>
                                <option value="<?php echo $responsabile['id_utente']; ?>">
                                    <?php echo $responsabile['nome'] . " " . $responsabile['cognome']; ?>
                                </option>               
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select id="stato" name="stato" onchange="showMotivoSospensione(this)"> 
                        <option value="<?php echo $compito['stato']; ?>"><?php echo $compito['stato']; ?></option> 
                        <option value="Non Iniziato">Non Iniziato</option> 
                        <option value="In Corso">In Corso</option>  
                        <option value="Sospeso">Sospeso</option> 
                        <option value="Completato">Completato</option>  
                    </select>
                </td>
                <td><input type="number" value="<?php echo $compito['costo_effettivo']; ?>" name="costo_effettivo"> €</td>
                <td>
                    <input type="hidden" name="id_compito" value="<?php echo $compito['id_compito']; ?>">
                    <button type="submit" onclick="return confirm('Sei sicuro di voler modificare questa attività?');" name="modifica">Modifica</button>
                    <button type="submit" onclick="return confirm('Sei sicuro di voler eliminare questa attività?');" name="elimina">Elimina</button>
                </td>
            </tr>
        </form>

    <?php endforeach; ?>

    <?php if ($trovate): ?>
        </table>
    <?php else: ?>
        <p><strong>Nessuna attività attualmente in corso.</strong></p>
    <?php endif; ?>
</div>
    </body>
</html>