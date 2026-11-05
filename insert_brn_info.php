<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brn'], $_POST['dob'])) {
    $brn = trim($_POST['brn']);
    $dob = trim($_POST['dob']);

    if (empty($brn) || empty($dob)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Both BRN and DOB are required.'
        ]);
        exit;
    }

    $apiUrl = "https://api-sonaliseba.site/BIRTH/json.php?key=SA_d3858d1618546c6d7eb4684cac6cbc29&brn=$brn&dob=$dob";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to connect to the remote API: ' . curl_error($ch)
        ]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    if (!isset($decoded['data']) || !is_array($decoded['data'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or empty API response.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $decoded['data']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method or missing parameters.'
    ]);
}
?>