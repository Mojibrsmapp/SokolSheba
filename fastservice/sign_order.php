<?php include("header.php"); ?>

<!-- Link FontAwesome if not included in header -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Premium UI Styles */
    .order-card-container {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .form-neu {
        border: 2px solid #e1e8ee !important;
        border-radius: 12px !important;
        padding: 12px 15px !important;
        font-size: 15px !important;
        transition: all 0.3s ease;
        background: #f8f9fa !important;
    }

    .form-neu:focus {
        border-color: #4b49ac !important;
        box-shadow: 0 0 15px rgba(75, 73, 172, 0.2) !important;
        background: #ffffff !important;
    }

    /* Radio Group Styling */
    #operatorOptions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 10px;
    }

    .radio-item {
        position: relative;
    }

    .radio-item input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .radio-item label {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        background: #f1f3f8;
        border: 2px solid #e1e8ee;
        border-radius: 10px;
        font-weight: 600;
        color: #444;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .radio-item input[type="radio"]:checked + label {
        background: #4b49ac;
        color: #ffffff;
        border-color: #4b49ac;
        box-shadow: 0 4px 12px rgba(75, 73, 172, 0.3);
    }

    /* Table Customization */
    .table-custom-container {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .table th {
        background-color: #4b49ac !important;
        color: #ffffff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none !important;
        padding: 14px 12px !important;
    }

    .table td {
        vertical-align: middle !important;
        padding: 12px !important;
    }

    .btn-submit-order {
        background: linear-gradient(45deg, #4b49ac, #7978e9);
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 6px 18px rgba(75, 73, 172, 0.3);
        transition: all 0.3s ease;
    }

    .btn-submit-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(75, 73, 172, 0.4);
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <?php
            include 'includes/marquee.php';
            $id = 2;
            $marquee = new Marquee($conn, $id);$marquee->display();
            ?>
        </div>
    </div>
</div>

<div class="card order-card-container pt-4 px-3 pb-4">
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <div class="my-2 p-2">
                
                <!-- Work Off Alert Box -->
                <div class="KajOff text-center border border-danger py-5 px-3 rounded-4 d-none bg-light">
                    <h2 class="offButton mb-3">
                        <i class="fa fa-power-off bg-danger text-white p-3 rounded-circle shadow-sm" aria-hidden="true"></i>
                    </h2>
                    <h3 class="text-danger fw-bold">আজকের জন্য কাজ বন্ধ রয়েছে</h3>
                    <p class="text-muted mb-0">পুনরায় কাজ চালু হলে এখানে অর্ডার অপশনগুলো দেখতে পারবেন।</p>
                </div>

                <!-- Order Form -->
                <form id="submitForm" method="POST" class="forms-sample" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label class="form-label text-primary fw-bold fs-5">
                            <i class="fas fa-file-signature me-2"></i>সাইন অফিশিয়াল সার্ভিস অর্ডার
                        </label>
                        <div id="operatorOptions" class="mb-3">
                            <!-- AJAX will populate this area with styled radio buttons -->
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="phone" class="form-label fw-semibold text-secondary">
                            <i class="fas fa-info-circle me-1"></i> প্রয়োজনীয় তথ্য দিন (NID & Name):
                        </label>
                        <textarea name="phone" class="form-control form-neu w-100" style="height: 90px; resize: none;" id="phone" required></textarea>
                    </div>

                    <button type="button" id="submitBtn" class="btn btn-primary btn-submit-order w-100 mt-2">
                        <i class="fas fa-paper-plane me-2"></i> সাইন ডাউনলোড  করুন
                    </button>
                </form>
            </div>
        </div>

        <!-- Orders List Table -->
        <div class="col-md-12 px-3 mt-4">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fas fa-list-alt me-2"></i>আপনার সাম্প্রতিক অর্ডারসমূহ</h5>
            <div id="mainTableDiv" class="table-responsive table-custom-container">
                <table id="myTable" class="table table-striped table-hover align-middle mb-0 text-center">
                    <thead>
                        <tr>
                            <th id="custom_head">#</th>
                            <th id="custom_head">অর্ডারের ধরন</th>
                            <th id="custom_head">তথ্য</th>
                            <th id="custom_head">রিসিভ স্ট্যাটাস</th>
                            <th id="custom_head">ডাউনলোড / স্ট্যাটাস</th>
                            <th id="custom_head">রেট</th>
                            <th id="custom_head">সময়</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic Data -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
$(document).ready(function () {
    let currentOperators = [];

    // Set textarea placeholder safely
    $('#phone').attr('placeholder', 'উদাহরণ:\nNID: 1335465786\nName: সাইফুদ্দিন');

    // Function to fetch operators and update UI
    function fetchOperators() {
        $.ajax({
            url: 'order/list/fetch_data_for_sign.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (!data || data.length === 0) {
                    $('#operatorOptions').empty();
                    $('#submitForm').hide();
                    $('.KajOff').removeClass("d-none");
                } else if (JSON.stringify(data) !== JSON.stringify(currentOperators)) {
                    currentOperators = data;
                    $('#operatorOptions').empty();

                    data.forEach(function (operator) {
                        $('#operatorOptions').append(`
                            <div class="radio-item">
                                <input type="radio" name="operator" id="${operator.orderName}" value="${operator.orderName}">
                                <label for="${operator.orderName}">
                                    <i class="fas fa-check-circle me-2 opacity-50"></i>${operator.name} 
                                    <span class="badge bg-warning text-dark ms-2">${operator.price} ৳</span>
                                </label>
                            </div>
                        `);
                    });

                    // Select the first radio button by default
                    $('#operatorOptions input[type="radio"]').first().prop('checked', true);

                    $('#submitForm').show();
                    $('.KajOff').addClass("d-none");
                }
            },
            error: function () {
                console.error("Failed to fetch operators");
            }
        });
    }

    // Initial fetch and reasonable polling interval (3 seconds for smooth UX)
    fetchOperators();
    setInterval(fetchOperators, 3000);

    // Place Order Button Click
    $('#submitBtn').click(function () {
        const selectedOperator = $('input[name="operator"]:checked').val();
        const phone = $('#phone').val().trim();

        if (selectedOperator && phone) {
            Swal.fire({
                title: 'অর্ডার নিশ্চিতকরণ!',
                html: `
                    <div class="text-start p-2">
                        <p class="mb-1">অর্ডার টাইপ: <b class='text-primary'>${selectedOperator}</b></p>
                        <p class="mb-0">প্রদত্ত তথ্য: <br><code class="text-dark bg-light p-2 rounded d-block mt-1">${phone}</code></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i> কনফার্ম করুন',
                cancelButtonText: 'ক্যান্সেল',
                confirmButtonColor: '#4b49ac'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#submitBtn').html("<i class='fas fa-spinner fa-spin me-2'></i>প্রসেসিং হচ্ছে...").prop('disabled', true);

                    $.ajax({
                        url: 'order/insert/place_file_order.php',
                        method: 'POST',
                        data: {
                            operator: selectedOperator,
                            phone: phone
                        },
                        success: function (response) {
                            $("#submitBtn").prop("disabled", false).html("<i class='fas fa-paper-plane me-2'></i> অর্ডার রিকোয়েস্ট পাঠান");
                            if (response.includes('Error')) {
                                Swal.fire('ব্যর্থ!', response, 'error');
                            } else {
                                $('#phone').val('');
                                Swal.fire('সফল!', response + "\n\nঅর্ডারটি সম্পন্ন হতে ৫ থেকে ২০ মিনিট সময় লাগতে পারে।", 'success');
                                fetchOrders(); // Instantly refresh table
                            }
                        },
                        error: function () {
                            $("#submitBtn").prop("disabled", false).html("<i class='fas fa-paper-plane me-2'></i> অর্ডার রিকোয়েস্ট পাঠান");
                            Swal.fire({
                                icon: 'error',
                                title: 'ব্যর্থ! 😞',
                                text: 'সার্ভারে সমস্যার কারণে অর্ডার প্রসেস করা যায়নি। কিছুক্ষন পর চেষ্টা করুন।'
                            });
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'সতর্কীকরণ',
                text: 'অনুগ্রহ করে অর্ডারের ধরন সিলেক্ট করুন এবং প্রয়োজনীয় তথ্য ইনপুট দিন।'
            });
        }
    });
});

// Navigation active state
$(document).ready(function () {
    $('.nav-item').removeClass('active');$('.sign_order').addClass('active');
});

// Orders Table Fetching & Updating
let lastUpdate = null;

function fetchOrders() {
    $.ajax({
        url: 'order/list/list_of_sign_order.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
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
    const diffMs = now - orderDate;

    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHour = Math.floor(diffMin / 60);

    if (diffHour > 0) {
        return `${diffHour} ঘণ্টা ${diffMin % 60} মিনিট আগে`;
    } else if (diffMin > 0) {
        return `${diffMin} মিনিট আগে`;
    }
    return 'এইমাত্র';
}

function updateTable(data) {
    const tableBody = $('#myTable tbody');
    tableBody.empty();

    if (data.length === 0) {
        tableBody.append(`<tr><td colspan="7" class="text-muted py-4">কোনো অর্ডারের রেকর্ড পাওয়া যায়নি।</td></tr>`);
        return;
    }

    data.forEach((order, index) => {
        const button = order.status === 'success'
            ? `<a class="btn btn-sm btn-success px-3 shadow-sm" download href="${order.file_one}"><i class="fas fa-download me-1"></i> ডাউনলোড</a>`
            : order.status === 'pending'
            ? `<button class="btn btn-sm btn-warning text-dark fw-bold px-3 shadow-sm" onclick="orderPending()"><i class="fas fa-clock me-1"></i> পেন্ডিং</button>`
            : `<button class="btn btn-sm btn-danger px-3 shadow-sm" onclick="orderCancel()"><i class="fas fa-times-circle me-1"></i> ক্যানসেল</button>`;

        const received_btn = order.received === '1'
            ? `<button class="btn btn-outline-success btn-sm rounded-circle p-2" onclick="orderRecevied()" title="রিসিভ হয়েছে"><i class="fas fa-check-double"></i></button>`
            : `<button class="btn btn-outline-secondary btn-sm rounded-circle p-2" onclick="orderNotRecevied()" title="এখনো রিসিভ হয়নি"><i class="fas fa-hourglass-half"></i></button>`;

        const timeDiff = formatTimeDifference(order.order_time);

        const row = `<tr>
            <td class="fw-bold">${index + 1}</td>
            <td><span class="badge bg-light text-dark border">${order.order_type}</span></td>
            <td><button class="btn btn-sm btn-info text-white totto_btn" data-bio="অর্ডার তথ্যঃ \n${order.nid}\nস্ট্যাটাসঃ ${order.status}"><i class="fas fa-eye me-1"></i> দেখুন</button></td>
            <td>${received_btn}</td>
            <td>${button}</td>
            <td class="fw-bold text-success">${order.price} ৳</td>
            <td class="small text-muted">${timeDiff}</td>
        </tr>`;
        tableBody.append(row);
    });

    // Event Delegation for info button click
    tableBody.find('.totto_btn').on('click', function() {
        const bioData = $(this).data('bio');
        Swal.fire({
            title: 'অর্ডারের তথ্য',
            html: '<div class="bg-light p-3 rounded text-start" style="white-space: pre-wrap; font-family: monospace;">' + bioData + '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-copy me-1"></i> কপি করুন',
            cancelButtonText: 'বন্ধ করুন',
            confirmButtonColor: '#4b49ac'
        }).then((result) => {
            if (result.isConfirmed) {
                navigator.clipboard.writeText(bioData).then(() => {
                    Swal.fire('কপি হয়েছে!', 'তথ্যটি আপনার ক্লিপবোর্ডে কপি করা হয়েছে।', 'success');
                }).catch(() => {
                    Swal.fire('ত্রুটি!', 'কপি করা সম্ভব হয়নি।', 'error');
                });
            }
        });
    });
}

// Order Fetching Polling (3 seconds interval for better performance)
fetchOrders();
setInterval(fetchOrders, 3000);

// Info Alerts
function orderRecevied(){
    Swal.fire({
        title: "রিসিভ হয়েছে!",
        text: "আপনার অর্ডারটি রিসিভ হয়েছে। খুব শীঘ্রই ফাইল ডাউনলোড লিংক দেখতে পাবেন।",
        icon: "info"
    });
}

function orderNotRecevied(){
   Swal.fire({
        title: "অপেক্ষা করুন!",
        text: "admin receved complite screching now",
        icon: "info"
    });
}

function orderPending(){
    Swal.fire({
        title: "অর্ডার পেন্ডিং!",
        text: "আপনার কাজ চলমান রয়েছে। ডেলিভারি সম্পন্ন হলে এখানে ডাউনলোড বাটন দেখতে পাবেন।",
        icon: "info"
    });
}  

function orderCancel(){
    Swal.fire({
        title: "অর্ডার ক্যানসেলড!",
        text: "দুঃখিত! সঠিক তথ্য না পাওয়ায় অথবা সার্ভার জনিত কারণে আপনার অর্ডারটি ক্যানসেল করা হয়েছে।",
        icon: "warning"
    });
}    
</script>