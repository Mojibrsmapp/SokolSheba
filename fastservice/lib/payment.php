<?php
$amount = isset($_GET['amount']) ? $_GET['amount'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Page</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background: #f5faff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .payment-container {
            max-width: 400px;
            width: 100%;
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgb(0 0 0 / 0.1);
        }

        .logo {
            display: block;
            margin: 0 auto 1rem auto;
            width: 120px;
            height: auto;
            object-fit: contain;
        }

        .business-name {
            text-align: center;
            font-weight: 700;
            color: #0057d0;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
        }

        .bank-options {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 1.8rem;
        }

        .bank-img-div {
            cursor: pointer;
            flex: 1;
            border: 1px solid transparent;
            border-radius: 12px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 3px 8px rgb(0 0 0 / 0.05);
            padding: 10px;
            text-align: center;
            background: #f9faff;
            user-select: none;
            border-color: #0057d0 !important;
        }

        .bank-img-div.selected {
            border-color: #0057d0 !important;
            box-shadow: 0 0 12px #0057d0;
            background: #e6f0ff;
        }

        .bank-img-div img {
            max-width: 80px;
            height: auto;
            margin-bottom: 8px;
            user-select: none;
        }

        #payInfo {
            font-weight: 600;
            font-size: 1.1rem;
            color: #003d99;
            margin-bottom: 0.7rem;
            text-align: center;
            min-height: 28px; /* জায়গা রাখবে */
        }

        #payButton {
            width: 100%;
            padding: 12px 0;
            font-size: 18px;
            font-weight: 600;
            color: white;
            background-color: #0057d0;
            border: none;
            border-radius: 10px;
            cursor: not-allowed;
            transition: background-color 0.3s ease;
        }

        #payButton:enabled {
            cursor: pointer;
            background-color: #003d99;
        }

        #cancelButton {
            margin-top: 12px;
            width: 100%;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 600;
            color: #0057d0;
            background-color: #e6f0ff;
            border: 2px solid #0057d0;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #cancelButton:hover {
            background-color: #0057d0;
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 400px) {
            .bank-options {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="payment-container shadow-sm">
        <!-- Logo -->
        <img src="https://goriberseba.top/assets/images/logo.png" alt="Business Logo" class="logo" />

        <!-- Business Name -->
        <div class="business-name">পেমেন্ট অপসন সিলেক্ট করুন </div>

        <!-- Payment Methods -->
        <div class="bank-options">
            <div class="bank-img-div" data-url="bkash.php" data-name="Bkash">
                <img src="img/bkash.svg" alt="bKash" />
            </div>

            <!--<div class="bank-img-div" data-url="nagad.php" data-name="Nagad">-->
            <!--    <img src="img/nagad.png" alt="Nagad" />-->
            <!--</div>-->
        </div>

        <!-- Payment info text -->
        <div id="payInfo"></div>

        <!-- Pay Button -->
        <button id="payButton" disabled>Pay ৳<?php echo htmlspecialchars($amount); ?> BDT</button>

        <!-- Cancel Button -->
        <button id="cancelButton" type="button">Cancel</button>
    </div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        $('#payButton').prop('disabled', true);
        $('#payInfo').text('');

        $('.bank-img-div').click(function () {
            $('.bank-img-div').removeClass('selected');
            $(this).addClass('selected');

            var payName = $(this).data('name');
            var amount = <?php echo json_encode($amount); ?>;

            $('#payInfo').text(payName + " Pay ৳" + amount + " BDT");
            $('#payButton').prop('disabled', false);
        });

        $('#payButton').click(function () {
            if ($(this).prop('disabled')) return;

            var payUrl = $('.bank-img-div.selected').data('url');
            if (!payUrl) {
                alert('Please select a payment method first.');
                return;
            }

            var amount = <?php echo json_encode($amount); ?>;
            window.location.href = payUrl + '?amount=' + encodeURIComponent(amount);
        });

        $('#cancelButton').click(function () {
            Swal.fire({
                icon: 'error',
                title: 'আপনি কি রিচার্জ পেইজে ফিরে যেতে চান?',
                text: 'যদি ফিরে যান তাহলে এই পেমেন্ট বাতিল হবে।',
                showCancelButton: true,
                confirmButtonText: 'হ্যাঁ',
                cancelButtonText: 'না',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger px-4 py-2 ms-2',
                    cancelButton: 'btn btn-secondary px-4 py-2 me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'https://nbolineseba.xyz/recharge.php';
                }
            });
        });
    });
</script>


</body>

</html>
