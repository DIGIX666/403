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

// Request to API
$apiUrl = "http://ip-api.com/json/{$ipaddr}";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true); 

// 3. Vérification de l'état de la requête
if ($data['status'] == 'success') {
    // Receive the data
    $city = isset($data['city']) ? $data['city'] : 'Unknown';
    $regionName = isset($data['regionName']) ? $data['regionName'] : 'Unknown';
    $country = isset($data['country']) ? $data['country'] : 'Unknown';
    $isp = isset($data['isp']) ? $data['isp'] : 'Unknown ISP';
    $lat = isset($data['lat']) ? $data['lat'] : 'Unknown';
    $lon = isset($data['lon']) ? $data['lon'] : 'Unknown';

    // Format the log message
    $log = "IP Address: " . $ipaddr . "\r\n" .
           "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
           "Location: " . $city . ", " . $regionName . ", " . $country . "\r\n" .
           "ISP: " . $isp . "\r\n" .
           "Latitude: " . $lat . " - Longitude: " . $lon . "\r\n" .
           "--------------------------------------------\r\n";

    
    $subject = "Nouvelle visite détectée sur la page 403";
    $message = $log;

    
    $to = "digixit66@gmail.com";

    // Header mail
    $headers = "From: noreply@tondomaine.com" . "\r\n" .
               "Reply-To: noreply@tondomaine.com" . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Send mail
    if (mail($to, $subject, $message, $headers)) {
        echo 'Email envoyé avec succès.';
    } else {
        echo 'Échec de l\'envoi de l\'email.';
    }

} else {
    // Si l'API renvoie un statut d'erreur, on log l'erreur
    $log = "Failed to get location for IP: " . $ipaddr . "\r\n" .
           "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
           "--------------------------------------------\r\n";
}
?>