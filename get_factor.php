<?php
header('Content-Type: application/json');
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $xml = simplexml_load_file('users.xml');
    foreach ($xml->user as $user) {
        if ((string)$user->email === $email) {
            echo json_encode(['factor' => (float)$user->factor]);
            exit;
        }
    }
    echo json_encode(['error' => 'User not found']);
} else {
    echo json_encode(['error' => 'No email provided']);
}
?>
