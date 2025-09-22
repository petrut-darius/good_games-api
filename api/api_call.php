<?php
header('Content-type: application/json');
require_once "functions.inc.php";
//require_once "router.php";

$request_method = $_SERVER["REQUEST_METHOD"];
echo gateway($request_method);