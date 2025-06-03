<?php
// Connessione al database
require 'db.php';

// Dati dell'amministratore
$nome = 'Giovanbattista';
$cognome = 'DAmico';
$email = 'giovanbattista.damico1@studenti.unicampania.it'; // Usa un'email unica per l'amministratore
$password = 'giovanni'; // La password che vuoi assegnare all'amministratore
$tipo_utente = 'Admin'; // Tipo di utente come amministratore
$id_azienda = NULL; // Poiché l'amministratore non è legato a un'azienda

// Cripta la password
$password_hashed = password_hash($password, PASSWORD_DEFAULT);

// Impostazione dello stato dell'amministratore
$stato = 'Attivo'; // Stato per l'amministratore


// Inserisci i dati dell'amministratore nella tabella utenti
$sql_insert = "INSERT INTO utenti(nome, cognome, email, password, tipo_utente, stato, ruolo, id_azienda, data_registrazione) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$sql_utenti = $conn->prepare($sql_insert);

if ($sql_utenti) {
    // Associa i parametri alla query preparata
    $sql_utenti->bind_param("sssssssi", $nome, $cognome, $email, $password_hashed, $tipo_utente, $stato, $ruolo, $id_azienda);
    
    if ($sql_utenti->execute()) {
        echo "Amministratore inserito con successo!";
    } else {
        echo 'Errore durante l\'inserimento: ' . $sql_utenti->error;
    }

    // Chiudi la query
    $sql_utenti->close();
} else {
    echo 'Errore nella preparazione della query: ' . $conn->error;
}

// Chiudi la connessione al database
$conn->close();
?>
