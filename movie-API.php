<?php
ini_set("display_errors", 1); 

require_once "functions.php";

$filename = "movies.json";

$requestMethod = $_SERVER["REQUEST_METHOD"];
$allowedMethods = ["GET", "POST", "PATCH", "DELETE"];

if (!in_array($requestMethod, $allowedMethods)){
    $error = ["error" => "Invalid HHTP method"];
    sendJSON($error, 405);
}

$movies = []; //startar m. tom data  

if (file_exists($filename)) { //hämtar data 
    $movies_json = file_get_contents($filename);
    $movies = json_decode($json, true);
}

if ($requestMethod == "GET") {
    if (isset ($_GET["id"])) {
        $id = $_GET["id"];

        foreach ($movies as $movies) {
            if ($movie["id"] == $id) {
                sendJSON($movie);
            }
        }

        $error = ["error" => "Movie not found"];
        sendJSON($error, 404);
    }
    sendJSON($movies);
}


?>