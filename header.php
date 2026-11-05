<?php 
session_start();

include_once("includes/configuration.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];
    
    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile, image FROM users WHERE token = ?");
    $stmt->bind_param("s", $token_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row["id"];
        $balance = $row["balance"];
        $email = $row["email"];
        $name = $row["name"];
        $token = $row["token"];
        $phone = $row["mobile"];
        $user_image = (!empty($row["image"])) ? $row["image"] : 'assets/images/faces/face8.jpg';
    } else {
        unset($_SESSION['user_token']);
        unset($_SESSION['user_id']);
        header("Location: index.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
$user_ip = getUserIP();

$whatsapp_group_link = get_site_setting($conn, 'whatsapp_group', '#');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sokol Sheba - ড্যাশবোর্ড</title>
    
    <link rel="stylesheet" href="assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="shortcut icon" href="/assets/logo.jpg" />
    <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/app_core.js"></script>

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

    html, body {
        background-color: var(--bg-page) !important;
        color: var(--paragraph-color);
        font-family: 'SolaimanLipi', 'Plus Jakarta Sans', Arial, sans-serif !important;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
        font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
    }

    .container-scroller { 
        background-color: var(--bg-page) !important; 
    }

    .navbar, .navbar.default-layout {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: #FFFFFF !important;
        border-bottom: 1.5px solid var(--border-color) !important;
        box-shadow: 0 2px 15px rgba(7, 139, 56, 0.05) !important;
        height: 65px !important;
        min-height: 65px !important;
        padding: 0 16px !important;
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        left: 0 !important;
        z-index: 1030 !important;
    }

    .navbar .navbar-brand-wrapper {
        display: flex !important;
        align-items: center !important;
        background: transparent !important;
        border: none !important;
        width: auto !important;
        height: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .navbar .navbar-brand-wrapper .navbar-brand {
        display: flex !important;
        align-items: center !important;
        gap: 8px;
        margin: 0 !important;
        padding: 0 !important;
        text-decoration: none;
    }

    .navbar .navbar-brand-wrapper .navbar-brand img {
        height: 40px !important;
        width: 40px !important;
        object-fit: cover !important;
        border-radius: 50% !important;
        border: 1.5px solid var(--brand-green) !important;
    }

    .navbar .navbar-brand-wrapper .brand-text-wrap {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .navbar .navbar-brand-wrapper .brand-name {
        font-size: 17px;
        font-weight: 800;
        color: var(--brand-green);
    }

    .navbar .navbar-brand-wrapper .brand-slogan {
        font-size: 10px;
        font-weight: 700;
        color: var(--brand-red);
    }

    .navbar .navbar-menu-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        background: transparent !important;
        border: none !important;
        height: 100% !important;
        width: auto !important;
        padding: 0 !important;
        margin: 0 !important;
        flex-grow: 1 !important;
    }

    .navbar .navbar-nav {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        margin: 0 !important;
        padding: 0 !important;
        gap: 12px !important;
    }

    .balance-badge {
        background: linear-gradient(135deg, #078B38 0%, #0A9C43 60%, #E51B23 100%) !important;
        color: #FFFFFF !important;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        box-shadow: 0 4px 14px rgba(7, 139, 56, 0.28);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        white-space: nowrap;
        border: none !important;
        cursor: pointer;
    }

    .page-body-wrapper {
        min-height: calc(100vh - 65px);
        display: flex;
        flex-direction: row;
        padding-left: 0 !important;
        padding-top: 65px !important;
    }

    .sidebar, .sidebar-offcanvas {
        background: #FFFFFF !important;
        border-right: 1.5px solid var(--border-color);
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.02);
        width: 235px !important;
        min-width: 235px !important;
        position: relative !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 11;
        transition: all 0.25s ease;
    }

    .main-panel {
        flex-grow: 1;
        width: calc(100% - 235px);
        min-height: calc(100vh - 65px);
        display: flex;
        flex-direction: column;
    }

    .content-wrapper {
        background-color: var(--bg-page) !important;
        padding: 16px 20px !important;
        margin-top: 0 !important;
        width: 100% !important;
    }

    .sidebar .nav {
        padding-top: 14px;
    }

    .sidebar .nav .nav-item .nav-link {
        color: var(--paragraph-color) !important;
        padding: 10px 14px !important;
        border-radius: 12px;
        margin: 3px 10px !important;
        display: flex;
        align-items: center;
        font-weight: 700;
        text-decoration: none;
    }

    .sidebar .nav .nav-item .nav-link:hover {
        background: rgba(7, 139, 56, 0.08) !important;
        color: var(--brand-green) !important;
    }

    .sidebar .nav .nav-item.active > .nav-link {
        background: linear-gradient(135deg, #078B38 0%, #065F2D 100%) !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(7, 139, 56, 0.25);
    }

    .sidebar .nav .nav-item.active > .nav-link i,
    .sidebar .nav .nav-item.active > .nav-link span {
        color: #FFFFFF !important;
    }

    .sidebar .nav .nav-item .menu-title {
        font-size: 13.5px !important;
        margin-left: 9px !important;
    }

    .sidebar .nav .nav-item .menu-icon {
        font-size: 15px !important;
        width: 20px;
        text-align: center;
    }

    @media (max-width: 991px) {
        .navbar, .navbar.default-layout {
            height: 58px !important;
            min-height: 58px !important;
            padding: 0 12px !important;
        }

        .page-body-wrapper {
            padding-top: 58px !important;
        }

        .main-panel {
            width: 100% !important;
        }

        .content-wrapper {
            padding: 12px 10px !important;
        }

        .sidebar-offcanvas {
            position: fixed !important;
            top: 58px !important;
            bottom: 0 !important;
            height: calc(100vh - 58px) !important;
            width: 250px !important;
            right: -260px !important;
            left: auto !important;
            z-index: 1040 !important;
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-offcanvas.active {
            right: 0 !important;
        }
    }

    </style>
</head>
<body class="with-welcome-text">
    <div class="container-scroller">
      
      <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <div class="me-2">
            <button class="navbar-toggler navbar-toggler align-self-center p-1 border-0 bg-transparent" type="button" data-bs-toggle="minimize">
              <i class="fa-solid fa-bars-staggered" style="color: #078B38; font-size: 18px;"></i>
            </button>
          </div>
          <div>
            <a class="navbar-brand brand-logo" href="dashboard.php">
              <img src="/assets/logo.jpg" alt="Sokol Sheba" />
              <div class="brand-text-wrap d-none d-sm-flex">
                <span class="brand-name">সকল সেবা</span>
                <span class="brand-slogan">সব কাজ, এক প্ল্যাটফর্মে</span>
              </div>
            </a>
          </div>
        </div>

        <div class="navbar-menu-wrapper d-flex align-items-center">
          <ul class="navbar-nav ms-auto flex-row align-items-center"> 
            <li class="nav-item">
              <button class="balance-badge" onclick="location.href='recharge.php'" type="button">
                <i class="fa-solid fa-wallet"></i>
                <span id="balance-display"><?php echo htmlspecialchars($balance); ?></span> ৳
              </button>
            </li>
            
            <li class="nav-item dropdown user-dropdown ms-2">
              <a class="nav-link p-0" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="rounded-circle" src="<?php echo htmlspecialchars($user_image); ?>" alt="Profile" style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #078B38;"> 
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="rounded-circle mb-2" src="<?php echo htmlspecialchars($user_image); ?>" alt="Profile" style="width: 44px; height: 44px; object-fit: cover; border: 2px solid #E51B23;">
                  <p class="mb-0 fw-bold" style="color: #124D2B; font-size: 14px;"><?php echo htmlspecialchars($name); ?></p>
                  <p class="mb-0 small" style="color: #53665A;"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user-gear me-2" style="color: #078B38;"></i> প্রোফাইল</a>
                <a class="dropdown-item" href="logout.php"><i class="fa-solid fa-power-off me-2" style="color: #E51B23;"></i> লগআউট</a>
              </div>
            </li>
          </ul>

          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center p-0 border-0 bg-transparent ms-2" type="button" data-toggle="offcanvas">
            <i class="fa-solid fa-bars" style="color: #078B38; font-size: 19px;"></i>
          </button>
        </div>
      </nav>

      <div class="container-fluid page-body-wrapper">
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-house-chimney menu-icon" style="color: #078B38;"></i><span class="menu-title">ড্যাশবোর্ড</span></a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'serverCopy.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="serverCopy.php"><i class="fa-solid fa-server menu-icon" style="color: #078B38;"></i><span class="menu-title">অটো সার্ভার কপি</span></a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'nid.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="nid.php"><i class="fa-solid fa-id-card menu-icon" style="color: #078B38;"></i><span class="menu-title">এনআইডি মেইক</span></a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'recharge.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="recharge.php"><i class="fa-solid fa-wallet menu-icon" style="color: #078B38;"></i><span class="menu-title">রিচার্জ করুন</span></a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'work_history.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="work_history.php"><i class="fa-solid fa-clock-rotate-left menu-icon" style="color: #078B38;"></i><span class="menu-title">কাজের হিস্টোরি</span></a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="profile.php"><i class="fa-solid fa-user-shield menu-icon" style="color: #078B38;"></i><span class="menu-title">প্রোফাইল</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket menu-icon" style="color: #E51B23;"></i><span class="menu-title" style="color: #E51B23;">লগআউট</span></a>
            </li>
          </ul>
        </nav>

        <div class="main-panel">
          <div class="content-wrapper pt-0" style="margin-top: 65px;">

<div class="fixed bottom-5 right-5 z-50">
    <a href="<?php echo htmlspecialchars($whatsapp_group_link); ?>" target="_blank" class="btn-join-whatsapp font-bold px-4 py-2.5 rounded-full flex items-center gap-2 text-xs transition transform hover:scale-105 text-decoration-none">
        <i class="fa-brands fa-whatsapp text-base"></i> Join Group
    </a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let previousBalance = null;

    function fetchBalance() {
        $.ajax({
            url: 'pages/fetch_balance.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.balance !== undefined) {
                    const currentBalance = response.balance;
                    if (currentBalance !== previousBalance) {
                        $('#balance-display').text(currentBalance);
                        previousBalance = currentBalance;
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        fetchBalance();
        setInterval(fetchBalance, 3000);

        $('[data-toggle="offcanvas"]').on("click", function () {
            $('.sidebar-offcanvas').toggleClass('active');
        });
    });
</script>

<?php if (isset($_GET['msg']) && isset($_GET['text'])): ?>
<script>
    Swal.fire({
        icon: '<?php echo ($_GET['msg'] === 'success') ? 'success' : 'error'; ?>',
        title: '<?php echo ($_GET['msg'] === 'success') ? 'সফল হয়েছে!' : 'ত্রুটি!'; ?>',
        text: '<?php echo htmlspecialchars(urldecode($_GET['text'])); ?>',
        confirmButtonText: 'ঠিক আছে',
        background: '#FFFFFF',
        color: '#124D2B',
        confirmButtonColor: '#078B38'
    }).then(() => {
        if (history.pushState) {
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({ path: newUrl }, '', newUrl);
        }
    });
</script>
<?php endif; ?>