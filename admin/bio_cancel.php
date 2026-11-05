<?php include("header.php"); ?>
<style>
  .dataCopy{
  width: 180px!important;
  }
textarea.form-control {
    line-height: 15px!important;
    
}
  .orderCompleted{
  width: 300px!important
  }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <?php
            include 'includes/marquee.php';
            $id = 6;
            $marquee = new Marquee($conn, $id);
            $marquee->display();
            ?>
        </div>
    </div>
</div>


<div class="card pt-3 px-2 pb-4">
    <div class="row">
        <div class="col-md-12 px-3 mt-3">
          <div id="mainTableDiv">
        <table id="myTable" class="table table-striped table-hover ">
<thead>
            <tr>
                <th id="custom_head" style="background: #F95F53!important;">#</th>
                <th id="custom_head" style="background: #F95F53!important;">ইউজার</th>
                <th id="custom_head" style="background: #F95F53!important;"> অর্ডারের ধরন </th>
                <th id="custom_head" style="background: #F95F53!important;"> অর্ডারের তথ্য </th>
            </tr>
        </thead>
        <tbody>
       
       </tbody>
    </table>
      </div>
    </div>
   </div>
</div>

<?php include("footer.php"); ?>
<script>
  $(document).ready(function () {
      
       $('.nav-item').removeClass('active');
       $('.order_one_cancel').addClass('active');
      
     
    });

  
       let lastUpdate = null;

function fetchOrders() {
    $.ajax({
        url: 'order/list/list_order_cancel.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const currentUpdate = new Date().toISOString(); // Example to track updates
            if (JSON.stringify(data) !== lastUpdate) {
                lastUpdate = JSON.stringify(data);
                updateTable(data);
            }
        },
        error: function(error) {
            console.error('Error fetching data:', error);
        }
    });
}

function updateTable(data) {
    const tableBody = $('#myTable tbody');
    tableBody.empty(); // Clear existing rows

    data.forEach((order, index) => {
        const received_btn = `<button>Received</button>`; // Example button
        const timeDiff = calculateTimeDiff(order.timestamp); // Example function to calculate time diff

        const row = `<tr>
            <td>${index + 1}</td>
            <td>${order.email}</td>
            <td>${order.order_type}</td>
            <td><button class="btn btn-danger completed_totto" data-order-info="${"<b> ইউজারের নামঃ  </b>" + order.email + "\n <b>অর্ডারের ধরনঃ  </b>" + order.order_type +  "\n <b> অর্ডারের তথ্যঃ  </b>" + order.nid +  "\n <b> অর্ডারের সময়ঃ  </b>" + order.order_time+ "\n <b> ক্যানসেল সময়ঃ  </b>" + order.order_time}">তথ্য দেখুন</button></td>
            
        </tr>`;
        
        tableBody.append(row);
    });

    // Add click event listener to the buttons
    $('.completed_totto').off('click').on('click', function() {
        const orderInfo = $(this).data('order-info');
        
      Swal.fire({
    title: 'এই ক্যানসেল অর্ডারের তথ্য',
    html: orderInfo.replace(/\n/g, '<br>'), // Replace line breaks with <br>
    icon: 'warning',
    confirmButtonText: 'OK',
    customClass: {
        confirmButton: 'btn-danger', // Add a custom class for the button
        title: 'text-danger'
    }
});
    });
}

// Function to calculate time difference
function calculateTimeDiff(timestamp) {
    // Implement your logic here to calculate time difference
    // For example, return a formatted string based on the timestamp
    return new Date(timestamp).toLocaleString(); // Example
}

// Start fetching orders every 5 seconds
setInterval(fetchOrders, 5000);
fetchOrders(); // Initial call to fetch orders

</script>