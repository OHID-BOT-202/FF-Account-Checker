<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

$uid = isset($_GET["uid"]) ? trim($_GET["uid"]) : "";
$server = isset($_GET["server"]) ? strtoupper(trim($_GET["server"])) : "BD";

if (!preg_match("/^[0-9]{5,20}$/", $uid)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid UID"
    ]);
    exit;
}

$allowedServers = [
    "IND", "SG", "RU", "ID", "TW", "US",
    "VN", "TH", "ME", "PK", "CIS", "BR", "BD"
];

if (!in_array($server, $allowedServers, true)) {
    $server = "BD";
}

$apiUrl =
    "https://freefireinfo-zy9l.onrender.com/api/v1/player-profile" .
    "?uid=" . urlencode($uid) .
    "&server=" . urlencode($server);

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "User-Agent: Ohid-AI-UID-Checker/1.0"
    ]
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($response === false || $error) {
    http_response_code(502);

    echo json_encode([
        "success" => false,
        "message" => "API server unreachable",
        "details" => $error
    ]);
    exit;
}

if ($status < 200 || $status >= 300) {
    http_response_code($status ?: 502);

    echo json_encode([
        "success" => false,
        "message" => "API returned HTTP status " . $status
    ]);
    exit;
}

$data = json_decode($response, true);

if (!is_array($data)) {
    http_response_code(502);

    echo json_encode([
        "success" => false,
        "message" => "Invalid API response"
    ]);
    exit;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
