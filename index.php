<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Parameter sammeln
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Content-Type prüfen
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
            // Standard POST-Daten
            $kartennummer = isset($_POST['ean']) ? $_POST['ean'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
        } else {
            // Raw POST-Daten (falls nötig)
            parse_str(file_get_contents('php://input'), $post_data);
            $kartennummer = isset($post_data['ean']) ? $post_data['ean'] : '';
            $email = isset($post_data['email']) ? $post_data['email'] : '';
        }
    } else {
        // GET-Parameter
        $kartennummer = isset($_GET['kartennummer']) ? $_GET['kartennummer'] : '';
        $email = isset($_GET['email']) ? $_GET['email'] : '';
    }
    
    // Debug-Logging (temporär)
    error_log("Heroku Gateway - EAN: $kartennummer, Email: $email");
    
    // Validation
    if (empty($kartennummer) || empty($email)) {
        echo 'no_email';
        exit;
    }
    
    // Weiterleitung an Ihren Server
    $target_url = "http://87.138.159.219:83/guthaben-shopify.php";
    
    // POST-Request an Ihren Server
    $post_fields = http_build_query([
        'kartennummer' => $kartennummer,
        'email' => $email
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $post_fields,
            'timeout' => 30
        ]
    ]);
    
    $result = file_get_contents($target_url, false, $context);
    
    if ($result === false) {
        throw new Exception('Server nicht erreichbar');
    }
    
    // Debug-Logging
    error_log("Server Response: " . $result);
    
    // Antwort weiterleiten
    echo trim($result);
    
} catch (Exception $e) {
    error_log('Gateway Error: ' . $e->getMessage());
    echo 'email_error';
}
?>
