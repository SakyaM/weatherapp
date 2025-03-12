<?php

if (isset($_GET['city']) && !empty($_GET['city'])) {
    $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . $_GET['city'] . '&appid=f3efc227fce43c2803f7c78792b23924&units=metric';

} else {
    echo json_encode(["error" => "City parameter is empty or not provided."]);
    exit();
}

$data = file_get_contents($url);

// Get data from openweathermap and store in JSON object
$data = file_get_contents($url);
$json = json_decode($data, true);

// Fetch required fields
$weather_description = $json['weather'][0]['description'];
$weather_temperature = $json['main']['temp'];
$weather_wind = $json['wind']['speed'];
$weather_when = date("Y-m-d H:i:s"); 
$city = $json['name']; 
$weather_humidity = $json['main']['humidity'];
$weather_icon = $json['weather'][0]['icon'];

// INSERT SQL statements
$sql_insert = "INSERT INTO weather (weather_description, weather_temperature, weather_wind, weather_when, city, weather_humidity, weather_icon)
    VALUES('{$weather_description}', {$weather_temperature}, {$weather_wind}, '{$weather_when}', '{$city}', {$weather_humidity}, '{$weather_icon}')";

// Display errors
if (!$mysqli->query($sql_insert)) {
    echo json_encode(["error" => "SQL Error occurred: " . $mysqli->error]);
}
?>