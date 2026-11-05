<?php
header('Content-Type: application/json');

include_once("../../includes/configuration.php");

// Get the JSON input from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['bio_totto'])) {
    $orderId = $data['id'];
    $bioTotto = $data['bio_totto'];
    $status = "success"; // Assign status to a variable

    // Prepare the SQL query to update the bio_totto field
    $sql = "UPDATE order_list SET bio_totto = ?, status = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssi', $bioTotto, $status, $orderId); // Bind the parameters (string, string, integer)
        
        if ($stmt->execute()) {
            // Return a success response
            echo json_encode(['status' => 'success', 'message' => 'Bio Totto updated successfully.']);
        } else {
            // Return an error response if the query fails
            echo json_encode(['status' => 'error', 'message' => 'Failed to update Bio Totto.']);
        }

        $stmt->close();
    } else {
        // Return an error response if the SQL statement couldn't be prepared
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
    
    $conn->close();
} else {
    // Return an error response if required data is missing
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
}
?>
