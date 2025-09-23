<?php
function gateway(string $request_method) {

    
    $db = db_connection();
    
    switch($request_method){
        case "GET":
            if(isset($_GET["id"]) && $_GET["id"]) {
                $id = $_GET["id"];
                if($data = get_data($db, $id)){
                    if($data[1] >= 2) {
                        return json_encode(["msg" => "query error."], JSON_PRETTY_PRINT);
                        //break;
                    }elseif($data[1] == 0) {
                        return json_encode(["msg" => "there is no game with the id: " . $id], JSON_PRETTY_PRINT);
                        //break;
                    }elseif($data[1] == 1) {
                        return json_encode([$data[0]], JSON_PRETTY_PRINT);
                    }
                }else{
                    return json_encode(["msg" => "'get_data' fn error."], JSON_PRETTY_PRINT);
                    //break;
                }
            }else{
                return json_encode(get_all_data($db), JSON_PRETTY_PRINT);
                //break;
            }
            break;
        case "POST":
            if(isset($_POST["marca"]) && !empty($_POST["marca"]) && isset($_POST["model"]) && !empty($_POST["model"]) && isset($_POST["an"]) && !empty($_POST["an"]) && isset($_POST["serie_sasiu"]) && !empty($_POST["serie_sasiu"]) && isset($_POST["numar_inmatriculare"]) && !empty($_POST["numar_inmatriculare"])) {
                $marca = $_POST["marca"];
                $model = $_POST["model"];
                $an = $_POST["an"];
                $serie_sasiu = $_POST["serie_sasiu"];
                $numar_inmatriculare = $_POST["numar_inmatriculare"];
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
            if(isset($_GET["id"]) && !empty($_GET["id"]) && ((isset($_GET["name"]) && !empty($_GET["name"])) || (isset($_GET["in_stock"]) && !empty($_GET["in_stock"])))) {
                $id = $_GET["id"];
                $name = $_GET["name"] ?? null;
                $stock = $_GET["in_stock"] ?? null;
                $types = "";
                if($name != null) {
                    $fields[] = "name = ?";
                    $params[] = $name;
                    $types .= "s";
                }

                if($stock != null) {
                    $fields[] = "in_stock = ?";
                    $params[] = $stock;
                    $types .= "i";
                }

                if(empty($fields) || empty($params) || empty($types)) {
                    return json_encode(["msg" => "problem with getting data."], JSON_PRETTY_PRINT);
                }

                if(!empty($fields) && !empty($params) && !empty($types)) {
                    $result = update_data($db, $id, $params, $fields, $types);
                    return json_encode(["msg" => "updated the game with id: " . $id], JSON_PRETTY_PRINT);
                }

            }else{
                return json_encode(["msg" => "problem with the params you inserted."], JSON_PRETTY_PRINT);
            }

            break;
        case "DELETE":
            if(isset($_GET["name"]) && !empty($_GET["name"])) {
                $name = $_GET["name"];
                if($result = delete_game($db,$name)) {
                    return json_encode(["msg" => "you deleted " . $name . "."], JSON_PRETTY_PRINT);
                }else{
                    return json_encode(["msg" => $name . " is not saved in the db, so you can't delete it."], JSON_PRETTY_PRINT);
                }
            }else{
                return json_encode(["msg" => "problem with the params you inserted."], JSON_PRETTY_PRINT);
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

function get_all_data(mysqli $db):array {
    $query = "SELECT * FROM `cars`";

    $result = $db->query($query);
    $games = [];

    while($row = mysqli_fetch_assoc($result)) {
        $games[] = $row; 
    }

    return $games;
}

function get_data($db, $id):array {
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

function update_data($db, int $id, array $params, array $fields, string $types) {
    $query = "UPDATE `games` SET " . implode(",", $fields) . " WHERE id = " . $id;
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function delete_game($db, $car_id) {
    $query = "DELETE FROM `cars` WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    return ($affected_rows > 0);
}

?>