<?php include_once("header.php");

// Prepare the SQL query
$sql = "SELECT id, token, reg_token, whatsapp_group FROM admin_user";

// Execute the query
$result = $conn->query($sql);

// Initialize an array to store the users
$users = array();

// Check if any records were found
if ($result->num_rows > 0) {
    // Fetch all rows as an associative array and store them in the $users array
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    echo "No users found.";
}

// Check if the tokenGen=success is present in the URL
if (isset($_GET['tokenGen']) && $_GET['tokenGen'] === 'success') {
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'New Token generated successfully!',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed || result.isDismissed) {
                if (window.history.replaceState) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('tokenGen');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }
        });
    </script>";
}
?>

<main id="main-container">
<div class="content card px-2">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="text-center mt-4">একাউন্ট তৈরির লিংক</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">
            <form action="pages/update_admin_user.php" method="POST">
                <table id="myTable" class="display table text-center table-striped text-nowrap table-bordered">
                    <thead>
                        <tr style="background:black!important;">
                            <th class="text-center">লিংক</th>
                            <th class="text-center">কপি বাটন</th>
                            <th class="text-center">লিংক পরির্বতন</th>
                            <th class="text-center">WhatsApp গ্রুপ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td>
                                <pre><?php echo "https://" . $_SERVER['HTTP_HOST'] . "/register_user.php?register_id=" . $user['reg_token']; ?></pre>
                            </td>
                            <td>
                                <input type="text" id="copyText<?php echo $index; ?>" value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/register_user.php?register_id=" . $user['reg_token']; ?>" hidden>
                                <button class="btn btn-info" onclick="copyToClipboard('copyText<?php echo $index; ?>')">কপি করুন</button>
                            </td>

                          
                 <td>
                                <input type="text" name="reg_token[<?php echo $user['id']; ?>]" value="<?php echo htmlspecialchars($user['reg_token']); ?>" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="whatsapp_group[<?php echo $user['id']; ?>]" value="<?php echo htmlspecialchars($user['whatsapp_group']); ?>" class="form-control">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include_once("footer.php"); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.js"></script>
<script>
    $(document).ready(function(){
        $('.nav-item').removeClass("active");
        $('.accountLink').addClass("active");

    });

    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.removeAttribute('hidden');
        copyText.select();
        document.execCommand("copy");
        copyText.setAttribute('hidden', true);

        Swal.fire({
            title: "Copied the link: ",
            text:  copyText.value,
            icon: "success"
        });
    }
</script>