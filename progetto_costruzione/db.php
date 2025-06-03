<?php
$servername = "localhost";
$username = "root";  //username per il database
$password = "";      //password per il database
$dbname = "progetto_costruzione";  //nome del database

// Crea connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
