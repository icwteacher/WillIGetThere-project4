<?php
header('Content-Type: application/json');

if (!isset($_POST['effectief']) || !isset($_POST['energieZonderCorrectie'])) {
    echo json_encode(['success' => false, 'error' => 'Ongeldige invoer.']);
    exit;
}

$effectief = floatval($_POST['effectief']);
$energieZonder = floatval($_POST['energieZonderCorrectie']);

if ($energieZonder <= 0) {
    echo json_encode(['success' => false, 'error' => 'Ongeldige energiewaarde.']);
    exit;
}

$nieuweFactor = $effectief / $energieZonder;

$xmlFile = 'gebruikers.xml';
$email = 'default@gebruiker'; // als placeholder of via sessie

if (!file_exists($xmlFile)) {
    $xml = new SimpleXMLElement('<gebruikers></gebruikers>');
} else {
    $xml = simplexml_load_file($xmlFile);
}

// Kijk of gebruiker al bestaat
$gevonden = false;
foreach ($xml->gebruiker as $gebruiker) {
    if ((string)$gebruiker->email === $email) {
        $gebruiker->factor = $nieuweFactor;
        $gevonden = true;
        break;
    }
}

if (!$gevonden) {
    $gebruiker = $xml->addChild('gebruiker');
    $gebruiker->email = $email;
    $gebruiker->factor = $nieuweFactor;
    $gebruiker->wachtwoord = 'placeholder';
}

$xml->asXML($xmlFile);

echo json_encode(['success' => true, 'newFactor' => $nieuweFactor]);
