<?php include_once("header.php");


// Prepare the SQL query
$sql = "SELECT * FROM add_remove_balance WHERE user='$email' ORDER BY id DESC";


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
<div class="content">
          <!-- UserList Table Full -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="text-center">ব্যাল্যান্স যুক্ত/বাদ এডমিন কর্তৃক </h3>
            </div>
            <div class="block-content block-content-full overflow-x-auto">
            <table id="myTable" class="display table text-center table-striped text-nowrap table-bordered">
    <thead>
        <tr style="background:black!important;" >
            <th class="text-center">#</th>
            <th class="text-center">যুক্ত/রিমুভ</th>
            <th class="text-center">টাকার পরিমান</th>
            <th class="text-center">সময়</th>
        </tr>
    </thead>
    <tbody >
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $index => $user): ?>
            <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>

                    <td class="text-center"><?php 
$action = $user['action'];
if($action == "add"){
echo "<span style='color:green;'>যোগ করা হয়েছে</span>";
}else if($action == "remove"){
echo "<span style='color:red;'> কেটে নেয়া হয়েছে</span>";
}else{
echo $action;
}
?></td>
                    <td class="text-center"><?php echo $user['amount']; ?></td>
                    <td class="text-center"><?php echo $user['update_at']; ?></td>

                    
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
        $('.nav-item').removeClass("active");
        $('.add_remove_balance').addClass("active");


        // Initialize DataTable
        var table = $('#myTable').DataTable({
            "ordering": false
        });
});

</script>