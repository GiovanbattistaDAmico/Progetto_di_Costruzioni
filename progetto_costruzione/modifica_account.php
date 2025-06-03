<?php
// Connessione al database
require 'db.php';

session_start();

// Verifica che l'utente sia autenticato
if (!isset($_SESSION['id_utente'])) {
    header("Location: index.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$messaggio = "";

// Recupera le informazioni dell'utente
$query_utente = "SELECT email FROM utenti WHERE id_utente = ?";
$stmt = $conn->prepare($query_utente);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();

// Modifica email e password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifica'])) {
    $nuova_email = trim($_POST['email']);
    $nuova_password = trim($_POST['password']);
    
    // Verifica che l'email non sia già in uso
    $query_check_email = "SELECT id_utente FROM utenti WHERE email = ? AND id_utente != ?";
    $stmt_check = $conn->prepare($query_check_email);
    $stmt_check->bind_param("si", $nuova_email, $id_utente);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $messaggio = "Errore: l'email è già in uso.";
    } else {
        // Aggiorna email e password
        if (!empty($nuova_password)) {
            $hashed_password = password_hash($nuova_password, PASSWORD_DEFAULT);
            $query_update = "UPDATE utenti SET email = ?, password = ? WHERE id_utente = ?";
            $stmt = $conn->prepare($query_update);
            $stmt->bind_param("ssi", $nuova_email, $hashed_password, $id_utente);
        } else {
            $query_update = "UPDATE utenti SET email = ? WHERE id_utente = ?";
            $stmt = $conn->prepare($query_update);
            $stmt->bind_param("si", $nuova_email, $id_utente);
        }

        if ($stmt->execute()) {
            $_SESSION['email'] = $nuova_email;
            $messaggio = "Modifica avvenuta con successo.";
        } else {
            $messaggio = "Errore nella modifica dell'account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Modifica Account</title>
    <?php include "grafica_menu.php"  ?>

</head>
<body>
    <!--Sezione di modifica dell'account-->
    <h1 class="titolo">Modifica Account</h1>
    <div class="divbutton2">
    <button class="indietro"onclick="location.href='menu_amministratore.php'">Indietro</button>
</div>
    <?php if (!empty($messaggio)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $messaggio; ?></p>
    <?php endif; ?>
        <div class="ncredenziali">
    <form method="POST" action="modifica_account.php">
        <label for="email">Nuova Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($utente['email']); ?>" required>

        <label for="password">Nuova Password (lascia vuoto per non modificare):</label>
        <input type="password" name="password">
        </div>
        <div class="salva">
        <input type="submit" name="modifica" value="Salva Modifiche">
    </div>
    </form>
</body>
</html>
<?php
$conn->close();
?>
