<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['phoneNumber'])) {
    $phoneNumber = trim($_POST['phoneNumber']);
    if (strlen($phoneNumber) === 11 && strpos($phoneNumber, '01') === 0) {
        $url = "https://blush-janaye-60.tiiny.io/?msisdn=" . $phoneNumber;
        $response = file_get_contents($url);

        if ($response !== FALSE) {
            $data = json_decode($response, true);
            if ($data) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'name' => $data['name'] ?? 'N/A',
                        'userId' => $data['userId'] ?? 'N/A',
                        'status' => $data['status'] ?? 'N/A',
                        'userType' => $data['userType'] ?? 'N/A',
                        'rbBase' => isset($data['rbBase']) && $data['rbBase'] ? 'Yes' : 'No',
                        'authTokenInfo' => $data['authTokenInfo'] ?? 'N/A',
                        'verificationStatus' => $data['verificationStatus'] ?? 'N/A',
                        'executionStatus' => $data['executionStatus'] ?? 'N/A',
                    ]
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid data received from server or no information found.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error in fetching data. Please try again later.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid phone number format. Please enter a valid number starting with "01".']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>