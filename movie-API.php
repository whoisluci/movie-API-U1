<?php
ini_set("display_errors", 1); 

require_once "functions.php";

$filename = "movies.json";

$requestMethod = $_SERVER["REQUEST_METHOD"];
$allowedMethods = ["GET", "POST", "PATCH", "DELETE"];


if (!in_array($requestMethod, $allowedMethods)){
    $error = ["error" => "Invalid HTTP method."];
    sendJSON($error, 405);
}

$movies = []; //startar m. tom data  

if (file_exists($filename)) { //hämtar data 
    $movies_json = file_get_contents($filename);
    $movies = json_decode($movies_json, true);
}

//GET METHOD
if ($requestMethod == "GET") {
    if (isset ($_GET["id"])) {
        $id = $_GET["id"];

        foreach ($movies as $movie) {
            if ($movie["id"] == $id) {
                sendJSON($movie);
            }
        }

        $error = ["ERROR" => "Movie not found"];
        sendJSON($error, 404);
    }

    if (isset($_GET["language"])) {
        $language = $_GET["language"];
        $filteredMovies = [];

        foreach ($movies as $movie) {
            if (in_array($language, $movie["language"])) {
                $filteredMovies[] = $movie;
            }
        }
        
        if(!empty($filteredMovies)) {
            sendJSON($filteredMovies);
        }

        $error = ["ERROR" => "Movie not found"];
        sendJSON($error, 404);
    }

    if (isset($_GET["releaseYear"])) {
        $releaseYear = $_GET["releaseYear"];
        $filteredMovies = [];
        
        foreach ($movies as $movie) {
            if ($movie["releaseYear"] <= $releaseYear) { 
                //releaseYear och under
                $filteredMovies[] = $movie; 
            }
        }
        
        if(!empty($filteredMovies)) {
            sendJSON($filteredMovies);
        }

        $error = ["ERROR" => "Movie not found"];
        sendJSON($error, 404);
    }

    if (isset($_GET["avgRating"])) {
        $avgRating = $_GET["avgRating"];
        $filteredMovies = [];
        
        foreach ($movies as $movie) {
            if ($movie["avgRating"] >= $avgRating) { 
                //avgRating och under
                $filteredMovies[] = $movie; 
            }
        }
        
        if(!empty($filteredMovies)) {
            sendJSON($filteredMovies);
        }

        $error = ["ERROR" => "Movie not found"];
        sendJSON($error, 404);
    }


    sendJSON($movies);
}

//POST METHOD
$contentType = $_SERVER["CONTENT_TYPE"];
if ($contentType != "application/json") {
    $error = ["ERROR" => "Invalid content type, only JSON is allowed."];
    sendJSON ($error, 400);
}

$inputJSON = file_get_contents("php://input");
$inputData = json_decode($inputJSON, true);

if ($requestMethod == "POST") {
    $requiredKeys = ["title", "releaseYear", "director", "language", "runtime", "avgRating"];
    $allRequiredKeys = true;

    foreach ($requiredKeys as $key) {
        if (!isset ($inputData[$key])){
            $allRequiredKeys = false; 
            break;
        }
    }

    if (!$allRequiredKeys){
        $error = ["ERROR" => "Bad Request: One or more keys are missing."];
        sendJSON($error, 400);
    }

   
    $highestID = 0;
    foreach ($movies as $movie) {
        if ($movie["id"] > $highestID) {
            $highestID = $movie["id"];
        }
    }
    $nextID = $highestID + 1;

    $addedMovie = [];
    $addedMovie["id"] = $nextID;
    foreach ($requiredKeys as $key) {
            $$key = $inputData[$key];
            $addedMovie[$key] = $$key;
        }

    $movies[] = $addedMovie;
    $movies_json = json_encode($movies, JSON_PRETTY_PRINT);
    file_put_contents($filename, $movies_json);
    sendJSON($addedMovie); 
}

//DELETE METHOD
if ($requestMethod == "DELETE") {
    if (!isset($inputData["id"])) {
        $error = ["ERROR" => "Bad Request: Movie ID required."];
        sendJSON($error, 400);
    }

    $id = $inputData["id"];
    foreach ($movies as $index => $movie) {
        if ($movie["id"] == $id) {
            array_splice($movies, $index, 1);
            $movies_json = json_encode($movies, JSON_PRETTY_PRINT);
            file_put_contents($filename, $movies_json);
            sendJSON($movie);
        }
    }

    $error = ["ERROR" => "Movie not found."];
    sendJSON($error, 404);
}

//PATCH METHOD
if ($requestMethod == "PATCH") {
    if (!isset($inputData["id"], $inputData["key"])) {
        $error = ["ERROR" => "Bad Request: ID or Key is missing"];
        sendJSON($error, 400);
    }

    $id = $inputData["id"];
    $editedKey = $inputData["key"];

    foreach ($movies as $index => $movie) {
        if ($movie["id"] == $id) {
            $movie["key"] = $editedKey;
            $movies[$index] = $movie; 

            $movies_json = json_encode($movies, JSON_PRETTY_PRINT);
            file_put_contents($filename, $movies_json);
            sendJSON($movie);
        }
    }

    $error = ["ERROR" => "Movie not found."];
    sendJSON($error, 404);
}
?>