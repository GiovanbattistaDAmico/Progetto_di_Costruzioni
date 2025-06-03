<?php 
    require 'db.php';
    session_start();

    //Controllo del ruolo
    if($_SESSION['ruolo']=='Magazziniere'){
        $id_azienda = $_SESSION['id_azienda'];
    }

    //Select per i materiali dell'azienda
    $sql = "SELECT * FROM materiali_attrezzature WHERE id_azienda = ?";
    $sql_selezione = $conn->prepare($sql);
    $sql_selezione ->bind_param("i",$id_azienda);
    $sql_selezione -> execute();
    $result = $sql_selezione->get_result();
    $materiali = [];
    while ($row = $result->fetch_assoc()) {
        $materiali[] = $row;
    }
    $sql_selezione->close();

    //In caso di modifica
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['azione'])) {
        $azione = $_POST['azione'];
    
        if ($azione == 'modifica') {
            $quantita = $_POST['quantita'];
            $stato = $_POST['stato'];
            $ubicazione = $_POST['ubicazione'];
            $id = $_POST['id'];
            $scorta_minima = $_POST['scorta_minima'];
    
            $sql2 = "UPDATE materiali_attrezzature SET quantita = ?, stato = ?, ubicazione = ?, scorta_minima = ? WHERE id = ?";
            $sql_modifica = $conn->prepare($sql2);
            $sql_modifica->bind_param("issdi", $quantita, $stato, $ubicazione, $scorta_minima, $id);
            
            if ($sql_modifica->execute()) {
                echo "<script>alert('Materiale aggiornato con successo!'); window.location.href='gestione_mat_e_attr.php';</script>";
            } else {
                echo "<script>alert('Errore durante l\'aggiornamento del materiale.');</script>";
            }
            $sql_modifica->close();
        }
        //Caso di eliminazione
        elseif ($azione == 'elimina') {
            $id_risorsa = $_POST['id'];
        
            // Recupera il materiale
            $sql_get_materiale = "SELECT * FROM materiali_attrezzature WHERE id = ?";
            $sql_get = $conn->prepare($sql_get_materiale);
            $sql_get->bind_param("i", $id_risorsa);
            $sql_get->execute();
            $result = $sql_get->get_result();
            $materiale = $result->fetch_assoc();
        
            if ($materiale) {
                $quantita_scartata = $materiale['quantita'];
                $id_mittente = $_SESSION['id_utente'];
                $tipo = 'Scarto';
                $stato = 'Approvato';
                $note= 'Scarto del materiale (quantità impostata a 0)';
        
                // Inserisce un movimento di scarto
                $sql_movimento = "INSERT INTO movimenti_materiali (id_risorsa, id_mittente, quantita, tipo, stato, note) 
                                  VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_movimento = $conn->prepare($sql_movimento);
                $stmt_movimento->bind_param("iiisss", $id_risorsa, $id_mittente, $quantita_scartata, $tipo, $stato, $note);
        
                if ($stmt_movimento->execute()) {
                    // Imposta la quantità del materiale a 0
                    $sql_update = "UPDATE materiali_attrezzature SET quantita = 0 WHERE id=?";
                    $sql_update_stmt = $conn->prepare($sql_update);
                    $sql_update_stmt->bind_param("i", $id_risorsa);
        
                    if ($sql_update_stmt->execute()) {
                        echo "<script>alert('Materiale scartato con successo (quantità impostata a 0)!'); window.location.href='gestione_mat_e_attr.php';</script>";
                    } else {
                        echo "<script>alert('Errore durante l\'aggiornamento della quantità del materiale.');</script>";
                    }
                    $sql_update_stmt->close();
                } else {
                    echo "<script>alert('Errore durante la registrazione del movimento di scarto.');</script>";
                }
                $stmt_movimento->close();
            } else {
                echo "<script>alert('Materiale non trovato.');</script>";
            }
            $sql_get->close();
        }
    }        
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifica Materiali e Attrezzature</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <?php include "materialiCSS.php" ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
        <!--Intestazione con logo-->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Gestione Materiali e Attrezzature</h1>
            <div class="div_button">
            <button onclick="location.href='gestione_mat_e_attr.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
            </div>
        </div>
        <!--Lista dei materiali e attrezzature dell'azienda-->
        <div id="lista_materiali" class="lista_materiali">
            <h1>Lista Materiali e Attrezzature</h1>
            <?php if(!empty($materiali)): ?>
            <table>
                <tr> 
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Tipo</th>
                    <th>Quantità</th>
                    <th>Unità di Misura</th>
                    <th>Stato</th>
                    <th>Ubicazione</th> 
                    <th>Scorta Minima</th> 
                    <th>Opzioni</th>                
                </tr>
                <?php foreach ($materiali as $materiale): ?>
                    <tr>
                <td><?php echo ($materiale['nome']); ?></td>
                <td><?php echo ($materiale['descrizione']); ?></td>
                <td><?php echo ($materiale['categoria']); ?></td>
                <td>
                    <form action="modifica_mat.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $materiale['id']; ?>">
                        <input type="hidden" name="azione" value="modifica">
                        <input type="number" name="quantita" value="<?php echo $materiale['quantita']; ?>" required>
                </td>
                <td><?php echo ($materiale['unita_misura']); ?></td>
                <td>
                        <select name="stato" required>
                            <option value="Disponibile" <?php if($materiale['stato'] == 'Disponibile') echo 'selected'; ?>>Disponibile</option>
                            <option value="In Uso" <?php if($materiale['stato'] == 'In Uso') echo 'selected'; ?>>In Uso</option>
                            <option value="Non Disponibile" <?php if($materiale['stato'] == 'Non Disponibile') echo 'selected'; ?>>Non Disponibile</option>
                        </select>
                </td>
                <td>
                        <input type="text" name="ubicazione" value="<?php echo ($materiale['ubicazione']); ?>" required>
                </td>
                <td>
                        <input type="number" name="scorta_minima" value="<?php echo $materiale['scorta_minima']; ?>" required>
                </td>
                <td>
                        <button type="submit" name="azione" value="modifica"  onclick="return confirm('Confermi le modifiche?')">Salva</button>
                    </form>
                    <form action="modifica_mat.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $materiale['id']; ?>">
                        <input type="hidden" name="azione" value="elimina">
                        <button type="submit" onclick="return confirm('Sei sicuro di voler eliminare questo materiale?')">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
            </table>
            <?php else: ?>                    
                <p> Non ci sono Materiali o Attrezzature salvate.</p>
               <?php endif; ?>
        </div>
    </body>
</html>