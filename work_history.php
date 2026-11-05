<?php include_once("header.php");?>
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
     <?php
        include 'includes/marquee.php';
        $id =2;
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
                <th>কাজের ধরন</th>
                <th>পূর্ববর্তী ব্যাল্যন্স</th>
                <th>কাজের রেইট</th>
                 <th>পরর্বতী ব্যাল্যন্স</th>
                <th>কাজের সময়</th>
            </tr>
        </thead>
        <tbody>
        <?php 
// Suspicious code removed
        $sql = "SELECT * from history_work where email = '$user' ORDER BY `history_work`.`id` DESC";
        $result = $conn->query($sql);
        $cnt = 1;
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              $id = $row["id"];
              $orderType =  $row["order_type"];
              $price =  $row["price"];
              $beforeBalance = $row["current_balance"];
              $afterBalance = $row["balance_after_cut"];
              $oderTime = $row["order_time"];
      
              
           ?>
        <tr>
            <td><?php echo $cnt;?></td>
            <td><?php echo $orderType;?></td>
            <td><?php echo intval($beforeBalance);?></td>
            <td><?php echo intval($price);?></td>
            <td><?php echo intval($afterBalance);?></td>
          	<td><?php echo $oderTime;?></td>
           
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
       $('.work_history').addClass('active');
      
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
            window.location.href = 'pages/delete_card.php?id=' + id;
        }
    });
}
</script>