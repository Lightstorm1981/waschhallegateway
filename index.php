<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Nur guthaben-abfragen.php erlauben
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/guthaben.php') {
    http_response_code(404);
    echo "Zugriff verweigert";
    exit;
}

try {
    // Parameter sammeln
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        parse_str(file_get_contents('php://input'), $post_data);
        $kartennummer = isset($post_data['ean']) ? $post_data['ean'] : '';
        $email = isset($post_data['email']) ? $post_data['email'] : '';
    } else {
        $kartennummer = isset($_GET['kartennummer']) ? $_GET['kartennummer'] : '';
        $email = isset($_GET['email']) ? $_GET['email'] : '';
    }

    // Weiterleitung an Ihren Server
    $target_url = "http://87.138.159.219:83/guthaben.php?kartennummer=" . urlencode($kartennummer) . "&email=" . urlencode($email);

    $context = stream_context_create([
        'http' => [
            'timeout' => 30
        ]
    ]);

    $result = file_get_contents($target_url, false, $context);

    if ($result === false) {
        throw new Exception('Server nicht erreichbar');
    }

    echo $result;

} catch (Exception $e) {
    http_response_code(500);
    echo "Fehler: " . $e->getMessage();
}
?>
