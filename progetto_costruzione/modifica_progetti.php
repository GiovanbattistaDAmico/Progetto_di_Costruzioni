<?php
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $ruolo = $_SESSION['ruolo'];
    $id_azienda = $_SESSION['id_azienda']; 
    $id_utente = $_SESSION['id_utente'];
    $link = $_SERVER['REQUEST_URI'];
    $id_utente = $_SESSION['id_utente'];
    $tipo_utente = $_SESSION['tipo_utente'];

    //Select per i progetti dell'azienda
    $progetti = [];
    if($tipo_utente == 'Azienda'){
        $sql="SELECT pr.*,c.nome AS committente_nome ,c.cognome AS committente_cognome,r.nome AS responsabile_nome,r.cognome AS 
        responsabile_cognome FROM progetti AS pr JOIN utenti AS c ON pr.id_committente=c.id_utente JOIN utenti AS r ON pr.id_responsabile=
        r.id_utente WHERE pr.id_azienda=? AND (pr.id_responsabile = ? OR ? = 'Azienda')";
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza->bind_param("iis", $id_azienda, $id_utente, $tipo_utente);
    }
    $sql_visualizza->execute();
    $result = $sql_visualizza->get_result();
    while ($progetto = $result->fetch_assoc()) {
        $progetti[] = $progetto;
    }

    //Selezione di tutti i responsabili dell'azienda
    if($tipo_utente=='Azienda'){
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

    //Azione di modifica di un progetto
    if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['modifica'])){
        $id_progetto = $_POST['id_progetto'];
        //Recupero del committente per l'invio delle notifiche
        $stmt = $conn->prepare("SELECT id_committente FROM progetti WHERE id_progetto = ?");
        $stmt->bind_param("i", $id_progetto);
        $stmt->execute();
        $stmt->bind_result($id_committente);
        $stmt->fetch();
        $stmt->close();
        $old_responsabile_id = null;
        $sql_old_responsabile = "SELECT id_responsabile FROM progetti WHERE id_progetto=? ";
        $stmt_old_responsabile = $conn->prepare ($sql_old_responsabile);
        $stmt_old_responsabile ->bind_param("i",$id_progetto);
        $stmt_old_responsabile->execute();
        $result_old_responsabile = $stmt_old_responsabile->get_result();
        if ($row_old_responsabile = $result_old_responsabile->fetch_assoc()) {
            $old_responsabile_id = $row_old_responsabile['id_responsabile'];
        }
        $data_inizio=$_POST['data_inizio'];
        $data_scadenza=$_POST['data_scadenza'];
        $budget=$_POST['budget'];
        $id_responsabile=$_POST['id_responsabile'];
        $costo_effettivo = $_POST['costo_effettivo'];
        $stato=$_POST['stato'];
        if($tipo_utente == 'Azienda'){
            if ($stato == 'Completato') {
                $oggi = date('Y-m-d');
                $sql_modifica = "UPDATE progetti SET data_inizio=?, data_scadenza=?, budget=?, id_responsabile=?, stato=?, data_fine_effettiva=? , costo_effettivo=? WHERE id_progetto=?";
                $stmt_modifica = $conn->prepare($sql_modifica);
                $stmt_modifica->bind_param("sssissii", $data_inizio, $data_scadenza, $budget, $id_responsabile, $stato, $oggi,$costo_effettivo,$id_progetto);
            } else {
                $sql_modifica = "UPDATE progetti SET data_inizio=?, data_scadenza=?, budget=?, id_responsabile=?, stato=? WHERE id_progetto=?";
                $stmt_modifica = $conn->prepare($sql_modifica);
                $stmt_modifica->bind_param("sssisi", $data_inizio, $data_scadenza, $budget, $id_responsabile, $stato, $id_progetto);
            }
        }if($stmt_modifica->execute()){
            echo "<script>alert('Modifica avvenuta con successo!'); window.location.href = 'modifica_progetti.php';</script>";
            inviaNotifica($conn,$id_responsabile,"Progetto Modificato","Ti è stato assegnato un nuovo Progetto","gestione_progetti.php");
            inviaNotifica($conn,$id_committente,"Progetto Modificato","E' stato modificato il progetto da te richiesto","gestione_progetti.php");
            inviaNotifica($conn,$old_responsabile_id,"Progetto Modificato","Sei stato rimosso dall'incarico di Responsabile del progetto","gestione_progetti.php");
        } else {
            echo "Errore nella modifica del progetto: " . $stmt_modifica->error;
        }
    }

    //Eliminazione del progetto
    if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['elimina'])){
        $id_progetto = $_POST['id_progetto'];
        //Recupero del committente per l'invio delle notifiche
        $stmt = $conn->prepare("SELECT id_committente FROM progetti WHERE id_progetto = ?");
        $stmt->bind_param("i", $id_progetto);
        $stmt->execute();
        $stmt->bind_result($id_committente);
        $stmt->fetch();
        $stmt->close();
        $sql_responsabile_committente = "SELECT id_responsabile, id_committente FROM progetti WHERE id_progetto=?";
        $stmt_responsabile_committente = $conn->prepare($sql_responsabile_committente);
        $stmt_responsabile_committente->bind_param("i", $id_progetto);
        $stmt_responsabile_committente->execute();
        $result_responsabile_committente = $stmt_responsabile_committente->get_result();
        $row = $result_responsabile_committente->fetch_assoc();

        $id_responsabile = $row['id_responsabile'];
        $id_committente = $row['id_committente'];
        $sql_elimina = "DELETE FROM progetti WHERE id_progetto=?";
        $stmt_elimina =$conn->prepare($sql_elimina);
        $stmt_elimina->bind_param("i",$id_progetto);
        if($stmt_elimina->execute()){
            inviaNotifica($conn,$id_responsabile,"Progetto Eliminato","Il progetto al quale eri stato assegnato è stato elimato","gestione_progetti.php");
            inviaNotifica($conn,$id_committente,"Progetto Eliminato","E' stato eliminato il progetto da te richiesto","gestione_progetti.php");
            echo "<script>alert('Eliminazione del progetto avvenuta con successo!'); window.location.href = 'modifica_progetti.php';</script>";

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
        <title>Gestione Progetti</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!--Intestazione con logo-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Gestione Progetti</h1>
                <div class="div_button">
                <button onclick='location.href="gestione_progetti.php"' class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Lista dei progetti con parametri modificabili-->
            <div class="lista_progetti" id="progetti">
            <h1>Elenco Progetti</h1>
            <?php if(count($progetti) > 0): ?>
                <table>
                    <tr>
                        <th>Nome Progetto</th>
                        <th>Descrizione</th>
                        <th>Data Inizio</th>
                        <th>Data Scadenza</th>
                        <th>Budget</th>
                        <?php if($tipo_utente == 'Azienda' || $tipo_utente == 'Libero Professionista'|| $tipo_utente == 'Dipendente Aziendale'): ?>
                            <th>Committente</th>
                            <?php endif; ?>
                      
                        <?php if($tipo_utente == 'Azienda' || $tipo_utente == 'Libero Professionista'||$tipo_utente == 'Committente'): ?>
                            <th>Responsabile</th>
                        <?php endif; ?>
                        <th>Stato</th>
                        <td>Costo Effettivo(€)</th>
                        <th>Azioni</th>
                    </tr>
                    <?php foreach($progetti as $progetto): ?>
                    <form action="modifica_progetti.php" method="POST">
                        <tr>
                            <td><?php echo $progetto['nome_progetto']; ?></td>
                            <td><?php echo $progetto['descrizione']; ?></td>
                            <td><input type="date" value="<?php echo $progetto['data_inizio']; ?>" name="data_inizio"></input></td>
                            <td><input type="date" value="<?php echo $progetto['data_scadenza']; ?>" name="data_scadenza"></input></td>
                            <td><input type="number" value="<?php echo $progetto['budget']; ?>" name="budget"></input></td>
                            <td><?php echo $progetto['committente_nome'] . " " . $progetto['committente_cognome']; ?></td>
                            <?php if($tipo_utente == 'Libero Professionista'): ?>
                                <td><?php echo $progetto['responsabile_nome'] . " " . $progetto['responsabile_cognome']; ?></td>
                            <?php else: ?>
                            <td>
                                <select id="responsabile" name="id_responsabile">
                                    <option value="<?php echo $progetto['id_responsabile'] ?>">
                                    <?php echo $progetto['responsabile_nome'] . " " . $progetto['responsabile_cognome']; ?>
                                    </option> 
                                    <?php foreach($responsabili as $responsabile):?>
                                        <?php if($responsabile['nome'] != $progetto['responsabile_nome'] && $responsabile['cognome'] != $progetto['responsabile_cognome']):?>
                                            <option value="<?php echo $responsabile['id_utente'];?>">
                                                <?php echo $responsabile['nome']." ".$responsabile['cognome'];?>
                                            </option>   
                                        <?php endif; ?>             
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                            </td> 
                            <td><select id="stato" name="stato">
                                <option value="<?php echo $progetto['stato']?>"><?php echo $progetto['stato']?></option>
                                <option value="Non Iniziato">Non Iniziato</option>
                                <option value="In Corso">In Corso</option>
                                <option value="In sospeso">In Sospeso</option>
                                <option value="Annullato">Annullato</option>
                                <option value="Completato">Completato</option>
                            </select>
                            </td>
                                <td><input type="number" value="<?php echo $progetto['costo_effettivo']; ?>" name="costo_effettivo"></input>
                            <td>
                                <input type="hidden" name="id_progetto" value =<?php echo $progetto['id_progetto']?> ></input>
                                <button type="submit" onclick="return confirm('Sei sicuro di voler modificare questo progetto?');" name="modifica">Modifica</button>
                                <input type="hidden" name="id_progetto" value =<?php echo $progetto['id_progetto']?> ></input>
                                <button type="submit" onclick="return confirm('Sei sicuro di voler eliminare questo progetto?');"  name="elimina">Elimina</button>
                            </td>
                        </tr>
                    </form>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p><strong>Nessun Progetto attualmente in corso.</strong></p>
            <?php endif; ?>
 </div>
</body>
</html>
