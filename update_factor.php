<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $effectief = $_POST['effectief'] ?? '';
    $energieZonderCorrectie = $_POST['energieZonderCorrectie'] ?? '';

    if (
        empty($email) ||
        !is_numeric($effectief) || !is_numeric($energieZonderCorrectie) ||
        floatval($effectief) <= 0 || floatval($energieZonderCorrectie) <= 0
    ) {
        echo json_encode(['success' => false, 'error' => 'Ongeldige of ontbrekende invoer']);
        exit;
    }

    $effectief = floatval($effectief);
    $energieZonderCorrectie = floatval($energieZonderCorrectie);

    $xml = simplexml_load_file('users.xml');
    foreach ($xml->user as $user) {
        if ((string)$user->email === $email) {
            $rawFactor = $effectief / $energieZonderCorrectie;
            $newFactor = round(max(0.7, min(1.3, $rawFactor)), 6);
            $user->factor = $newFactor;
            $xml->asXML('users.xml');
            echo json_encode(['success' => true, 'newFactor' => $newFactor]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'error' => 'Gebruiker niet gevonden']);
} else {
    echo json_encode(['success' => false, 'error' => 'Geen POST request']);
}
?>
