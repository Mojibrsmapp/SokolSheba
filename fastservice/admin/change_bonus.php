<?php 

if (isset($_POST['submit'])) {
    include_once("includes/configuration.php");
    // Get the submitted values
    $one_hundred = $_POST['one_hundred'];
    $two_hundred = $_POST['two_hundred'];
    $three_hundred = $_POST['three_hundred'];
    $four_hundred = $_POST['four_hundred'];
    $five_hundred = $_POST['five_hundred'];
    $one_thousand = $_POST['one_thousand'];

    $id = 1; // Replace with the actual ID to update (e.g., from the session or form)

    // Prepare the SQL statement
    $sql = "UPDATE rech_bonus SET 
                one_hundred = ?, 
                two_hundred = ?, 
                three_hundred = ?, 
                four_hundred = ?, 
                five_hundred = ?, 
                one_thousand = ? 
            WHERE id = ?";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiii", $one_hundred, $two_hundred, $three_hundred, $four_hundred, $five_hundred, $one_thousand, $id);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: bonus_control.php?msg=success&text=successfully change the rechange bonus");
        exit();
    } else {
        header("Location: bonus_control.php?msg=error&text=something have wrong here!");
        exit();
    }

    // Close the statement
    $stmt->close();
}else{
    header("location: dashboard.php");
}
?>