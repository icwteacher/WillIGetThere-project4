<?php
session_start();

$xmlFile = 'users.xml';

if (!file_exists($xmlFile)) {
    $xml = new SimpleXMLElement('<users></users>');
    $xml->asXML($xmlFile);
}

function registerUser($username, $password) {
    global $xmlFile;
    $xml = simplexml_load_file($xmlFile);

    foreach ($xml->user as $user) {
        if ($user->username == $username) {
            return "Gebruiker bestaat al.";
        }
    }

    $newUser = $xml->addChild('user');
    $newUser->addChild('username', $username);
    $newUser->addChild('password', password_hash($password, PASSWORD_DEFAULT));
    $xml->asXML($xmlFile);

    header("Location: Inlogpagina.php?success=registered");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $message = registerUser($_POST['username'], $_POST['password']);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registreren</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 50px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #0000001a;
            display: inline-block;
        }
        input, button {
            margin: 10px;
            padding: 10px;
            width: 80%;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Account aanmaken</h2>
        <?php if (isset($message)) echo "<p style='color: red;'>$message</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Gebruikersnaam" required><br>
            <input type="password" name="password" placeholder="Wachtwoord" required><br>
            <button type="submit" name="register">Registreren</button>
        </form>
        <a href="Inlogpagina.php">Terug naar inloggen</a>
    </div>
</body>
</html>