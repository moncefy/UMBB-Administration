<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style3.css">
    <title>Document Request</title>
</head>
<body>

<?php
session_start();
require_once '../class/db.php';
require_once '../class/UserHandler.php';
$db = new Database('localhost', 'root', '', 'php');
$conn = $db->getConnection();

?>
</body>
</html>
