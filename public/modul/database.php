<?php

require_once "Table.php";

$server = "mysql";
$user = "root";
$pass = "admin123";
$databasename = "m151";

// Datenbankanbindung nach PDO 
try {
    $dbconnect = new PDO("mysql:host=$server;dbname=$databasename", $user, $pass);
} catch (Exception $exception) {
    echo "Die Verbindung ist erfolgreich" . $exception->getMessage();
    die();
}