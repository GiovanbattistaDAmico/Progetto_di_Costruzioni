<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';

    
    //Query per la notifica della compilazione dei campi dell'azienda
    $id_utente = $_SESSION['id_utente'];
    $ruolo = $_SESSION['ruolo'];
    $id_azienda = $_SESSION['id_azienda'];
    $sql= "SELECT a.* FROM aziende AS a JOIN utenti AS u ON a.id_azienda = u.id_azienda WHERE u.id_utente=? AND u.ruolo='Amministratore Aziendale'";
    $sql_azienda = $conn-> prepare($sql);
    $sql_azienda -> bind_param("i",$id_utente);
    $sql_azienda -> execute();
    $result = $sql_azienda -> get_result();
    $azienda = $result -> fetch_assoc();
    if(empty($azienda['nome_azienda']) || empty($azienda['indirizzo']) || empty($azienda['telefono']) || empty($azienda['email_aziendale']) 
    || empty($azienda['partita_iva'])){
        inviaNotifica($conn,$id_utente,"Attenzione","Completa i dati della tua azienda per continuare.","gestione_azienda.php");
    }
    $sql_azienda->close();

    //query per il conteggio delle notifiche 
    $sql2="SELECT COUNT(*) AS numero FROM notifiche WHERE id_utente =?  AND letto = FALSE ";
    $sql_azienda2 = $conn->prepare($sql2);
    $sql_azienda2->bind_param("i", $_SESSION['id_utente']);
    $sql_azienda2->execute();
    $result2 = $sql_azienda2->get_result();
    $row = $result2->fetch_assoc();

    //Notifica per l'avvio del progetto
    $totale = $row['numero'];
    $oggi = date('Y-m-d');
    $id_azienda = $_SESSION['id_azienda'];
    $sql_data_progetti = "SELECT id_progetto, nome_progetto , data_inizio FROM progetti WHERE data_inizio <= ? AND stato = 'Non Iniziato' 
    AND id_azienda = ?";
    $stmt_data_progetti = $conn->prepare($sql_data_progetti);
    $stmt_data_progetti->bind_param("si", $oggi, $id_azienda);
    $stmt_data_progetti->execute();
    $result = $stmt_data_progetti->get_result();
    while($row = $result->fetch_assoc()) {
        $nome_progetto = $row['nome_progetto'];
        inviaNotifica($conn, $id_utente, "Avvio Progetto","Il progetto \"$nome_progetto\" è arrivato alla data di inizio, aggiorna lo stato a In corso.", 
        "gestione_progetti.php");
    }
    $stmt_data_progetti->close();
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include "menu_adminCSS.php"; ?>
        <?php include "amministratore_aziendaleCSS.php"; ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Menu Amministratore Aziendale</title>
    </head>
    <body>
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
        <a class="voce" href="gestione_utenti.php"><i class="fa-solid fa-users"></i>&nbsp; Gestione Dipendenti</a>
        <a class="voce" href="gestione_azienda.php"><i class="fa-solid fa-building"></i>&nbsp; Gestione Azienda</a>
        <a class="voce" href="gestione_progetti.php"><i class="fas fa-folder-open"></i>&nbsp; Gestione Progetti</a>
        <a class="voce" href="gestione_attivita.php"><i class="fas fa-tasks"></i>&nbsp; Attività</a>
        <a class="voce" href="gestione_compiti.php"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Compiti</a>
        <a class="voce" href="gestione_mat_e_attr.php"><i class="fas fa-boxes"></i>&nbsp;Materiali e Attrezzature</a>
        <a class="voce" href="monitoraggio.php"><i class="fas fa-eye"></i>&nbsp; Monitoraggio</a>
        <a class="voce" href="report_Aziendale.php"><i class="fas fa-chart-pie"></i>&nbsp; Reportistica</a>
        <a class="voce" href="messaggi.php"><i class="fas fa-envelope"></i></i>&nbsp; Messaggi</a>
        <a class="voce" href="account.php"><i class="fas fa-user-cog"></i>&nbsp; Account</a>
        <a class="voce" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp; Logout</a>
    </div>
    <!--Guida per l'Amministratore Aziendale-->
    <div class="guida">
        <h2>Benvenuto nella Sezione di Gestione dell'Amministratore Aziendale</h2>
        <p>In qualità di amministratore aziendale, hai il compito di gestire e monitorare tutte le attività relative alla tua 
            azienda all'interno della piattaforma. Hai a disposizione le seguenti funzionalità:</p>
        <ul>
            <li><i class="fas fa-bell"></i> <strong>Notifiche:</strong> Accedi all'elenco delle notifiche relative a eventi importanti sulla 
            piattaforma. Le notifiche sono provviste di link su cui agire per una gestione rapida.</li><br>
            <li><i class="fa-solid fa-users"></i> <strong>Gestione Utenti:</strong> Gestisci gli utenti della tua azienda, accettando o rifiutando le richieste di 
            registrazione e modificando le informazioni necessarie. Mantieni il controllo sulla tua forza lavoro e assicura che ogni utente abbia i giusti permessi e ruoli.</li><br>
            <li><i class="fa-solid fa-building"></i> <strong>Gestione Azienda:</strong> Accedi a una pagina dedicata alla gestione delle 
            informazioni della tua azienda. Puoi aggiornare i dati aziendali e mantenere tutte le informazioni aggiornate e accurate.</li><br>
            <li><i class="fas fa-folder-open"></i><strong> Gestione Progetti:</strong> Monitora le richieste di progetto inviate dai 
            Committenti con possibilità di accettare o rifiutare in base alla disponibilità dell'azienda. Monitora le richieste di progetto inviate dai Committenti.
            Hai la possibilità di accettare o rifiutare le richieste in base alla disponibilità e alle necessità aziendali.
            Crea, modifica ed elimina progetti associati alla tua azienda.</li><br>
            <li><i class="fas fa-tasks"></i> <strong>Attività:</strong> Visualizza e monitora le attività legate ai progetti in corso. 
            Tieni traccia dello stato di avanzamento, intervenendo quando necessario per ottimizzare i processi.</li><br>
            <li><i class="fa-solid fa-clipboard-list"></i> <strong>Compiti:</strong> Gestisci i compiti associati alle attività. 
            Puoi assegnare e monitorare il progresso di ogni compito, garantendo che vengano completati in tempo.</li><br>
            <li><i class="fas fa-boxes"></i> <strong>Materiali e Attrezzature:</strong> Gestisci l'inventario di materiali e attrezzature aziendali. 
            Monitora le necessità e assicurati che le risorse siano sempre disponibili per i progetti in corso.</li><br>
            <li><i class="fas fa-eye"></i> <strong>Monitoraggio:</strong> Accedi a una panoramica completa su progetti, attività e compiti della tua azienda. 
            La barra di avanzamento ti permetterà di visualizzare facilmente lo stato di ciascun progetto e le attività collegate.</li><br>
            <li><i class="fas fa-chart-pie"></i> <strong>Reportistica:</strong> Accedi alla pagina per visualizzare report come 
            elenco dei progetti in scadenza e grafici di analisi rapida</li><br>
            <li><i class="fas fa-envelope"></i> <strong>Messaggi:</strong> Accedi all'area messaggi per comunicare direttamente con i membri della tua azienda e con altri 
            utenti della piattaforma.</li><br>
            <li><i class="fas fa-user-cog"></i> <strong>Account:</strong> Visualizza e aggiorna le informazioni relative al tuo account personale come amministratore 
            aziendale.</li><br>
            </ul>
            </li>
        </ul>
    </div>


    <!--Script far comparire il menu a tendina-->
    </body>
    <script>
        function menu_tendina(){
            let menu=document.getElementById("mySidenav");
            let width=menu.style.width || "0px";
            if(width=="0px"){apri_menu()} else {chiudi_menu();}
        }
        function apri_menu(){
            document.getElementById("mySidenav").style.width="250px";
        }
        function chiudi_menu(){
            document.getElementById("mySidenav").style.width="0px";
        }
    </script>
</html>