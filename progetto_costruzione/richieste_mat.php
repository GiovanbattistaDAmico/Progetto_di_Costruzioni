<?php 
    session_start();
    require 'db.php';
    include 'funzioni.php';
    verificaLogin();

    //Ottenimento menu per il tipo di utente
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $id_utente = $_SESSION['id_utente'];
    $tipo_utente = $_SESSION['tipo_utente'];
    $link = $_SERVER['REQUEST_URI'];

    //Rimozione delle notifiche riguardanti la pagina
    rimuoviNotifiche($conn,$id_utente,$link);


    $nome = $_SESSION['nome'];
    $cognome = $_SESSION['cognome'];
    $id_utente = $_SESSION['id_utente']; 
    $tipo_utente = $_SESSION['tipo_utente'];    
    //SELECT DELLE RICHIESTE GIA FATTE 
    $richieste=[];
    $sql_richieste="SELECT r.*,u.id_utente AS id_responsabile,u.nome AS nome_responsabile,u.cognome AS cognome_responsabile,um.nome 
    AS nome_magazziniere,um.cognome AS 
    cognome_magazziniere,um.id_utente AS id_magazziniere,m.categoria,m.unita_misura,m.nome,c.descrizione 
    FROM movimenti_materiali AS r LEFT JOIN utenti AS u ON r.id_mittente = u.id_utente LEFT JOIN compiti AS c ON r.id_compito=c.id_compito
    LEFT JOIN materiali_attrezzature AS m ON r.id_risorsa = m.id LEFT JOIN utenti AS um ON r.id_destinatario = um.id_utente
    WHERE r.id_mittente=? OR r.id_destinatario=?";
    $stmt_richiesta=$conn->prepare($sql_richieste);
    $stmt_richiesta->bind_param("ii",$id_utente,$id_utente);
    $stmt_richiesta->execute();
    $result = $stmt_richiesta->get_result();
    while($row=$result->fetch_assoc()){
        $richieste[] = $row;
    }
    
    //Gestione delle richieste accettazione/rifiuto con notifiche
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $id_movimento = $_POST['id_movimento'];
        if($_POST['azione']=='accetta'){
            $sql_stato = "UPDATE movimenti_materiali SET stato='Approvato' WHERE id_movimento = $id_movimento";
            $conn->query($sql_stato);
            inviaNotifica($conn,$richieste_reso['id_responsabile'],"Richiesta di movimento Approvata: ",
            "La tua richiesta di movimento dei materiali è stata approvata dal Magazziniere".$nome ." ".$cognome);
        }else{
            $sql_stato = "UPDATE movimenti_materiali SET stato='Rifiutato' WHERE id_movimento = $id_movimento";
            $conn->query($sql_stato);
            inviaNotifica($conn,$richieste_reso['id_responsabile'],"Richiesta di movimento Rifiutata: ",
            "La tua richiesta di movimento dei materiali è stata rifiutata dal Magazziniere".$nome ." ".$cognome);
        }
    }
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Richieste Materiali e Attrezzature</title>
    </head>
    <body>
        <?php include "gestioneCSS.php" ?>
        <?php include "materialiCSS.php" ?>
        <?php include 'progettiCSS.php'?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>

        <!--Intestazione con logo-->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Richieste Materiali e Attrezzature</h1>
            <div class="div_button">
            <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>  
            </div>
        </div>

        <!--Sezione con bottoni-->
        <div class="button">
            <div class="buttons1">
            </div>
            <div class="buttons2">
                <?php if($_SESSION['ruolo']=='Responsabile'):?>
                <button class="add_button2" onclick='location.href="nuova_richiesta_mat.php"'>Richiesta Materiale</button>
                <button class="add_button" onclick='location.href="restituzione_mat.php"'>Invia richiesta di Reso/Scarto</button>
                <?php endif; ?>
            </div>
        </div>

        <!--Lista delle richieste Effettuate-->
        <div class="lista_richieste_mat" id="richieste">
            <h1>Elenco Richieste Materiali/Attrezzature</h1>
            <?php if(count($richieste)>0){ ?>
                <table>
                    <tr>
                        <?php if($_SESSION['ruolo']=='Responsabile'):?>
                        <th>Magazziniere</th>
                        <?php else: ?>
                        <th>Responsabile</th>
                        <?php endif; ?>
                        <th>Categoria Risorsa</th>
                        <th>Nome Risorsa</th>
                        <th>Tipo Richiesta</th>
                        <th>Descrizione Compito</th>
                        <th>Quantità</th>
                        <th>Note</th>
                        <th>Data Richiesta</th>
                        <th>Stato</th>
                        <th>Risposta</th>
                   
                    </tr>
                    <?php foreach($richieste as $richiesta){?>
                        <tr>
                            <?php if($_SESSION['ruolo']=='Responsabile'):?>
                            <td><?php echo $richiesta['nome_magazziniere'] . ' ' . $richiesta['cognome_magazziniere']; ?></td>
                            <?php else: ?>
                            <td><?php echo $richiesta['nome_responsabile'] . ' ' . $richiesta['cognome_responsabile']; ?></td>
                            <?php endif; ?>
                            <td><?php echo $richiesta['categoria'] == 'Materiale' ? 'Materiale' : 'Attrezzatura'; ?></td>
                            <td><?php echo $richiesta['nome'];?></td>
                            <td><?php echo $richiesta['tipo']; ?></td>
                            <td><?php echo $richiesta['descrizione']; ?></td>
                            <td><?php echo $richiesta['quantita']." ".$richiesta['unita_misura']; ?></td>
                            <td><?php echo $richiesta['note']; ?></td>
                            <td><?php echo $richiesta['data_richiesta']; ?></td>
                            <td><?php echo $richiesta['stato']; ?></td>
                            <td><?php echo !empty($richiesta['risposta']) ? $richiesta['risposta'] : '—'; ?></td>
                        </tr>
                        <?php } ?>
                    </table>
            <?php } else { ?>
                <p><strong>Nessuna Richiesta ricevuta.</strong></p>
            <?php } ?> </div>
            
    
    </body>
</html>