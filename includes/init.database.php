<?php

//
// Notwendige Dateien einbinden.
require_once "includes/database/pdo.php";

//
// Klasse initialisieren
$sql = new Database();
$sql->debug = true;
$sql->hostname = "localhost";
$sql->database = "database";
$sql->username = "root";
$sql->password = "";
$sql->connect();
