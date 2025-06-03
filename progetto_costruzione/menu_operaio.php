<?php 
    require 'db.php';
    session_start();

    //Controllo del tipo di utente
    if(!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] != 'Dipendente Aziendale' || $_SESSION['ruolo'] != 'Operaio'){
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
    $conn->close();
?> 
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include "menu_adminCSS.php"; ?>
        <title>Menu Operaio</title>
    </head>
    <body>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <div class="intestazione">
    <!--Intestazione con logo-->
    <video class="logo" autoplay muted>
        <source src="edil_planner.mp4" type="video/mp4">
    </video>
    <h1 class="titolo">Edil Planner</h1>
    <div class="div_button">
        <button onclick='location.href="logout.php"'><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</button>
        <button onclick='menu_tendina()'> <i id="menuIcon" class="fas fa-bars"></i>&nbsp; Menu</button>
    </div>
</div>
    <!--menu a tendina-->
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
        <a class="voce" href="gestione_compiti.php"><i class="fas fa-tasks"></i>&nbsp;I miei Compiti</a>
        <a class="voce" href="ore_lavorate.php"><i class="fas fa-clock"></i>&nbsp;Ore Lavorate</a>
        <a class="voce" href="messaggi.php"><i class="fa-solid fa-chart-line"></i>&nbsp;Messaggi</a>
        <a class="voce" href="account.php"><i class="fa-solid fa-user"></i>&nbsp;Account</a>
        <a class="voce" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
    </div>
    <!--Guida per l'operaio-->
    <div class="guida">
        <h2>Benvenuto nella Sezione di Gestione per l'Operaio</h2>
        <p>Come operaio hai il compito di svolgere i compiti assegnati, monitorare i tuoi progressi e comunicare il completamento dei 
            compiti nel rispetto delle tempistiche stabilite.</p>
        <ul>
            <li><i class="fa-solid fa-bell"></i>&nbsp;<strong>Notifiche:</strong> Ricevi aggiornamenti sui nuovi compiti assegnati e 
            sugli avvisi importanti relativi al tuo lavoro.</li><br>
            <li><i class="fas fa-tasks"></i>&nbsp;<strong>I miei Compiti:</strong> Visualizza la lista dei compiti a te assegnati, 
            con dettagli e scadenze associate.</li><br>
            <li><i class="fas fa-clock"></i>&nbsp;<strong>Ore Lavorate:</strong> Inserisci le ore impiegate per ogni compito e invia 
            comunicazioni di completamento direttamente dalla piattaforma.</li><br>
            <li><i class="fa-solid fa-chart-line"></i>&nbsp;<strong>Messaggi:</strong> Comunica con responsabili e colleghi per chiarimenti o 
            aggiornamenti sulle attività in corso.</li><br>
            <li><i class="fa-solid fa-user"></i>&nbsp;<strong>Account:</strong> Visualizza e aggiorna le informazioni relative al tuo account personale.</li><br>
            <li><i class="fa-solid fa-right-from-bracket"></i>&nbsp;<strong>Logout:</strong> Esci dalla piattaforma in modo sicuro al termine 
            delle operazioni.</li>
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