<?php
session_start();
session_destroy();
header("Location: Inlogpagina.php");
exit();
?>