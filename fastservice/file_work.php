<?php include("header.php"); ?>

<!-- FontAwesome 6 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Global & Container Styles */
    .custom-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }

    /* Service Card Grid System */
    .service-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 6px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: #ffffff;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }

    .service-card:hover {
        transform: translateY(-3px);
        border-color: #0d6efd;
        box-shadow: 0 8px 18px rgba(13, 110, 253, 0.12);
    }

    /* Selection Active State */
    .btn-check:checked + .service-card {
        border-color: #0d6efd;
        background: linear-gradient(145deg, #eff6ff, #ffffff);
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.2);
    }

    .btn-check:checked + .service-card .service-icon-wrapper,
    .btn-check:checked + .service-card .service-img-icon {
        transform: scale(1.1);
    }

    .btn-check:checked + .service-card .service-title {
        color: #0d6efd !important;
        font-weight: 700;
    }

    /* Dynamic Icon Wrapper */
    .service-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px auto;
        font-size: 20px;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .service-img-icon {
        width: 44px;
        height: 44px;
        object-fit: contain;
        margin-bottom: 6px;
        border-radius: 10px;
        transition: transform 0.25s ease;
    }

    /* Full-Color Brand Gradients */
    .icon-bg-pdf { background: linear-gradient(135deg, #ff416c, #ff4b2b); }        /* PDF Red */
    .icon-bg-nid { background: linear-gradient(135deg, #1a237e, #283593); }        /* NID Navy */
    .icon-bg-server { background: linear-gradient(135deg, #00b4db, #0083b0); }     /* Server Copy Cyan */
    .icon-bg-sign { background: linear-gradient(135deg, #8e2de2, #4a00e0); }       /* Sign Copy Purple */
    .icon-bg-file { background: linear-gradient(135deg, #11998e, #38ef7d); }       /* File Emerald */
    .icon-bg-smart { background: linear-gradient(135deg, #3f51b5, #2196f3); }      /* Smart Card Blue */

    .icon-bg-bkash { background: linear-gradient(135deg, #e2136e, #a80e50); }      /* bKash Pink */
    .icon-bg-nagad { background: linear-gradient(135deg, #f7941d, #d87000); }      /* Nagad Orange */
    .icon-bg-rocket { background: linear-gradient(135deg, #8c3494, #5c1d63); }     /* Rocket Purple */
    .icon-bg-upay { background: linear-gradient(135deg, #ffb703, #fb8500); }       /* Upay Gold */

    .icon-bg-gp { background: linear-gradient(135deg, #00a896, #028090); }         /* GP Teal */
    .icon-bg-robi { background: linear-gradient(135deg, #e63946, #d62828); }       /* Robi Red */
    .icon-bg-bl { background: linear-gradient(135deg, #f77f00, #fcbf49); }         /* Banglalink Orange */
    .icon-bg-teletalk { background: linear-gradient(135deg, #2a9d8f, #38b000); }   /* Teletalk Green */
    .icon-bg-default { background: linear-gradient(135deg, #4b6cb7, #182848); }    /* Default Gradient */

    .service-title {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        display: block;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    /* Price Badge */
    .price-badge {
        background: linear-gradient(45deg, #0d6efd, #0b5ed7);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
    }

    /* Easy Input Area Styling */
    .input-box-wrapper {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        margin-top: 20px;
    }

    .custom-textarea {
        border: 2px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 15px;
        color: #1e293b;
        background-color: #ffffff;
        transition: all 0.2s ease;
        resize: vertical;
    }

    .custom-textarea:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
        outline: none;
    }

    .btn-helper {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        transition: all 0.2s ease;
    }

    .btn-helper:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Modern Gradient Action Button */
    .btn-gradient {
        background: linear-gradient(45deg, #0d6efd, #0b5ed7);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 13px;
        font-weight: 600;
        font-size: 16px;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.3);
        transition: all 0.25s ease;
    }

    .btn-gradient:hover {
        background: linear-gradient(45deg, #0b5ed7, #0a58ca);
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.4);
        color: #fff;
    }

    /* Advanced Table Styling */
    .custom-table-container {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .custom-table th {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 12px 10px;
    }

    /* Mobile Device Responsiveness */
    @media (max-width: 576px) {
        .custom-card {
            padding: 12px !important;
        }
        .service-card {
            padding: 10px 4px;
            border-radius: 12px;
        }
        .service-icon-wrapper, .service-img-icon {
            width: 38px;
            height: 38px;
            font-size: 17px;
            margin-bottom: 4px;
        }
        .service-title {
            font-size: 11px;
        }
        .price-badge {
            font-size: 9px;
            padding: 2px 6px;
        }
        .input-box-wrapper {
            padding: 12px;
        }
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-3 border-0 shadow-sm rounded-4">
            <?php
            include 'includes/marquee.php';
            $id = 2;
            $marquee = new Marquee($conn, $id);$marquee->display();
            ?>
        </div>
    </div>
</div>

<div class="card custom-card p-3 p-md-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="my-1">
                <!-- Work Off Notice -->
                <div class="KajOff text-center border border-danger border-2 py-5 px-3 rounded-4 d-none bg-light">
                    <h2 class="offButton mb-3">
                        <i class="fa-solid fa-power-off bg-danger text-white p-3 rounded-circle shadow" style="font-size: 28px;"></i>
                    </h2>
                    <div class="h3 fw-bold text-danger">কাজ বন্ধ আছে</div>
                    <div class="text-muted">কাজ চালু হলে অপশনগুলো এখানে দেখতে পাবেন।</div>
                </div>

                <!-- Main Form -->
                <form id="submitForm" method="POST" class="forms-sample" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label class="form-label text-primary fw-bold fs-5 mb-3 d-flex align-items-center">
                            <i class="fa-solid fa-layer-group text-primary me-2"></i> অপশন সিলেক্ট করুনঃ
                        </label>
                        
                        <!-- Dynamic 3-Column Responsive Grid -->
                        <div id="operatorOptions" class="row g-2 g-md-3">
                            <!-- AJAX will populate dynamic items here -->
                        </div>
                    </div>

                    <!-- Easy Input Area -->
                    <div class="input-box-wrapper" id="inputSection">
                        <div class="form-group mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label text-dark fw-semibold m-0">
                                    <i class="fa-solid fa-pen-to-square text-success me-1"></i> প্রয়োজনীয় তথ্য প্রদান করুন:
                                </label>
                                <!-- Quick Helper Paste / Clear Buttons -->
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-helper" id="pasteBtn">
                                        <i class="fa-solid fa-paste me-1"></i> পেস্ট
                                    </button>
                                    <button type="button" class="btn btn-helper text-danger" id="clearBtn">
                                        <i class="fa-solid fa-xmark me-1"></i> ক্লিয়ার
                                    </button>
                                </div>
                            </div>
                            <textarea name="phone" class="form-control custom-textarea w-100" id="phone" required rows="2" placeholder="এখানে তথ্য লিখুন..."></textarea>
                        </div>

                        <button type="button" id="submitBtn" class="btn btn-gradient w-100">
                            <i class="fa-solid fa-paper-plane me-2"></i> রিকোয়েস্ট পাঠান
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order List Table Section -->
        <div class="col-md-12 mt-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> সাম্প্রতিক অর্ডার সমূহ</h5>
            <div id="mainTableDiv" class="table-responsive custom-table-container shadow-sm">
                <table id="myTable" class="table custom-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-hashtag text-primary me-1"></i> #</th>
                            <th><i class="fa-solid fa-file-lines text-info me-1"></i> অর্ডারের ধরন</th>
                            <th><i class="fa-solid fa-id-card text-warning me-1"></i> তথ্য</th>
                            <th><i class="fa-solid fa-bell text-danger me-1"></i> রিসিভ</th>
                            <th><i class="fa-solid fa-download text-success me-1"></i> ডাউনলোড / স্ট্যাটাস</th>
                            <th><i class="fa-solid fa-tag text-success me-1"></i> রেট</th>
                            <th><i class="fa-solid fa-clock text-muted me-1"></i> সময়</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic Rows loaded via JS -->
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

    // Intelligent Brand Matching and Icon Generator
    function getServiceIconData(operator) {
        let imgUrl = operator.image || operator.logo || operator.img;
        if (!imgUrl && operator.icon && (operator.icon.includes('/') || operator.icon.includes('.'))) {
            imgUrl = operator.icon;
        }

        if (imgUrl) {
            return { type: 'img', url: imgUrl };
        }

        if (operator.icon && operator.icon.startsWith('fa')) {
            return { type: 'fa', icon: operator.icon, bg: 'icon-bg-default' };
        }

        const name = (operator.name || '').toLowerCase();

        // Files & Government Services
        if (name.includes('pdf')) {
            return { type: 'fa', icon: 'fa-solid fa-file-pdf', bg: 'icon-bg-pdf' };
        }
        if (name.includes('server') || name.includes('সার্ভার')) {
            return { type: 'fa', icon: 'fa-solid fa-server', bg: 'icon-bg-server' };
        }
        if (name.includes('sign') || name.includes('সাইন')) {
            return { type: 'fa', icon: 'fa-solid fa-file-signature', bg: 'icon-bg-sign' };
        }
        if (name.includes('nid') || name.includes('এনআইডি') || name.includes('ভোটার')) {
            return { type: 'fa', icon: 'fa-solid fa-id-card', bg: 'icon-bg-nid' };
        }
        if (name.includes('smart') || name.includes('স্মার্ট')) {
            return { type: 'fa', icon: 'fa-solid fa-address-card', bg: 'icon-bg-smart' };
        }

        // MFS Services
        if (name.includes('bkash') || name.includes('বিকাশ')) {
            return { type: 'fa', icon: 'fa-solid fa-wallet', bg: 'icon-bg-bkash' };
        }
        if (name.includes('nagad') || name.includes('নগদ')) {
            return { type: 'fa', icon: 'fa-solid fa-building-columns', bg: 'icon-bg-nagad' };
        }
        if (name.includes('rocket') || name.includes('রকেট')) {
            return { type: 'fa', icon: 'fa-solid fa-rocket', bg: 'icon-bg-rocket' };
        }
        if (name.includes('upay') || name.includes('উপায়') || name.includes('উপায়')) {
            return { type: 'fa', icon: 'fa-solid fa-money-bill-transfer', bg: 'icon-bg-upay' };
        }

        // Telecom Operators
        if (name.includes('gp') || name.includes('grameen') || name.includes('গ্রামীন') || name.includes('গ্রামীণ')) {
            return { type: 'fa', icon: 'fa-solid fa-signal', bg: 'icon-bg-gp' };
        }
        if (name.includes('robi') || name.includes('রবি')) {
            return { type: 'fa', icon: 'fa-solid fa-tower-cell', bg: 'icon-bg-robi' };
        }
        if (name.includes('bl') || name.includes('banglalink') || name.includes('বাংলালিংক')) {
            return { type: 'fa', icon: 'fa-solid fa-mobile-retro', bg: 'icon-bg-bl' };
        }
        if (name.includes('teletalk') || name.includes('টেলিটক')) {
            return { type: 'fa', icon: 'fa-solid fa-sim-card', bg: 'icon-bg-teletalk' };
        }

        return { type: 'fa', icon: 'fa-solid fa-file-lines', bg: 'icon-bg-file' };
    }

    // Fetch operator items via AJAX
    function fetchOperators() {
        $.ajax({
            url: 'order/list/fetch_data_for_file.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.length === 0) {
                    $('#operatorOptions').empty();
                    $('#submitForm').hide();
                    $('.KajOff').removeClass("d-none");
                } else if (JSON.stringify(data) !== JSON.stringify(currentOperators)) {
                    currentOperators = data;
                    $('#operatorOptions').empty();

                    data.forEach(function (operator, index) {
                        const iconData = getServiceIconData(operator);

                        let iconHtml = '';
                        if (iconData.type === 'img') {
                            iconHtml = `<img src="${iconData.url}" class="service-img-icon" alt="${operator.name}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'service-icon-wrapper icon-bg-default\'><i class=\'fa-solid fa-file-lines\'></i></div>';">`;
                        } else {
                            iconHtml = `
                                <div class="service-icon-wrapper ${iconData.bg}">
                                    <i class="${iconData.icon}"></i>
                                </div>
                            `;
                        }

                        // Guaranteed 3 items per line (col-4)
                        const cardHtml = `
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="operator" id="${operator.orderName}" value="${operator.orderName}" autocomplete="off" ${index === 0 ? 'checked' : ''}>
                                <label class="service-card w-100" for="${operator.orderName}">
                                    ${iconHtml}
                                    <span class="service-title">${operator.name}</span>
                                    <span class="price-badge">${operator.price} TK</span>
                                </label>
                            </div>
                        `;
                        $('#operatorOptions').append(cardHtml);
                    });

                    // Set textarea attributes for default checked option
                    if (data.length > 0) {
                        setTextareaAttributes(data[0]);
                    }

                    $('#submitForm').show();
                    $('.KajOff').addClass("d-none");
                }
            },
            error: function () {
                console.error("Failed to fetch operators");
            }
        });
    }

    // Auto Scroll & Dynamic Attributes on Selection
    $('#operatorOptions').on('change', 'input[name="operator"]', function () {
        const selectedOperator = $(this).val();
        const operatorData = currentOperators.find(op => op.orderName === selectedOperator);

        if (operatorData) {
            setTextareaAttributes(operatorData);
        }

        // Smooth scroll to input section for mobile ease
        $('html, body').animate({
            scrollTop: $("#inputSection").offset().top - 80
        }, 300);
        $('#phone').focus();
    });

    function setTextareaAttributes(operatorData) {
        if (operatorData && operatorData.field_placeholder) {
            $('textarea#phone').attr('placeholder', operatorData.field_placeholder);
        } else {
            $('textarea#phone').attr('placeholder', 'এখানে প্রয়োজনীয় তথ্য প্রদান করুন...');
        }

        let height = parseInt(operatorData ? operatorData.field_height : 0, 10);
        if (!isNaN(height) && height > 0) {
            $('textarea#phone').css('height', height + 'px');
        } else {
            $('textarea#phone').css('height', 'auto');
        }
    }

    // Easy Helper Actions: Paste Button
    $('#pasteBtn').click(async function () {
        try {
            const text = await navigator.clipboard.readText();
            if (text) {
                $('#phone').val(text).focus();
            }
        } catch (err) {
            Swal.fire('ক্লিপবোর্ড সমস্যা', 'অনুমতি না থাকায় টেক্সট পেস্ট করা যায়নি। হাতে টাইপ করুন।', 'info');
        }
    });

    // Easy Helper Actions: Clear Button
    $('#clearBtn').click(function () {
        $('#phone').val('').focus();
    });

    // Initial Fetch & Polling
    fetchOperators();
    setInterval(fetchOperators, 5000);

    // Nav active menu setter
    $('.nav-item').removeClass('active');$('.file_work').addClass('active');

    // Place Order Action
    $('#submitBtn').click(function () {
        const selectedOperator = $('input[name="operator"]:checked').val();
        const phone = $('#phone').val();

        if (selectedOperator && phone.trim() !== '') {
            Swal.fire({
                title: 'অর্ডার কনফার্ম করুন!',
                html: `<div class="p-2 text-start">
                        <p class="mb-1"><i class="fa-solid fa-circle-dot text-primary me-2"></i>অর্ডার টাইপ: <b class='text-primary'>${selectedOperator}</b></p>
                        <p class="mb-0"><i class="fa-solid fa-file-lines text-secondary me-2"></i>তথ্য: <strong>${phone}</strong></p>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-check me-1"></i> কনফার্ম',
                cancelButtonText: '<i class="fa-solid fa-xmark me-1"></i> ক্যানসেল',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#submitBtn').html('<i class="fa-solid fa-spinner fa-spin me-2"></i> অপেক্ষা করুন....').prop('disabled', true);
                    $.ajax({
                        url: 'order/insert/place_file_order.php',
                        method: 'POST',
                        data: {
                            operator: selectedOperator,
                            phone: phone
                        },
                        success: function (response) {
                            $("#submitBtn").prop("disabled", false).html('<i class="fa-solid fa-paper-plane me-2"></i> রিকোয়েস্ট পাঠান');
                            if (response.includes('Error')) {
                                Swal.fire('ব্যর্থ!', response, 'error');
                            } else {
                                $('#phone').val('');
                                Swal.fire('সফল!', response + " অর্ডারটি সম্পন্ন হতে ৫ থেকে ২০ মিনিট সময় অনুগ্রহপূর্বক অপেক্ষা করুন।", 'success');
                            }
                        },
                        error: function () {
                            $("#submitBtn").prop("disabled", false).html('<i class="fa-solid fa-paper-plane me-2"></i> রিকোয়েস্ট পাঠান');
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
                text: 'অর্ডারের ধরন সিলেক্ট করুন এবং প্রয়োজনীয় তথ্য লিখে রিকোয়েস্ট পাঠান।'
            });
        }
    });

    // Orders Table Handling Logic
    let lastUpdate = null;

    function fetchOrders() {
        $.ajax({
            url: 'order/list/list_of_file_order.php',
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

        let result = '';
        if (diffHour > 0) result += `${diffHour} hour${diffHour > 1 ? 's' : ''}`;
        if (diffMin % 60 > 0) {
            if (result) result += ' ';
            result += `${diffMin % 60} min${(diffMin % 60) > 1 ? 's' : ''}`;
        }
        return result || 'just now';
    }

    function updateTable(data) {
        const tableBody = $('#myTable tbody');
        tableBody.empty();

        data.forEach((order, index) => {
            const button = order.status === 'success'
                ? `<a class="btn btn-sm btn-success rounded-pill px-3" download href="${order.file_one}"><i class="fa-solid fa-download me-1"></i> ${order.status}</a>`
                : order.status === 'pending'
                ? `<button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="orderPending()"><i class="fa-solid fa-hourglass-half me-1"></i> ${order.status}</button>`
                : `<button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="orderCancel()"><i class="fa-solid fa-circle-xmark me-1"></i> ${order.status}</button>`;

            const received_btn = order.received === '1'
                ? `<span class="badge bg-success-subtle text-success p-2 rounded-circle" onclick="orderRecevied()" style="cursor:pointer;"><i class="fa-solid fa-check"></i></span>`
                : `<span class="badge bg-danger-subtle text-danger p-2 rounded-circle" onclick="orderNotRecevied()" style="cursor:pointer;"><i class="fa-solid fa-clock"></i></span>`;

            const timeDiff = formatTimeDifference(order.order_time);

            const row = `<tr>
                <td class="fw-bold">${index + 1}</td>
                <td><span class="badge bg-light text-dark border">${order.order_type}</span></td>
                <td><button class="btn btn-sm btn-outline-info rounded-pill px-3 totto_btn" data-bio="${'অর্ডার তথ্যঃ \n'+order.nid+ '\n স্ট্যাটাসঃ '+order.status }"><i class="fa-solid fa-circle-info me-1"></i> তথ্য</button></td>
                <td>${received_btn}</td>
                <td>${button}</td>
                <td><strong class="text-primary">${order.price}</strong> tk</td>
                <td class="text-muted small">${timeDiff}</td>
            </tr>`;
            tableBody.append(row);
        });

        tableBody.find('button').on('click', function() {
            if ($(this).hasClass('totto_btn')) {
                const bioData = $(this).data('bio');
                Swal.fire({
                    title: 'এই অর্ডারের তথ্য',
                    html: '<div class="p-3 bg-light rounded text-start border" style="white-space: pre-wrap; font-family: monospace;">' + bioData + '</div>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-copy me-1"></i> কপি করুন',
                    cancelButtonText: '<i class="fa-solid fa-xmark me-1"></i> ক্যানসেল',
                    confirmButtonColor: '#0d6efd'
                }).then((result) => {
                    if (result.isConfirmed) {
                        navigator.clipboard.writeText(bioData).then(() => {
                            Swal.fire('কপি হয়েছে!', 'তথ্য আপনার ক্লিপবোর্ডে কপি হয়েছে।', 'success');
                        }).catch(err => {
                            Swal.fire('ত্রুটি!', 'কপি করা সম্ভব হয়নি।', 'error');
                        });
                    }
                });
            }
        });
    }

    setInterval(fetchOrders, 3000);
});

// Helper Alert Functions
function orderRecevied(){
    Swal.fire({ title: "বার্তা!", text: "আপনার অর্ডারটি রিসিভ হয়েছে। কিছুক্ষণের মধ্যে ডেলিভারি পাবেন।", icon: "info" });
}

function orderNotRecevied(){
    Swal.fire({ title: "Opps!", text: "আপনার অর্ডারটি এখনো রিসিভ হয়নি। খুব শীঘ্রই রিসিভ হবে।", icon: "info" });
}  

function orderPending(){
    Swal.fire({ title: "বার্তা!", text: "আপনার অর্ডারটি পেন্ডিং রয়েছে। ডেলিভারি হলে এখানে সাকসেস বাটন দেখতে পাবেন এবং ডাইরেক্ট ডাউনলোড করতে পারবেন।", icon: "info" });
}  

function orderCancel(){
    Swal.fire({ title: "বার্তা!", text: "অর্ডারটি ক্যানসেল করা হয়েছে। কোনো তথ্য পাওয়া যায়নি অথবা কাজ সাময়িক বন্ধ রয়েছে।", icon: "warning" });
}    
</script>