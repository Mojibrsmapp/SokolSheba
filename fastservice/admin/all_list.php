<link rel="stylesheet" href="assets/vendors/datatables.net-bs4/datatable.css">
<?php
include_once("header.php");
 if($email != ""){ 
   header("location: dashboard.php");
   exit();
} 
?>
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


<div class="container my-5 overflow-auto">
<table id="myTable" class="table table-striped table-hover">
<thead>
            <tr>
                <th>#</th>
                <th>user</th>
             	 <th>Number</th>
                <th>Name</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $user = $_SESSION["user_token"];
        $sql = "SELECT * from log  ORDER BY `log`.`id` DESC";
        $result = $conn->query($sql);
        $cnt = 1;
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              $id =  $row["id"];
               $nid =  $row["nid"];
              $user =  $row["user"];
              $name =  $row["name"];
              $create_at =  $row["create_at"];
              
              
              
           ?>
        <tr>
            <td><?php echo $cnt;?></td>
            <td><?php echo $user;?></td>
            <td><?php echo $nid;?></td>
            <td><?php echo $name;?></td>
            <td><?php echo $create_at;?></td>
           
        </tr>
        <?php
         $cnt = $cnt + 1;
    }
            
        } 
      ?>
       </tbody>
    </table>
</div>



  

<?php include_once("footer.php");?>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#myTable').DataTable({
            "info": false,
            "ordering": false,
            "language": {
                "search": '<i class="fa-solid fa-magnifying-glass"></i>',
                'searchPlaceholder': "search here..."
            }
        });
    });
</script>