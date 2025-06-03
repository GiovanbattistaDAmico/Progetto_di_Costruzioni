<?php 
    session_start();
    require 'db.php';
    include 'funzioni.php';
    verificaLogin();
    
    //Ottenimento del link per la pagina del menu in base al tipo di utente
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $id_utente = $_SESSION['id_utente'];

    //Selezione dei parametri di ogni utente 
    $query = "SELECT * FROM utenti WHERE id_utente=?";
    $query_utente = $conn->prepare($query);
    $query_utente->bind_param("i",$id_utente);
    $query_utente->execute();
    $result=$query_utente->get_result();
    $user=$result->fetch_assoc();
    
    //Query per la modifica dei parametri
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $nome=$_POST['nome'];
        $cognome=$_POST['cognome'];
        $email=$_POST['email'];
        $password=$_POST['password'];
        if(!empty($password)){
            $password_hashed=password_hash($password,PASSWORD_DEFAULT);
            $query2="UPDATE utenti SET nome=?, cognome = ?, email = ?, password = ? WHERE id_utente = ?";
            $query_password=$conn->prepare($query2);
            $query_password->bind_param("ssssi", $nome, $cognome, $email, $password_hashed, $id_utente);
        }else{
            $query3="UPDATE utenti SET nome=?, cognome = ?, email = ? WHERE id_utente = ?";
            $query_password=$conn->prepare($query3);
            $query_password->bind_param("sssi", $nome, $cognome, $email, $id_utente);
        }
        if ($query_password->execute()) {
            echo "<script>alert('Dati aggiornati correttamente!'); window.location.href = 'account.php';</script>";

        } else {
            $message = "Errore durante l'aggiornamento dei dati.";
        }

    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <?php include 'accountCSS.php'?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account</title>
    </head>
    <body>
        <p class="titolo">Modifica il tuo Account</p>
        <!--Contenitore per inserire parametri utente-->
        <div class="container">
                <div class="box_form">
                            <form method="POST" action="">
                                <fieldset>
                                    <legend>Dati:</legend>
                                    <div class="parametri">
                                        <label for="nome">Nome:</label>
                                        <input type="text" id="nome" name="nome" value="<?php echo $user['nome']?>" required>
                                    </div>
                                    <div class="parametri">
                                        <label for="cognome">Cognome:</label>
                                        <input type="text" id="cognome" name="cognome" value="<?php echo $user['cognome']?>"required>
                                    </div>
                                    <div class="parametri">
                                        <label for="email">E-mail:</label>
                                        <input type="email" id="email" name="email" value="<?php echo $user['email']?>" required>
                                    </div>
                                    <div class="parametri">
                                        <label for="password">Nuova Password (opzionale):</label>
                                        <input type="password" id="password" name="password">
                                    </div>
                                </fieldset>
            <!--Sezione dei bottoni-->
                            <div class="last">
                                <button class="salva" type="submit"><i class="fas fa-save"></i>&nbsp; Salva modifiche</button>
                                <a href="<?php echo  $menu; ?>" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</a>
                            </form> 
                            </div>   
                </div>
        </div><br>
        <div class="messaggio" style="font-size:20px;">
        <?php 
            if(isset($message)){echo " ".$message;}
        ?>
        </div>
    </body>
</html>
