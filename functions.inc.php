<?php//swithch to get for params
function gateway(string $request_method) {
    $raw_data = file_get_contents("php://input");
    $data = json_decode(($raw_data), true);

    
    $db = db_connection();

    switch($request_method){
        case "GET":
            if(isset($data["id"]) && !empty($data["id"])) {
                $id = $data["id"];
                if($data = get_data($db, $id)){
                    if($data[1] >= 2) {
                        return json_encode(["msg" => "query error."], JSON_PRETTY_PRINT);
                        //break;
                    }elseif($data[1] == 0) {
                        return json_encode(["msg" => "there is no game with the id: " . $id], JSON_PRETTY_PRINT);
                        //break;
                    }
                }else{
                    return json_encode(["msg" => "'get_data' fn error."], JSON_PRETTY_PRINT);
                    //break;
                }
            }else{
                return json_encode(get_all_data($db), JSON_PRETTY_PRINT);
                //break;
            }
        case "POST":
            if(isset($data["name"]) && !empty($data["name"]) && isset($data["in_stock"]) && !empty($data["in_stock"])) {
                $name = $data["name"];
                $stock = ($data["in_stock"] === "yes") ? 1 : 0;
                if(add_game($db, $name, $stock)) {
                    return json_encode(["msg" => "you added a game to the db."], JSON_PRETTY_PRINT);
                }
            }else{
                return json_encode(["msg" => "problem with the params you inserted."], JSON_PRETTY_PRINT);
            }
            break;
        case "DELETE":
            if(isset($data["name"]) && !empty($data["name"])) {
                $name = $data["name"];
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

function get_data($db, $id):array {
    $query = "SELECT * FROM `games` WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $num_rows = $result->num_rows;
    $game = $result->fetch_assoc();

    $stmt->close();

    return [$game, $num_rows];
}

function add_game($db, $game_name, $game_in_stock) {
    $query = "INSERT INTO `games` (name, in_stock) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("si", $game_name, $game_in_stock);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function delete_game($db, $game_name) {
    $query = "DELETE FROM `games` WHERE name = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $game_name);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    return ($affected_rows > 0);
}

?>