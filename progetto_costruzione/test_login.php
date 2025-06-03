<?php

function test_login($email, $password) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'http://localhost/progetto_costruzione/login.php'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password
    ]));

    curl_setopt($ch, CURLOPT_HEADER, true); // per vedere header (es. redirect)
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    echo "\n=== Test login ===\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "HTTP Code: $httpcode\n";
    echo "Redirect URL: $final_url\n";
    echo "Risposta: \n$response\n\n";
}

// TEST 1: Login corretto
test_login('utente_valido@example.com', 'passwordCorretta');

// TEST 2: Password sbagliata
test_login('utente_valido@example.com', 'passwordSbagliata');

// TEST 3: Email non registrata
test_login('inesistente@example.com', 'qualcosa');

// TEST 4: Campi vuoti
test_login('', '');
