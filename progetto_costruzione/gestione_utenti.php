<?php
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    //Assegnazione alla variabile il link del menu per il tipo di utente
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $id_utente = $_SESSION['id_utente'];
    $tipo_utente = $_SESSION['tipo_utente'];
    $link = $_SERVER['REQUEST_URI'];

    //Eliminazione delle notifiche riguardanti la pagina 
    rimuoviNotifiche($conn,$id_utente,$link);

    if($tipo_utente == 'Azienda'){
    //Seleziona gli utenti che appartengono all'azienda
        $id_azienda = $_SESSION['id_azienda'];
        $sql = "SELECT id_utente, nome, cognome, ruolo, stato FROM utenti WHERE id_azienda=? AND ruolo != 'Amministratore Aziendale'";
        $sql_dipendenti = $conn->prepare($sql);
        if ($sql_dipendenti) {
            $sql_dipendenti->bind_param("i", $id_azienda);
            $sql_dipendenti->execute();
            $result1 = $sql_dipendenti->get_result();
        } else {
            die("Errore nella preparazione della query.");
    }

    //Seleziona gli utenti che devono essere ancora accettati dall'Amministratore Aziendale
    $sql = "SELECT id_utente, nome, cognome FROM utenti WHERE stato='In Attesa' AND id_azienda=?";
    $sql_dipendenti = $conn->prepare($sql);
    if ($sql_dipendenti) {
        $sql_dipendenti->bind_param("i", $id_azienda);
        $sql_dipendenti->execute();
        $result2 = $sql_dipendenti->get_result();
    } else {
        die("Errore nella preparazione della query.");
    }

    //Gestione delle azioni inviate tramite POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
        $id_utente = $_POST['id_utente'];
        if ($_POST['azione'] == 'accetta') {
            $ruolo = $_POST['ruolo'];
            $sql = "UPDATE utenti SET ruolo=?, stato='Attivo' WHERE id_utente=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $ruolo, $id_utente);
            if ($stmt->execute()) {
                echo "<script>alert('Utente accettato con successo.'); window.location.href='gestione_utenti.php';</script>";
                } else {
                    echo "<script>alert('Errore durante l\'accettazione.'); window.location.href='gestione_utenti.php';</script>";
            }
        } elseif ($_POST['azione'] == 'rifiuta') {
            $sql = "DELETE FROM utenti WHERE id_utente=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_utente);
            if ($stmt->execute()) {
                echo "<script>alert('Utente rifiutato con successo.'); window.location.href='gestione_utenti.php';</script>";
            } else {
                echo "<script>alert('Errore durante il rifiuto.'); window.location.href='gestione_utenti.php';</script>";
            }
        } elseif ($_POST['azione'] == 'modifica') {
            if (isset($_POST['ruolo']) && isset($_POST['stato'])) {
                $ruolo = $_POST['ruolo'];
                $stato = $_POST['stato'];
                $sql = "UPDATE utenti SET ruolo=?, stato=? WHERE id_utente=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $ruolo, $stato, $id_utente);
                    if ($stmt->execute()) {
                    echo "<script>alert('Utente aggiornato con successo.'); window.location.href='gestione_utenti.php';</script>";
                } else {
                    echo "<script>alert('Errore durante l\'aggiornamento.'); window.location.href='gestione_utenti.php';</script>";
                }
            }
        } elseif ($_POST['azione'] == 'annulla') {
            header("Location:menu_Aaziendale.php"); 
        }
        exit;
    }

    //Conteggio del numero degli utenti con richieste in attesa
    $sql2="SELECT COUNT(*) AS numero FROM utenti WHERE stato = 'In Attesa' AND id_azienda = ?";
    $sql_azienda2 = $conn->prepare($sql2);
    $sql_azienda2->bind_param("i", $_SESSION['id_azienda']);
    $sql_azienda2->execute();
    $result = $sql_azienda2->get_result();
    $row = $result->fetch_assoc();
    $number = $row['numero'];
    $sql_azienda2->close();
    }

    //Query per ottenere il resto dei dipendenti e le loro informazioni
    if($tipo_utente == 'Admin'){
        $utenti = [];
        $sql = "SELECT u.*,a.id_azienda,a.nome_azienda FROM utenti AS u LEFT JOIN aziende AS a ON u.id_azienda = a.id_azienda WHERE tipo_utente != 'Admin'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $utenti[] = $row;
        }
    }
        if ($tipo_utente == 'Admin') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'elimina') {
            
            // Recupero sicuro dell'ID utente
            $id_utente = intval($_POST['id_utente']);

            // Modifica lo stato invece di eliminare fisicamente
            $sql = "UPDATE utenti SET stato = 'Eliminato' WHERE id_utente = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_utente);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Utente segnato come Eliminato.');
                    window.location.href = window.location.href;
                </script>";
            } else {
                echo "<script>
                    alert('Errore durante l\'aggiornamento dello stato.');
                    window.location.href = window.location.href;
                </script>";
            }

            $stmt->close();
        }
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Menu Admin</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
        <!--Intestazione con logo-->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Gestione Utenti</h1>
            <div class="div_button">
            <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button> 
            </div>
        </div>
        <!--Bottoni per cambiare sezione-->
        <?php if($_SESSION['tipo_utente']=='Azienda'): ?>
        <div class="buttons">
            <button id="btnDipendenti" class="active" onclick="toggleSection('dipendenti')">
                <i class="fas fa-users"></i> Dipendenti
            </button>
            <button id="btnRichieste" class="active2" onclick="toggleSection('richieste')">
                <i class="fas fa-user-clock"></i> Richieste &nbsp;<?php if($number>0) echo "<span style='color:red;'>(" . $number . ")</span>"; ?>
            </button>
        </div>
        <!--Lista dei dipendenti dell'azienda-->
        <div id="dipendenti" class="section visible" style="display:block">
            <h3 class="sottotitolo">Lista Dipendenti</h3>
            <table>
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Ruolo</th>
                    <th>Stato</th>
                    <th>Azione</th>
                    <th>Salva Modifica</th>
                </tr>
                <?php while($row1 = $result1 -> fetch_assoc()){?>
                <tr class="utenti">
                    <form method="POST">
                    <td><?php echo $row1['nome']; ?></td>
                    <td><?php echo $row1['cognome'];?></td>
                    <td>
                        <select name="ruolo" required>
                            <option value="Responsabile" <?php if ($row1['ruolo'] == 'Responsabile') echo 'selected'; ?>>Responsabile</option>
                            <option value="Operaio" <?php if ($row1['ruolo'] == 'Operaio') echo 'selected'; ?>>Operaio</option>
                            <option value="Contabile" <?php if ($row1['ruolo'] == 'Contabile') echo 'selected'; ?>>Contabile</option>
                            <option value="Magazziniere" <?php if ($row1['ruolo'] == 'Magazziniere') echo 'selected'; ?>>Magazziniere</option>

                        </select>
                    </td>
                    <td>
                        <select name="stato" required>
                            <option value="Attivo" <?php if ($row1['stato'] == 'Attivo') echo 'selected'; ?>>Attivo</option>
                            <option value="Sospeso" <?php if ($row1['stato'] == 'Sospeso') echo 'selected'; ?>>Sospeso</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="id_utente" value="<?php echo $row1['id_utente']; ?>">
                        <button type="submit" name="azione" value="modifica">Salva Modifica</button>
                    </td>
                    </form>
                    <td>
                        <!-- Colonna per il pulsante "Rifiuta" -->
                        <form method="POST">
                            <input type="hidden" name="id_utente" value="<?php echo $row1['id_utente']; ?>">
                            <button type="submit" name="azione" value="annulla">Annulla Modifica</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
                
        <!--Lista delle richieste dei dipendenti in attesa -->
        <div id="richieste" class="section" style="display:none">
            <h3 class="sottotitolo">Richieste di Approvazione</h3>
            <table>
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Ruolo</th>
                    <th>Accetta</th>
                    <th>Rifiuta</th>                
                </tr>
                <?php while($row2 = $result2 -> fetch_assoc()){?>
                <tr>
                    <form method="POST">
                    <td><?php echo $row2['nome']; ?></td>
                    <td><?php echo $row2['cognome'];?></td>
                    <td>
                        <select name="ruolo" required>
                            <option value="" disabled selected> Seleziona Ruolo</option>
                            <option value="Responsabile">Responsabile</option>
                            <option value="Operaio">Operaio</option>
                            <option value="Magazziniere">Magazziniere</option>
                            <option value="Contabile">Contabile</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="id_utente" value="<?php echo $row2['id_utente']; ?>">
                        <button type="submit" name="azione" value="accetta">Accetta</button>
                    </td>
                    </form>
                    <td>
                        <!-- Colonna per il pulsante "Rifiuta" -->
                        <form method="POST">
                            <input type="hidden" name="id_utente" value="<?php echo $row2['id_utente']; ?>">
                            <button type="submit" name="azione" value="rifiuta">Rifiuta</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <?php endif; ?>
        </div>

        <!--Lista degli utenti-->
        <?php if($tipo_utente == 'Admin'):?> 
        <div id="utenti" class="lista_utenti" style="display:block">
            <h3 class="sottotitolo">Lista Utenti</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Tipo Utente</th>
                    <th>Ruolo</th>
                    <th>Stato</th>
                    <th>Azienda</th>
                    <th>Data Registrazione Account</th>
                    <th>Azione</th>
                </tr>
                <?php foreach($utenti as $utente):?>
                <tr class="utenti">
                    <form method="POST">
                    <td><?php echo $utente['id_utente']; ?></td>
                    <td><?php echo $utente['nome']; ?></td>
                    <td><?php echo $utente['cognome'];?></td>
                    <td><?php echo $utente['tipo_utente'];?></td>
                    <td><?php if (!empty($utente['ruolo'])) {
                        echo $utente['ruolo'];
                    } else {
                        echo "Non Definito";
                    }?></td>
                    <td>
                    <select name="nuovo_stato">
                        <option value="Attivo" <?php if($utente['stato'] == 'Attivo') echo 'selected'; ?>>Attivo</option>
                        <option value="Eliminato" <?php if($utente['stato'] == 'Eliminato') echo 'selected'; ?>>Eliminato</option>
                    </select>
                    </td>
                    <td><?php if (!empty($utente['nome_azienda'])) {
                        echo $utente['nome_azienda'];
                    } else {
                        echo "Non Definito";
                    }?></td>
                    <td><?php echo $utente['data_registrazione']; ?></td>
                    </form>
                    <td>
                        <!-- Colonna per il pulsante "Rifiuta" -->
                        <form method="POST">
                            <input type="hidden" name="id_utente" value="<?php echo $utente['id_utente']; ?>">
                            <button type="submit" name="azione" value="elimina" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?');">Modifica Stato</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?> 
        </div>
    </body>

    <!--Script per la funzione di cambio sezione-->
    <script>
        function toggleSection(section){
            if(section ==='dipendenti'){
                document.getElementById('dipendenti').style.display = 'block';
                document.getElementById('richieste').style.display = 'none';
            }else{
                document.getElementById('dipendenti').style.display = 'none';
                document.getElementById('richieste').style.display = 'block';
            }
        } 
    </script>
</html>