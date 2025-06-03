<?php 
    session_start();
    require 'db.php';
    include 'funzioni.php';
    verificaLogin();

    //Assegnazione alla variabile del link per il menu per tipo utente
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $id_utente = $_SESSION['id_utente'];
    $tipo_utente = $_SESSION['tipo_utente'];
    $link = $_SERVER['REQUEST_URI'];
    $id_azienda = $_SESSION['id_azienda'];

    //Funzione per la rimozione delle notifiche 
    rimuoviNotifiche($conn,$id_utente,$link);
    $nome = $_SESSION['nome'];
    $cognome = $_SESSION['cognome'];

    // Se l'azienda non ha i parametri definiti, blocca l'accesso ai servizi
    $id_utente = $_SESSION['id_utente']; 
    $tipo_utente = $_SESSION['tipo_utente'];    
    $materiali = [];
    if($tipo_utente == 'Admin'){
        $sql = "SELECT m.*,a.nome_azienda FROM materiali_attrezzature AS m JOIN aziende AS a ON m.id_azienda = a.id_azienda";
        $result = $conn->query($sql);
    }
    elseif($_SESSION['ruolo'] == 'Amministratore Aziendale' || $_SESSION['ruolo'] == 'Magazziniere'){
    //PRELEVA TUTTI I MATERIALI O ATTREZZATURE DELL'AZIENDA
        $sql_mat = "SELECT * FROM materiali_attrezzature WHERE id_azienda = ?";
        $stmt_mat = $conn->prepare($sql_mat);
        $stmt_mat ->bind_param("i",$_SESSION['id_azienda']);
        $stmt_mat -> execute();
        $result = $stmt_mat->get_result();         
    } 
    while ($row = $result->fetch_assoc()) {
        $materiali[] = $row;
        } 
    //Invio della notifica se la scorta minima supera quella attuale del materiale
    foreach ($materiali as $materiale) {
        if ($materiale['quantita'] < $materiale['scorta_minima'] && $materiale['categoria'] == 'Materiale') {
            inviaNotifica($conn,$id_utente,"Scorta materiale insufficiente","La quantità del materiale \"{$materiale['nome']}\" 
            è inferiore alla scorta minima.","gestione_mat_e_attr.php");
        }
    }

    //Query per le richieste di movimento dei materiali 
    $richieste=[];
    $sql_richieste_mag = "SELECT m.*, u.id_utente, u.nome AS nome_responsabile, u.cognome AS cognome_responsabile,
                    mat.id,mat.descrizione, mat.nome AS nome_materiale,mat.unita_misura,mat.categoria
                    FROM movimenti_materiali AS m 
                    JOIN utenti AS u ON m.id_mittente = u.id_utente 
                    JOIN materiali_attrezzature AS mat ON m.id_risorsa = mat.id
                    WHERE u.id_azienda = ? AND m.stato = 'In Attesa'";
    $stmt_richieste_mag = $conn->prepare($sql_richieste_mag);
    $stmt_richieste_mag ->bind_param("i",$_SESSION['id_azienda']);
    $stmt_richieste_mag -> execute();
    $result = $stmt_richieste_mag->get_result();
    while($row = $result->fetch_assoc()){
        $richieste[] = $row;
    }
    $stmt_richieste_mag->close();

    //Query per la risposta del Magazziniere
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $id_movimento = $_POST['id_movimento'];
        $risposta = $_POST ['risposta'];
        $query = "SELECT id_mittente FROM movimenti_materiali WHERE id_movimento = $id_movimento";
        $result = $conn->query($query);
    
        //Query in base all'accettazione o rifiuto della richieste e successivo invio della notifica
    if ($result && $row = $result->fetch_assoc()) {
        $id_mittente = $row['id_mittente'];
        if ($_POST['azione'] == 'accetta') {
            $sql_stato = "UPDATE movimenti_materiali SET stato = 'Approvato', risposta = '$risposta', data_risposta = NOW() 
            WHERE id_movimento = $id_movimento";
            $conn->query($sql_stato);
            inviaNotifica($conn,$id_mittente,"Richiesta di movimento Approvata: ",
            "La tua richiesta di movimento dei materiali è stata approvata dal Magazziniere ".$nome ." ".$cognome,"richieste_mat.php");
        }else{
            $sql_stato = "UPDATE movimenti_materiali SET stato = 'Rifiutato', risposta = '$risposta', data_risposta = NOW() 
            WHERE id_movimento = $id_movimento";
            $conn->query($sql_stato);
            inviaNotifica($conn,$id_mittente,"Richiesta di movimento Rifiutata: ",
            "La tua richiesta di movimento dei materiali è stata rifiutata dal Magazziniere ".$nome ." ".$cognome,"richieste_mat.php");
        }
    }
        header("Location: gestione_mat_e_attr.php");
        exit;
    }
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestione Materiali e Attrezzature</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <?php include "materialiCSS.php" ?>
        <?php include 'progettiCSS.php'?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
        <!--Intestazione con logo -->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Gestione Materiali e Attrezzature</h1>
            <div class="div_button">
            <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>  
            </div>
        </div>
        <!--Bottoni per cambiare sezione per il Magazziniere -->
        <?php if($tipo_utente != 'Admin'  &&  $_SESSION['ruolo'] != 'Amministratore Aziendale'): ?>
        <div class="button">
            <div class="buttons1">
                    <button class="add_button1" onclick="toggleSection('materiali')">Elenco Materiali/Attrezzature</button>
                   <button class="add_button1" onclick="toggleSection('richieste')">Richieste Materiali/Attrezzature</button>
            </div>
            <div class="buttons2">
                <button class="add_button" onclick='location.href="aggiungi_mat.php"'>Aggiungi Materiale/Attrezzatura</button>
                <button class="add_button" onclick='location.href="modifica_mat.php"'>Modifica Materiale/Attrezzatura</button>
        </div>
        </div>
        <?php endif; ?>
        <!--Lista dei materiali e attrezzature con parametri-->
        <div id="materiali" class="lista_materiali">
            <h1>Lista Materiali e Attrezzature</h1>
            <?php if(!empty($materiali)): ?>
            <table>
                <tr> 
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Tipo</th>
                    <th>Quantità</th>
                    <th>Stato</th>
                    <?php if($tipo_utente != 'Admin'):?>
                    <th>Ubicazione</th> 
                    <th>Costo Unitario</th>
                    <th>Scorta Minima</th> 
                    <?php else: ?>
                    <th>Azienda Proprietaria</th>
                    <?php endif; ?>                
                </tr>
                <?php foreach($materiali as $materiale): ?>
                <tr>
                        <td><?php echo $materiale['nome']; ?></td>
                        <td><?php echo $materiale['descrizione']; ?></td>
                        <td><?php echo $materiale['categoria']; ?></td>
                        <td><?php echo $materiale['quantita'] ." ". $materiale['unita_misura'];
                        if($materiale['quantita'] < $materiale['scorta_minima']):?>
                        <i class="fas fa-exclamation-triangle" style="color:red;" title="Sotto scorta minima"></i>
                        <?php endif; ?>
                        </td>
                        <td><?php echo $materiale['stato']; ?></td>
                        <?php if($tipo_utente != 'Admin'):?>
                        <td><?php echo $materiale['ubicazione']; ?></td>
                        <td><?php echo $materiale['costo_unitario']; ?></td>
                        <td><?php echo $materiale['scorta_minima'] ." ". $materiale['unita_misura']; ?></td>
                        <!-- Parte dell'admin -->     
                        <?php else: ?>
                        <td><?php echo $materiale['nome_azienda']; ?></td>
                        <?php endif; ?>

                </tr>
                <?php endforeach;?>
            </table>
            <?php else: ?>                    
                <p> Non ci sono Materiali o Attrezzature salvate.</p>
               <?php endif; ?>
        </div>

        <!--Lista delle richieste dei materiali-->
        <div class="lista_richieste_mat" id="richieste" style="display:none">
            <h1>Elenco Richieste Materiali/Attrezzature</h1>
            <?php if(count($richieste)>0){ ?>
                <table>
                    <tr>
                        <th>Richiedente</th>
                        <th>Tipo Richiesta</th>
                        <th>Descrizione Compito</th>
                        <th>Categoria Risorsa</th>
                        <th>Quantità</th>
                        <th>Note</th>
                        <th>Data Richiesta</th>
                        <th>Risposta</th>
                    </tr>
                    <?php foreach($richieste as $richiesta){?>
                        <tr>
                            <td><?php echo $richiesta['nome_responsabile'] . ' ' . $richiesta['cognome_responsabile']; ?></td>
                            <td><?php echo $richiesta['tipo']; ?></td>
                            <td><?php echo $richiesta['descrizione']; ?></td>
                            <td><?php echo $richiesta['categoria'] == 'Materiale' ? 'Materiale' : 'Attrezzatura'; ?></td>
                            <td><?php echo $richiesta['quantita']." ".$richiesta['unita_misura']; ?></td>
                            <td><?php echo $richiesta['note']; ?></td>
                            <td><?php echo $richiesta['data_richiesta']; ?></td>
                            <td>
                                <form action="gestione_mat_e_attr.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id_movimento" value="<?php echo $richiesta['id_movimento']; ?>">
                                    <textarea name="risposta" placeholder="Scrivi una risposta..." rows="2" cols="20" required></textarea><br>
                                    <button type="submit" name="azione" onclick="return confirm('Sei sicuro di voler accettare la richiesta?');" value="accetta">Accetta</button>
                                    <button type="submit" name="azione" onclick="return confirm('Sei sicuro di voler rifiutare la richiesta?');" value="rifiuta">Rifiuta</button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
            <?php } else { ?>
                <p><strong>Nessuna Richiesta ricevuta.</strong></p>
            <?php } ?> </div>
        <!-- Script per cambiare sezione -->
        <script>
    function toggleSection(section){
        if(section ==='materiali'){
            document.getElementById('materiali').style.display = 'block';
            document.getElementById('richieste').style.display = 'none';
        }else if(section=='richieste'){
            document.getElementById('materiali').style.display = 'none';
            document.getElementById('richieste').style.display = 'block';
        }
    } 
    </script>
    </body>
</html>