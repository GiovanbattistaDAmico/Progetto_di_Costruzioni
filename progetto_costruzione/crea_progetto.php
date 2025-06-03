<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    //Query per la creazione del progetto da parte dell'amministratore aziendale
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $nome_progetto = $_POST['nome_progetto'];
        $descrizione = $_POST['descrizione'];
        $data_inizio = $_POST['data_inizio'];
        $data_scadenza = $_POST['data_scadenza'];
        $budget = $_POST['budget'];
        $id_committente = $_POST['id_committente'];
        $id_responsabile = $_POST['id_responsabile']; // Responsabile selezionato durante la creazione del progetto
        $id_azienda=$_SESSION['id_azienda'];
        // Inserimento progetto
        $sql = "INSERT INTO progetti (nome_progetto, descrizione, data_inizio, data_scadenza, budget, id_committente, id_responsabile,id_azienda) VALUES (?,?,?,?,?,?,?,?)";
        $sql_progetto = $conn->prepare($sql);
        $sql_progetto->bind_param("ssssdiii", $nome_progetto, $descrizione, $data_inizio, $data_scadenza, $budget, $id_committente, $id_responsabile,$id_azienda);
        if($sql_progetto->execute()){ 
            //funzione di invio delle notifiche per il responsabile e il committente
            inviaNotifica($conn,$id_responsabile,"Nuovo Progetto","Ti è stato assegnato un nuovo Progetto","gestione_progetti.php");
            inviaNotifica($conn,$id_committente,"Nuovo Progetto","E' stato sviluppato il progetto da te richiesto","gestione_progetti.php");
        }
        $sql_progetto->close();
        echo "Progetto creato con successo!";
    }

    // Selezione responsabili
    $sql = "SELECT id_utente, nome, cognome FROM utenti WHERE id_azienda = ? AND ruolo = 'Responsabile'";
    $sql_selezione = $conn->prepare($sql);
    $sql_selezione->bind_param("i", $_SESSION['id_azienda']);
    $sql_selezione->execute();
    $result = $sql_selezione->get_result();

    $responsabili = [];
    while ($row = $result->fetch_assoc()) {
        $responsabili[] = $row; // Memorizza i responsabili
    }

    //Selezione dei committenti
    $sql_committenti = "SELECT id_utente, nome, cognome FROM utenti WHERE tipo_utente = 'Committente'";
    $sql_selezione_committenti = $conn->prepare($sql_committenti);
    $sql_selezione_committenti->execute();
    $result_committenti = $sql_selezione_committenti->get_result();
    $committenti = [];

    while ($row = $result_committenti->fetch_assoc()) {
        $committenti[] = $row; // Memorizza tutti i committenti
    }
    $conn->close();
?>  
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'progettiCSS.php'?>
        <title>Crea Progetti</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
            <div class="intestazione">
                <!--Intestazione con logo della pagina-->
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Crea Progetti</h1>
                <div class="div_button">
                <button onclick="location.href='menu_Aaziendale.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Sezione dati della form per i progetti-->
            <div class="container">
                <div class="box_form">
                    
                                <form action="crea_progetto.php" method="POST">
                                <div class="parametri">    
                                    <label for="nome_progetto">Nome Progetto:</label>
                                    <input type="text" id="nome_progetto" name="nome_progetto" required>
                                </div>
                                <div class="parametri">
                                    <label for="descrizione">Descrizione:</label>
                                    <textarea id="descrizione" name="descrizione" required></textarea>
                                </div>
                                <div class="parametri">
                                    <label for="data_inizio">Data Inizio:</label>
                                    <input type="date" id="data_inizio" name="data_inizio" required>
                                </div>
                                <div class="parametri">
                                    <label for="data_scadenza">Data Scadenza:</label>
                                    <input type="date" id="data_scadenza" name="data_scadenza" required>
                                </div>
                                <div class="parametri">
                                    <label for="budget">Budget (€):</label>
                                    <input type="number" id="budget" name="budget" step="0.01" min="0" required>
                                </div>
                                <div class="parametri">
                                    <label for="committente">Seleziona il Committente:</label>
                                    <select id="committente" name="id_committente" required>
                                        <option value="" selected disable>-- Seleziona --</option>
                                        <?php foreach($committenti as $committente):?>
                                        <option value="<?php echo $committente['id_utente'];?>">
                                            <?php echo $committente['nome']." ".$committente['cognome'];?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="parametri">
                                <label for="responsabile">Seleziona il Responsabile:</label>
                                    <select id="responsabile" name="id_responsabile">
                                    <option value="" selected disabled>--Seleziona Responsabile--</option> 
                                    <?php foreach($responsabili as $responsabile):?>
                                    <option value="<?php echo $responsabile['id_utente'];?>"><?php echo $responsabile['nome']." ".$responsabile['cognome'];?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
        <!--Sezione dei bottoni-->
                <div class="last">
                <button type="submit"  onclick="return confirm('Sei sicuro di voler creare questo progetto?');"  class="crea"><i class="fas fa-plus"></i>&nbsp;Crea</button>
                        </form> 
                </div>
                
            </div>
    </div>
    </body>
</html>