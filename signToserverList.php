<?php include_once("header.php");?>
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
     <?php
        include 'includes/marquee.php';
        $id =11;
        $marquee = new Marquee($conn, $id);
        $marquee->display();
      ?>
    </div>
  </div>

<link rel="stylesheet" href="assets/vendors/datatables.net-bs4/datatable.css">

<div class="container my-2 overflow-auto">
 <div class="card px-3 py-5"> 
<table id="myTable" class="   table table-striped table-hover">
<thead>
            <tr>
                <th>#</th>
                <th>Number</th>
                <th>Name</th>
                <th>Download</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $user = base64_decode($_SESSION["user_id"]);
        $sql = "SELECT * from sign_to_server where email = '$user' ORDER BY `sign_to_server`.`id` DESC";
        $result = $conn->query($sql);
        $cnt = 1;
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              $id = $row["id"];
              $nid =  $row["nid"];
              $name =  $row["name_english"];
      
              
           ?>
        <tr>
            <td><?php echo $cnt;?></td>
            <td><?php echo $nid;?></td>
            <td><?php echo $name;?></td>
            <td><a target='_blank' class="btn btn-success" href="signToServerV1.php?id=<?php echo base64_encode($id);?>">V1</a>
            <a target='_blank' class="btn btn-success" href="signToServerV0.php?id=<?php echo base64_encode($id);?>">V2</a>
            <a target='_blank' class="btn btn-success" href="signToServerV2.php?id=<?php echo base64_encode($id);?>">V3</a>
            </td>
            <td><a class="btn btn-danger" href="javascript:void(0);" onclick="confirmDelete('<?php echo base64_encode($id); ?>')">Delete</a></td>
           
        </tr>
        <?php
         $cnt = $cnt + 1;
    }
            
        } 
      ?>
       </tbody>
    </table>
</div>
</div>


  

<?php include_once("footer.php");?>

<script>
    $(document).ready(function () {
      
       $('.nav-item').removeClass('active');
       $('.signToserverList').addClass('active');
      
        $('#myTable').DataTable({
            "info": false,
            "ordering": false,
            "language": {
                "search": '<i class="fa-solid fa-magnifying-glass"></i>',
                'searchPlaceholder': "search here..."
            }
        });
    });

function confirmDelete(id) {
    // SweetAlert confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to delete this item?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // If confirmed, redirect to delete_card.php with the id
            window.location.href = 'pages/delete_server.php?id=' + id;
        }
    });
}
</script>