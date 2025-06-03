<?php 
    require 'db.php';
    session_start();

    //Controllo del tipo di utente
    if(!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] != 'Dipendente Aziendale' || $_SESSION['ruolo'] != 'Magazziniere'){
        die("Connessione non riuscita");  
    }
    if($conn->connect_error){
        die("Connessione Fallita: " .$conn->connect_error);
    }

    //query per il conteggio delle notifiche 
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
        <title>Menu Magazziniere</title>
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
        <a class="voce" href="gestione_mat_e_attr.php"><i class="fas fa-warehouse"></i>&nbsp;Gestione Materiali/Attrezzature</a>
        <a class="voce" href="richieste_mat.php"><i class="fas fa-history"></i>&nbsp;Storico Movimenti</a>
        <a class="voce" href="report_magazziniere.php"><i class="fas fa-chart-line"></i>&nbsp;Report</a>
        <a class="voce" href="messaggi.php"><i class="fa-solid fa-envelope"></i>&nbsp;Messaggi</a>
        <a class="voce" href="account.php"><i class="fa-solid fa-user"></i>&nbsp;Account</a>
        <a class="voce" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
    </div>
    <!--Guida per il magazziniere-->
    <div class="guida">
        <h2>Benvenuto nella Sezione di Gestione per il Magazziniere</h2>
        <p>Come magazziniere puoi gestire materiali e attrezzature aziendali, monitorare i movimenti e comunicare con gli altri utenti 
            della piattaforma.</p>
        <ul>
            <li><i class="fa-solid fa-bell"></i>&nbsp;<strong>Notifiche:</strong> Ricevi aggiornamenti sulle richieste di materiali e altre 
            comunicazioni importanti.</li><br>
            <li><i class="fas fa-warehouse"></i>&nbsp;<strong>Gestione Materiali/Attrezzature:</strong> Aggiungi, modifica o elimina i 
            materiali e le attrezzature aziendali, accetta o rifiuta le richieste 
            di materiali provenienti dai responsabili.</li><br>
            <li><i class="fas fa-history"></i>&nbsp;<strong>Storico Movimenti:</strong> Consulta una sezione dedicata ai movimenti dei 
            materiali e delle attrezzature.</li><br>
            <li><i class="fas fa-chart-line"></i>&nbsp;<strong>Report:</strong> Analizza i grafici relativi ai tipi di movimenti, materiali
            richiesti e richieste gestite per utente.</li><br>
            <li><i class="fa-solid fa-envelope"></i>&nbsp;<strong>Messaggi:</strong> Comunica direttamente con altri utenti della piattaforma 
            per coordinare le attività di magazzino.</li><br>
            <li><i class="fa-solid fa-user"></i>&nbsp;<strong>Account:</strong> Gestisci e aggiorna i dati relativi al tuo profilo personale.</li><br>
            <li><i class="fa-solid fa-right-from-bracket"></i>&nbsp;<strong>Logout:</strong> Esci in sicurezza dal sistema una volta concluse le operazioni.</li>
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