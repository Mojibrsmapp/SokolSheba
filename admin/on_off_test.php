<?php include_once("header.php");
// Check if the form has been submitted for updating individual statuses
    if (isset($_POST['toggle'])) {
        $id = intval($_POST['id']);
        $currentStatus = intval($_POST['current_status']);
        $newStatus = $currentStatus === 1 ? 0 : 1; // Toggle the status

        // Update the status in the database
        $query = "UPDATE prices SET work_status = $newStatus WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            // Status updated
        }
    }

?>
<style>
    tbody td { font-size: 15px!important; }
    .input-group.price_add { width: 200px!important; }
</style>
<main id="main-container">
    <div class="content">

        <!-- Insert Data Button -->
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
            নতুন কাজ যুক্ত করুন
        </button>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">নতুন কাজের তথ্য</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="insertForm">
                            <div class="form-group">
                                <label for="name">কাজের নাম</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="orderName">কাজের নাম ইংরেজিতে সংক্ষিপ্ত</label>
                                <input type="text" class="form-control" id="orderName" name="orderName" required>
                            </div>
                            <div class="form-group">
                                <label for="price">কাজের রেট</label>
                                <input type="number" class="form-control" id="price" name="price" required>
                            </div>
                            <div class="form-group">
                                <label for="typeOforder">কাজের ধরন</label>
                                <select class="form-control" id="typeOforder" name="typeOforder" required>
                                    <option value="text">অর্ডার ১ (যদি টেক্স ডেলিভারি হয়)</option>
                                    <option value="file">অর্ডার ২ (যদি ফাইল ডেলিভারি হয়)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="work_status">কাজ বন্ধ নাকি চালু রাখতে চান?</label>
                                <select class="form-control" id="work_status" name="work_status" required>
                                    <option value="1">অন</option>
                                    <option value="0">অফ</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="field_height">ফিল্ড উচ্চতা</label>
                                <input type="text" class="form-control" id="field_height" name="field_height">
                            </div>
                            <div class="form-group">
                                <label for="field_placeholder">ফিল্ড প্লেসহোল্ডার</label>
                                <input type="text" class="form-control" id="field_placeholder" name="field_placeholder">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP Logic -->
        <?php
        // Fetch all rows from the prices table
        $query = "SELECT * FROM prices";
        $result = mysqli_query($conn, $query);

        // Update specific row
        if (isset($_POST['edit'])) {
            $id = intval($_POST['id']);
            $name = $_POST['name'];
            $orderName = $_POST['orderName'];
            $price = $_POST['price'];
            $typeOforder = $_POST['typeOforder'];
            $work_status = intval($_POST['work_status']);
            $field_height = $_POST['field_height'];
            $field_placeholder = $_POST['field_placeholder'];

            $updateQuery = "UPDATE prices SET name='$name', orderName='$orderName', price='$price', typeOforder='$typeOforder', work_status='$work_status', field_height='$field_height', field_placeholder='$field_placeholder' WHERE id=$id";
            mysqli_query($conn, $updateQuery);
        }

        // Update all statuses
        if (isset($_POST['update_all'])) {
            $status = intval($_POST['status']);
            $query = "UPDATE prices SET work_status = $status";
            mysqli_query($conn, $query);
        }
        ?>

        <!-- Main Table -->
        <div class="card mb-3 p-3">
            <h4 class="py-2 fw-bold text-center text-primary"> কাজ চালু বন্ধ করুন </h4>
            <div id="mainTableDiv">
                <table border="1" class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>কাজের নাম</th>
                        <th>কাজের নাম ইংরেজিতে</th>
                        <th>ফিল্ড উচ্চতা</th>
                        <th>ফিল্ড প্লেসহোল্ডার</th>
                        <th>Status</th>
                        <th>ON/OFF</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    // Loop through rows and display them
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td>" . $row['orderName'] . "</td>";
                            echo "<td>" . $row['field_height'] . "</td>";
                            echo "<td>" . $row['field_placeholder'] . "</td>";
                            echo "<td>" . ($row['work_status'] == 1 ? '<span class="text-success">অন আছে</span>' : '<span class="text-danger">অফ আছে</span>') . "</td>";
                            ?>
                           <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $row['work_status']; ?>">
                                <button type="submit" name="toggle" class="px-3 py-2 btn <?php echo $row['work_status'] == 1 ? 'btn-success' : 'btn-danger'; ?>" 
                                        onclick="return confirm('<?php echo $row['work_status'] == 1 ? 'আপনি কি  এই সাভির্সটি বন্ধ করে চান?' : 'আপন কি  এই সাভির্সটি চালু করতে চান?'; ?>');">
                                    <i class="fa fa-2x fa-power-off"></i>
                                </button>
                            </form>
                        </td>
                            <td><?php echo $row['price']; ?></td>
                            <td>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $row['id']; ?>">
                                    Edit
                                </button>
                            </td>
                            <?php
                            echo "</tr>";
                            ?>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">তথ্য সম্পাদনা করুন</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <div class="form-group">
                                                    <label for="name">কাজের নাম</label>
                                                    <input type="text" class="form-control" name="name" value="<?php echo $row['name']; ?>" required>
                                                </div>
                                                <div class="form-group d-none">
                                                    <label for="orderName">কাজের নাম ইংরেজিতে</label>
                                                    <input type="text" class="form-control" name="orderName" value="<?php echo $row['orderName']; ?>" required>
                                                </div>
                                                <div class="form-group ">
                                                    <label for="price">কাজের রেট</label>
                                                    <input type="number" class="form-control" name="price" value="<?php echo $row['price']; ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="typeOforder">কাজের ধরন</label>
                                                    <select class="form-control" name="typeOforder" required>
                                                        <option value="text" <?php echo $row['typeOforder'] == 'text' ? 'selected' : ''; ?>>অর্ডার ১</option>
                                                        <option value="file" <?php echo $row['typeOforder'] == 'file' ? 'selected' : ''; ?>>অর্ডার ২</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="work_status">কাজের স্ট্যাটাস</label>
                                                    <select class="form-control" name="work_status" required>
                                                        <option value="1" <?php echo $row['work_status'] == 1 ? 'selected' : ''; ?>>অন</option>
                                                        <option value="0" <?php echo $row['work_status'] == 0 ? 'selected' : ''; ?>>অফ</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="field_height">ফিল্ড উচ্চতা</label>
                                                    <input type="text" class="form-control" name="field_height" value="<?php echo $row['field_height']; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="field_placeholder">ফিল্ড প্লেসহোল্ডার</label>
                                                    <input type="text" class="form-control" name="field_placeholder" value="<?php echo $row['field_placeholder']; ?>">
                                                </div>
                                                <button type="submit" name="edit" class="btn btn-primary">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</main>
<?php include_once("footer.php"); ?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#insertForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: 'pages/insert_price.php', // The PHP file to handle the request
            type: 'POST',
            data: $(this).serialize(), // Serialize form data
            success: function(response) {
                alert(response); // Show success message
                $('#insertModal').modal('hide'); // Hide the modal
                $('#insertForm')[0].reset(); // Reset the form
                location.reload(); // Optionally refresh the page to see the new data
            },
            error: function() {
                alert('An error occurred. Please try again.'); // Handle errors
            }
        });
    });

    // Set active class on the appropriate navigation link
    $('.nav-item').removeClass("active");
    $('.on_off').addClass("active");
});
</script>
