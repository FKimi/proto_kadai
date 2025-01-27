<?php
// mypage/fetch_metadata.php
session_start();
include('../functions.php');
check_session_id();

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['url'])) {
    echo json_encode(['error' => 'URL not provided']);
    exit();
}

$metadata = fetch_url_metadata($data['url']);
echo json_encode($metadata);