<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin(); 

    //Menu per il tipo di utente
    $menu = getMenuPerUtente($_SESSION['tipo_utente'],$_SESSION['ruolo']);	
    $id_utente = $_SESSION['id_utente'];

    //Select per le risorse di un'azienda
    $risorse = [];
    $magazzinieri=[];
    $sql_mat = "SELECT id,nome,categoria,unita_misura FROM materiali_attrezzature WHERE id_azienda=?" ;
    $stmt_mat = $conn->prepare($sql_mat);
    $stmt_mat -> bind_param("i",$_SESSION['id_azienda']);
    $stmt_mat -> execute();
    $result = $stmt_mat -> get_result();
    while($row = $result -> fetch_assoc()){
            $risorse[]=$row;
    }
    $stmt_mat->close();

    //Select per i magazzinieri
    $sql_destinatari = "SELECT id_utente,nome,cognome FROM utenti WHERE ruolo = 'Magazziniere' AND id_azienda=?";
    $sql_utenti_richiesta = $conn->prepare($sql_destinatari);
    $sql_utenti_richiesta -> bind_param("i",$_SESSION['id_azienda']);
    $sql_utenti_richiesta->execute();
    $result = $sql_utenti_richiesta->get_result();
    while($row = $result->fetch_assoc()){
            $magazzinieri[]=$row;
        }
    $sql_utenti_richiesta->close();

    //Inserimento della richiesta del materiale
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $id_mittente=$id_utente;
        $id_destinatario =$_POST['id_destinatario'];
        $id_risorsa=$_POST['id_risorsa'];
        $id_compito = $_POST['id_compito'];
        $quantita = $_POST['quantita'];
        $note = $_POST['note'];	
        $stato = 'In Attesa';
        $tipo = 'Uscita';
        $sql_richiesta="INSERT INTO movimenti_materiali(id_mittente,id_destinatario,id_risorsa,id_compito,quantita,stato,note,tipo)
        VALUES (?,?,?,?,?,?,?,?)";
        $stmt_richiesta=$conn->prepare($sql_richiesta);
        $stmt_richiesta->bind_param("iiiiisss",$id_mittente,$id_destinatario,$id_risorsa,$id_compito,$quantita,$stato,$note,$tipo);
        
        //Invio delle notifiche 
        if($stmt_richiesta->execute()){
            echo "Richiesta inviata con successo!";
            $messaggio="Hai ricevuto una nuova richiesta di materiale da parte di " . $_SESSION['nome'] . " " . $_SESSION['cognome'];
            inviaNotifica($conn,$id_destinatario,'Nuova Richiesta Materiale',$messaggio,"gestione_mat_e_attr.php");
        }else {
            echo "Errore durante l'inserimento della richiesta.";
        }
    }

    //Select per i compiti 
    $compiti = []; 
    $sql_compiti = "SELECT c.descrizione,c.id_compito,c.id_attivita,a.id_attivita,a.id_responsabile 
    FROM compiti AS c JOIN attivita AS a ON c.id_attivita = a.id_attivita
    WHERE a.id_responsabile = ? AND c.stato ='In Corso'";
    $stmt_compiti = $conn->prepare($sql_compiti);
    $stmt_compiti -> bind_param("i",$id_utente);
    $stmt_compiti -> execute();
    $result = $stmt_compiti -> get_result();
    while($row = $result -> fetch_assoc()){
        $compiti[] = $row;
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
        <title>Richiesta Materiali</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
        <!--Intestazione con logo -->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Invia Richiesta Materiale</h1>
                <div class="div_button">
                <button onclick="location.href='richieste_mat.php'" class="back">
                <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>                
            </div>
            </div>
            <!--Form per la richiesta dei materiali-->
            <div class="container">
                <div class="box_form">
                                <form action="nuova_richiesta_mat.php" method="POST">
                                <div class="parametri">
                                <label for="destinatario">Seleziona il Destinatario della Richiesta:</label>
                                    <select id="destinatario" name="id_destinatario" required>
                                    <option value="" selected disabled></option> 
                                    <?php foreach($magazzinieri as $magazziniere):?>
                                    <option value="<?php echo $magazziniere['id_utente'];?>">
                                        <?php echo $magazziniere['nome']." ".$magazziniere['cognome']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select> 
                            </div> 
                            <div class="parametri">
                                <label for="compito">Seleziona il Compito:</label>
                                    <select id="compito" name="id_compito" required>
                                    <option value="" selected disabled></option> 
                                    <?php foreach($compiti as $compito):?>
                                    <option value="<?php echo $compito['id_compito']; ?>">
                                        <?php echo $compito['descrizione']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select> 
                            </div> 
                            <div class="parametri">
                                <label for="risorsa">Materiale:</label>
                                    <select id="risorsa" name="id_risorsa" required>
                                    <option value="" selected disabled></option> 
                                    <?php foreach($risorse as $risorsa):?>
                                    <option value="<?php echo $risorsa['id'];?>">
                                        <?php echo $risorsa['nome'] ." (".$risorsa['categoria']. " ".$risorsa['unita_misura'].")"; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select> 
                            </div> 
                                <div class="parametri">
                                <label for="budget">Quantità:</label>
                                <input type="number" name="quantita" id="quantita" required>
                            </div>
                                <div class="parametri">    
                                    <label for="note">Note:</label>
                                    <textarea id="note" name="note"></textarea>
                                </div>


        <!--Sezione dei bottoni-->
                <div class="last">
                        <button type="submit" class="crea"onclick="return confirm('Sei sicuro di voler inviare questa richiesta?');" ><i class="fas fa-plus"></i>&nbsp;Invia</button>
                        <a href="javascript:history.back()" class="back2"><i class="fas fa-times"></i>&nbsp;Annulla</a>
                    </form> 
                </div>   
            </div>
        </div>
    </body>
</html>