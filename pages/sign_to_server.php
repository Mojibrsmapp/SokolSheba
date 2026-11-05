<?php
// Start the session
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Check if user is logged in
if (strlen($_SESSION['user_id']) == 0) {
    header('location:index.php');
    exit;
}

// Check if 'nid' and 'id' are set in the URL
if (isset($_GET["id"])) {
    $id = base64_decode($_GET["id"]);
    
    include_once("../includes/configuration.php");
    // Prepare SQL query to fetch data based on nid and id
    $sql = "SELECT * FROM sign_to_server WHERE  id = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare was successful
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    } 
    
    // Bind parameters (assuming nid is a string and id is an integer)
    $stmt->bind_param("i", $id); // "s" for string, "i" for integer
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch data
        while ($data = $result->fetch_assoc()) {
            // Access the fetched data
            $nameBn = $data['name_bangla'];
            $nameEn = $data['name_english'];
            $nid = $data['nid'];
            $pin = $data['pin'];
          	$voterArea = $data['voterArea'];
          	$voter_no = $data['voterNo'];
            $dob = date("Y-m-d", strtotime($data['dob']));
            $father = $data['name_father'];
            $mother = $data['name_mother'];
    	    $spouse = $data['name_spouse'];
            $gender = $data['gender'];
            $photoPath = $data['image_url_12'];
            $permanentAddress = $data['permanentAddress'];
            $presentAddress = $data['presentAddress'];
            $bloodGroup = $data['blood_group'];
         	$occupation = $data['occupation'];
            $fatherNID = $data['father_nid'];
        	$motherNID = $data['mother_nid'];
            $birthPlace = $data['birth_place'];
            $religion = $data['religion'];
            $formNo = $data['formNo'];
            $education = $data['education'];
            $mobile = $data['mobile'];
            $voter_no = $data['voterNo'];
            $sl_no = $data['sl_no'];
            function convertToBanglaNumbers($input) {
                $englishToBanglaDigits = [
                    '0' => '০',
                    '1' => '১',
                    '2' => '২',
                    '3' => '৩',
                    '4' => '৪',
                    '5' => '',
                    '6' => '৬',
                    '7' => '৭',
                    '8' => '৮',
                    '9' => '৯'
                ];
                return strtr($input, $englishToBanglaDigits);
            }        
          

          
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




function renderRow($label, $value)
{
    if (!empty($value) && $value !== "N/A") {
        echo "<tr><td><strong>$label</strong></td><td>$value</td></tr>";
    }
}

$data = [
    "national_id" => $nid,
    "pin_number" => $pin,
    "fatherNID" => $fatherNID,
    "motherNID" => $motherNID,
    "voter_area" => $voterArea,
    "name_bangla" => $nameBn,
    "name_english" => $nameEn,
    "dob" => $dob,
    "father_name" => $father,
    "mother_name" => $mother,
    "spouse_name" => $spouse,
    "gender" => $gender,
    "education" => $education,
    "birthplace" => $birthPlace,
    "current_address" => $presentAddress,
    "permanent_address" => $permanentAddress,
    "occupation" => $occupation,
    "bloodGroup" => $bloodGroup,
    "form_no" => $formNo,
    "voterNo" => $voter_no,
];









