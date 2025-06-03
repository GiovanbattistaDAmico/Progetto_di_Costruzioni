<?php 
    require 'db.php';
    session_start();
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $nome=$_POST['nome'];
        $descrizione=$_POST['descrizione'];
        $quantita=$_POST['quantita'];
        $categoria=$_POST['categoria'];
        if ($categoria == 'attrezzatura' || empty($unita_misura)) {
            $unita_misura = NULL;  // Imposta su NULL se è attrezzatura o non è stata selezionata un'unità di misura
        }else{
            $unita_misura = $_POST['unita_misura'];
        }

        $stato=$_POST['stato'];
        $ubicazione=$_POST['ubicazione'];
        $scorta_minima=$_POST['scorta_minima'];
        $id_azienda = $_SESSION['id_azienda'];
        $tipo_utente = $_SESSION['tipo_utente'];
        $costo_unitario = $_POST['costo_unitario'];
        $unita_misura = $_POST['unita_misura'];
        if($categoria == 'Materiale'){
            $sql = "INSERT INTO materiali_attrezzature (nome,descrizione,quantita,unita_misura,categoria,id_azienda,stato,costo_unitario,ubicazione,scorta_minima) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $sql_mat = $conn -> prepare($sql);
            $sql_mat -> bind_param("ssissisisi",$nome,$descrizione,$quantita,$unita_misura,$categoria,$id_azienda,$stato,$costo_unitario,$ubicazione,$scorta_minima);
        }else{
            $sql = "INSERT INTO materiali_attrezzature (nome,descrizione,quantita,categoria,id_azienda,stato,costo_unitario,ubicazione,scorta_minima) VALUES (?,?,?,?,?,?,?,?,?)";
            $sql_mat = $conn -> prepare($sql);
            $sql_mat -> bind_param("ssisisisi",$nome,$descrizione,$quantita,$categoria,$id_azienda,$stato,$costo_unitario,$ubicazione,$scorta_minima);

        }
        if($sql_mat -> execute()){
            $id_risorsa = $conn->insert_id;
            $quantita_entrata = $quantita; // Imposta la quantità di entrata (potrebbe essere variabile se lo richiedi)
            $id_mittente = $_SESSION['id_utente']; // ID del magazziniere o chi sta aggiungendo il materiale
            $tipo_movimento = 'Entrata'; // Tipo di movimento (entrata)
            $stato_movimento = 'Approvato'; // Stato del movimento
            $note_movimento = 'Aggiunta iniziale del materiale'; // Nota associata al movimento

            // Registriamo l'entrata del materiale
            $sql_movimento = "INSERT INTO movimenti_materiali (id_risorsa, id_mittente, quantita, tipo, stato, note) 
                            VALUES (?,?,?,?,?,?)";
            $stmt_movimento = $conn->prepare($sql_movimento);
            $stmt_movimento->bind_param("iiisss", $id_risorsa, $id_mittente, $quantita_entrata, $tipo_movimento, $stato_movimento, $note_movimento);

            if ($stmt_movimento->execute()) {
                echo "Materiale/Attrezzo inserito e movimento di entrata registrato correttamente!";
            } else {
                echo "Errore durante la registrazione del movimento di entrata.";
            }
        }else{
            echo "Errore: " . $sql_mat->error;
        }
        $sql_mat->close();
        $conn->close();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'materialiCSS.php'?>
        <title>Aggiunti Materiali/Attrezzature</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Aggiungi Materiali/Attrezzatura</h1>
                <div class="div_button">
                <button onclick="location.href='gestione_mat_e_attr.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <div class="container">
            <div class="box_form">
                                <form action="aggiungi_mat.php" method="POST">
                                <div class="parametri">    
                                    <label for="nome">Nome:</label>
                                    <input type="text" id="nome" name="nome" required>
                                </div>
                                <div class="parametri">
                                    <label for="descrizione">Descrizione:</label>
                                    <input type="text" id="descrizione" name="descrizione" required>
                                </div>
                                <div class="parametri">
                                    <label for="quantita">Quantità:</label>
                                    <input type="number" id="quantita" name="quantita" required>
                                </div>
                                <div class="parametri">
                                    <label for="unita_misura">Unità di Misura:</label>
                                    <select id="unita_misura" name="unita_misura" >
                                        <option value=""></option>
                                        <option value="kg">Kg</option>
                                        <option value="litri">Litri</option>
                                        <option value="metri">Metri</option>
                                        <option value="pezzi">Pezzi</option>
                                    </select>
                                </div>
                                <div class="parametri">
                                    <label for="categoria">Categoria:</label>
                                    <select id="categoria" name="categoria" required>
                                        <option value="Attrezzatura">Attrezzatura</option>
                                        <option value="Materiale">Materiale</option>
                                    </select>
                                </div>
                                <div class="parametri">
                                <label for="stato">Stato:</label>
                                    <select id="stato" name="stato">
                                        <option value="Disponibile">Disponibile</option>
                                        <option value="In Uso">In Uso</option>
                                        <option value="Non Disponibile">Non Disponibile</option>
                                    </select>
                                </div>
                                <div class="parametri">
                                    <label for="costo_unitario">Costo Unitario (€):</label>
                                    <input type="number" id="costo_unitario" name="costo_unitario" required>
                                </div>
                                <div class="parametri">
                                    <label for="scorta_minima">Scorta Minima:</label>
                                    <input type="number" id="scorta_minima" name="scorta_minima" required>
                                </div>
                                <div class="parametri">
                                    <label for="ubicazione">Ubicazione:</label>
                                    <input type="text" id="ubicazione" name="ubicazione" required>
                                </div>
        <!--Sezione dei bottoni-->
                <div class="last">
                    <button type="submit" onclick="return confirm('Sei sicuro di voler aggiungere questa risorsa?');" class="crea"><i class="fas fa-plus"></i>&nbsp;Aggiungi</button>
                    <a href="javascript:history.back()" class="back2"><i class="fas fa-times"></i>&nbsp;Annulla</a>
                        </form> 
                </div>
            </div>
    </div>
    </body>
</html>