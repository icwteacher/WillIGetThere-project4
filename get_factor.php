<?php
header('Content-Type: application/json');

$xmlFile = 'users.xml';

if (!file_exists($xmlFile)) {
    echo json_encode(['factor' => 1.0]); // standaard
    exit;
}

$xml = simplexml_load_file($xmlFile);
$email = 'default@gebruiker'; // of haal dit dynamisch op

foreach ($xml->gebruiker as $gebruiker) {
    if ((string)$gebruiker->email === $email) {
        echo json_encode(['factor' => (float)$gebruiker->factor]);
        exit;
    }
}

echo json_encode(['factor' => 1.0]); // fallback
