<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "error" => "Only GET requests are allowed"
    ]);

    exit;
}

$uid = isset($_GET["uid"])
    ? trim($_GET["uid"])
    : "";

$server = isset($_GET["server"])
    ? trim($_GET["server"])
    : "BD";

if ($uid === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "UID is required"
    ]);

    exit;
}

if (!preg_match('/^[0-9]+$/', $uid)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid UID"
    ]);

    exit;
}

/*
 * Free Fire unofficial API
 */

$apiUrl =
    "https://freefireinfo-zy9l.onrender.com/api/v1/player-profile"
    . "?uid=" . urlencode($uid)
    . "&server=" . urlencode($server);

$ch = curl_init();

curl_setopt_array($ch, [

    CURLOPT_URL => $apiUrl,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_FOLLOWLOCATION => true,

    CURLOPT_CONNECTTIMEOUT => 15,

    CURLOPT_TIMEOUT => 30,

    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "User-Agent: Mozilla/5.0"
    ]

]);

$result = curl_exec($ch);

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

$curlError = curl_error($ch);

curl_close($ch);

if ($result === false || $curlError) {

    http_response_code(502);

    echo json_encode([
        "error" => "Unable to connect to Free Fire API",
        "details" => $curlError
    ]);

    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {

    http_response_code($httpCode ?: 502);

    echo json_encode([
        "error" => "Free Fire API returned HTTP " . $httpCode
    ]);

    exit;
}

$data = json_decode($result, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {

    http_response_code(502);

    echo json_encode([
        "error" => "Invalid response from Free Fire API"
    ]);

    exit;
}

echo json_encode(
    $data,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);
?>
