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
    $id_utente = $_SESSION['id_utente'];
    $tipo_utente = $_SESSION['tipo_utente'];

    //Query per i progetti in base al ruolo 
    $progetti = [];
    if($tipo_utente == 'Azienda' || $tipo_utente == 'Dipendente Aziendale'){
        $sql="SELECT pr.*,c.nome AS committente_nome ,c.cognome AS committente_cognome,r.nome AS responsabile_nome,r.cognome AS 
        responsabile_cognome FROM progetti AS pr JOIN utenti AS c ON pr.id_committente=c.id_utente JOIN utenti AS r ON pr.id_responsabile=
        r.id_utente WHERE pr.id_azienda=? AND (pr.id_responsabile = ? OR ? = 'Azienda')";
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza->bind_param("iis", $id_azienda, $id_utente, $tipo_utente);
    }elseif($tipo_utente == 'Responsabile'){
        $sql="SELECT pr.*,u.nome,u.cognome FROM progetti AS pr JOIN utenti AS u ON id_responsabile=id_utente WHERE pr.id_responsabile=?";
        $id_responsabile = $id_utente;
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza -> bind_param("i",$id_responsabile);
    }else{
        $sql="SELECT pr.*, u.nome AS responsabile_nome, u.cognome AS responsabile_cognome, a.nome_azienda FROM progetti AS pr 
        JOIN utenti AS u ON pr.id_responsabile = u.id_utente
            LEFT JOIN aziende AS a ON pr.id_azienda = a.id_azienda WHERE pr.id_committente = ?";
        $id_committente = $id_utente;
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza -> bind_param("i",$id_committente);
    }
    $sql_visualizza->execute();
    $result = $sql_visualizza->get_result();
    while ($progetto = $result->fetch_assoc()) {
        $progetti[] = $progetto;
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
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Bottoni per l'Amministratore aziendale-->
            <div class="button">
                <div class="button_change">
                </div>
                <?php if($_SESSION['tipo_utente']=='Azienda'){?>
                <div class="azioni">
                <button class="add_button3" onclick='location.href="modifica_progetti.php"'>Modifica Progetti</button>
                <button class="add_button3" onclick='location.href="crea_progetto.php"'>Crea Progetto</button>
                <?php } ?></div>
            </div>
            <!-- Elenco dei progetti-->
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
                        <?php if($tipo_utente == 'Azienda' || $tipo_utente == 'Dipendente Aziendale'): ?>
                            <th>Committente</th>
                            <?php endif; ?>
                        <?php if($tipo_utente == 'Committente'): ?>
                            <th>Azienda</th>
                        <?php endif; ?>
                        <?php if($tipo_utente == 'Azienda' ||$tipo_utente == 'Committente'): ?>
                            <th>Responsabile</th>
                        <?php endif; ?>
                        <th>Costo Effettivo</th>
                        <th>Stato</th>
                    </tr>
                    <?php foreach($progetti as $progetto): ?>
                        <tr>
                            <td><?php echo $progetto['nome_progetto']; ?></td>
                            <td><?php echo $progetto['descrizione']; ?></td>
                            <td><?php echo $progetto['data_inizio']; ?></td>
                            <td><?php echo $progetto['data_scadenza']; ?></td>
                            <td><?php echo $progetto['budget']; ?></td>
                            <?php if($tipo_utente == 'Azienda' || $tipo_utente == 'Libero Professionista'|| $tipo_utente == 'Dipendente Aziendale'): ?>
                                <td><?php echo $progetto['committente_nome'] . " " . $progetto['committente_cognome']; ?></td>
                            <?php endif; ?>
                            <?php if($tipo_utente == 'Committente'): ?>
                                <td><?php echo $progetto['nome_azienda']; ?></td>
                            <?php endif; ?>
                            <?php if($tipo_utente == 'Azienda' || $tipo_utente == 'Libero Professionista'||$tipo_utente == 'Committente'): ?>
                                <td><?php echo $progetto['responsabile_nome'] . " " . $progetto['responsabile_cognome']; ?></td>
                            <?php endif; ?>
                            <td><?php echo $progetto['costo_effettivo']; ?></td>
                            <td><?php echo $progetto['stato']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p><strong>Nessun Progetto attualmente in corso.</strong></p>
            <?php endif; ?>
        </div>
    </body>
</html>