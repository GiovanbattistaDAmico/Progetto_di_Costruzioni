<?php 
    session_start();
    require 'db.php';
    include 'funzioni.php';
    $id_utente=$_SESSION['id_utente'];
    $id_azienda = $_SESSION['id_azienda'];
    $tipo_utente=$_SESSION['tipo_utente'];
    $azienda=null;
    $link = $_SERVER['REQUEST_URI'];

    //Eliminazione delle notifiche riguardanti la pagina 
    rimuoviNotifiche($conn,$id_utente,$link);

    //query per selezionare i dati dell'azienda
    if($tipo_utente=='Azienda'){
        $sql = "SELECT * FROM aziende WHERE id_azienda=?";
        $sql_select = $conn->prepare($sql);
        $sql_select->bind_param("i",$id_azienda);
        $sql_select->execute();
        $result = $sql_select->get_result();
        $azienda= $result->fetch_assoc();
    }

    //query per inserire i dati modificati tramite form
        if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['salva'])){
            $nome_azienda=trim($_POST['nome_azienda']);
            $partita_iva=trim($_POST['partita_iva']);
            $indirizzo=trim($_POST['indirizzo']);
            $telefono=trim($_POST['telefono']);
            $email_aziendale=trim($_POST['email_aziendale']);
            $id_azienda=$_SESSION['id_azienda'];
            $sql2 = "UPDATE aziende SET nome_azienda=?, partita_iva=?, indirizzo=?, telefono=?, email_aziendale=? WHERE id_azienda=?";
            $sql_insert = $conn->prepare($sql2);
            $sql_insert -> bind_param("sssssi",$nome_azienda,$partita_iva,$indirizzo,$telefono,$email_aziendale,$id_azienda);
            if($sql_insert->execute()){
                echo "I dati sono stati modificati con successo!";
                header("Location: gestione_azienda.php");
                exit();
            }else{echo "I dati non sono stati modificati riprova";}
        }elseif($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['annulla'])){
            header("Location: gestione_azienda.php");
            exit();
        }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'gestione_ACSS.php'?>
        <title>Gestione Azienda</title>
    </head>
    <body>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!--Intestazione con logo del sito-->
        <div class="intestazione">
            <video class="logo" autoplay muted>
                <source src="edil_planner.mp4" type="video/mp4">
            </video> 
            <h1 class="titolo">Gestione Azienda</h1>
            <div class="div_button">
            <button onclick="location.href='menu_Aaziendale.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
            </div>
        </div>
        <!--Tabella con le informazioni riguardanti l'azienda -->
        <table class="tabella_azienda">
            <tr>
                <th>Nome Azienda:</th>
                <th>Partita IVA:</th>
                <th>Indirizzo:</th>
                <th>Telefono:</th>
                <th>E-Mail Aziendale:</th>
                <th>Azioni</th>
            </tr>
            <tr>
                <form method="POST" action="gestione_azienda.php">
                    <th><input type="text" id=nome name="nome_azienda" value="<?php echo !empty($azienda['nome_azienda']) ? $azienda['nome_azienda'] : 'Non Definito'; ?>"></input></th>
                    <th><input type="text" id="partita_iva" name="partita_iva" value="<?php echo !empty($azienda['partita_iva']) ? $azienda['partita_iva'] : 'Non Definito'; ?>"></input></th>
                    <th><input type="text" id="indirizzo" name="indirizzo" value="<?php echo !empty($azienda['indirizzo']) ? $azienda['indirizzo'] : 'Non Definito'; ?>"></input></th>
                    <th><input type="text" id="telefono" name="telefono" value="<?php echo !empty($azienda['telefono']) ? $azienda['telefono'] : 'Non Definito'; ?>"></input></th>
                    <th><input type="text" id="email_aziendale" name="email_aziendale" value="<?php echo !empty($azienda['email_aziendale']) ? $azienda['email_aziendale'] : 'Non Definito'; ?>"></input></th>
                    <th><input type="submit" id="salva" name="salva" value="Salva Modifica"></input><input type="submit" id="annulla" name="annulla" value="Annulla Modifica"></input></th>

                </form>
            </tr>
        </table>
    </body>
</html>