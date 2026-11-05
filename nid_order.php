<?php include("header.php"); ?>

<!-- FontAwesome Icons Support -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Premium Page Styling */
    .order-card-container {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
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
        border-color: #0d6efd !important;
        box-shadow: 0 0 15px rgba(13, 110, 253, 0.2) !important;
        background: #ffffff !important;
    }

    /* Operator Selection Card Layout */
    #operatorOptions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        background: #f8f9fa;
        border: 2px solid #e1e8ee;
        border-radius: 12px;
        font-weight: 600;
        color: #333;
        cursor: pointer;
        transition: all 0.25s ease;
        user-select: none;
    }

    .radio-item input[type="radio"]:checked + label {
        background: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }

    .radio-item input[type="radio"]:checked + label .badge {
        background-color: #ffffff !important;
        color: #0d6efd !important;
    }

    /* Custom Table Styling */
    .table-custom-container {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .table th {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        border: none !important;
        padding: 14px 12px !important;
    }

    .table td {
        vertical-align: middle !important;
        padding: 12px !important;
    }

    .btn-submit-order {
        background: linear-gradient(45deg, #0d6efd, #00d2ff);
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: 700;
        font-size: 16px;
        color: white;
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
    }

    .btn-submit-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(13, 110, 253, 0.4);
        color: white;
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
                    <h3 class="text-danger fw-bold">আজকের জন্য সার্ভিস বন্ধ রয়েছে</h3>
                    <p class="text-muted mb-0">পুনরায় সার্ভিস চালু হলে এখানে অপশনগুলো দেখতে পারবেন।</p>
                </div>

                <!-- Order Form -->
                <form id="submitForm" method="POST" class="forms-sample" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label class="form-label text-primary fw-bold fs-5">
                            <i class="id-card-icon fas fa-id-card me-2"></i>আইডি কার্ড অর্ডার করুন
                        </label>
                        <div id="operatorOptions" class="mb-3">
                            <!-- AJAX Dynamic Radio Buttons -->
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="phone" class="form-label fw-semibold text-secondary">
                            <i class="fas fa-info-circle me-1"></i> প্রয়োজনীয় তথ্য প্রদান করুন (NID & Name):
                        </label>
                        <textarea name="phone" class="form-control form-neu w-100" style="height: 90px; resize: none;" id="phone" required></textarea>
                    </div>

                    <button type="button" id="submitBtn" class="btn btn-submit-order w-100 mt-2">
                        <i class="fas fa-paper-plane me-2"></i> কাড ডাউনলোড করুন
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
                            <th>#</th>
                            <th>অর্ডারের ধরন</th>
                            <th>তথ্য</th>
                            <th>রিসিভ</th>
                            <th>ডাউনলোড</th>
                            <th>রেট</th>
                            <th>সময়</th>
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

    // Set Placeholder
    $('#phone').attr('placeholder', 'উদাহরণ:\nNID: 1335465786\nName: Abdul hamid');

    // Fetch operators function
    function fetchOperators() {
        $.ajax({
            url: 'order/list/fetch_data_for_nid.php',
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
                                    <span><i class="fas fa-id-badge me-2 opacity-75"></i>${operator.name}</span>
                                    <span class="badge bg-primary text-white ms-2">${operator.price} ৳</span>
                                </label>
                            </div>
                        `);
                    });

                    // Select the first option by default
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

    fetchOperators();
    setInterval(fetchOperators, 3000);

    // Place order click action
    $('#submitBtn').click(function () {
        const selectedOperator = $('input[name="operator"]:checked').val();
        const phone = $('#phone').val().trim();

        if (selectedOperator && phone) {
            Swal.fire({
                title: 'অর্ডার কনফার্ম করুন!',
                html: `
                    <div class="text-start p-2">
                        <p class="mb-1">অর্ডার টাইপ: <b class='text-primary'>${selectedOperator}</b></p>
                        <p class="mb-0">তথ্য: <br><code class="text-dark bg-light p-2 rounded d-block mt-1">${phone}</code></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i> কনফার্ম',
                cancelButtonText: 'ক্যানসেল',
                confirmButtonColor: '#0d6efd'
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
                            $("#submitBtn").prop("disabled", false).html("<i class='fas fa-paper-plane me-2'></i> রিকোয়েষ্ট পাঠান");
                            if (response.includes('Error')) {
                                Swal.fire('ব্যর্থ!', response, 'error');
                            } else {
                                $('#phone').val('');
                                Swal.fire('সফল!', response + "\n\nঅর্ডারটি সম্পূর্ণ হতে ৫ থেকে ২০ মিনিট সময় অনুগ্রহপূর্বক অপেক্ষা করুন।", 'success');
                                fetchOrders();
                            }
                        },
                        error: function () {
                            $("#submitBtn").prop("disabled", false).html("<i class='fas fa-paper-plane me-2'></i> রিকোয়েষ্ট পাঠান");
                            Swal.fire({
                                icon: 'error',
                                title: 'ব্যর্থ! 😞',
                                text: 'সার্ভারে সমস্যার কারণে এখন অর্ডার করতে পারবেন না। একটু পর চেষ্টা করুন।'
                            });
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'সতর্কীকরণ',
                text: 'অর্ডার ধরন সিলেক্ট এবং তথ্য প্রদান করে রিকোয়েষ্ট পাঠান বাটনে ক্লিক করুন।'
            });
        }
    });
});

// Active menu highlight
$(document).ready(function () {
    $('.nav-item').removeClass('active');$('.nid_order').addClass('active');
});

// Orders Table Auto-fetch
let lastUpdate = null;

function fetchOrders() {
    $.ajax({
        url: 'order/list/list_of_nid_order.php',
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

    if (!data || data.length === 0) {
        tableBody.append(`<tr><td colspan="7" class="text-muted py-4">কোনো অর্ডারের তথ্য পাওয়া যায়নি।</td></tr>`);
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
            : `<button class="btn btn-outline-secondary btn-sm rounded-circle p-2" onclick="orderNotRecevied()" title="রিসিভ হয়নি"><i class="fas fa-hourglass-half"></i></button>`;

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

    // Info Modal Trigger
    tableBody.find('.totto_btn').on('click', function() {
        const bioData = $(this).data('bio');
        Swal.fire({
            title: 'অর্ডারের তথ্য',
            html: '<div class="bg-light p-3 rounded text-start" style="white-space: pre-wrap; font-family: monospace;">' + bioData + '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-copy me-1"></i> কপি করুন',
            cancelButtonText: 'বন্ধ করুন',
            confirmButtonColor: '#0d6efd'
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

fetchOrders();
setInterval(fetchOrders, 3000);

// Status Alerts
function orderRecevied(){
    Swal.fire({
        title: "বার্তা!",
        text: "আপনার অর্ডারটি রিসিভ হয়েছে। কিছুক্ষনের মধ্যে ডেলিভারি পাবেন।",
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
        title: "বার্তা!",
        text: "আপনার অর্ডারটি পেন্ডিং রয়েছে। ডেলিভারি হলে এখানে সাকসেস বাটন দেখতে পারবেন। এবং বাটনে ক্লিক করলে ডাউনলোড হবে।",
        icon: "info"
    });
}  

function orderCancel(){
    Swal.fire({
        title: "বার্তা!",
        text: "অর্ডারটি ক্যানসেল করা হয়েছে। কোনো তথ্য পাওয়া যায়নি। অথবা কাজ বন্ধ হয়ে গিয়েছে আজকের মতো।",
        icon: "warning"
    });
}    
</script>