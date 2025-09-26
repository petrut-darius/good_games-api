<?php
function gateway(string $request_method) {

    
    $db = db_connection();
    
    switch($request_method){
        case "GET":
            if(isset($_GET["id"]) && $_GET["id"]) {
                $id = $_GET["id"];
                if($data = get_car($db, $id)){
                    if($data[1] >= 2) {
                        return json_encode(["msg" => "query error."], JSON_PRETTY_PRINT);
                        //break;
                    }elseif($data[1] == 0) {
                        return json_encode(["msg" => "there is no game with the id: " . $id], JSON_PRETTY_PRINT);
                        //break;
                    }elseif($data[1] == 1) {
                        return json_encode($data[0], JSON_PRETTY_PRINT);
                    }
                }else{
                    return json_encode(["msg" => "'get_data' fn error."], JSON_PRETTY_PRINT);
                    //break;
                }
            }else{
                return json_encode(get_all_cars($db), JSON_PRETTY_PRINT);
                //break;
            }
            break;
        case "POST":
            if(isset($_POST["marca"]) && !empty($_POST["marca"]) && isset($_POSt["model"]) && !empty($_POSt["model"]) && isset($_POST["an"]) && !empty($_POST["an"]) && isset($_POST["serie_sasiu"]) && !empty($_POST["serie_sasiu"]) && isset($_POST["numar_inmatriculare"]) && !empty($_POST["numar_inmatriculare"])) {
                $marca = $_POST["marca"];
                $model = $_POST["model"];
                $an = $_POST["an"];
                $serie_sasiu = $_POST["serie_sasiu"];
                $numar_inmatriculare = $_POST["numar_inmatricualre"];
                try{
                    if (!preg_match('/^[\w\s\-\']{1,100}$/u', $marca)) {
                        throw new Exception("data trimisa nu e buna la marca.");
                    }
                    if (!preg_match('/^[\w\s\-\']{1,100}$/u', $model)) {
                        throw new Exception("data trimisa nu e buna la model.");
                    }   
                    if (!preg_match('/^[\w\s\-\']{1,100}$/u', $serie_sasiu)) {
                        throw new Exception("data trimisa nu e buna la seria de sasiu.");
                    }
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $an)) {
                        throw new Exception("data trimisa nu e buna la an.");
                    }
                    if (!preg_match('/^[\w\s\-\']{1,100}$/u', $numar_inmatriculare)) {
                        throw new Exception("data trimisa nu e buna la numarul de inmatriculare.");
                    }
                }catch(Exception $e) {
                    return json_encode($e->getMessage(), JSON_PRETTY_PRINT);
                }

                if(add_car($db, $marca, $model, $an, $serie_sasiu, $numar_inmatriculare)) {
                    return json_encode(["msg" => "you added a car to the db."], JSON_PRETTY_PRINT);
                }
            }else{
                return json_encode(["msg" => "problem with the params you inserted."], JSON_PRETTY_PRINT);
            }
            break;
        case "PATCH":
        case "PUT":
            parse_str(file_get_contents("php://input", $update));
            if(isset($update["numar_inmatriculare"]) && !empty($update["numar_inmatriculare"]) && isset($update["id"]) && !empty($update["id"])) {
                $numar_inmatriculare = $update["numar_inmatriculare"];
                $id = $update["id"];

                if(update_car($db, $id, $numar_inmatriculare)) {
                    return json_encode(["msg" => "you updated the number."], JSON_PRETTY_PRINT);
                }else{
                    return json_encode(["msg" => "query error."], JSON_PRETTY_PRINT);
                }

            }else{
                return json_encode(["msg" => "the ajax call did not send the update params"], JSON_PRETTY_PRINT);
            }

            break;
        case "DELETE":
            parse_str(file_get_contents("php://input", $delete));
            if(isset($delete["id"]) && !empty($delete["id"])) {
                $id = $delete["id"];

                if(delete_car($db, $id )) {
                    return json_encode(["msg" => "you deleted the car."], JSON_PRETTY_PRINT);
                }else{
                    return json_encode(["msg" => "query error."], JSON_PRETTY_PRINT);
                }

            }else{
                return json_encode(["msg" => "the ajax call did not send the delete params"], JSON_PRETTY_PRINT);
            }


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
        mysqli_select_db($db_connection, "car-info");
    }catch(mysqli_sql_exception $e){
        echo "error: " . $e->getMessage() . " at line: " . $e->getLine();
    }

    return $db_connection;
}

function get_all_cars(mysqli $db):array {
    $query = "SELECT * FROM `cars`";

    $result = $db->query($query);
    $games = [];

    while($row = mysqli_fetch_assoc($result)) {
        $games[] = $row; 
    }

    return $games;
}

function get_car($db, $id):array {
    $query = "SELECT * FROM `cars` WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $num_rows = $result->num_rows;
    $game = $result->fetch_assoc();

    $stmt->close();

    return [$game, $num_rows];
}

function add_car($db, $marca, $model, $an, $serie_sasiu, $numar_inmatriculare) {
    $query = "INSERT INTO `cars` (marca, model, an, serie, numar) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("sssss", $marca, $model, $an, $serie_sasiu, $numar_inmatriculare);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function update_car($db, int $id, $numar_inmatriculare) {
    $query = "UPDATE `games` SET (numar_inmatriculare) VALUES(?) WHERE id = " . $id;
    $stmt = $db->prepare($query);
    $stmt->bind_param($numar_inmatriculare);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function delete_car($db, $car_id) {
    $query = "DELETE FROM `cars` WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    return ($affected_rows > 0);
}

?>