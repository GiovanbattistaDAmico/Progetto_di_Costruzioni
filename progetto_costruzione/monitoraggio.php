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
    $tipo_utente = $_SESSION['tipo_utente'];

    $progetti = [];
    //SELECT PER L'AMMINISTRATORE AZIENDALE PER RECUPERARE TUTTI I PROGETTI 
    if($tipo_utente == 'Admin'){
        $sql="SELECT pr.*,c.nome AS committente_nome ,c.cognome AS committente_cognome,r.nome AS responsabile_nome,r.cognome AS 
        responsabile_cognome,a.nome_azienda FROM progetti AS pr JOIN utenti AS c ON pr.id_committente=c.id_utente JOIN utenti AS r ON 
        pr.id_responsabile = r.id_utente JOIN aziende AS a ON pr.id_azienda = a.id_azienda";
        $sql_visualizza = $conn -> prepare($sql);
    //SELECT PER L'AMMINISTRATORE AZIENDALE PER RECUPERARE TUTTI I PROGETTI DELL'AZIENDA
    }elseif($tipo_utente == 'Azienda'){
        $sql="SELECT pr.*,c.nome AS committente_nome ,c.cognome AS committente_cognome,r.nome AS responsabile_nome,r.cognome AS 
        responsabile_cognome FROM progetti AS pr JOIN utenti AS c ON pr.id_committente = c.id_utente JOIN utenti AS r ON 
        pr.id_responsabile = r.id_utente WHERE pr.id_azienda=?";
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza->bind_param("i", $id_azienda);
        //SELECT PER IL RESPONSABILE DI PROGETTO PER RECUPERARE TUTTI I PROGETTI A LUI ASSEGNATI
    }elseif($tipo_utente == 'Dipendente Aziendale' && $_SESSION['ruolo']=='Responsabile'){
        $sql="SELECT pr.*,c.nome AS committente_nome ,c.cognome AS committente_cognome,r.nome AS responsabile_nome,r.cognome AS 
        responsabile_cognome FROM progetti AS pr JOIN utenti AS c ON pr.id_committente = c.id_utente JOIN utenti AS r ON 
        pr.id_responsabile=r.id_utente WHERE pr.id_responsabile=?";
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza->bind_param("i", $id_utente);
        //SELECT PER IL COMMITTENTE PER RECUPERARE I PROGETTI COMMISSIONATI
    }elseif($tipo_utente == 'Committente'){
        $sql="SELECT pr.*, c.nome AS committente_nome, c.cognome AS committente_cognome, r.nome AS responsabile_nome, r.cognome 
        AS responsabile_cognome, a.nome_azienda FROM progetti AS pr JOIN utenti AS c ON pr.id_committente = c.id_utente
        JOIN utenti AS r ON pr.id_responsabile = r.id_utente JOIN aziende AS a ON pr.id_azienda = a.id_azienda
        WHERE pr.id_committente = ?";
        $sql_visualizza = $conn -> prepare($sql);
        $sql_visualizza->bind_param("i", $id_utente);
    }
    $sql_visualizza->execute();
    $result = $sql_visualizza->get_result();

    // Ciclo su ogni progetto per calcolare la percentuale di completamento
    while ($progetto = $result->fetch_assoc()) {

        // Query per ottenere il numero totale di attività e quelle completate per ciascun progetto
        $sql_attivita_completate = "SELECT COUNT(*) AS totale_attivita, SUM(CASE WHEN a.stato = 'Conclusa' THEN 1 ELSE 0 END) AS
        attivita_completate FROM attivita AS a WHERE a.id_progetto = ?";
        $stmt_attivita_completate = $conn->prepare($sql_attivita_completate);
        $stmt_attivita_completate -> bind_param("i",$progetto['id_progetto']);
        $stmt_attivita_completate -> execute();
        $result_progetto = $stmt_attivita_completate ->get_result();
        $risultato_progresso = $result_progetto ->fetch_assoc();

        // Inizializzazione della percentuale di completamento
        $percentuale_completamento_progetti=0;

        // Calcolo della percentuale solo se ci sono attività associate al progetto
        if($risultato_progresso['totale_attivita'] >0){
            $percentuale_completamento_progetti = round(($risultato_progresso['attivita_completate'] / $risultato_progresso['totale_attivita']) * 100);
        }
        // Aggiunta della percentuale calcolata all'array del progetto
        $progetto['percentuale_completamento'] = $percentuale_completamento_progetti;
        // Inserimento del progetto nell'array finale
        $progetti[] = $progetto;
    }
    $attivita = [];
    //Select per L'Admin  per vedere tutte le attività 
    if($_SESSION['tipo_utente']=='Admin'){
        $sql_attivita_admin="SELECT a.*,u.id_utente,u.nome AS nome_responsabile,u.cognome AS cognome_responsabile,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN utenti AS u ON a.id_responsabile = u.id_utente JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto";
        $result_attivita=$conn->query($sql_attivita_admin);

    //Select per L'Amministratore Aziendale per vedere tutte le attività dell'azienda
    }elseif($_SESSION['tipo_utente']=='Azienda'){
        $sql_attivita_azienda="SELECT a.*,u.id_utente,u.nome AS nome_responsabile,u.cognome AS cognome_responsabile,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN utenti AS u ON a.id_responsabile = u.id_utente JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto WHERE pr.id_azienda=?";
        $stmt_attivita_azienda=$conn->prepare($sql_attivita_azienda);
        $stmt_attivita_azienda->bind_param("i",$_SESSION['id_azienda']);
        $stmt_attivita_azienda->execute();
        $result_attivita=$stmt_attivita_azienda->get_result();

        // Se l'utente è un Responsabile (dipendente), recupera le attività legate ai progetti dove è responsabile
    }elseif($_SESSION['ruolo']=='Responsabile'){

        // Primo tentativo: cerca le attività dove l'utente è responsabile del progetto
        $sql_attivita_professionista="SELECT a.*,u.id_utente,u.nome,u.cognome,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto JOIN utenti AS u ON a.id_responsabile = u.id_utente WHERE pr.id_responsabile=?";
        $stmt_attivita_professionista=$conn->prepare($sql_attivita_professionista);
        $stmt_attivita_professionista->bind_param("i",$id_utente);
        $stmt_attivita_professionista->execute();
        $result_attivita=$stmt_attivita_professionista->get_result();

        // Se non ci sono risultati, cerca le attività dove è direttamente responsabile
        if($result_attivita->num_rows==0){
            $sql_attivita_professionista="SELECT a.*,u.id_utente,u.nome,u.cognome,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto JOIN utenti AS u ON a.id_responsabile = u.id_utente WHERE a.id_responsabile=?";
        $stmt_attivita_professionista=$conn->prepare($sql_attivita_professionista);
        $stmt_attivita_professionista->bind_param("i",$id_utente);
        $stmt_attivita_professionista->execute();
        $result_attivita=$stmt_attivita_professionista->get_result();
        }
    }

    // Calcolo della percentuale di completamento per ogni attività (basata sui compiti completati)
    if (isset($result_attivita) && $result_attivita->num_rows > 0){
    while($row = $result_attivita->fetch_assoc()){

        // Query per contare i compiti totali e quelli completati dell'attività
        $sql_compiti_completati = "SELECT COUNT(*) AS totale_compiti,SUM(CASE WHEN stato = 'Completato' THEN 1 ELSE 0 END) AS compiti_completati
        FROM compiti AS c WHERE id_attivita=? ";
        $stmt_compiti_completati  = $conn->prepare($sql_compiti_completati);
        $stmt_compiti_completati ->bind_param("i",$row['id_attivita']);
        $stmt_compiti_completati->execute();
        $result_compiti=$stmt_compiti_completati->get_result();
        $risultato_progresso = $result_compiti->fetch_assoc();
        // Inizializza la percentuale di completamento al 0%
        $percentuale_completamento_attivita=0;
        // Se ci sono compiti, calcola la percentuale
        if($risultato_progresso['totale_compiti']>0){
            $percentuale_completamento_attivita = round(($risultato_progresso['compiti_completati'] / $risultato_progresso['totale_compiti']) * 100);
        }
        // Aggiungi la percentuale ai dati dell'attività
        $row['percentuale_completamento']= $percentuale_completamento_attivita;
        $attivita[]=$row;
    }}
    //Select per l'Admin che vedrà tutti i compiti delle varie aziende
    $compiti=[];
    if ($_SESSION['tipo_utente'] == 'Admin') {
        $sql_compiti = "SELECT c.id_attivita, c.descrizione, c.stato, c.id_operaio,
                        c.costo_effettivo, a.nome_attivita, a.id_attivita, a.id_progetto,pr.id_azienda,
                        pr.nome_progetto, pr.id_progetto, u.nome AS nome_operaio, u.cognome AS cognome_operaio, u.id_utente
                        FROM compiti AS c 
                        JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                        JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                        JOIN utenti AS u ON c.id_operaio = u.id_utente ";
        $result_compiti = $conn->query($sql_compiti);
        while ($row = $result_compiti->fetch_assoc()) {
            $compiti[] = $row;
        }
    //Select per l'Amministratore Aziendale che potrà vedere tutti i compiti nella sua azienda
    }elseif ($_SESSION['tipo_utente'] == 'Azienda') {
        $sql_compiti = "SELECT c.id_attivita, c.descrizione, c.stato, c.id_operaio,
                        c.costo_effettivo, a.nome_attivita, a.id_attivita, a.id_progetto,pr.id_azienda,
                        pr.nome_progetto, pr.id_progetto, u.nome AS nome_operaio, u.cognome AS cognome_operaio, u.id_utente
                        FROM compiti AS c 
                        JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                        JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                        JOIN utenti AS u ON c.id_operaio = u.id_utente 
                        WHERE pr.id_azienda = ?";
        $stmt_compiti = $conn->prepare($sql_compiti);
        $stmt_compiti->bind_param("i", $_SESSION['id_azienda']);
        if ($stmt_compiti->execute()) {
            $result_compiti = $stmt_compiti->get_result();
            while ($row = $result_compiti->fetch_assoc()) {
                $compiti[] = $row;
            }
        }
    } elseif ($_SESSION['ruolo'] == 'Responsabile') {
        // Prima query: Responsabile del progetto
        $sql_compiti_resp_prog = "SELECT c.id_attivita, c.descrizione, c.stato, c.id_operaio,
                                  c.costo_effettivo, a. nome_attivita, a.id_attivita, a.id_progetto, 
                                  pr.nome_progetto, pr.id_progetto, u.nome AS nome_operaio, u.cognome AS cognome_operaio, u.id_utente
                                  FROM compiti AS c 
                                  JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                                  JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                                  JOIN utenti AS u ON c.id_operaio = u.id_utente 
                                  WHERE pr.id_responsabile = ?";
        $stmt_compiti_resp_prog = $conn->prepare($sql_compiti_resp_prog);
        $stmt_compiti_resp_prog->bind_param("i",  $_SESSION['id_utente']);
        // Esegui la query per il responsabile del progetto
        if ($stmt_compiti_resp_prog->execute()) {
            $result_compiti = $stmt_compiti_resp_prog->get_result();
            while ($row = $result_compiti->fetch_assoc()) {
                $compiti[] = $row;
            }
        }
    
        // Seconda query: Responsabile dell'attività
        $sql_compiti_resp_attivita = "SELECT c.id_attivita, c.descrizione, c.stato, c.id_operaio,
                                      c.costo_effettivo, a.nome_attivita, a.id_attivita, a.id_progetto, 
                                      pr.nome_progetto, pr.id_progetto, u.nome AS nome_operaio, u.cognome AS cognome_operaio, u.id_utente
                                      FROM compiti AS c 
                                      JOIN attivita AS a ON c.id_attivita = a.id_attivita 
                                      JOIN progetti AS pr ON a.id_progetto = pr.id_progetto 
                                      JOIN utenti AS u ON c.id_operaio = u.id_utente 
                                      WHERE a.id_responsabile = ?";
        $stmt_compiti_resp_attivita = $conn->prepare($sql_compiti_resp_attivita);
        $stmt_compiti_resp_attivita->bind_param("i", $_SESSION['id_utente']);
        // Esegue la query per il responsabile dell'attività
        if ($stmt_compiti_resp_attivita->execute()) {
            $result_compiti = $stmt_compiti_resp_attivita->get_result();
            while ($row = $result_compiti->fetch_assoc()) {
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
        <?php include 'monitoraggioCSS.php'?>
        <title>Monitoraggio Progetti</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!--Intestazione con logo -->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Monitoraggio Progetti</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <?php 
    $i = 1; 
    // Ciclo su tutti i progetti disponibili
    foreach ($progetti as $progetto): 
    // Mostra il progetto solo se:
    // - l'utente è Admin
    // - oppure è un Amministratore Aziendale
    // - oppure è il Responsabile del progetto corrente
    if ($tipo_utente == 'Admin' || $ruolo == 'Amministratore Aziendale' || ($ruolo == 'Responsabile' && $progetto['id_responsabile'] == $id_utente)): ?>
    
    <div class="box_progetti">
        <!-- Intestazione del progetto -->
        <h3>Progetto N.<?php echo $i++; ?></h3>

         <!-- Dettagli del progetto -->
        <strong><?php echo ($progetto['nome_progetto']); ?></strong><br>
        Committente: <?php echo ($progetto['committente_nome'] . ' ' . $progetto['committente_cognome']); ?><br>
        Responsabile di Progetto: <?php echo ($progetto['responsabile_nome'] . ' ' . $progetto['responsabile_cognome']); ?><br>
        Completamento: <?php echo $progetto['percentuale_completamento']; ?>%<br>
        <!-- Solo per Admin, mostra l'azienda responsabile -->
        <?php if($tipo_utente == 'Admin'):?>Azienda Responsabile: <?php echo $progetto['nome_azienda'];?> <?php endif; ?>

        <!-- Barra di avanzamento del progetto -->
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $progetto['percentuale_completamento']; ?>%;"></div>
        </div>

        <!-- Pulsante per mostrare/nascondere le attività del progetto -->
        <button onclick="mostraAttivita('attivita_<?php echo $progetto['id_progetto']; ?>')">Mostra Attività</button>

        <!-- Sezione attività (inizialmente nascosta) -->
        <div id="attivita_<?php echo $progetto['id_progetto']; ?>" class="box_attivita" style="display:none">
            <?php $j = 1; // Ciclo su tutte le attività e mostro solo quelle relative a questo progetto 
                foreach ($attivita as $att): ?>
                <?php if ($att['id_progetto'] == $progetto['id_progetto']): ?>
                    <h3>Attività N.<?php echo $j++; ?></h3>
                    <div class="box">

                        <!-- Dettagli attività -->
                        🔧 <strong><?php echo ($att['nome_attivita']); ?></strong><br>
                        Responsabile dell'Attività: <?php echo ($att['nome_responsabile'] . ' ' . $att['cognome_responsabile']); ?><br>
                        Descrizione: <?php echo $att['descrizione']; ?><br>
                        Completamento: <?php echo $att['percentuale_completamento']; ?>%

                        <!-- Barra di avanzamento dell’attività -->
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $att['percentuale_completamento']; ?>%;"></div>
                        </div>
                        <!-- Pulsante per mostrare i compiti -->
                        <button onclick="mostraCompito('compito_<?php echo $att['id_attivita']; ?>')">Mostra Compiti</button>

                        <!-- Sezione compiti (inizialmente nascosta) -->
                        <div id="compito_<?php echo $att['id_attivita']; ?>" style="display:none">
                            <?php 
                            $trovato = false; 
                            $k = 1; 
                            // Ciclo su tutti i compiti e mostro solo quelli relativi a questa attività
                            foreach ($compiti as $compito): 
                                if ($compito['id_attivita'] == $att['id_attivita']):
                                    $trovato = true; 
                            ?>  
                            <!-- Dettagli compiti -->                  
                                <div class="box_compiti">
                                    <h3>Compito N.<?php echo $k++; ?></h3>
                                    📌 <?php echo $compito['descrizione']; ?> - 
                                    Operaio: <strong><?php echo $compito['nome_operaio'] . " " . $compito['cognome_operaio']; ?></strong> 
                                    Stato: <strong><?php echo $compito['stato']; ?></strong>
                                </div>
                            <?php 
                                endif;
                            endforeach;
                            if (!$trovato) {
                                echo "<em>Nessun compito disponibile.</em>";
                            }
                            ?>
                            </div><!-- Fine sezione compiti -->
                        </div><!-- Fine box attività -->
                    <?php endif; ?>
                <?php endforeach; ?>
            </div><!-- Fine sezione attività -->
        </div> <!-- Fine box progetto -->
        <?php endif; // Fine controllo tipo utente o ruolo 
    endforeach; // Fine ciclo progetti  ?>
    

        <?php // Inizializza il contatore delle attività
        $j = 1; 
        foreach ($attivita as $att): 
            // Mostra solo le attività per cui l'utente loggato è il responsabile
            if ($ruolo == 'Responsabile' && $att['id_responsabile'] == $id_utente):
        ?>
        <!-- Box visivo per ogni attività -->
        <div class="box_attivita">
            <!-- Dettagli attività -->
            <h3>Attività N.<?php echo $j++; ?></h3>
            🔧 <strong><?php echo ($att['nome_attivita']); ?></strong><br>
            Descrizione: <?php echo $att['descrizione']; ?><br>
            Completamento: <?php echo $att['percentuale_completamento']; ?>%

            <!-- Barra di avanzamento -->
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $att['percentuale_completamento']; ?>%;"></div>
            </div>

            <!-- Bottone per mostrare/nascondere i compiti dell'attività -->
            <button onclick="mostraCompito('compito_<?php echo $att['id_attivita']; ?>')">Mostra Compiti</button>

            <!-- Contenitore dei compiti, inizialmente nascosto -->
            <div id="compito_<?php echo $att['id_attivita']; ?>" style="display:none">
                <?php 
                $trovato = false; 
                $k = 1;
                foreach ($compiti as $compito): 
                    if ($compito['id_attivita'] == $att['id_attivita']):
                        $trovato = true; 
                ?>           
                <!-- Descrizione compito e info operaio -->         
                    <div class="box_compiti">
                        <h3>Compito N.<?php echo $k++; ?></h3>
                        📌 <?php echo $compito['descrizione']; ?> - 
                        Operaio: <strong><?php echo $compito['nome_operaio'] . " " . $compito['cognome_operaio']; ?></strong> 
                        Stato: <strong><?php echo $compito['stato']; ?></strong>
                    </div>
                <?php 
                    endif;
                endforeach;
                if (!$trovato) {
                    echo "<em>Nessun compito disponibile.</em>";
                }
                ?>
            </div>
        </div>
        <?php 
            endif; 
        endforeach; // Fine ciclo attività
        ?>
        <?php // Scorre tutti i progetti
        foreach ($progetti as $progetto): 
        // Mostra solo se l'utente è un Committente
        if ($tipo_utente == 'Committente'): ?>

        <!-- Box visivo del progetto per il committente -->
        <div class="box_progetti">

        <!-- Descrizione progetto -->
            <h3>Progetto</h3>
            <strong><?php echo ($progetto['nome_progetto']); ?></strong><br>
            Responsabile di Progetto: <?php echo ($progetto['responsabile_nome'] . ' ' . $progetto['responsabile_cognome']); ?><br>
            Azienda: <?php echo ($progetto['nome_azienda']); ?><br> <!-- Aggiunta azienda -->
            Completamento: <?php echo $progetto['percentuale_completamento']; ?>%

             <!-- Barra di avanzamento grafica -->
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progetto['percentuale_completamento']; ?>%;"></div>
            </div>
        </div>
        <?php endif;
    endforeach; // End foreach per progetti ?>

        <script>
            // Funzione per mostrare o nascondere la sezione delle attività
            function mostraAttivita(id) {
                const container = document.getElementById(id);
                if(container.style.display=='none'){container.style.display='block';}
                else{container.style.display='none';}
            }
            // Funzione per mostrare o nascondere la sezione dei compiti
            function mostraCompito(id){
                const container_attivita = document.getElementById(id);
                if(container_attivita.style.display=='none'){container_attivita.style.display='block';}
                else{container_attivita.style.display='none';}    
            }
        </script>
    </body>
</html>