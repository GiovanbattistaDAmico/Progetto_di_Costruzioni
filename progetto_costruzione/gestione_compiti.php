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
    
    //Eliminazione delle notifiche riguardanti la pagina 
    rimuoviNotifiche($conn,$id_utente,$link);
    
    //Select per l'Amministratore Aziendale che potrà vedere tutti i compiti nella sua azienda
    $compiti=[];
    if ($_SESSION['tipo_utente'] == 'Azienda') {
        $sql_compiti = "SELECT c.*, 
                a.nome_attivita, a.id_attivita, a.id_progetto, a.id_responsabile,
                pr.nome_progetto, pr.id_progetto, pr.id_responsabile,
                u_compito.nome AS nome_operaio, u_compito.cognome AS cognome_operaio, u_compito.id_utente AS id_operaio,
                u_attivita.nome AS nome_responsabile_attivita, u_attivita.cognome AS cognome_responsabile_attivita, u_attivita.id_utente AS id_responsabile_attivita,
                u_progetto.nome AS nome_responsabile_progetto, u_progetto.cognome AS cognome_responsabile_progetto, u_progetto.id_utente AS id_responsabile_progetto
                FROM compiti AS c 
                JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                JOIN utenti AS u_compito ON c.id_operaio = u_compito.id_utente
                JOIN utenti AS u_attivita ON a.id_responsabile = u_attivita.id_utente
                JOIN utenti AS u_progetto ON pr.id_responsabile = u_progetto.id_utente
                WHERE pr.id_azienda = ?";
        $stmt_compiti = $conn->prepare($sql_compiti);
        $stmt_compiti->bind_param("i", $_SESSION['id_azienda']);
        if ($stmt_compiti->execute()) {
            $result = $stmt_compiti->get_result();
            while ($row = $result->fetch_assoc()) {
                $compiti[] = $row;
            }
        }
    } elseif ($_SESSION['ruolo'] == 'Responsabile') {
        // Query per il Responsabile 
        $sql_compiti_resp = "SELECT c.*, 
                          a.nome_attivita, a.id_attivita, a.id_progetto, a.id_responsabile,
                          pr.nome_progetto, pr.id_progetto, pr.id_responsabile,
                          u_compito.nome AS nome_operaio, u_compito.cognome AS cognome_operaio, u_compito.id_utente AS id_operaio,
                          u_attivita.nome AS nome_responsabile_attivita, u_attivita.cognome AS cognome_responsabile_attivita, u_attivita.id_utente AS id_responsabile_attivita,
                          u_progetto.nome AS nome_responsabile_progetto, u_progetto.cognome AS cognome_responsabile_progetto, u_progetto.id_utente AS id_responsabile_progetto
                          FROM compiti AS c 
                          JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                          JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                          JOIN utenti AS u_compito ON c.id_operaio = u_compito.id_utente
                          JOIN utenti AS u_attivita ON a.id_responsabile = u_attivita.id_utente
                          JOIN utenti AS u_progetto ON pr.id_responsabile = u_progetto.id_utente
                          WHERE pr.id_responsabile = ? OR a.id_responsabile = ?";

        $stmt_compiti_resp = $conn->prepare($sql_compiti_resp);
        $stmt_compiti_resp->bind_param("ii",$_SESSION['id_utente'], $_SESSION['id_utente']);
        if ($stmt_compiti_resp->execute()) {
            $result = $stmt_compiti_resp->get_result();
            while ($row = $result->fetch_assoc()) {
                $compiti[] = $row;
            }
        }
    } else {
        // Query per l'Operaio
        $sql_compiti_operaio = "SELECT c.*, a.nome_attivita , a.id_attivita, a.id_progetto,a.id_responsabile,
                        pr.nome_progetto, pr.id_progetto,pr.id_responsabile, u_compito.nome AS nome_operaio, 
                        u_compito.cognome AS cognome_operaio, u_compito.id_utente AS id_operaio,u_attivita.nome 
                        AS nome_responsabile_attivita, u_attivita.cognome AS cognome_responsabile_attivita,u_attivita.id_utente AS id_responsabile_attivita,
                        u_progetto.nome AS nome_responsabile_progetto, u_progetto.cognome AS cognome_responsabile_progetto,
                        u_progetto.id_utente AS id_responsabile_progetto
                        FROM compiti AS c 
                        JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                        JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                        JOIN utenti AS u_attivita ON a.id_responsabile = u_attivita.id_utente
                        JOIN utenti AS u_progetto ON pr.id_responsabile =  u_progetto.id_utente
                        JOIN utenti AS u_compito ON c.id_operaio = u_compito.id_utente 
                        WHERE  c.id_operaio = ?";
        $stmt_compiti_operaio = $conn->prepare($sql_compiti_operaio);
        $stmt_compiti_operaio->bind_param("i", $id_utente);
        if ($stmt_compiti_operaio->execute()) {
            $result = $stmt_compiti_operaio->get_result();
            while ($row = $result->fetch_assoc()) {
                $compiti[] = $row;
            }
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
        <title>Gestione Compiti</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!--Intestazione con logo-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Gestione Compiti</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Bottoni per il responsabile -->
            <div class="button">
                <?php if($_SESSION['ruolo']=='Responsabile'){?>
                <div class="azioni">
                <button class="add_button2" onclick='location.href="modifica_compito.php"'>Modifica Compito</button>
                <button class="add_button3" onclick='location.href="crea_compito.php"'>Crea Compito</button>
                <?php } ?>
            </div>
            </div>
            <!--Elenco dei compiti-->
            <div class="lista_compiti" id="corso">
                <h1>Elenco Compiti</h1>
                <?php 
                $trovate = false;
                foreach($compiti as $c):
                        if(!$trovate): $trovate = true; ?>
                        <table>
                            <tr>
                                <th>Nome Progetto</th>
                                <?php if($c['id_responsabile_progetto'] != $_SESSION['id_utente']){ ?>
                                <th>Responsabile di Progetto</th>
                                <?php }?>
                                <th>Nome Attività</th>
                                <?php if($c['id_responsabile_attivita'] != $_SESSION['id_utente']){ ?>
                                <th>Responsabile di Attività</th>
                                <?php }?>
                                <th>Descrizione</th>
                                <?php if($_SESSION['ruolo']!='Operaio'){ ?>
                                <th>Operaio</th>
                                <?php }?>
                                <th>Stato</th>
                                <th>Costo Effettivo</th>
                                <th>Ore Lavorate</th>
                            </tr>
                    <?php endif; ?>
                        <tr>
                            <td><?php echo $c['nome_progetto']; ?></td>
                            <?php if($c['id_responsabile_progetto'] != $_SESSION['id_utente']){ ?>
                            <td><?php echo $c['nome_responsabile_progetto']." ".$c['cognome_responsabile_progetto']; ?></td>
                            <?php }?>
                            <td><?php echo $c['nome_attivita']; ?></td>
                            <?php if($c['id_responsabile_attivita'] != $_SESSION['id_utente']){ ?>
                            <td><?php echo $c['nome_responsabile_attivita']." ".$c['cognome_responsabile_attivita']; ?></td>
                            <?php }?>
                            <td><?php echo $c['descrizione']; ?></td>
                            <?php if($_SESSION['ruolo']!='Operaio'){ ?>
                                <td><?php echo $c['nome_operaio']." " .$c['cognome_operaio']; ?></td><?php }?>
                                <td><?php echo $c['stato']; ?></td>
                            <td><?php echo $c['costo_effettivo']; ?></td>
                            <td><?php echo $c['ore_lavorate']; ?></td>
                        </tr>
                <?php endforeach; ?>
                <?php if($trovate): ?>
                    </table>
                <?php else: ?>
                    <p><strong>Nessun Compito presente nella lista.</strong></p>
                <?php endif; ?>
            </div>
    </body>
</html>