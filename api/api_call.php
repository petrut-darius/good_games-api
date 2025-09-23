<?php
header('Content-type: application/json');
require_once "cars/functions.inc.php";
//require_once "router.php";



$request_method = $_SERVER["REQUEST_METHOD"];
//need to make the call based on the url something like a router
echo gateway($request_method);