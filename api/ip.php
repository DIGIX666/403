<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Inclure PHPMailer via Composer

// Récupération de l'adresse IP
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

// Requête vers l'API pour obtenir les informations IP
$apiUrl = "http://ip-api.com/json/{$ipaddr}";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if ($data['status'] == 'success') {
    // Récupération des informations de localisation
    $city = isset($data['city']) ? $data['city'] : 'Unknown';
    $regionName = isset($data['regionName']) ? $data['regionName'] : 'Unknown';
    $country = isset($data['country']) ? $data['country'] : 'Unknown';
    $isp = isset($data['isp']) ? $data['isp'] : 'Unknown ISP';
    $lat = isset($data['lat']) ? $data['lat'] : 'Unknown';
    $lon = isset($data['lon']) ? $data['lon'] : 'Unknown';

    // Formatage du message
    $log = "IP Address: " . $ipaddr . "\r\n" .
           "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
           "Location: " . $city . ", " . $regionName . ", " . $country . "\r\n" .
           "ISP: " . $isp . "\r\n" .
           "Latitude: " . $lat . " - Longitude: " . $lon . "\r\n" .
           "--------------------------------------------\r\n";

    // Paramètres d'envoi de l'email avec PHPMailer
    $subject = "Nouvelle visite détectée sur la page 403";
    $message = $log;
    $to = "digixit66@gmail.com";
    $smtpUsername = getenv('OVH_SMTP_USERNAME');
    $smtpPassword = getenv('OVH_SMTP_PASSWORD');

    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP d'OVH
        $mail->isSMTP();
        $mail->Host       = 'ssl0.ovh.net'; // Serveur SMTP OVH
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;  // Ton adresse email OVH
        $mail->Password   = $smtpPassword;  // Ton mot de passe email OVH
        $mail->SMTPSecure = 'tls';            // Utilise TLS pour sécuriser la connexion
        $mail->Port       = 587;              // Port TLS d'OVH

        // Destinataires
        $mail->setFrom('tonadresse@tondomaine.com', 'Ton Nom');
        $mail->addAddress($to);  // Adresse du destinataire

        // Contenu de l'email
        $mail->isHTML(false);    // Format texte brut pour l'email
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Envoyer l'email
        $mail->send();
        error_log('Email envoyé avec succès.');
    } catch (Exception $e) {
        error_log("L'envoi de l'email a échoué. Erreur: {$mail->ErrorInfo}");
    }

} else {
    // Si l'API échoue, on enregistre l'erreur
    $log = "Failed to get location for IP: " . $ipaddr . "\r\n" .
           "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
           "--------------------------------------------\r\n";
    error_log($log);
}
?>