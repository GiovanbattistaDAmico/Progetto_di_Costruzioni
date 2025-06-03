<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Autenticazione.php';
require_once __DIR__ . '/../src/UserRepository.php';

class AutenticazioneTest extends TestCase {
    public function testLoginSuccess() {
        $userMock = [
            'id_utente' => 1,
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'stato' => 'Attivo',
            'ruolo' => 'Operaio',
            'tipo_utente' => 'Standard',
            'id_azienda' => 1,
        ];

        $userRepoMock = $this->createMock(UserRepository::class);
        $userRepoMock->method('getUserByEmail')
                     ->willReturn($userMock);

        $auth = new Autenticazione($userRepoMock);
        $result = $auth->login('mario@example.com', 'password123');

        $this->assertIsArray($result);
        $this->assertEquals('Mario', $result['nome']);
    }

    public function testLoginWrongPassword() {
        $userMock = [
            'id_utente' => 1,
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'stato' => 'Attivo',
            'ruolo' => 'Operaio',
            'tipo_utente' => 'Standard',
            'id_azienda' => 1,
        ];

        $userRepoMock = $this->createMock(UserRepository::class);
        $userRepoMock->method('getUserByEmail')
                     ->willReturn($userMock);

        $auth = new Autenticazione($userRepoMock);
        $result = $auth->login('mario@example.com', 'wrongpass');

        $this->assertEquals("Credenziali errate.", $result);
    }

    public function testLoginEmailNonEsistente() {
        $userRepoMock = $this->createMock(UserRepository::class);
        $userRepoMock->method('getUserByEmail')
                     ->willReturn(null);

        $auth = new Autenticazione($userRepoMock);
        $result = $auth->login('nonesiste@example.com', 'qualcosa');

        $this->assertEquals("Email non trovata.", $result);
    }
    
    public function testLoginCampiVuoti() {
        $userRepoMock = $this->createMock(UserRepository::class);
        $auth = new Autenticazione($userRepoMock);
        
        $this->assertEquals("Tutti i campi sono obbligatori", $auth->login('', ''));
    }
}
