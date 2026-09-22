<?php
require_once 'db.php';

header('Content-Type: text/plain');

$apiKey = getOpenWeatherApiKey();
echo "API key in use: " . substr($apiKey, 0, 6) . "..." . substr($apiKey, -4) . "\n\n";

// Nizamabad coordinates as a test point - adjust if needed
$lat = 18.6725;
$lon = 78.0941;

$url = "https://api.openweathermap.org/data/2.5/weather"
     . "?lat=" . urlencode($lat)
     . "&lon=" . urlencode($lon)
     . "&appid=" . urlencode($apiKey)
     . "&units=metric";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP status code: $httpCode\n";
echo "cURL error (if any): " . ($curlError ?: "none") . "\n\n";
echo "Raw response:\n";
echo $response;