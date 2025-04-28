<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Inlogpagina.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welkom</title>
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
        button {
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
            margin-top: 20px;
        }
        button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welkom op de site!</h2>
        <p>Je bent succesvol ingelogd als <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>
        <form action="logout.php" method="post">
            <button type="submit">Uitloggen</button>
        </form>
    </div>
</body>
</html>