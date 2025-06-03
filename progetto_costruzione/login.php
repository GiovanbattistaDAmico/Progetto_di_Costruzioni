<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';

    //Assegnazione alle variabili per il controllo
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

    //In caso di campi non completati mostrerà errore
    if(empty($email) || empty($password)) {
        $errore = "Tutti i campi sono obbligatori";
    } else {
        if(!$conn) {
            die("Connessione fallita: " . mysqli_connect_error());
        }
    //Select per l'utente in base alla mail inserita
        $sql = "SELECT id_utente, nome, cognome, password,stato, ruolo, tipo_utente, id_azienda FROM utenti WHERE email = ?";
        $sql_mail = $conn->prepare($sql);
        $sql_mail->bind_param("s", $email);
        $sql_mail->execute();
        $sql_mail->store_result();

        if($sql_mail->num_rows > 0) {  
            $sql_mail->bind_result($id_utente, $nome, $cognome, $hash_password,$stato,$ruolo, $tipo_utente, $id_azienda);
            $sql_mail->fetch();
        
            //Verifica della password
            if(password_verify($password, $hash_password)) {
                $_SESSION["id_utente"] = $id_utente;
                $_SESSION["nome"] = $nome;
                $_SESSION["cognome"] = $cognome;
                $_SESSION["ruolo"] = $ruolo;
                $_SESSION["tipo_utente"] = $tipo_utente;
                $_SESSION["id_azienda"] = $id_azienda;
                $_SESSION["stato"]=$stato;
            //Se lo stato è in attesa mostrerà il messaggio 
                if($stato == "In Attesa"){
                    die("Il tuo Amministratore Aziendale non ha ancora accettato la tua richiesta, per ora non puoi effettuare il login, Attendi");
                }
                // Redirezione in base al ruolo
                if ($tipo_utente === "Admin") {
                    header("Location: menu_admin.php");
                } else if ($ruolo === "Amministratore Aziendale") {
                    header("Location: menu_Aaziendale.php");
                } else if ($ruolo === "Magazziniere") {
                    header("Location: menu_magazziniere.php");
                } else if ($ruolo === "Operaio") {
                    header("Location: menu_operaio.php");
                } else if ($ruolo === "Responsabile") {
                    header("Location: menu_responsabile.php");
                } else if ($tipo_utente === "Committente") {
                    header("Location: menu_committente.php");
                } else if ($ruolo === "Contabile"){
                    header("Location: menu_contabile.php");
                }
                exit;
            } else {
                $errore = "Credenziali errate.";
            }
        } else {
            $errore = "Email non trovata.";
        }
        $sql_mail->close();
    }
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Login</title>
</head>
<body>
    <?php include "loginCSS.php"?>
    <p class="titolo">Benvenuto sulla pagina di Login di Edil Planner</p>
    <!--Contenitore per inserire parametri utente-->
    <div class="container">
            <div class="box_form">
                    <section>
                        <fieldset>
                            <legend>Dati:</legend>
                                <form action="login.php" method="POST">
                                <div class="parametri">    
                                    <label for="email">E-Mail:</label>
                                    <input type="text" id="email" name="email" required=""><br><br>
                                </div>
                                <div class="parametri">
                                    <label for="password">Password:</label>
                                    <input type="password" id="password" name="password" required=""><br><br>
                                </div>
                        </fieldset>
                    </section>
            
        <!--Sezione dei bottoni-->
                <div class="last">
                <button type="submit" class="reg"><i class="fa fa-sign-in-alt"></i> Login</button>
                        <p>Non hai un Account? <a href="register.php">Registrati</a></p>
                        <a href="javascript:history.back()" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</a>
                        </form> 
                </div>
                
            </div>
    </div>
</body>
</html>
