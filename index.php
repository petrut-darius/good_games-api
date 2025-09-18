<?php
header('Application-type: application/json');
require_once "functions.inc.php";
//require_once "router.php";

$request_method = $_SERVER["REQUEST_METHOD"];

$result_of_call = gateway($request_method);
echo $result_of_call;