<?php
include_once("header.php");
if (!isset($_SESSION["user_id"])) {
  header("Location: index.php");
  exit;
}
$user = base64_decode($_SESSION["user_id"]);
?>

<link rel="stylesheet" href="assets/vendors/datatables.net-bs4/datatable.css">

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <?php
      include 'includes/marquee.php';
      $id = 11;
      $marquee = new Marquee($conn, $id);
      $marquee->display();
      ?>
    </div>
  </div>
</div>

<div class="container my-2 overflow-auto">
  <div class="card px-3 py-5">
    <table id="myTable" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>NID</th>
          <th>Name (English)</th>
          <th>Download</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM smart WHERE email = ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        $cnt = 1;
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $nid = $row["nid"];
            $name = $row["name_english"];
        ?>
            <tr>
              <td><?= $cnt++; ?></td>
              <td><?= htmlspecialchars($nid); ?></td>
              <td><?= htmlspecialchars($name); ?></td>
              <td>
                <a class="btn btn-success" target="_blank" href="smart/smart_log.php?id=<?= base64_encode($id); ?>">Download</a>
              </td>
              <td>
                <a class="btn btn-danger" href="javascript:void(0);" onclick="confirmDelete('<?= base64_encode($id); ?>')">Delete</a>
              </td>
            </tr>
        <?php
          }
        } else {
          echo "<tr><td colspan='5' class='text-center text-danger'>No records found.</td></tr>";
        }
        $stmt->close();
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once("footer.php"); ?>

<!-- Scripts -->
<script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
<script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>

<script>
  $(document).ready(function() {
    $('.nav-item').removeClass('active');
    $('.file_list').addClass('active');

    $('#myTable').DataTable({
      "info": false,
      "ordering": false,
      "language": {
        "search": '<i class="fa fa-search"></i>',
        'searchPlaceholder': "Search..."
      }
    });
  });

  function confirmDelete(id) {
    Swal.fire({
      title: 'Are you sure?',
      text: "Do you really want to delete this record?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'pages/delete_smart.php?id=' + id;
      }
    });
  }
</script>
