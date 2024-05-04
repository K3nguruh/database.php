<?php

//
// Notwendige Dateie einbinden.
require_once "includes/init.database.php";

//
// Fetch
$sql->prepare("SELECT `name` FROM `table` WHERE `id` = :id");
$sql->bindValue(":id", "ID");
$fetch = $sql->fetch();

$name = $fetch["name"];

//
// FetchAll
$sql->prepare("SELECT `name` FROM `table` WHERE `name` = :val");
$sql->bindValue(":val", "NAME");
$fetchAll = $sql->fetchAll();
$rowCount = $sql->rowCount();

$result = [];
foreach ($fetchAll as $row) {
  $result[] = [
    "name" => $row["name"],
  ];
}

//
// Execute
$sql->prepare("UPDATE `table` SET `name` = :val WHERE `id` = :id");
$sql->bindValue(":val", "NAME");
$sql->bindValue(":id", "ID");
$sql->execute();
