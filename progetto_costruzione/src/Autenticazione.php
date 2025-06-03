<?php
require_once 'UserRepository.php';

class Autenticazione {
    private $userRepo;

    public function __construct(UserRepository $userRepo) {
        $this->userRepo = $userRepo;
    }

    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return "Tutti i campi sono obbligatori";
        }

        $user = $this->userRepo->getUserByEmail($email);

        if (!$user) {
            return "Email non trovata.";
        }

        if (!password_verify($password, $user['password'])) {
            return "Credenziali errate.";
        }

        if ($user['stato'] === "In Attesa") {
            return "Il tuo Amministratore Aziendale non ha ancora accettato la tua richiesta, per ora non puoi effettuare il login, Attendi";
        }

        return $user;
    }
}
