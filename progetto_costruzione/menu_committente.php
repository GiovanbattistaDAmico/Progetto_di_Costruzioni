<?php 
    require 'db.php';
    session_start();

    //Controllo del tipo di utente 
    if(!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] != 'Committente'){
        die("Connessione non riuscita");  
    }
    if($conn->connect_error){
        die("Connessione Fallita: " .$conn->connect_error);
    }

    //Conteggio Notifiche
    $sql2="SELECT COUNT(*) AS numero FROM notifiche WHERE id_utente =?  AND letto = FALSE ";
    $sql_azienda2 = $conn->prepare($sql2);
    $sql_azienda2->bind_param("i", $_SESSION['id_utente']);
    $sql_azienda2->execute();
    $result2 = $sql_azienda2->get_result();
    $row = $result2->fetch_assoc();
    $totale = $row['numero'];
    $conn->close();
?> 
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include "menu_adminCSS.php"; ?>
        <title>Menu Committente</title>
    </head>
    <body>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
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
        <a class="voce" href="gestione_progetti.php"><i class="fa-solid fa-diagram-project"></i>&nbsp;I miei Progetti:</a>
        <a class="voce" href="monitoraggio.php"><i class="fa-solid fa-chart-line"></i>&nbsp;Monitoraggio</a>
        <a class="voce" href="messaggi.php"><i class="fa-solid fa-envelope"></i>&nbsp;Messaggi</a>
        <a class="voce" href="account.php"><i class="fa-solid fa-user"></i>&nbsp;Account</a>
        <a class="voce" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
    </div>
    <!--Guida per il committente-->
    <div class="guida">
        <h2>Benvenuto nella Sezione di Gestione per il Committente</h2>
        <p>Come committente puoi monitorare lo stato di avanzamento dei progetti approvati e comunicare con Amministratore e Responsabili
            in caso di necessità.</p>
        <ul>
            <li><i class="fa-solid fa-bell"></i>&nbsp;<strong>Notifiche:</strong> Ricevi aggiornamenti sulle richieste di progetto inviate e sui 
            loro stati di approvazione.</li><br>
            <li><i class="fa-solid fa-diagram-project"></i>&nbsp;<strong>I miei Progetti:</strong> Consulta l'elenco dei progetti che 
            sono stati accettati dalle aziende, con tutte le relative informazioni dettagliate.</li><br>
            <li><i class="fa-solid fa-chart-line"></i>&nbsp;<strong>Monitoraggio:</strong> Tieni traccia dell'avanzamento dei tuoi progetti 
            approvati tramite un'interfaccia intuitiva e aggiornata.</li><br>
            <li><i class="fa-solid fa-envelope"></i>&nbsp;<strong>Messaggi:</strong> Comunica direttamente con le aziende relativamente alle 
            tue richieste o ai progetti in corso.</li><br>
            <li><i class="fa-solid fa-user"></i>&nbsp;<strong>Account:</strong> Gestisci le informazioni personali del tuo profilo.</li><br>
            <li><i class="fa-solid fa-right-from-bracket"></i>&nbsp;<strong>Logout:</strong> Esci dalla piattaforma in modo sicuro al termine 
            delle operazioni.</li>
        </ul>
    </div>

</body>
    <!--Script per il menu a tendina-->
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