<?php
header('Application-type: application/json');
require_once "functions.inc.php";

$request_method = $_SERVER["REQUEST_METHOD"];
//$raw_data = file_get_contents("php://input");
//$data = json_decode($raw_data, true);

$games = gateway($request_method);
echo json_encode($games, JSON_PRETTY_PRINT);