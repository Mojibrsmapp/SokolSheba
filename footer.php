


<?php 

$id = 1;
$query = "SELECT whatsapp_group FROM admin_user WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
  $whatsapp_group = get_site_setting($conn, "whatsapp_group", "https://chat.whatsapp.com/KBeWP9QSjUQ1Gi7sfwErVy");
} else {
    $whatsapp_group = get_site_setting($conn, "whatsapp_group", "https://chat.whatsapp.com/KBeWP9QSjUQ1Gi7sfwErVy");
}



?>
<div class=" position-fixed bottom-0 end-0 p-2"><a href="<?php echo $whatsapp_group; ?>"><button class="btn btn-success btn-rounded btn-icon"><i class="fab fa-whatsapp"></i> Join Group</button></a></div>
</div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/progressbar.js/progressbar.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/template.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
