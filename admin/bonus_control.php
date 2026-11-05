<?php include_once("header.php"); 
if($email != ""){ 
  header("location: dashboard.php");
  exit();
} 
?>
<!-- api detail in form -->
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
    <?php
        include 'includes/marquee.php';
        $id =5;
        $marquee = new Marquee($conn, $id);
        $marquee->display();


        $sql = "SELECT * FROM rech_bonus";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $one_hundred = $row["one_hundred"];
                $two_hundred = $row["two_hundred"];
                $three_hundred = $row["three_hundred"];
                $four_hundred = $row["four_hundred"];
                $five_hundred = $row["five_hundred"];
                $one_thousand = $row["one_thousand"];
            }
        }
      ?>
    </div>
  </div>
<div class="col-md-3"></div>
<div class="col-md-6 grid-margin stretch-card">
      <div class="card ">
        <div class="card-body">
          <h4 class="card-title text-secondary text-center">Recharge Bonus</h4>
          <form class="forms-sample" method="post" action="pages/change_bonus.php">
             <!-- for one hundred -->
             <div class="form-group">
                    <label for="exampleInputEmail1">Bonus in 100tk</label>
                      <div class="input-group">
                      <div class="input-group-append w-40">
                          <span class="input-group-text "><?php echo $one_hundred; ?></span>
                        </div>
                        <input type="number" name="one_hundred" class="form-control">
                      </div>
                    </div>
             <!-- for two hundred -->
             <div class="form-group">
                    <label for="exampleInputEmail1">Bonus in 200tk</label>
                      <div class="input-group">
                      <div class="input-group-append w-40">
                          <span class="input-group-text "><?php echo $two_hundred; ?></span>
                        </div>
                        <input type="number" name="two_hundred" class="form-control">
                      </div>
                    </div>
             <!-- for three hundred -->
             <div class="form-group">
                    <label for="exampleInputEmail1">Bonus in 300tk</label>
                      <div class="input-group">
                      <div class="input-group-append w-40">
                          <span class="input-group-text "><?php echo $three_hundred; ?></span>
                        </div>
                        <input type="number" name="three_hundred" class="form-control">
                      </div>
                    </div>
             <!-- for four thousand -->
             <div class="form-group">
                    <label for="exampleInputEmail1">Bonus in 400tk</label>
                      <div class="input-group">
                      <div class="input-group-append w-40">
                          <span class="input-group-text "><?php echo $four_hundred; ?></span>
                        </div>
                        <input type="number" name="four_hundred" class="form-control">
                      </div>
                    </div>
             <!-- for one thousand -->
             <div class="form-group">
                    <label for="exampleInputEmail1">Bonus in 500tk</label>
                      <div class="input-group">
                      <div class="input-group-append w-40">
                          <span class="input-group-text "><?php echo $five_hundred; ?></span>
                        </div>
                        <input type="number" name="five_hundred" class="form-control">
                      </div>
                    </div>
             <!-- for one thousand -->
                    <div class="form-group">
                    <label for="exampleInputEmail1">Bonus in 1000tk</label>
                      <div class="input-group">
                      <div class="input-group-append w-40">
                          <span class="input-group-text "><?php echo $one_thousand; ?></span>
                        </div>
                        <input type="number" name="one_thousand" class="form-control">
                      </div>
                    </div>
            <button name="submit" type="submit" class="btn btn-secondary me-2 w-100 ">Change Bonus</button>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-md-5"></div>

    </div>
<!-- api details end -->

<?php include_once("footer.php"); ?>