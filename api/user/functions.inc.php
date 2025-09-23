<?php
function gateway(string $request_method) {

    
    $db = db_connection();
    
    switch($request_method){
        case "GET":
            return json_encode(get_all_users($db), JSON_PRETTY_PRINT);
        case "POST":    
            if( isset($_POST["first_name"]) && !empty($_POST["first_name"])
                && isset($_POST["last_name"]) && !empty($_POST["last_name"])
                && isset($_POST["birth_date"]) && !empty($_POST["birth_date"])
                && isset($_POST["password"]) && !empty($_POST["password"])
                && isset($_POST["password_again"]) && !empty($_POST["password_again"])
                && isset($_POST["email"]) && !empty($_POST["email"])
                ) {
                    $first_name = $_POST["first_name"];
                    $last_name = $_POST["last_name"];
                    $birth_date = $_POST["birth_date"];
                    $password = $_POST["password"];
                    $password_again = $_POST["password_again"];
                    $email = $_POST["email"];

                    if($password !== $password_again ) {
                        return json_encode(["msg" => "the passwords are not identical."], JSON_PRETTY_PRINT);
                    }



                    // make sure that all the params in the post are good using regex
                    
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    return json_encode( add_user($db, $first_name, $last_name, $birth_date, $hashed_password, $hashed_password, $email), JSON_PRETTY_PRINT);

                }
                break;
        case "PATCH":
        case "PUT":
                
        case "DELETE":

        default:
    }



}

function db_connection():mysqli {
    $host = "localhost";
    $user = "root";
    $password = "";

    try{
        $db_connection = new mysqli($host, $user, $password);
        mysqli_select_db($db_connection, "car-info");
    }catch(mysqli_sql_exception $e){
        echo "error: " . $e->getMessage() . " at line: " . $e->getLine();
    }

    return $db_connection;
}

function get_all_users(mysqli $db):array {
    $query = "SELECT * FROM `users`";

    $result = $db->query($query);
    $games = [];

    while($row = mysqli_fetch_assoc($result)) {
        $games[] = $row; 
    }

    return $games;
}

function get_user($db, $id):array {
    $query = "SELECT * FROM `users` WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $num_rows = $result->num_rows;
    $game = $result->fetch_assoc();

    $stmt->close();

    return [$user, $num_rows];
}

function add_user($db, $first_name, $last_name, $birth_date, $password, $password_again, $email) {
    $query = "INSERT INTO `users` (first_name, last_name, birth_date, password, password_again, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssssss", $first_name, $last_name, $birth_date, $password, $password_again, $email);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function update_user($db, int $id, array $params, array $fields, string $types) {
    $query = "UPDATE `users` SET " . implode(",", $fields) . " WHERE id = " . $id;
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function delete_game($db, $car_id) {
    $query = "DELETE FROM `users` WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    return ($affected_rows > 0);
}

?>