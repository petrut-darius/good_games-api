<?php
function gateway(string $request_method):array {
    $raw_data = file_get_contents("php://input");
    $data = json_decode(($raw_data), true);

    
    $db = db_connection();

    switch($request_method){
        case "GET":
            return get_all_data($db);
            break;
        case "POST":
            
            break;
        case "DELETE":

            break;
        default:
    }



}

function db_connection():mysqli {
    $host = "localhost";
    $user = "root";
    $password = "";

    try{
        $db_connection = new mysqli($host, $user, $password);
        mysqli_select_db($db_connection, "test");
    }catch(mysqli_sql_exception $e){
        echo "error: " . $e->getMessage() . " at line: " . $e->getLine();
    }

    return $db_connection;
}

function get_all_data(mysqli $db):array {
    $query = "SELECT * FROM `games` WHERE in_stock = 1";

    $result = $db->query($query);

    while($row = mysqli_fetch_assoc($result)) {
        $games[] = $row; 
    }

    return $games;
}




?>