<?php

header('Content-Type: application/json');

// Connect to database
$mysqli = new mysqli("mi-linux.wlv.ac.uk", "2411673", "1dz63e", "db2411673");
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit();
}


if (isset($_GET['city']) && !empty($_GET['city'])) {
    // Search for the city in the database
    $sql = "SELECT *
            FROM weather
            WHERE city = ?
            AND weather_when >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY weather_when DESC LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $_GET['city']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the city is found in the database
    if ($result->num_rows > 0) {
        // City found, return the data
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        // City not found, fetch new data from the OpenWeatherMap API
        $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . $_GET['city'] . '&appid=f3efc227fce43c2803f7c78792b23924&units=metric';
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

        // Run SQL statement and report errors
        if ($mysqli->query($sql_insert)) {
        
            $new_data = [
                "weather_description" => $weather_description,
                "weather_temperature" => $weather_temperature,
                "weather_wind" => $weather_wind,
                "weather_when" => $weather_when,
                "city" => $city,
                "weather_humidity" => $weather_humidity,
                "weather_icon" => $weather_icon
            ];

            echo json_encode($new_data);
        } else {
            echo json_encode(["error" => "Failed to insert data into the database: " . $mysqli->error]);
        }
    }

    // Close the database connection
    $stmt->close();
    $mysqli->close();
} else {
    // When the city is not provided
    echo json_encode(["error" => "City parameter is not provided."]);
}
?>