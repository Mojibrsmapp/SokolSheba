<?php
session_start();
include_once("includes/configuration.php");

// Function to generate a random string (24 characters)
function generateRandomString($length = 24) {
    return bin2hex(random_bytes($length / 2));
}

if (isset($_FILES['pdf'])) {
    $targetDir = "../pdf/";
    $response = [];

    foreach ($_FILES['pdf']['name'] as $key => $name) {
        $pdf = [
            'name' => $_FILES['pdf']['name'][$key],
            'tmp_name' => $_FILES['pdf']['tmp_name'][$key],
            'error' => $_FILES['pdf']['error'][$key],
            'size' => $_FILES['pdf']['size'][$key]
        ];

        // Validate the file type
        $fileType = strtolower(pathinfo($pdf["name"], PATHINFO_EXTENSION));
        if ($fileType !== "pdf") {
            $response[] = "Invalid file type for: " . htmlspecialchars($pdf['name']);
            continue;
        }

        // Extract the numeric part from the nid
        $pdfNameWithoutExtension = pathinfo($pdf["name"], PATHINFO_FILENAME);
        preg_match('/\d+/', $pdfNameWithoutExtension, $matches); // Extract numeric part
        $nidNumber = $matches[0] ?? null;

        if (!$nidNumber) {
            $response[] = "No valid numeric NID found in: " . htmlspecialchars($pdf['name']);
            continue;
        }

        // Check for matching order in the database
        $sql = "SELECT id, status FROM order_list WHERE nid LIKE CONCAT('%', ?, '%') AND status != 'success'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nidNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $orderId = $row['id'];

            // Generate a random file name
            $randomFileName = generateRandomString() . ".pdf";
            $targetFile = $targetDir . $randomFileName;
            $inserData = "pdf/" . $randomFileName;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($pdf["tmp_name"], $targetFile)) {
                // Update the database
                $updateSql = "UPDATE order_list SET file_one = ?, complete_time = CURRENT_TIMESTAMP, status = 'success' WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $inserData, $orderId);

                if ($updateStmt->execute()) {
                    $response[] = "Success: " . htmlspecialchars($pdf['name']);
                } else {
                    $response[] = "Error updating database for: " . htmlspecialchars($pdf['name']);
                }
                $updateStmt->close();
            } else {
                $response[] = "Error moving file: " . htmlspecialchars($pdf['name']);
            }
        } else {
            $response[] = "No matching order or already completed for: " . htmlspecialchars($pdf['name']);
        }
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => $response]);
}

$conn->close();