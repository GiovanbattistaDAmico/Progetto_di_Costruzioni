<?php
class UserRepository {
    private $conn;

    public function __construct($mysqli_connection) {
        $this->conn = $mysqli_connection;
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id_utente, nome, cognome, password, stato, ruolo, tipo_utente, id_azienda FROM utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
