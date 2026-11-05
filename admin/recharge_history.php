<?php include_once("header.php");

// Prepare the SQL query with JOIN
$sql = "SELECT t.*, u.email 
        FROM transactions t
        LEFT JOIN users u ON t.token = u.token
        ORDER BY t.id DESC"; // Order by 'id' column in descending order


// Execute the query
$result = $conn->query($sql);

// Initialize an array to store the data
$transactions = array();

// Check if any records were found
if ($result->num_rows > 0) {
    // Fetch all rows as an associative array and store them in the $transactions array
    while($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
} else {
    $transactions = []; // No transactions found
}
?>

<main id="main-container">
<div class="content card px-2">
    <!-- User List Table Full -->
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="text-center mt-4">ইউজারদের সকল রিচার্জ হিস্টোরি</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">
            <table id="myTable" class="display table text-center table-striped text-nowrap table-bordered">
                <thead>
                    <tr style="background:black!important;">
                        <th class="text-center">#</th>
                        <th class="text-center">ইমেইল</th>
                        <th class="text-center">পেমেন্ট আইডি</th>
                        <th class="text-center">ট্রানজিশন আইডি</th>
                        <th class="text-center">স্যাটাস</th>
                        <th class="text-center">টাকার পরিমান</th>
                        <th class="text-center">রির্চাজের সময়</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index => $transaction): ?>
                            <tr>
                                <td><pre><?php echo $index + 1; ?></pre></td>
                                <td><pre><?php echo $transaction['email'] ?: 'N/A'; ?></pre></td> <!-- Show email or 'N/A' if not found -->
                                <td><pre><?php echo $transaction['paymentID']; ?></pre></td>
                                <td><pre><?php echo $transaction['trxID']; ?></pre></td>
                                <td><pre><?php echo $transaction['transactionStatus']; ?></pre></td>
                                <td><pre><?php echo $transaction['amount']; ?></pre></td>
                                <td><pre><?php echo $transaction['paymentExecuteTime']; ?></pre></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No records found.</td>
                        </tr>
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
        $('.nav-main-link').removeClass("active");
        $('.recharge_history').addClass("active");

        // Initialize DataTable
        $('#myTable').DataTable({
            "ordering": false
        });
    });
</script>