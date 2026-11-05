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
<div class="card mb-4">
    <div class="row px-3 pt-3">
        <div class="col-md-12">
            <label for="multiFileUpload" class="form-label text-primary h5">Upload Multiple PDFs for Order Completion</label>
            <input type="file" id="multiFileUpload" class="form-control" accept=".pdf" multiple>
            <button id="uploadMultipleFilesBtn" class="btn btn-success mt-3">Submit Files</button>
        </div>
    </div>
</div>


<div class="card pt-3 px-2 pb-4">
    <div class="row">
        <div class="col-md-12 px-3 mt-3">
          <div class="text-center h4 mb-3 text-primary"> পেন্ডিং অর্ডার সাইন গুলো  </div>
          <div id="mainTableDiv">
        <table id="myTable" class="table table-striped table-hover ">
<thead>
            <tr>
                <th id="custom_head">#</th>
                <th id="custom_head">তথ্য</th>
                <th id="custom_head">ডেলিভারি </th>
                <th id="custom_head">রিসিভ</th>
              	<th id="custom_head">ক্যানসেল</th>
            	<th id="custom_head">সময়</th>
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
   // radio select option design start
   $('#operatorOptions').on('change', 'input[name="operator"]', function() {
    $('#operatorOptions label').removeClass('text-info');
    $('#operatorOptions label').removeClass('fw-bold');
    $(this).next('label').addClass('text-info');
    $(this).next('label').addClass('fw-bold');
});
  $(document).ready(function () {
    $('#uploadMultipleFilesBtn').on('click', function () {
        const files = $('#multiFileUpload')[0].files;
        
        if (files.length === 0) {
            Swal.fire('Error!', 'Please select at least one file.', 'error');
            return;
        }

        const formData = new FormData();
        Array.from(files).forEach((file) => {
            if (file.type !== 'application/pdf') {
                Swal.fire('Invalid File!', `${file.name} is not a valid PDF.`, 'error');
                return;
            }
            formData.append('pdf[]', file);
        });

        $.ajax({
            url: 'admin_file_upload.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $("#multiFileUpload").val("");
                Swal.fire('Success!', 'Files processed successfully.', 'success');
                // Optionally refresh the orders table
                fetchOrders();
            },
            error: function (error) {
                Swal.fire('Error!', 'Failed to process files.', 'error');
            }
        });
    });
});
 
  
</script>

<script>
  $(document).ready(function () {
      
       $('.nav-item').removeClass('active');
       $('.sign_copy').addClass('active');
      
     
    });

  
        let lastUpdate = null;

        function fetchOrders() {
            $.ajax({
                url: 'order/list/sign_order_pending.php',
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
                    <label for="fileUpload-${order.id}"></label>
                     <input type="file" class="form-control bio-totto-file" accept=".pdf, .xls, .xlsx" data-order-id="${order.id}" id="fileUpload-${order.id}">
                    <div class="input-group-append">
                        <button class="btn btn-success mt-3" data-order-id="${order.id}" data-file="${order.nid}"> ডেলিভারি </button>
                    </div>
                </div>
            </td>
            <td>${received_btn}</td>
            <td><button class="btn btn-danger mt-3 cancel_order" data-cancel-id="${order.id}" data-file="${order.nid}"> ক্যানসেল </button></td>
            <td>${timeDiff}</td>
        </tr>`;
        
        tableBody.append(row);
    });

    // Add click event listener for the status buttons and the new user type button
    tableBody.find('button').on('click', function() {
        if ($(this).hasClass('totto_btn')) {
            // Copy functionality
            const bioData = $(this).data('bio');
            Swal.fire({
                title: 'এই অর্ডারের তথ্য',
                html: `<div style="white-space: pre-wrap;">${bioData}</div>`,
                showCancelButton: true,
                confirmButtonText: 'কপি করুন',
                cancelButtonText: 'শেয়ার',
                preConfirm: () => {
                    return navigator.clipboard.writeText(bioData).then(() => {
                        Swal.fire('Copied!', 'তথ্য কপি হয়েছে আপনার ক্লিপবোর্ডে। এন যেকোনো জাগায় পেষ্ট কতে পারবেন।', 'success');
                    }).catch(err => {
                        Swal.fire('Error!', 'Could not copy the data.', 'error');
                    });
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    const whatsappURL = `https://wa.me/?text=${encodeURIComponent(bioData)}`;
                    window.open(whatsappURL, '_blank');
                }
            });
        } else if ($(this).hasClass('recevied_id')) {
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
        }else if ($(this).hasClass('btn-success mt-3')) {
    const orderId = $(this).data('order-id');
    const orderName = $(this).data('file');
    const fileInput = $(this).closest('.orderCompleted').find('.bio-totto-file')[0];
    const file = fileInput.files[0];
    const allowedFileTypes = [
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    // Check if the file is valid
    if (file && !allowedFileTypes.includes(file.type)) {
        Swal.fire('Invalid File!', 'Please select a valid PDF or Excel file.', 'error');
    } else if (file) { // Proceed only if the file is valid
        Swal.fire({
            title: 'Are you sure?',
            html: `আপনি কি এই <b class='text-primary'>${orderName}</b> অর্ডারটির জন্য: <b class='text-success'> "${file.name}"</b> ? এই ফাইলটি ডেলিভারি করতে চান?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deliver it!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', orderId);
                formData.append('pdfFile', file);

                $.ajax({
                    url: 'order/insert/save_pdf.php',
                    method: 'POST',
                    data: formData,
                    processData: false, // Prevent jQuery from processing the data
                    contentType: false, // Prevent jQuery from setting the content type
                    success: (response) => {
                        if (response.status === 'success') {
                            Swal.fire('Updated!', response.message + " " + response.fileName, 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'Failed to update Bio Totto.', 'error');
                        }
                    },
                    error: () => {
                        Swal.fire('Error!', 'Could not update Bio Totto.', 'error');
                    }
                });
            }
        });
    } else {
        Swal.fire('Invalid File!', 'Please select a file.', 'error'); // If no file is selected
    }
}

else if ($(this).hasClass('cancel_order')) {
            // Confirm before canceling the order
            const cancelId = $(this).data('cancel-id');
            const cancel_file = $(this).data('file');
            Swal.fire({
                title: 'Are you sure?',
                 html: `আপনি কি এই <b class="text-danger">${cancel_file}</b> অর্ডারটি ক্যানসেল করতে চান??`,
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
        } else if ($(this).hasClass('show-info')) {
            const orderInfo = $(this).data('order-info');
            Swal.fire({
                title: 'অর্ডারের তথ্য',
                html: `<div style="white-space: pre-wrap;">${orderInfo}</div>`,
                showCancelButton: true,
                confirmButtonText: 'কপি করুন',
                cancelButtonText: 'শেয়ার',
                preConfirm: () => {
                    return navigator.clipboard.writeText(orderInfo).then(() => {
                        Swal.fire('Copied!', 'অর্ডারের সকল তথ্য কপি হয়েছে আপনার ক্লিপবোর্ডে। এখন যেকোনো জায়গায় পেষ্ট করতে পারবেন।', 'success');
                    }).catch(err => {
                        Swal.fire('Error!', 'Could not copy the data.', 'error');
                    });
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    const whatsappURL = `https://wa.me/?text=${encodeURIComponent(orderInfo)}`;
                    window.open(whatsappURL, '_blank');
                }
            });
        }
    });
}


        // Fetch orders every 5 seconds
        setInterval(fetchOrders, 500);
  



</script>