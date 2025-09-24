<?php
function gateway(string $request_method) {

    
    $db = db_connection();
    
    switch($request_method){
        case "GET":
            return json_encode(["msg" => "why get request."], JSON_PRETTY_PRINT);
            break;
        case "POST":  
            //signup  
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

                    return json_encode(add_user($db, $first_name, $last_name, $birth_date, $hashed_password, $email), JSON_PRETTY_PRINT);
                    break;
                }

            //login
            if(isset($_POST["email"]) && !empty($_POST["email"]) && isset($_POST["password"]) && !empty($_POST["password"])) {
                $email = $_POST["email"];
                $password = $_POST["password"];

                login_user($db, $email, $password);

            }                
            break;
        case "PATCH":
        case "PUT":
            if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {
                $user_id = $_SESSION["user_id"];
                if(isset($_POST["password"]) && !empty($_POST["password"]) && isset($_POST["password_again"]) && !empty($_POST["password_again"])) {
                    $password = $_POST["password"];
                    $password_again = $_POST["password_again"];

                        //regex

                    if($password !== $password_again) {
                        return json_encode(["msg" => "the password are not identical."], JSON_PRETTY_PRINT);
                    }


                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    
                    if(update_password($db, $user_id, $password)) {
                        return json_encode(["msg"] => "you updated your password.", JSON_PRETTY_PRINT);
                    }
                }
            }
        case "DELETE":
            if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {
                if(user_log_out()) {
                    return json_encode(["msg" => "log out complete."], JSON_PRETTY_PRINT);
                }else{
                    return json_encode(["msg" => "we couldn't log you out."], JSON_PRETTY_PRINT);
                }
            }
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


/* this is an admin function
function get_all_users(mysqli $db):array {
    $query = "SELECT * FROM `users`";

    $result = $db->query($query);
    $games = [];

    while($row = mysqli_fetch_assoc($result)) {
        $games[] = $row; 
    }

    return $games;
}
*/

function login_user(mysqli $db, $email, $password):array {
    $query = "SELECT * FROM `users` WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_rows = $result->num_rows;

    if($num_rows === 1) {
        $user = $result->fetch_assoc();

        if(password_verify($password, $user["password"])) {
            session_start();
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["id"];
            return json_encode(["msg" => "you are loged in as " . $user["first_name"] . " " . $user["last_name"] . "."], JSON_PRETTY_PRINT);
        }else{
            return json_encode(["msg" => "password invalid."], JSON_PRETTY_PRINT);
        }
    }else{
        return json_encode(["msg" => "user not found."], JSON_PRETTY_PRINT);
    }
    $stmt->close();

}

function get_user_data(mysqli $db,int $id,string $column_name) {
    $allowed = ["first_name", "last_name", "email"];

    if(in_array($column_name, $allowed)) {
        $query = "SELECT " . $column_name . " FROM `users` WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->fetch_assoc();
    }else{ 
        return false;
    }
}

function add_user(mysqli $db, $first_name, $last_name, $birth_date, $password, $email) {
    $query = "INSERT INTO `users` (first_name, last_name, birth_date, password, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("sssss", $first_name, $last_name, $birth_date, $password, $email);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function update_password(mysqli $db, int $id, $password) {
    $query = "UPDATE `users` SET (password) VALUES (?) WHERE id = " . $id;
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $password);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function user_log_out() {
    session_start();
    session_unset();
    session_destroy();
    return true;
}

?>