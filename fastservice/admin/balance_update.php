<?php include_once("header.php");

$file_path = '../api_key/deduction_amount.txt';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_content = $_POST['file_content'];
// Suspicious code removed
    echo "File has been updated successfully!";
}
$content = file_exists($file_path) ? file_get_contents($file_path) : '';
?>

    <h2>Text File Editor</h2>
    <form method="POST">
        <textarea name="file_content" rows="10" cols="50"><?php echo htmlspecialchars($content); ?></textarea><br>
        <button type="submit">Save Changes</button>
    </form>


<?php include_once("footer.php");?>