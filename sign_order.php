<?php include("header.php"); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    :root {
        --brand-green: #078B38;
        --brand-red: #E51B23;
        --dark-green: #065F2D;
        --dark-red: #B91018;
        --bg-page: #F5FAF6;
        --card-bg: #FFFFFF;
        --heading-color: #124D2B;
        --paragraph-color: #53665A;
        --border-color: #DFEBE1;
        --footer-color: #073D20;
    }

    .main-panel .content-wrapper > .fixed.bottom-5.right-5,
    .content-wrapper > .fixed.bottom-5.right-5,
    .content-wrapper a[href*="whatsapp.com"],
    .content-wrapper .btn-join-whatsapp {
        display: none !important;
    }

    .page-wrapper-custom {
        padding-bottom: 85px;
    }

    @media (min-width: 992px) {
        .page-wrapper-custom {
            padding-bottom: 25px;
        }
    }

    .notice-marquee-card {
        background: #FFFFFF;
        border: 1.5px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(7, 139, 56, 0.04);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .order-action-card {
        background: #FFFFFF;
        border: 1.5px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(7, 139, 56, 0.05);
        overflow: hidden;
        margin-bottom: 22px;
    }

    .order-card-header {
        background: linear-gradient(135deg, #F5FAF6 0%, #FFFFFF 100%);
        border-bottom: 1.5px solid var(--border-color);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .order-card-header h4 {
        margin: 0;
        font-size: 16.5px;
        font-weight: 800;
        color: var(--heading-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .order-card-header .brand-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        background: #EAF6EE;
        color: var(--brand-green);
        border: 1px solid #C8E3CF;
    }

    .order-card-body {
        padding: 20px;
    }

    .field-label {
        font-size: 13px;
        font-weight: 800;
        color: var(--heading-color);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    #operatorOptions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }

    .radio-item {
        position: relative;
    }

    .radio-item input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
        margin: 0;
        z-index: 2;
    }

    .radio-item label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: #FFFFFF;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        font-weight: 700;
        font-size: 13.5px;
        color: var(--paragraph-color);
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
        margin: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .radio-item label .price-badge {
        font-size: 11.5px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 8px;
        background: #EAF6EE;
        color: var(--brand-green);
        border: 1px solid #C8E3CF;
        transition: all 0.2s ease;
    }

    .radio-item input[type="radio"]:checked + label {
        background: var(--brand-green);
        color: #FFFFFF;
        border-color: var(--brand-green);
        box-shadow: 0 4px 14px rgba(7, 139, 56, 0.28);
    }

    .radio-item input[type="radio"]:checked + label .price-badge {
        background: rgba(255, 255, 255, 0.2);
        color: #FFFFFF;
        border-color: rgba(255, 255, 255, 0.35);
    }

    .input-textarea-neu {
        width: 100%;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 14px !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
        font-weight: 600;
        background: #FAFCFA !important;
        color: #1E293B !important;
        outline: none !important;
        transition: all 0.2s ease;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .input-textarea-neu:focus {
        border-color: var(--brand-green) !important;
        box-shadow: 0 0 0 3px rgba(7, 139, 56, 0.12) !important;
        background: #FFFFFF !important;
    }

    .btn-submit-green {
        width: 100%;
        background: linear-gradient(135deg, var(--brand-green) 0%, var(--dark-green) 100%);
        border: none;
        border-radius: 14px;
        padding: 13px 20px;
        font-weight: 800;
        font-size: 15px;
        color: #FFFFFF;
        box-shadow: 0 6px 18px rgba(7, 139, 56, 0.28);
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-submit-green:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 8px 22px rgba(7, 139, 56, 0.38);
        color: #FFFFFF;
    }

    .history-table-card {
        background: #FFFFFF;
        border: 1.5px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(7, 139, 56, 0.05);
        overflow: hidden;
    }

    .history-card-header {
        background: linear-gradient(135deg, #F5FAF6 0%, #FFFFFF 100%);
        border-bottom: 1.5px solid var(--border-color);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .history-card-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--heading-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .custom-table-container {
        overflow-x: auto;
    }

    .table th {
        background-color: #073D20 !important;
        color: #FFFFFF !important;
        font-weight: 800;
        font-size: 12.5px;
        border: none !important;
        padding: 12px 10px !important;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle !important;
        padding: 12px 10px !important;
        font-size: 13px;
        border-color: #EEF4F0 !important;
        color: #1E293B;
    }

    .mobile-bottom-nav {
        display: none;
    }

    @media (max-width: 991px) {
        .mobile-bottom-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #FFFFFF;
            border-top: 1.5px solid var(--border-color);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
            z-index: 1050;
            align-items: center;
            justify-content: space-around;
            padding: 0 10px;
        }

        .m-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            color: #64748B;
            font-size: 10.5px;
            font-weight: 700;
            flex: 1;
            transition: color 0.2s ease;
            gap: 3px;
        }

        .m-nav-link i {
            font-size: 18px;
        }

        .m-nav-link.active, .m-nav-link:hover {
            color: var(--brand-green);
        }

        .m-nav-center {
            position: relative;
            top: -16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .m-nav-center-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-green) 0%, var(--dark-green) 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            font-size: 20px;
            box-shadow: 0 6px 18px rgba(7, 139, 56, 0.45);
            border: 3.5px solid #FFFFFF;
            transition: transform 0.2s ease;
        }

        .m-nav-center-btn:active {
            transform: scale(0.92);
        }
    }
</style>

<div class="page-wrapper-custom">
    
    <div class="notice-marquee-card">
        <?php
        include 'includes/marquee.php';
        $id = 2;
        $marquee = new Marquee($conn, $id);
        $marquee->display();
        ?>
    </div>

    <div class="order-action-card">
        <div class="order-card-header">
            <h4>
                <i class="fa-solid fa-file-signature text-success"></i>
                <span>সাইন অফিশিয়াল সার্ভিস অর্ডার</span>
            </h4>
            <span class="brand-pill">Sokol Sheba</span>
        </div>

        <div class="order-card-body">
            <div class="KajOff text-center border border-danger py-5 px-3 rounded-4 d-none bg-light">
                <h2 class="offButton mb-3">
                    <i class="fa fa-power-off text-white p-3 rounded-circle shadow-sm" style="background: var(--brand-red);" aria-hidden="true"></i>
                </h2>
                <h3 class="fw-bold" style="color: var(--brand-red);">আজকের জন্য কাজ বন্ধ রয়েছে</h3>
                <p class="text-muted mb-0">পুনরায় কাজ চালু হলে এখানে অর্ডার অপশনগুলো দেখতে পারবেন।</p>
            </div>

            <form id="submitForm" method="POST" class="forms-sample" enctype="multipart/form-data">
                <div class="form-group mb-3">
                    <label class="field-label">
                        <i class="fa-solid fa-layer-group text-success"></i>
                        <span>অর্ডারের ধরন নির্বাচন করুন:</span>
                    </label>
                    <div id="operatorOptions">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="phone" class="field-label">
                        <i class="fa-solid fa-circle-info text-success"></i>
                        <span>প্রয়োজনীয় তথ্য দিন (NID & Name):</span>
                    </label>
                    <textarea name="phone" class="input-textarea-neu" style="height: 95px; resize: none;" id="phone" required></textarea>
                </div>

                <button type="button" id="submitBtn" class="btn-submit-green">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>অর্ডার সম্পন্ন করুন</span>
                </button>
            </form>
        </div>
    </div>

    <div class="history-table-card">
        <div class="history-card-header">
            <h5>
                <i class="fa-solid fa-clock-rotate-left text-success"></i>
                <span>আপনার সাম্প্রতিক অর্ডারসমূহ</span>
            </h5>
            <span class="badge" style="background: #EAF6EE; color: var(--brand-green); font-size: 11px; font-weight: 800; border: 1px solid #C8E3CF;">লাইভ রেকর্ড</span>
        </div>

        <div id="mainTableDiv" class="custom-table-container">
            <table id="myTable" class="table table-hover align-middle mb-0 text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>অর্ডারের ধরন</th>
                        <th>তথ্য</th>
                        <th>রিসিভ স্ট্যাটাস</th>
                        <th>ডাউনলোড / স্ট্যাটাস</th>
                        <th>রেট</th>
                        <th>সময়</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="mobile-bottom-nav">
    <a href="dashboard.php" class="m-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span>হোম</span>
    </a>

    <a href="dashboard.php#all_services" class="m-nav-link">
        <i class="fa-solid fa-shapes"></i>
        <span>সেবা</span>
    </a>

    <div class="m-nav-center">
        <a href="recharge.php" class="m-nav-center-btn" title="রিচার্জ করুন">
            <i class="fa-solid fa-bolt" style="color: #ffdd44;"></i>
        </a>
    </div>

    <a href="work_history.php" class="m-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'work_history.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-wallet"></i>
        <span>হিস্টোরি</span>
    </a>

    <a href="profile.php" class="m-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-user"></i>
        <span>প্রোফাইল</span>
    </a>
</div>

<?php include("footer.php"); ?>

<script>
$(document).ready(function () {
    let currentOperators = [];

    $('#phone').attr('placeholder', 'উদাহরণ:\nNID: 1335465786\nName: সাইফুদ্দিন');

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
                                    <i class="fa-regular fa-circle-check"></i>
                                    <span>${operator.name}</span>
                                    <span class="price-badge">৳ ${operator.price}</span>
                                </label>
                            </div>
                        `);
                    });

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

    $('#submitBtn').click(function () {
        const selectedOperator = $('input[name="operator"]:checked').val();
        const phone = $('#phone').val().trim();

        if (selectedOperator && phone) {
            Swal.fire({
                title: 'অর্ডার নিশ্চিতকরণ!',
                html: `
                    <div class="text-start p-2" style="font-size: 13.5px;">
                        <p class="mb-1">অর্ডারের ধরন: <b style="color: #078B38;">${selectedOperator}</b></p>
                        <p class="mb-0">প্রদত্ত তথ্য: <br><code class="text-dark bg-light p-2 rounded d-block mt-1" style="font-size: 13px;">${phone}</code></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-check me-1"></i> কনফার্ম করুন',
                cancelButtonText: 'বাতিল',
                confirmButtonColor: '#078B38'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#submitBtn').html("<i class='fa-solid fa-spinner fa-spin me-1'></i> প্রসেসিং হচ্ছে...").prop('disabled', true);

                    $.ajax({
                        url: 'order/insert/place_file_order.php',
                        method: 'POST',
                        data: {
                            operator: selectedOperator,
                            phone: phone
                        },
                        success: function (response) {
                            $("#submitBtn").prop("disabled", false).html("<i class='fa-solid fa-paper-plane'></i><span>অর্ডার সম্পন্ন করুন</span>");
                            if (response.includes('Error')) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ব্যর্থ!',
                                    text: response,
                                    confirmButtonColor: '#E51B23'
                                });
                            } else {
                                $('#phone').val('');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'সফল!',
                                    text: response + "\n\nঅর্ডারটি সম্পন্ন হতে ৫ থেকে ২০ মিনিট সময় লাগতে পারে।",
                                    confirmButtonColor: '#078B38'
                                });
                                fetchOrders();
                            }
                        },
                        error: function () {
                            $("#submitBtn").prop("disabled", false).html("<i class='fa-solid fa-paper-plane'></i><span>অর্ডার সম্পন্ন করুন</span>");
                            Swal.fire({
                                icon: 'error',
                                title: 'সার্ভার ত্রুটি!',
                                text: 'সার্ভারে সমস্যার কারণে অর্ডার প্রসেস করা যায়নি। কিছুক্ষণ পর চেষ্টা করুন।',
                                confirmButtonColor: '#E51B23'
                            });
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'সতর্কীকরণ',
                text: 'অনুগ্রহ করে অর্ডারের ধরন সিলেক্ট করুন এবং প্রয়োজনীয় তথ্য ইনপুট দিন।',
                confirmButtonColor: '#078B38'
            });
        }
    });

    $('.nav-item').removeClass('active');
    $('.sign_order').addClass('active');
});

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
        tableBody.append(`<tr><td colspan="7" class="text-muted py-4">কোনো অর্ডারের রেকর্ড পাওয়া যায়নি।</td></tr>`);
        return;
    }

    data.forEach((order, index) => {
        const button = order.status === 'success'
            ? `<a class="btn btn-sm text-white px-3 shadow-sm rounded-pill" style="background: var(--brand-green); font-size: 11.5px; font-weight: 700;" download href="${order.file_one}"><i class="fa-solid fa-download me-1"></i> ডাউনলোড</a>`
            : order.status === 'pending'
            ? `<button class="btn btn-sm btn-warning text-dark fw-bold px-3 shadow-sm rounded-pill" style="font-size: 11.5px;" onclick="orderPending()"><i class="fa-solid fa-clock me-1"></i> পেন্ডিং</button>`
            : `<button class="btn btn-sm text-white px-3 shadow-sm rounded-pill" style="background: var(--brand-red); font-size: 11.5px; font-weight: 700;" onclick="orderCancel()"><i class="fa-solid fa-circle-xmark me-1"></i> বাতিল</button>`;

        const received_btn = order.received === '1'
            ? `<button class="btn btn-outline-success btn-sm rounded-circle p-2" onclick="orderRecevied()" title="রিসিভ হয়েছে"><i class="fa-solid fa-check-double"></i></button>`
            : `<button class="btn btn-outline-secondary btn-sm rounded-circle p-2" onclick="orderNotRecevied()" title="এখনো রিসিভ হয়নি"><i class="fa-solid fa-hourglass-half"></i></button>`;

        const timeDiff = formatTimeDifference(order.order_time);

        const row = `<tr>
            <td class="fw-bold">${index + 1}</td>
            <td><span class="badge border" style="background: #F5FAF6; color: var(--heading-color); border-color: var(--border-color) !important; font-size: 12px; font-weight: 700;">${order.order_type}</span></td>
            <td><button class="btn btn-sm text-white totto_btn rounded-pill px-3" style="background: #0D6334; font-size: 11.5px; font-weight: 700;" data-bio="অর্ডার তথ্যঃ \n${order.nid}\nস্ট্যাটাসঃ ${order.status}"><i class="fa-solid fa-eye me-1"></i> দেখুন</button></td>
            <td>${received_btn}</td>
            <td>${button}</td>
            <td class="fw-bold" style="color: var(--brand-green);">৳ ${order.price}</td>
            <td class="small text-muted">${timeDiff}</td>
        </tr>`;
        tableBody.append(row);
    });

    tableBody.find('.totto_btn').on('click', function() {
        const bioData = $(this).data('bio');
        Swal.fire({
            title: 'অর্ডারের তথ্য',
            html: '<div class="bg-light p-3 rounded text-start" style="white-space: pre-wrap; font-family: monospace; font-size: 13px;">' + bioData + '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-regular fa-copy me-1"></i> কপি করুন',
            cancelButtonText: 'বন্ধ করুন',
            confirmButtonColor: '#078B38'
        }).then((result) => {
            if (result.isConfirmed) {
                navigator.clipboard.writeText(bioData).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'কপি হয়েছে!',
                        text: 'তথ্যটি ক্লিপবোর্ডে কপি করা হয়েছে।',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }).catch(() => {
                    Swal.fire('ত্রুটি!', 'কপি করা সম্ভব হয়নি।', 'error');
                });
            }
        });
    });
}

fetchOrders();
setInterval(fetchOrders, 3000);

function orderRecevied(){
    Swal.fire({
        title: "রিসিভ হয়েছে!",
        text: "আপনার অর্ডারটি রিসিভ হয়েছে। খুব শীঘ্রই ফাইল ডাউনলোড লিংক দেখতে পাবেন।",
        icon: "info",
        confirmButtonColor: '#078B38'
    });
}

function orderNotRecevied(){
    Swal.fire({
        title: "অপেক্ষা করুন!",
        text: "অর্ডারটি খোঁজা হচ্ছে, এডমিন খুব শীঘ্রই গ্রহণ করবেন।",
        icon: "info",
        confirmButtonColor: '#078B38'
    });
}

function orderPending(){
    Swal.fire({
        title: "অর্ডার পেন্ডিং!",
        text: "আপনার কাজ চলমান রয়েছে। ডেলিভারি সম্পন্ন হলে এখানে ডাউনলোড বাটন দেখতে পাবেন।",
        icon: "info",
        confirmButtonColor: '#078B38'
    });
}  

function orderCancel(){
    Swal.fire({
        title: "অর্ডার ক্যানসেলড!",
        text: "দুঃখিত! সঠিক তথ্য না পাওয়ায় অথবা সার্ভার জনিত কারণে আপনার অর্ডারটি ক্যানসেল করা হয়েছে।",
        icon: "warning",
        confirmButtonColor: '#E51B23'
    });
}    
</script>