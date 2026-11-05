<?php include_once("header.php");?>
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
<div class="card py-5 px-2">
  <div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
      <h3 class="text-center text-primary">টিন ডাউনলোড <?php echo  file_get_contents('api_key/auto_tin.txt'); ?> টাকা</h3>
      <form id="tinForm">
        <label for="tin">টিন নাম্বার বা আইডি নাম্বার(১০/১৭ ডিজিট):</label>
        <input type="text" id="tin" name="tin" class="form-control" >
        <button type="submit" class="btn btn-primary w-100 mt-2">সাবমিট</button>
      </form>
      <div id="response"></div>
    </div>
      <table id="data-table" class="text-center table">
        <thead>
          <tr>
            <th class="text-center">Name</th>
            <th class="text-center">NID</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody id="data-body">
          <!-- Data will be populated here -->
        </tbody>
      </table>
  </div>
</div>
<?php include_once("footer.php");?>
<script>
$(document).ready(function() {
    // Handle form submission
    $('#tinForm').on('submit', function(event) {
        event.preventDefault(); // Prevent form from reloading the page

        // Show the preloader
        $('#loddingPdf').css('display', 'block');

        // Get the TIN number entered by the user
        const tin = $('#tin').val();

        // Validate TIN field
        if (!tin) {
            Swal.fire({
                title: "warning",
                text: "সঠিক তথ্য দিন",
                icon: "warning"
            });

            // Hide the preloader after validation failure
            $('#loddingPdf').css('display', 'none');
            return;
        }

        // Make an AJAX request to the PHP file
        $.ajax({
            url: 'insert_tin_data.php', // The PHP handler
            type: 'POST',
            data: { tin: tin },
            success: function(response) {
                // Log response for debugging
                console.log(response); // Check if the response is a valid JSON string

                // Hide the preloader
                $('#loddingPdf').css('display', 'none');

                // Check if the response is in JSON format and contains a status
                if (response && typeof response === 'object' && response.status) {
                    // If status is success, show success alert
                    if (response.status === 'success') {
                        Swal.fire({
                            title: "সফল!",
                            text: "সফলভাবে ডাটা তৈরি হয়েছে। ফাইল লিষ্ট থেকে ডাটা ডাউনলোড করে নেন",
                            icon: "success"
                        });
                    } else {
                        // If status is not success, show error alert with message
                        Swal.fire({
                            title: "ব্যর্থ!",
                            text: response.message || "কোনো তথ্য পাওয়া যায়নি",
                            icon: "error"
                        });
                    }
                } else {
                    // If response is not valid or unexpected, show error alert
                    Swal.fire({
                        title: "Error",
                        text: "Unexpected response format.",
                        icon: "error"
                    });
                }
            },
            error: function(xhr, status, error) {
                // Hide the preloader on error
                $('#loddingPdf').css('display', 'none');

                // Display an error message if the AJAX request fails
                Swal.fire({
                    title: "ব্যর্থ!",
                    text: "রিকোয়েস্ট পাঠাতে ব্যর্থ! সাইটে সমস্যা এডমিন কে নক করুন, ঠিক করে দেয়ার জন্য। ",
                    icon: "error"
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
            url: 'fetch_tin_data.php', // Your PHP script for fetching data
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
                            item.name,
                            item.tin,
                            '<button class="download-button btn btn-primary me-1" onclick="downloadFile(\'' + item.tin + '\', \'' + item.id + '\')">Download</button>' 
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

function downloadFile(nid, id) {
    const downloadUrl = 'tin_download.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}
</script>
