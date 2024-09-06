<?php
// IP
if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $ipaddr = $_SERVER['HTTP_CLIENT_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ipaddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ipaddr = $_SERVER['REMOTE_ADDR'];
}

if (strpos($ipaddr, ',') !== false) {
    $ipaddr = preg_split("/\,/", $ipaddr)[0];
}

// Requête vers l'API
$apiUrl = "http://ip-api.com/json/{$ipaddr}";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

// Vérification de l'état de la requête
if ($data['status'] == 'success') {
    // Récupération des informations
    $city = isset($data['city']) ? $data['city'] : 'Unknown';
    $regionName = isset($data['regionName']) ? $data['regionName'] : 'Unknown';
    $country = isset($data['country']) ? $data['country'] : 'Unknown';
    $isp = isset($data['isp']) ? $data['isp'] : 'Unknown ISP';
    $lat = isset($data['lat']) ? $data['lat'] : 'Unknown';
    $lon = isset($data['lon']) ? $data['lon'] : 'Unknown';

    // Formatage du message à sauvegarder
    $logMessage = "IP Address: " . $ipaddr . "\r\n" .
                  "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
                  "Location: " . $city . ", " . $regionName . ", " . $country . "\r\n" .
                  "ISP: " . $isp . "\r\n" .
                  "Latitude: " . $lat . " - Longitude: " . $lon . "\r\n" .
                  "--------------------------------------------\r\n";

    // Sauvegarde dans un fichier log.txt
    $filePath = __DIR__ . '/log.txt';  // Chemin vers le fichier log.txt
    file_put_contents($filePath, $logMessage, FILE_APPEND | LOCK_EX);  // Ajout des données au fichier
    echo 'Données sauvegardées avec succès dans le fichier log.txt.';
} else {
    // Log si l'API échoue
    $errorMessage = "Failed to get location for IP: " . $ipaddr . "\r\n" .
                    "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
                    "--------------------------------------------\r\n";

    $filePath = __DIR__ . '/log.txt';
    file_put_contents($filePath, $errorMessage, FILE_APPEND | LOCK_EX);
    echo 'Une erreur est survenue. Les informations ont été sauvegardées.';
}