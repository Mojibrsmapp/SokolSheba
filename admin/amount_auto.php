<?php
include_once("header.php");

// Define the file paths for the three text files
$filePaths = [
    'server_copy' => '../api_key/deduction_amount.txt',
    'server_copy_two' => '../api_key/v3_deduction_amount.txt',
    'auto_tin' => '../api_key/auto_tin.txt',
    'card_make' => '../api_key/card_make.txt',
    'recharge' => '../api_key/recharge.txt'
];

// Check if the form has been submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($filePaths as $inputName => $filePath) {
        // Get the new value for each file from the form input
        if (isset($_POST[$inputName])) {
            $newValue = $_POST[$inputName];

            // Validate if the input is numeric
            if (is_numeric($newValue)) {
                if (file_put_contents($filePath, $newValue)) {
                    echo "$inputName updated successfully. <br>";
                } else {
                    echo "Error updating the file: $inputName. <br>";
                }
            } else {
                echo "Please enter a valid numeric value for $inputName. <br>";
            }
        }
    }
}

// Read the current values from the text files
$currentValues = [];
foreach ($filePaths as $inputName => $filePath) {
    if (file_exists($filePath)) {
        $currentValues[$inputName] = file_get_contents($filePath);
    } else {
        $currentValues[$inputName] = 'File not found!';
    }
}
?>

<div class="card p-3">
    <h2> প্রাইস আপেট করুন </h2>
    <form action="" method="POST">
        <?php foreach ($filePaths as $inputName => $filePath): ?>
            <label for="<?php echo $inputName; ?>"><?php echo $inputName; ?>:</label>
            <input class="form-control" type="text" id="<?php echo $inputName; ?>" name="<?php echo $inputName; ?>" 
                   value="<?php echo htmlspecialchars($currentValues[$inputName]); ?>" required>
            <br><br>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Update All</button>
    </form>
</div>

<?php
include_once("footer.php");
?>