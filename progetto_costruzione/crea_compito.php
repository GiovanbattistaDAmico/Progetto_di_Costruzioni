<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    
    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $ruolo = $_SESSION['ruolo'];
    $id_utente = $_SESSION['id_utente'];
    $link = $_SERVER['REQUEST_URI']; 

    //Parte di creazione del compito con i vari parametri da parte del responsabile di attività
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $id_attivita=$_POST['id_attivita'];
        $descrizione=$_POST['descrizione'];
        $id_operaio=$_POST['id_operaio'];
        $id_azienda=$_SESSION['id_azienda'];
        $sql_compito="INSERT INTO compiti(id_attivita,descrizione,id_operaio) VALUES (?,?,?)";
        $stmt_compito=$conn->prepare($sql_compito);
        $stmt_compito->bind_param("isi",$id_attivita,$descrizione,$id_operaio);
        if($stmt_compito->execute()){
            echo "Compito creato con successo";
             //funzione di invio delle notifiche per l'operaio
            inviaNotifica($conn,$id_operaio,"Nuovo Compito ","Sei stato assegnato al compito di un'attività","gestione_compiti.php");
        }else{
            echo "Errore nella creazione del compito" . $stmt_compito->error();
        }
    }

    //Selezione delle attività per il responsabile di attività
    $attivita=[];
    $sql_attivita = "SELECT id_attivita, nome_attivita FROM attivita WHERE id_responsabile = ?";
    $stmt_attivita = $conn->prepare($sql_attivita);
    $stmt_attivita -> bind_param("i",$id_utente);
    $stmt_attivita -> execute();
    $result = $stmt_attivita ->get_result();
    while ($row = $result -> fetch_assoc()){
        $attivita[]=$row;
    }

    //Selezione per i vari operai dell'azienda che poi verranno assegnati ai diversi compiti
    $operai =[];
    $sql_operai = "SELECT id_utente, nome,cognome FROM utenti WHERE id_azienda = ? AND ruolo = 'Operaio'";
    $stmt_operai = $conn->prepare($sql_operai);
    $stmt_operai -> bind_param("i",$_SESSION['id_azienda']);
    $stmt_operai -> execute();
    $result_operai = $stmt_operai ->get_result();
    while ($row = $result_operai -> fetch_assoc()){
        $operai[]=$row;
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'progettiCSS.php'?>
        <title>Crea Compito</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
            <div class="intestazione">
            <!--Intestazione con logo della pagina-->
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Crea Compito</h1>
                <div class="div_button">
                <button onclick="location.href='gestione_compiti.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Sezione dati della form per i compiti-->
            <div class="container">
                <div class="box_form">
                        <form action="crea_compito.php" method="POST">
                        <div class="parametri">
                            <label for="descrizione">Descrizione</label>
                            <textarea id="descrizione" name="descrizione" required></textarea>
                            </div>
                            <div class="parametri">
                            <label for="id_attivita">Seleziona Attività</label>
                            <select name="id_attivita" id="id_attivita">
                                <option value="" selected> 
                                <?php foreach($attivita as $a): ?>
                                    <option value="<?php echo $a['id_attivita']; ?>"> 
                                        <?php echo $a['nome_attivita']; ?> 
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <div class="parametri">
                            <label for="operaio">Operaio</label>
                            <select name="id_operaio" id="operaio">
                            <option value="" selected> 
                                <?php foreach($operai as $operaio): ?>
                                    <option value="<?php echo $operaio['id_utente']; ?>"> 
                                        <?php echo $operaio['nome'] . " ". $operaio['cognome']; ?> 
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
        <!--Sezione dei bottoni-->
                    <div class="last">
                        <button type="submit" onclick="return confirm('Sei sicuro di voler creare questo compito?');" class="crea"><i class="fas fa-plus"></i>&nbsp;Crea</button>
                        <a href="javascript:history.back()" class="back2"><i class="fas fa-times"></i>&nbsp;Annulla</a>
                        </form> 
                    </div>
                
                </div>
            </div>
    </body>
</html>