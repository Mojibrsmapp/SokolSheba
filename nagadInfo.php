<?php include("header.php"); ?>
<div class="container-fluid">
<div class="card pb-5">
  <div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
      <h1 class="h3 text-info text-center my-4"> নগদ ইনফরমেশন </h1>
      <form id="infoForm">
        <input type="tel" name="phoneNumber" class="input-field form-control" placeholder="Enter Number Here (e.g., 01XXXXXXXXX)" required>
        <div class="button-group">
          <?php if($balance > 20): ?>
            <button type="submit" class="button get-info btn btn-info w-100 mt-2">🔍 Get Info</button>
          <?php else: ?>
            <span class="button get-info btn btn-info w-100 mt-2" disabled>Recharge First</span>
          <?php endif; ?>
        </div>
      </form>

      <div id="infoContainer" class="info-container mt-4"></div>
      <div id="errorMessage" class="error-message text-danger mt-4"></div>
    </div>
  </div>
</div>
</div>
<?php include("footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#infoForm').on('submit', function(event) {
        event.preventDefault();
        
        // Disable the button and show loading text
        let submitButton = $('.get-info');
        submitButton.prop('disabled', true).text('🔍 Data Searching...');
        
        // Perform AJAX request
        $.ajax({
            url: 'fetch_info.php', // Path to the PHP file created above
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                // Clear previous messages and content
                $('#infoContainer').empty();
                $('#errorMessage').empty();
                
                if (response.status === 'success') {
                    let data = response.data;
                    $('#infoContainer').html(`
                        <div class="info-item"><span class="info-label me-2 fw-bold">👤 Name:</span><span>${data.name}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">🆔 User ID:</span><span>${data.userId}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">📍 Status:</span><span>${data.status}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">🔍 User Type:</span><span>${data.userType}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">💼 RB Base:</span><span>${data.rbBase}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">🔑 Auth Token Info:</span><span>${data.authTokenInfo}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">✅ Verification Status:</span><span>${data.verificationStatus}</span></div>
                        <div class="info-item"><span class="info-label me-2 fw-bold">⚙️ Execution Status:</span><span>${data.executionStatus}</span></div>
                    `);
                } else {
                    $('#errorMessage').text(response.message);
                }
                
                // Re-enable the button and reset the text
                submitButton.prop('disabled', false).text('🔍 Get Info');
            },
            error: function() {
                $('#errorMessage').text('❌ Error in fetching data. Please try again later.');
                
                // Re-enable the button and reset the text
                submitButton.prop('disabled', false).text('🔍 Get Info');
            }
        });
    });
});

</script>