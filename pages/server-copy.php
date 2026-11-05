<?php
// Start the session
session_start();
error_reporting(0);

// Check if user is logged in
if (strlen($_SESSION['user_id']) == 0) {
    header('location:index.php');
    exit;
}

// Check if 'nid' and 'id' are set in the URL
if (isset($_GET["nid"]) && isset($_GET["id"])) {
    $nid = $_GET["nid"];
    $id = $_GET["id"];
    
include_once("../includes/configuration.php"); 

    // Prepare SQL query to fetch data based on nid and id
    $sql = "SELECT * FROM server_copy WHERE nationalId = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare was successful
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    
    // Bind parameters (assuming nid is a string and id is an integer)
    $stmt->bind_param("si", $nid, $id); // "s" for string, "i" for integer
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch data
        while ($data = $result->fetch_assoc()) {
            // Access the fetched data
            $nameBn = $data['nameBn'];
            $nameEn = $data['nameEn'];
            $nid = $data['nationalId'];
            $pin = $data['pin'];
            $sl_no = $data['sl_no'];
          	$voter_aria_code = $data['voter_aria_code'];
          	$voter_no = $data['voter_no'];
            $dob = $data['dateOfBirth'];
            $father = $data['father'];
            $mother = $data['mother'];
            $gender = $data['gender'];
            $photoPath = $data['photo_path'];
            $permanentAddress = $data['permanent_address'];
            $presentAddress = $data['present_address'];
            $bloodGroup = $data['bloodGroup'];
            $permanentaddress = $data['permanentAddressLine'];
            $presentaddress = $data['presentAddressLine']; 
            $addressLine = $data['addressLine'];
         	$occupation = $data['occupation'];
            $birthPlace = $data['birthPlace'];
            $religion = $data['religion'];
            $signPath = $data['sign_path'];
            $bloodGroup = $data['bloodGroup'];
            if (is_numeric($voter_no)) {
            // $voter_no is a number
            $s_v = "V1";
        } else {
            // $voter_no is text
           $s_v = "V2";
        }

        }
    } else {
        echo "No records found for the given NID and ID.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "NID or ID not set.";
}
?>