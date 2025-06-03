<?php
    require 'db.php';
    include 'funzioni.php';

    //Salvataggio dei valori inviati dalle form
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $tipo_utente = $_POST['tipo_utente'];
        $id_azienda = NULL;
        $stato = 'In Attesa';
        $ruolo = 'Non Definito';

        if ($tipo_utente == 'Dipendente Aziendale' && isset($_POST['azienda'])) {
            $id_azienda = $_POST['azienda'];
        }

        if ($tipo_utente == 'Committente') {
            $id_azienda = NULL;
            $ruolo = NULL;
            $stato = 'Attivo';
        }

        // PRIMA DI QUALSIASI INSERIMENTO: Controllo email già registrata
        $sql_sel = "SELECT email FROM utenti WHERE email=?";
        $sql_mail = $conn->prepare($sql_sel);
        $sql_mail->bind_param("s", $email);
        $sql_mail->execute();
        $sql_mail->store_result();

        if ($sql_mail->num_rows > 0) {
            $errore = 'Mail già registrata';   
        } else {
            // Procedi con l'inserimento nel database solo se l'email non esiste già
            if ($tipo_utente == 'Azienda') {
                if (isset($_POST['nome_azienda']) && !empty($_POST['nome_azienda'])) {
                    $nome_azienda = trim($_POST['nome_azienda']);
                    $stato = 'Attivo';
                    $ruolo = 'Amministratore Aziendale';

                    // Inserimento azienda nel database
                    $sql_insert_azienda = "INSERT INTO aziende(nome_azienda) VALUES (?)";
                    $sql_nazienda = $conn->prepare($sql_insert_azienda);
                    $sql_nazienda->bind_param("s", $nome_azienda);

                    if ($sql_nazienda->execute()) {
                        $id_azienda = $conn->insert_id;
                        // Hash della password
                        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                        // Inserimento amministratore aziendale
                        $sql_insert_utente = "INSERT INTO utenti(nome, cognome, email, password, tipo_utente, stato, ruolo, id_azienda, data_registrazione) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                        $sql_utente = $conn->prepare($sql_insert_utente);
                        $sql_utente->bind_param("sssssssi", $nome, $cognome, $email, $password_hashed, $tipo_utente, $stato, $ruolo, $id_azienda);

                        if ($sql_utente->execute()) {
                            $messaggio = "Registrazione completata con successo! L'azienda e l'amministratore sono stati creati.";
                        } else {
                            $errore = 'Errore nell\'inserimento dell\'utente: ' . $sql_utente->error;
                        }
                        $sql_utente->close();
                    } else {
                        $errore = 'Errore nell\'inserimento dell\'azienda: ' . $sql_nazienda->error;
                    }
                    $sql_nazienda->close();
                } else {
                    $errore = 'Nome Azienda Obbligatorio';
                }
            } else {

                // Per tutti gli altri utenti
                // Hash della password
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                // Inserimento dell'utente
                $sql_insert = "INSERT INTO utenti(nome, cognome, email, password, tipo_utente, stato, ruolo, id_azienda, data_registrazione) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $sql_utenti = $conn->prepare($sql_insert);
                
                if ($sql_utenti) {
                    $sql_utenti->bind_param("sssssssi", $nome, $cognome, $email, $password_hashed, $tipo_utente, $stato, $ruolo, $id_azienda);
                    //Messaggio per i vari tipi di utenti
                    if ($sql_utenti->execute()) {
                        if ($tipo_utente == "Dipendente Aziendale") {
                            $messaggio = "La tua registrazione è stata completata. Attendi la verifica dall'Amministratore Aziendale per iniziare.";
                        } else {
                            $messaggio = "Registrazione completata con successo! Effettua il login.";
                        }
                    } else {
                        $errore = 'Registrazione Fallita: ' . $sql_utenti->error;
                    }
                    $sql_utenti->close();
                } else {
                    $errore = 'Errore nella connessione: ' . $conn->error;
                }
            }
        }
        $sql_mail->close();
    }

    $conn->close();

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <?php include "registerCSS.php" ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Registrazione</title> 
    <script>

        //Funzione per mostrare le varie sezioni 
        function mostra_azienda() {
            var tipoUtente = document.querySelector('input[name="tipo_utente"]:checked').value;
            var aziendaField = document.getElementById("lista_azienda");
            var aziendaInput = document.getElementById("campo_azienda");
            
            // Se il tipo utente è 'Dipendente', mostra la tendina azienda, altrimenti no
            if (tipoUtente === 'Dipendente Aziendale') {
                aziendaField.style.display = "block";
                aziendaInput.style.display = "none";
            } else if (tipoUtente === 'Azienda'){
                aziendaField.style.display = "none";
                aziendaInput.style.display = "block";
            } else {
                aziendaField.style.display = "none";
                aziendaInput.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <h1 class="titolo">Benvenuto nella pagina di registrazione di Edil Planner</h1>
    <div class="container">
        <div class="box_form">
            <form method="POST" action="register.php">
                <!-- Dati personali -->
                <fieldset>
                    <legend>Dati Personali</legend>
                    <div class="parametri">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" id="nome" required>
                    </div>

                    <div class="parametri">
                        <label for="cognome">Cognome:</label>
                        <input type="text" name="cognome" id="cognome" required>
                    </div>

                    <div class="parametri">
                        <label for="email">E-Mail:</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="parametri">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                </fieldset>

                <!-- Tipo di Utente -->
                <fieldset>
                    <legend>Tipo di Utente</legend>
                    <div class="tipo_utente">

                        <div class="radio-div">
                            <input type="radio" name="tipo_utente" id="committente" value="Committente" onclick="mostra_azienda()">
                            <label for="committente">&nbsp;Committente</label>
                        </div>

                        <div class="radio-div">
                            <input type="radio" name="tipo_utente" id="azienda" value="Azienda" onclick="mostra_azienda()">
                            <label for="azienda">&nbsp;Azienda</label>
                        </div>

                        <div class="radio-div">
                            <input type="radio" name="tipo_utente" id="dipendente" value="Dipendente Aziendale" onclick="mostra_azienda()">
                            <label for="dipendente">&nbsp;Dipendente</label>
                        </div>
                    </div>

                    <!--Nome Azienda solo per amministratore aziendale-->
                    <div id="campo_azienda" style="display:none">
                        <label for="nome_azienda">Nome della tua Azienda:</label>
                        <input type="text" name="nome_azienda" id="nome_azienda">
                    </div>

                    <!-- Selezione Azienda (solo per dipendenti) -->
                    <div id="lista_azienda" style="display:none;">
                        <label>Seleziona Azienda:</label>
                        <select name="azienda">
                            <option value="" disabled selected>Seleziona Azienda</option>
                            <?php
                            require 'db.php';
                            $query = "SELECT id_azienda, nome_azienda FROM aziende";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id_azienda'] . "'>" . $row['nome_azienda'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </fieldset>

                <!-- Privacy Policy -->
                <fieldset>
                    <legend>Privacy</legend>
                    <div class="privacy">
                        <input type="checkbox" name="privacy" id="privacy" required>
                        <label for="privacy">Accetto i termini della <a href="privacy.php" target="_blank">Policy Privacy</a></label>
                    </div>
                </fieldset>
                
                <!--Div per i bottoni-->
                <div class="last">
                    <!-- Bottone di Registrazione -->
                    <button type="submit" class="reg"><i class="fa-solid fa-user-plus"></i> Registrati</button>

                    <!-- Link per il Login -->
                    <p class="login-link">Hai già un account? <a href="login.php">Effettua il Login</a></p>

                    <!-- Bottone Indietro -->
                    <a href="javascript:history.back()" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</a>

                </div>
            </form>
        </div>
    </div><br>
    
    <div class="messaggio" style="font-size:20px;">
    <?php if(isset($errore)){
        echo "Errore: " .$errore;
    }else if(isset($messaggio)){
        echo " ".$messaggio;
    }?>
    </div>
</body>
</html>
