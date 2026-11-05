<?php include('header.php'); ?>
<link rel="stylesheet" href="assets/css/server.css">
<div id="loddingPdf">
  <div id="progress_main">
    <svg>
      <text x="50%" y="50%" dy=".35em" text-anchor="middle">
        তথ্য খোজা হচ্ছে
      </text>
    </svg>
  </div>
</div>
<div class="">
  <div class="">
    <div class="card mb-3">
      <?php
      include 'includes/marquee.php';
      $id = 6;
      $marquee = new Marquee($conn, $id);
      $marquee->display();
      ?>
    </div>
    <div class="card mb-3 px-1 scrollable-content">
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-5">
          <div class="">
            <form id="server_form" method="post" class="py-5 text-center">
              <h2>সার্ভার কপি</h2>
              <div class="inputIcon">
                <label for="nidNumber">NID number (10 or 17 digits)</label> <i class="s_icon fa fa-id-card"></i>
                <input type="number" name="nid" id="nidNumber" required placeholder="Type Nid Number">
              </div>
              <div class="inputIcon">
                <label for="DateOfBirth">Date of Birth (YYYY-MM-DD)</label> <i class="s_icon fa fa-calendar"></i>
                <input type="text" name="dob" required id="DateOfBirth" placeholder="2000-12-21">
              </div>
              <input type="text" hidden value="" name="email">
              <?php
                if ($balance > 5) {
                  echo '<button type="submit" class="btn btn-primary w-100 mt-3" name="submit">তথ্য অনুসন্ধান</button>';
                } else {
                  echo '<button class="btn btn-primary w-100 mt-3" disabled name="submit">Recharge Please</button>';
                }
              ?>
            </form>
          </div>
        </div>
        <div class="col-md-3"></div>
      </div>

      <table id="data-table" class="text-center table">
        <thead>
          <tr>
            <th class="text-center">Name</th>
            <th class="text-center">Number</th>
            <th class="text-center">Action</th>
            <th class="text-center">Auto NID</th>
          </tr>
        </thead>
        <tbody id="data-body">
          <!-- Data will be populated here -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Loading Scripts -->
<?php include('footer.php'); ?>
<script>
$(document).ready(function() {
    $(".menu-item").removeClass("active");
    $(".two_pages").addClass("active");

    $('#server_form').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        // Gather the form data
        var nid = $('#nidNumber').val();
        var dob = $('#DateOfBirth').val();
        var email = $("input[name='email']").val();

        // Set the request URL for the selected API (using a single API version here)
        var requestUrl = 'insert_un_server.php';

        // Validate NID for 10 or 17 digits
        if (!/^\d{10}$|^\d{17}$/.test(nid)) {
            Swal.fire({
                title: 'Validation Error!',
                text: 'আপনাকে অবশ্যই ১০ ডিজিট অথবা ১৭ ডিজিটের আইডি নাম্বার দিতে হবে। ১৩ ডিজিটের নাম্বার কাস্টমার দিয়ে থাকলে তার র্পূবে জন্মসাল দিয়ে সাবমিট করুন।',
                icon: 'error',
                confirmButtonText: 'ঠিক আছে'
            });
            return; // Stop the form submission
        }

        // SweetAlert confirmation
        Swal.fire({
            title: 'Are you sure?',
            html: `আপনি কি তথ্য NID: ${'<b>'+nid} Dob: ${dob+'</b>'} সাবমিট করতে চান? আপনার একাউন্ট থেকে ১৫ টাকা কেটে নেয়া হবে।`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading indicator
                $("#loddingPdf").css("display", "block");

                // Make AJAX request
                $.ajax({
                    url: requestUrl,
                    type: 'POST',
                    data: {
                        nid: nid,
                        dob: dob,
                        email: email
                    },
                    dataType: 'json',
                    success: function(response) {
                        $("#loddingPdf").css("display", "none");
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            $('#server_form')[0].reset(); // Reset the form
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#loddingPdf").css("display", "none");
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while submitting the form.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Initialize DataTable
    var table = $('#data-table').DataTable({
        paging: true,
        searching: true,
        ordering: false,
        info: false
    });

    let cachedData = []; // Array to store the previously fetched data

    // Fetch data
    function fetchData() {
        $.ajax({
            url: 'fetch_data.php', // Your PHP script for fetching data
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Check if response data is different from cached data
                if (JSON.stringify(response) !== JSON.stringify(cachedData)) {
                    cachedData = response; // Update cached data
                    table.clear(); // Clear existing data
                    
                    // Populate the table with new data
                    $.each(response, function(index, item) {
                        table.row.add([
                            item.nameEn,
                            item.nationalId,
                            '<button class="download-button btn btn-primary me-1" onclick="downloadFile(\'' + item.nationalId + '\', \'' + item.id + '\')">V1</button>' +
                            '<button class="download-button btn btn-info me-1" onclick="downloadFileOne(\'' + item.nationalId + '\', \'' + item.id + '\')">V2</button>',
                            '<button class="download-button btn btn-info" onclick="downloadFileNID(\'' + item.nationalId + '\', \'' + item.id + '\')">Download NID</button>'
                        ]).draw(false);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    }

    // Poll the server every 5 seconds
    setInterval(fetchData, 5000);
    fetchData();
});

// Download functions
function downloadFile(nid, id) {
    const downloadUrl = 'server_download_v1.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}

function downloadFileOne(nid, id) {
    const downloadUrl = 'server_download_v2.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}

function downloadFileTwo(nid, id) {
    const downloadUrl = 'server_download_v3.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}

function downloadFileNID(nid, id) {
    const downloadUrl = 'auto_nid.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}
</script>