<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);

    //Recupero dell'id del destinatario nel caso il mittente avesse cliccato su rispondi 
    if(isset($_GET['id_destinatario'])){
        $id_destinatario=$_GET['id_destinatario'];
    }else{
        $id_destinatario=null;
    }

    //Selezione del nome e cognome del destinatario nel caso non fosse vuoto l'id
    if($id_destinatario){
        $sql_destinatario = "SELECT nome,cognome FROM utenti WHERE id_utente=?";
        $stmt_destinatario = $conn->prepare($sql_destinatario);
        $stmt_destinatario -> bind_param("i",$id_destinatario);
        $stmt_destinatario -> execute();
        $result = $stmt_destinatario -> get_result();
        $destinatario = $result -> fetch_assoc();
    }

    //Invio del messaggio
    if($_SERVER['REQUEST_METHOD']=="POST"){
        if($id_destinatario==null){
        $id_destinatario = $_POST['id_destinatario'];}
        $oggetto = $_POST['oggetto'];
        $contenuto = $_POST['contenuto'];
        $sql_invio = "INSERT INTO messaggi (id_mittente,id_destinatario,oggetto,contenuto,data_invio,letto) 
        VALUES (?,?,?,?,NOW(),FALSE)";
        $stmt_invio = $conn->prepare($sql_invio);
        $stmt_invio -> bind_param("iiss",$_SESSION['id_utente'],$id_destinatario,$oggetto,$contenuto);
        if($stmt_invio -> execute()){
            echo "Messaggio inviato con successo";
            $mittente = $_SESSION['nome'] . ' ' . $_SESSION['cognome'];
            inviaNotifica($conn, $id_destinatario, "Nuovo Messaggio", "Hai ricevuto un messaggio da: $mittente", "messaggi.php");
            header("Location: messaggi.php"); // Reindirizza alla pagina dei messaggi
            exit;
        }
    }
    //SELECT per ottenere i nome e cognomi dei destinatari in base ai ruoli
    $destinatari = [];
    if($_SESSION['tipo_utente']=='Admin'){
        $sql_destinatari = "SELECT id_utente, nome, cognome , tipo_utente,ruolo FROM utenti WHERE id_utente != ?";
        $stmt_destinatari = $conn->prepare($sql_destinatari);
        $stmt_destinatari->bind_param("i", $_SESSION['id_utente']);
    }elseif($_SESSION['tipo_utente']=='Dipendente Aziendale' && $_SESSION['ruolo'] == 'Magazziniere'){
        $sql_destinatari = "SELECT id_utente, nome, cognome, tipo_utente,ruolo FROM utenti WHERE ruolo IN 
        ('Amministratore Aziendale', 'Responsabile','Magazziniere') AND id_utente != ? AND id_azienda=?";
        $stmt_destinatari = $conn->prepare($sql_destinatari);
        $stmt_destinatari->bind_param("ii", $_SESSION['id_utente'],$_SESSION['id_azienda']);
    }elseif($_SESSION['tipo_utente']=='Dipendente Aziendale' && $_SESSION['ruolo'] == 'Contabile'){
        $sql_destinatari = "SELECT id_utente, nome, cognome, tipo_utente,ruolo FROM utenti WHERE ruolo IN 
        ('Amministratore Aziendale', 'Responsabile','Admin','Contabile') AND id_utente != ? AND id_azienda=?";
        $stmt_destinatari = $conn->prepare($sql_destinatari);
        $stmt_destinatari->bind_param("ii", $_SESSION['id_utente'],$_SESSION['id_azienda']);
    }elseif($_SESSION['tipo_utente']=='Committente'){
        $sql_destinatari = "SELECT id_utente, nome, cognome, tipo_utente,ruolo FROM utenti WHERE ruolo IN ('Amministratore Aziendale', 'Responsabile') 
        AND id_utente != ?";
        $stmt_destinatari = $conn->prepare($sql_destinatari);
        $stmt_destinatari->bind_param("i", $_SESSION['id_utente']);
    }elseif($_SESSION['ruolo']=='Amministratore Aziendale' || $_SESSION['ruolo']=='Responsabile'){
        $sql_destinatari = "SELECT  id_utente,nome,cognome, tipo_utente,ruolo FROM utenti WHERE id_azienda=? AND id_utente != ? 
        OR tipo_utente = 'Committente'";
        $stmt_destinatari = $conn->prepare($sql_destinatari);
        $stmt_destinatari -> bind_param("ii",$_SESSION['id_azienda'],$_SESSION['id_utente']);
    }elseif($_SESSION['ruolo']=='Operaio'){
        $sql_destinatari = "SELECT  id_utente,nome,cognome, tipo_utente,ruolo FROM utenti WHERE id_azienda=? AND id_utente != ?";
        $stmt_destinatari = $conn->prepare($sql_destinatari);
        $stmt_destinatari -> bind_param("ii",$_SESSION['id_azienda'],$_SESSION['id_utente']);
    }
    if($stmt_destinatari->execute()){
        $result = $stmt_destinatari->get_result();
        while($row = $result -> fetch_assoc()){
            $destinatari[] = $row;
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'messaggiCSS.php'?>
        <title>Invia Messaggio</title>
    </head>
    <body>
        <!--Intestazione con ruolo -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Invia Messaggio</h1>
                <div class="div_button">
                <button onclick="location.href='<?php echo $menu; ?>'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>
                </div>
            </div>
            <!--Form per i campi per l'invio dei messaggi-->
            <div class="container">
                <div class="box_form">
                    
                            <form action="invia_messaggio.php" method="POST">
                            <div class="parametri">
                                <label for="destinatario">Seleziona il Destinatario del Messaggio:</label>
                                    <select id="destinatario" name="id_destinatario">
                                    <?php foreach($destinatari as $d):?>
                                        <option value="<?php echo $d['id_utente'];?>" <?php if($id_destinatario == $d['id_utente']) echo 'selected'; ?>>
                                            <?php echo $d['nome']." ".$d['cognome']." (".$d['tipo_utente'].")"; ?>
                                        </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div> 
                                <div class="parametri">    
                                    <label for="oggetto">Oggetto:</label>
                                    <input type="text" id="oggetto" name="oggetto" required>
                                </div>
                                <div class="parametri">
                                    <label for="contenuto">Contenuto:</label>
                                    <textarea id="contenuto" name="contenuto" required></textarea>
                                </div>
                       
        <!--Sezione dei bottoni-->
                <div class="last">
                        <button type="submit" class="invia"><i class="fas fa-plus"></i>&nbsp;Invia</button>
                        <a href="javascript:history.back()" class="annulla"><i class="fas fa-times"></i>&nbsp;Annulla</a>
                        </form> 
                </div>   
            </div>
        </div>
    </body>
</html>