<?php include_once("header.php");


// Prepare the SQL query
$sql = "SELECT * FROM transactions WHERE token = '$token'";

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
    $users = []; // No users found
}



?>

<main id="main-container">
<div class="content card px-2 ">
          <!-- UserList Table Full -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="text-center mt-4">আপনর কল রিচার্জ িস্টোরি</h3>
            </div>
            <div class="block-content block-content-full overflow-x-auto">
            <table id="myTable" class="display table text-center table-striped text-nowrap table-bordered">
    <thead>
        <tr style="background:black!important;" >
            <th class="text-center">#</th>
            <th class="text-center">পেমে্ট আইডি</th>
            <th class="text-center">্রানজিশন আডি</th>
            <th class="text-center">স্যাটাস</th>
            <th class="text-center">টাকার পরিমান</th>
            <th class="text-center">রর্চাজের সম</th>
        </tr>
    </thead>
    <tbody >
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $index => $user): ?>
            <tr>
                    <td><pre><?php echo $index + 1; ?></pre></td>

                    <td><?php echo $user['paymentID']; ?></td>
                    <td><?php echo $user['trxID']; ?></td>
                    <td><?php echo $user['transactionStatus']; ?></td>
                    <td><?php echo $user['amount']; ?></td>
                    <td><?php echo $user['paymentExecuteTime']; ?></td>

                    
 </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <?php endif; ?>
    </tbody>
</table>
            </div>
            </div>
            </div>
<?php include_once("footer.php");?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.css" />

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.js"></script>
<script>
    $(document).ready(function(){
        $('a.nav-main-link').removeClass("active");
        $('a.recharge_history').addClass("active");


        // Initialize DataTable
        var table = $('#myTable').DataTable({
            "ordering": false
        });
});

</script>