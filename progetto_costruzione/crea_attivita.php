<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    $ruolo = $_SESSION['ruolo'];
    $id_utente = $_SESSION['id_utente'];
    $link = $_SERVER['REQUEST_URI'];
    $mostra_responsabili=false;
    
    //Rimozione delle notifiche riguardanti la pagina
    rimuoviNotifiche($conn,$id_utente,$link);
    if($_SESSION['tipo_utente']=='Azienda' || $_SESSION['ruolo']=='Responsabile'){
        $id_azienda=$_SESSION['id_azienda'];
        $id_responsabile=NULL;
        $mostra_responsabili=true;
    }
    //Raccolta dei progetti di cui l'utente è il responsabile
    $sql_progetti="SELECT id_progetto,nome_progetto FROM progetti WHERE id_responsabile =?";
    $stmt_progetti=$conn->prepare($sql_progetti);
    $stmt_progetti->bind_param("i",$id_utente);
    $stmt_progetti->execute();
    $result2=$stmt_progetti->get_result();
    $progetti = [];
    while ($row2 = $result2->fetch_assoc()) {
            $progetti[] = $row2; // Memorizza tutti i progetti legati al responsabile
    }
    $stmt_progetti->close();

    //Creazione dell'Attività con tutti i parametri inseriti dal responsabile di Progetto
    if($_SERVER['REQUEST_METHOD']=='POST'){ 
        $id_responsabile = $_POST['id_responsabile'];
        $id_azienda = $_SESSION['id_azienda'];
        $nome_attivita = $_POST['nome_attivita'];
        $id_progetto = $_POST['id_progetto'];
        $descrizione = $_POST['descrizione'];
        $sql = "INSERT INTO attivita (id_progetto,nome_attivita,descrizione,id_responsabile) 
        VALUES (?,?,?,?)";
        $sql_attivita = $conn->prepare($sql);
        $sql_attivita->bind_param("issi", $id_progetto, $nome_attivita, $descrizione,$id_responsabile);        
        if ($sql_attivita->execute() && $ruolo=='Responsabile') {
             //funzione di invio delle notifiche per il responsabile di attività
            inviaNotifica($conn,$id_responsabile,"Nuova Attività","Sei stato assegnato come Responsabile di attività di un progetto",
            "gestione_attivita.php");
            echo "Attività creata con successo!";
        }else {
            echo "Errore nella creazione dell'attività: " . $conn->error;
        }
        $sql_attivita->close();
    }

    //Raccolta di tutti i responsabili dell'azienda tranne l'utente stesso
    $sql="SELECT id_utente,nome,cognome,ruolo FROM utenti WHERE (id_azienda = ? AND ruolo = 'Responsabile' AND id_utente != ?)";
    $sql_selezione=$conn->prepare($sql);
    $sql_selezione->bind_param("ii",$id_azienda,$id_utente);
    $sql_selezione->execute();
    $result=$sql_selezione->get_result();
    $responsabili = [];
    while ($row = $result->fetch_assoc()) {
            $responsabili[] = $row; // Memorizza tutti i responsabili
    }
        $sql_selezione->close();

        // Chiudere la connessione al database
        $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'progettiCSS.php'?>
        <title>Crea Attività</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

            <!--Intestazione con logo della pagina-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Crea Attività</h1>
                <div class="div_button">
                <button onclick="location.href='gestione_attivita.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>

            <!--Sezione dati della form per le attività-->
            <div class="container">
            <div class="box_form">
                                <form action="crea_attivita.php" method="POST">
                                <div class="parametri">    
                                    <label for="nome_attivita">Nome Attività:</label>
                                    <input type="text" id="nome_attivita" name="nome_attivita" required>
                                </div>
                                <div class="parametri">
                                <label for="progetto">Seleziona il Progetto:</label>
                                    <select id="progetto" name="id_progetto">
                                        <option value="" selected disable></option>
                                        <?php foreach($progetti as $progetto):?>
                                    <option value="<?php echo $progetto['id_progetto'];?>" name="id_progetto" id="progetto">
                                        <?php echo $progetto['nome_progetto'];?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="parametri">
                                    <label for="descrizione">Descrizione:</label>
                                    <textarea id="descrizione" name="descrizione" required></textarea>
                                </div>
                                <?php if($mostra_responsabili==true){?>
                                <div class="parametri">
                                <label for="responsabile">Seleziona il Responsabile:</label>
                                    <select id="responsabile" name="id_responsabile">
                                    <option value="" selected disabled></option> 
                                    <?php foreach($responsabili as $responsabile):?>
                                    <option value="<?php echo $responsabile['id_utente'];?>"><?php echo $responsabile['nome']." ".$responsabile['cognome'];?></option>
                                    <?php endforeach; ?>
                                </div>
                    <?php } ?>
                                    </select>
        <!--Sezione dei bottoni-->
                <div class="last">
                <button type="submit" onclick="return confirm('Sei sicuro di voler creare questa attività?');"class="crea"><i class="fas fa-plus"></i>&nbsp;Crea</button>
                        <a href="javascript:history.back()" class="back2"><i class="fas fa-times"></i>&nbsp;Annulla</a>
                        </form> 
                </div>
                
            </div>
    </div>
    </body>
</html>