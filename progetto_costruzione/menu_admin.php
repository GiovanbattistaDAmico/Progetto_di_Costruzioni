<?php 
    require 'db.php';
    session_start();

    //Controllo del tipo di utente
    if(!isset($_SESSION['id_utente'])  ||  $_SESSION['tipo_utente']!=='Admin'){
        die("Accesso non consentito");
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
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include "menu_adminCSS.php"; ?>
        <title>Menu Admin</title>
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
        <a class="voce" href="gestione_utenti.php"><i class="fa-solid fa-users"></i>&nbsp;Utenti</a>
        <a class="voce" href="monitoraggio.php"><i class="fa-solid fa-project-diagram"></i>&nbsp;Progetti</a>
        <a class="voce" href="gestione_mat_e_attr.php"><i class="fa-solid fa-tools"></i>&nbsp;Materiali e Attrezzature</a>
        <a class="voce" href="report.php"><i class="fa-solid fa-chart-line"></i>&nbsp;Reportistica</a>
        <a class="voce" href="messaggi.php"><i class="fas fa-envelope"></i></i>&nbsp; Messaggi</a>
        <a class="voce" href="account.php"><i class="fas fa-user-cog"></i>&nbsp;Account</a>
        <a class="voce" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
    </div>
    <!--Guida per l'Admin-->
    <div class="guida">
        <h2>Benvenuto nella Sezione di Gestione dell'Admin</h2>
        <p>In qualità di admin, hai accesso a tutte le funzionalità necessarie per gestire l'intera piattaforma. Puoi monitorare e 
            controllare i vari aspetti del sistema, garantendo che tutto funzioni in modo ottimale. Qui di seguito trovi 
            una panoramica delle principali funzionalità che ti permetteranno di gestire al meglio la piattaforma.</p>
        <ul>
            <li><i class="fas fa-bell"></i> <strong>Notifiche:</strong> Accedi all'elenco delle notifiche relative a eventi importanti sulla piattaforma. Le notifiche sono 
            provviste di link su cui agire per una gestione rapida.</li><br>
            <li><i class="fa-solid fa-users"></i> <strong>Utenti:</strong> Gestisci gli utenti registrati sulla piattaforma, visualizzando tutte le informazioni necessarie 
            come il tipo di utente e l'azienda di appartenenza. Puoi rimuovere utenti inappropriati o non conformi alle politiche della piattaforma.</li><br>
            <li><i class="fa-solid fa-project-diagram"></i> <strong>Progetti:</strong> Monitora l'elenco di tutti i progetti in corso, 
            visualizzando dettagli importanti come lo stato di avanzamento e le informazioni dei progetti creati dalle aziende.</li><br>
            <li><i class="fa-solid fa-tools"></i> <strong>Materiali e Attrezzature:</strong> Gestisci l'inventario di materiali e attrezzature, 
            monitorando le necessità delle aziende per garantire che tutti i progetti possano proseguire senza interruzioni. </li><br>
            <li><i class="fas fa-chart-line"></i> <strong>Reportistica:</strong> Accedi a una panoramica delle metriche principali attraverso i report. Puoi visualizzare dati 
            su utenti, progetti, e attività, permettendoti di analizzare l'andamento della piattaforma e prendere decisioni strategiche basate su informazioni dettagliate.</li><br>
            <li><i class="fas fa-envelope"></i> <strong>Messaggi:</strong> Accedi all'area messaggi per comunicare direttamente con gli utenti della piattaforma.</li><br>
            <li><i class="fas fa-user-cog"></i> <strong>Account:</strong> Visualizza e aggiorna le informazioni relative al tuo account personale.</li><br>
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