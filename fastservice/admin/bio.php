<?php include("header.php"); ?>
<style>
  .dataCopy {
    width: 180px !important;
  }
  textarea.form-control {
    line-height: 15px !important;
  }
  .orderCompleted {
    width: 300px !important;
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
      <!-- Filter dropdown for order type -->
      <div class="filter-section mb-3">
        <select id="orderTypeFilter" class="form-control">
          <option value="select">Select Order Type</option> <!-- "All" option -->
          <option selected value="all">All</option>
          <!-- Dynamically populated options will go here -->
        </select>
      </div>
      <div id="mainTableDiv">
        <table id="myTable" class="table table-striped table-hover ">
          <thead>
            <tr>
              <th id="custom_head">#</th>
              <th id="custom_head">তথ্য</th>
              <th id="custom_head">ডেলিভারি</th>
              <th id="custom_head">রিসিভ</th>
              <th id="custom_head">ক্যানসেল</th>
              <th id="custom_head">সময়</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include("footer.php"); ?>

<script>
  $(document).ready(function () {
    $('.nav-item').removeClass('active');
    $('.order_one').addClass('active');
  });

  let lastUpdate = null;

  // Fetch orders and order types
  function fetchOrdersAndTypes(orderTypeFilter = 'all') {
    $.ajax({
      url: 'order/list/list_of_bio_order.php', // Endpoint to fetch orders and order types
      method: 'GET',
      data: { order_type: orderTypeFilter },
      dataType: 'json',
      success: function(data) {
        const { orderTypes, orders } = data;
        
        // Populate the order types dropdown
        const orderTypeFilterElement = $('#orderTypeFilter');
        orderTypeFilterElement.empty();
        orderTypeFilterElement.append('<option value="">Select All</option><option selected value="all">All Order Types</option>'); // "All" option
        orderTypes.forEach(orderType => {
          orderTypeFilterElement.append(`<option value="${orderType}">${orderType}</option>`);
        });
        
        // Update the table with orders
        if (JSON.stringify(orders) !== lastUpdate) {
          lastUpdate = JSON.stringify(orders);
          updateTable(orders);
        }
      },
      error: function(error) {
        console.error('Error fetching data:', error);
      }
    });
  }

  // Format time difference for display
  function formatTimeDifference(orderTime) {
    const orderDate = new Date(orderTime);
    const now = new Date();
    const diffMs = now - orderDate; // Difference in milliseconds

    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHour = Math.floor(diffMin / 60);
    
    let result = '';

    if (diffHour > 0) {
      result += `${diffHour} hour${diffHour > 1 ? 's' : ''}`;
    }
    
    if (diffMin % 60 > 0) {
      if (result) result += ' ';
      result += `${diffMin % 60} min${(diffMin % 60) > 1 ? 's' : ''}`;
    }

    return result || 'just now'; // If no difference, return "just now"
  }

  // Update the table with fetched orders
  function updateTable(data) {
    const tableBody = $('#myTable tbody');
    tableBody.empty(); // Clear existing rows

    data.forEach((order, index) => {
      const received_btn = order.received === '1'
        ? `<button class="btn btn-success btn-rounded p-2" data-order-id="${order.id}" data-received="1"><i class="mdi mdi-ambulance"></i></button>`
        : `<button class="btn btn-danger btn-rounded p-2 recevied_id" data-order-id="${order.id}" data-received="0"><i class="mdi mdi-ambulance"></i></button>`;

      const timeDiff = formatTimeDifference(order.order_time);

      const row = `<tr>
        <td>${index + 1}</td>
        <td id="need_wrap"><span class="text-primary fw-bold">${order.order_type}: </span>${order.nid}</td>
        <td>
            <div class="input-group orderCompleted">
                <textarea class="form-control bio-totto" rows="4" placeholder="${order.nid}" data-order-id="${order.id}">${order.bio_totto || ''}</textarea>
                <div class="input-group-append">
                    <button class="btn btn-success mt-3" data-order-id="${order.id}"> ডেলিভারি </button>
                </div>
            </div>
        </td>
        <td>${received_btn}</td>
        <td><button class="btn btn-danger mt-3 cancel_order" data-cancel-id="${order.id}"> ক্যানসেল </button></td>
        <td>${timeDiff}</td>
      </tr>`;
      
      tableBody.append(row);
    });

    // Add click event listener for the status buttons
    tableBody.find('button').on('click', function() {
      if ($(this).hasClass('recevied_id')) {
        // Handle order received update
        const orderId = $(this).data('order-id');
        const newReceivedStatus = '1'; // Update to received

        $.ajax({
          url: 'order/insert/received_order.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ id: orderId, received: newReceivedStatus }),
          success: (response) => {
            if (response.status === 'success') {
              $(this).removeClass('btn-danger').addClass('btn-success');
              $(this).data('received', '1');
            } else {
              Swal.fire('Error!', response.message || 'Failed to update order status.', 'error');
            }
          },
          error: (err) => {
            Swal.fire('Error!', 'Could not update the order status.', 'error');
          }
        });
      } else if ($(this).hasClass('btn-success mt-3')) {
        // Confirm before delivering the order
        const orderId = $(this).data('order-id');
        const bioTotto = $(this).closest('.orderCompleted').find('.bio-totto').val();

        Swal.fire({
          title: 'Are you sure?',
          text: "Do you want to mark this order as delivered?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, deliver it!',
          cancelButtonText: 'No, cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            // Proceed with delivery update
            $.ajax({
              url: 'order/insert/bio_totto.php',
              method: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({ id: orderId, bio_totto: bioTotto }),
              success: (response) => {
                if (response.status === 'success') {
                  Swal.fire('Updated!', 'Bio Totto has been updated.', 'success');
                } else {
                  Swal.fire('Error!', response.message || 'Failed to update Bio Totto.', 'error');
                }
              },
              error: (err) => {
                Swal.fire('Error!', 'Could not update Bio Totto.', 'error');
              }
            });
          }
        });
      } else if ($(this).hasClass('cancel_order')) {
        // Confirm before canceling the order
        const cancelId = $(this).data('cancel-id');
        Swal.fire({
          title: 'Are you sure?',
          text: "Do you want to cancel this order?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, cancel it!',
          cancelButtonText: 'No, keep it'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'order/insert/cancel_order.php', // Your endpoint for canceling the order
              method: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({ id: cancelId, status: 'cancel' }),
              success: (response) => {
                if (response.status === 'success') {
                  Swal.fire('Canceled!', 'The order has been canceled.', 'success');
                } else {
                  Swal.fire('Error!', response.message || 'Failed to cancel the order.', 'error');
                }
              },
              error: (err) => {
                Swal.fire('Error!', 'Could not cancel the order.', 'error');
              }
            });
          }
        });
      }
    });
  }

  // Fetch orders initially
  fetchOrdersAndTypes();

  // Fetch orders when filter is changed
  $('#orderTypeFilter').on('change', function() {
    const orderTypeFilter = $(this).val();
    fetchOrdersAndTypes(orderTypeFilter);
  });
</script>