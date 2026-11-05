<?php
header('Content-Type: application/json');
include_once("../../includes/configuration.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_FILES['pdfFile'])) {
        $orderId = $_POST['id'];
        $uploadDir = __DIR__ . '../../../../pdf/'; // Ensure this path is correct and writable
        $fileTmpPath = $_FILES['pdfFile']['tmp_name'];
        
        // Generate a unique random file name
        $randomString = bin2hex(random_bytes(8)); // 16 characters
        $fileExtension = pathinfo($_FILES['pdfFile']['name'], PATHINFO_EXTENSION);
        $fileName = $randomString . '.' . $fileExtension; // Unique file name
        $insertFile = 'pdf/' . $fileName; // Path for database insertion
        $destinationPath = $uploadDir . $fileName; // Complete destination path

        // Attempt to move the uploaded file to the destination folder
        if (move_uploaded_file($fileTmpPath, $destinationPath)) {
            // Update the database with the file path and status
			$query = "UPDATE order_list SET file_one='$insertFile', status='success', complete_time=NOW() WHERE id='$orderId'";
            if ($conn->query($query) === TRUE) {
                echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully!', 'fileName' => $fileName]);
            } else {
                // Handle potential database update errors
                echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $conn->error]);
            }
        } else {
            // Debugging potential issues with file upload
            $errorMessage = 'Failed to move uploaded file. ';
            
            // Check if the directory is writable
            if (!is_writable($uploadDir)) {
                $errorMessage .= 'Upload directory is not writable. ';
            } 
            
            // Check if the temporary file exists
            elseif (!file_exists($fileTmpPath)) {
                $errorMessage .= 'Temporary file is missing. ';
            }
            
            // Add more details about the file and directory
            $errorMessage .= 'Directory: ' . $uploadDir . '. ';
            $errorMessage .= 'Temp file: ' . $fileTmpPath . '. ';
            if (file_exists($fileTmpPath)) {
                $errorMessage .= 'Temp file size: ' . filesize($fileTmpPath) . '. ';
            } else {
                $errorMessage .= 'Temp file does not exist. ';
            }

            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded or there was an error.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
