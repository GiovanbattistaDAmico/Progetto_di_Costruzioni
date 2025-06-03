<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';

    //Controllo del tipo di utente 
    if(!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] != 'Dipendente Aziendale' || $_SESSION['ruolo'] != 'Responsabile'){
        die("Connessione non riuscita");  
    }
    if($conn->connect_error){
        die("Connessione Fallita: " .$conn->connect_error);
    }

    //Conteggio delle notifiche
    $sql2="SELECT COUNT(*) AS numero FROM notifiche WHERE id_utente =?  AND letto = FALSE ";
    $sql_azienda2 = $conn->prepare($sql2);
    $sql_azienda2->bind_param("i", $_SESSION['id_utente']);
    $sql_azienda2->execute();
    $result2 = $sql_azienda2->get_result();
    $row = $result2->fetch_assoc();
    $totale = $row['numero'];
    $id_utente = $_SESSION['id_utente'];
    $ruolo = $_SESSION['ruolo'];
    $id_azienda = $_SESSION['id_azienda'];
    $conn->close();
?> 
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include "menu_adminCSS.php"; ?>
        <title>Menu Responsabile</title>
    </head>
    <body>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <!--Intestazione con logo-->
    <div class="intestazione">
    <video class="logo" autoplay muted>
        <source src="edil_planner.mp4" type="video/mp4">
    </video>
    <h1 class="titolo">Edil Planner</h1>
    <div class="div_button">
        <button onclick='location.href="logout.php"'><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</button>
        <button onclick='menu_tendina()'> <i id="menuIcon" class="fas fa-bars"></i>&nbsp; Menu</button>
    </div>
</div>
    <!--Menu a tendina-->
    <div class="side_nav" id="mySidenav">
        <a href="javascript:void(0)" class="close_bttn" onclick="chiudi_menu()">&times;</a>
        <p class="menu">Menù</p><hr>
        <a class="voce" href="notifiche.php">
            <!--Se ci sono notifiche indica il numero in rosso-->
            <i class="fa-solid fa-bell"></i>&nbsp; Notifiche
            <?php if ($totale > 0): ?>
                <span class="badge_notifiche" style="color:red;">(<?php echo $totale; ?>)</span>
            <?php endif; ?>
        </a>
        <a class="voce" href="gestione_progetti.php"><i class="fa-solid fa-project-diagram"></i>&nbsp;Gestione Progetti</a>
        <a class="voce" href="gestione_attivita.php"><i class="fas fa-tasks"></i>&nbsp;Gestione Attività</a>
        <a class="voce" href="gestione_compiti.php"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Gestione Compiti</a>
        <a class="voce" href="richieste_mat.php"><i class="fas fa-box"></i>&nbsp;Richieste Materiali</a>
        <a class="voce" href="monitoraggio.php"><i class="fas fa-eye"></i>&nbsp; Monitoraggio</a>
        <a class="voce" href="report_responsabile.php"><i class="fa-solid fa-chart-line"></i>&nbsp;Reportistica</a>
        <a class="voce" href="messaggi.php"><i class="fas fa-envelope"></i></i>&nbsp;Messaggi</a>
        <a class="voce" href="account.php"><i class="fas fa-user-cog"></i>&nbsp;Account</a>
        <a class="voce" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
    </div>
    <!--Guida per il responsabile-->
    <div class="guida">
        <h2>Benvenuto nella Sezione di Gestione per il Responsabile</h2>
        <p>Come responsabile hai il compito di seguire i progetti assegnati, coordinare attività e compiti, 
            monitorare l’avanzamento dei lavori e contribuire al corretto completamento degli obiettivi aziendali.</p>
        <ul>
            <li><i class="fas fa-bell"></i> <strong>Notifiche:</strong> Ricevi aggiornamenti e avvisi relativi a progetti, attività e 
            compiti di tua competenza.</li><br>
            <li><i class="fa-solid fa-project-diagram"></i> <strong>Gestione Progetti:</strong> Accedi alla lista dei progetti assegnati 
            dall'admin aziendale, visualizzandone dettagli e stato di avanzamento.</li><br>
            <li><i class="fas fa-tasks"></i> <strong>Gestione Attività:</strong> Crea, modifica ed elimina attività legate ai progetti di 
            cui sei responsabile, organizzando al meglio il lavoro.</li><br>
            <li><i class="fas fa-clipboard-list"></i> <strong>Gestione Compiti:</strong> Gestisci i compiti associati ai progetti 
            assegnati e, se responsabile di attività, crea nuovi compiti per i collaboratori.</li><br>
            <li><i class="fas fa-box"></i> <strong>Richieste Materiali:</strong> Richiedi i materiali necessari per i compiti assegnati 
            agli operai. Al termine dei compiti, puoi anche creare richieste di reso o di scarto per gestire correttamente i materiali avanzati o danneggiati.</li><br>
            <li><i class="fas fa-eye"></i> <strong>Monitoraggio:</strong> Monitora l'andamento dei progetti e delle attività tramite barre 
            di avanzamento.</li><br>
            <li><i class="fa-solid fa-chart-line"></i> <strong>Reportistica:</strong> Consulta report grafici come
            compiti assegnati e stato di avanzamento delle attività.</li><br>
            <li><i class="fas fa-envelope"></i> <strong>Messaggi:</strong> Accedi all'area messaggi per comunicare direttamente con i membri 
            della tua azienda e con altri utenti della piattaforma.</li><br>
            <li><i class="fas fa-user-cog"></i> <strong>Account:</strong> Visualizza e aggiorna le informazioni relative al tuo account 
            personale.</li><br>
            <li><i class="fas fa-sign-out-alt"></i> <strong>Logout:</strong> Termina in sicurezza la tua sessione di lavoro sulla piattaforma.</li>
        </ul>
    </div>
</body>
    <!--Script far comparire il menu a tendina-->
    <script>
        function menu_tendina(){
        let menu=document.getElementById("mySidenav");
        let width = menu.style.width || "0px";
            if(width=="0px"){
                apri_menu();
            }
            else{
                chiudi_menu();
            }
        }
        function apri_menu(){
            document.getElementById("mySidenav").style.width="250px";
        }
        function chiudi_menu(){
            document.getElementById("mySidenav").style.width="0px";
        }
    </script>
    </html>