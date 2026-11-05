<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

include('includes/config.php');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $email = base64_decode($_SESSION['user_id']);

    // Get user
    $userStmt = $dbh->prepare("SELECT id, balance FROM users WHERE email = ?");
    $userStmt->execute([$email]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    $user_id = $user['id'];
    $balance = floatval($user['balance']);

    // Fetch price
    $priceStmt = $dbh->prepare("SELECT price FROM price_list WHERE un_server = 'nid_make'");
    $priceStmt->execute();
    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$priceData) {
        throw new Exception("Price not found");
    }

    $cost = floatval($priceData['price']);

    if ($balance < $cost) {
        throw new Exception("Insufficient balance");
    }

    function cleanInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Form data
    $birthRegistrationNumber = cleanInput($_POST['birthRegistrationNumber']);
    $officeAddressFirst = ucwords(strtolower(cleanInput($_POST['officeAddressFirst'])));
    $officeAddressSecond = ucwords(strtolower(cleanInput($_POST['officeAddressSecond'])));
    $gender = cleanInput($_POST['gender']);
    $dateOfRegistration = cleanInput($_POST['dateOfRegistration']);
    $dateOfIssuance = cleanInput($_POST['dateOfIssuance']);
    $barCode = cleanInput($_POST['barCode']);
    $qrLink = cleanInput($_POST['qrLink']);
    $dateOfBirth = cleanInput($_POST['dateOfBirth']);
    $dateOfBirthText = cleanInput($_POST['dateOfBirthText']);
    $nameBangla = cleanInput($_POST['nameBangla']);
    $nameEnglish = ucwords(strtolower(cleanInput($_POST['nameEnglish'])));
    $fatherNameBangla = cleanInput($_POST['fatherNameBangla']);
    $fatherNameEnglish = ucwords(strtolower(cleanInput($_POST['fatherNameEnglish'])));
    $fatherNationalityBangla = cleanInput($_POST['fatherNationalityBangla']);
    $fatherNationalityEnglish = ucwords(strtolower(cleanInput($_POST['fatherNationalityEnglish'])));
    $motherNameBangla = cleanInput($_POST['motherNameBangla']);
    $motherNameEnglish = ucwords(strtolower(cleanInput($_POST['motherNameEnglish'])));
    $motherNationalityBangla = cleanInput($_POST['motherNationalityBangla']);
    $motherNationalityEnglish = ucwords(strtolower(cleanInput($_POST['motherNationalityEnglish'])));
    $birthplaceBangla = cleanInput($_POST['birthplaceBangla']);
    $birthplaceEnglish = ucwords(strtolower(cleanInput($_POST['birthplaceEnglish'])));
    $permanentAddressBangla = cleanInput($_POST['permanentAddressBangla']);
    $permanentAddressEnglish = ucwords(strtolower(cleanInput($_POST['permanentAddressEnglish'])));

    $dbh->beginTransaction();

    $insertStmt = $dbh->prepare("
        INSERT INTO birth_log (
            user, email, birthRegistrationNumber, officeAddressFirst, officeAddressSecond,
            gender, dateOfRegistration, dateOfIssuance, barCode, qrLink,
            dateOfBirth, dateOfBirthText, nameBangla, nameEnglish,
            fatherNameBangla, fatherNameEnglish, fatherNationalityBangla, fatherNationalityEnglish,
            motherNameBangla, motherNameEnglish, motherNationalityBangla, motherNationalityEnglish,
            birthplaceBangla, birthplaceEnglish, permanentAddressBangla, permanentAddressEnglish
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $insertStmt->execute([
        $user_id, $email, $birthRegistrationNumber, $officeAddressFirst, $officeAddressSecond,
        $gender, $dateOfRegistration, $dateOfIssuance, $barCode, $qrLink,
        $dateOfBirth, $dateOfBirthText, $nameBangla, $nameEnglish,
        $fatherNameBangla, $fatherNameEnglish, $fatherNationalityBangla, $fatherNationalityEnglish,
        $motherNameBangla, $motherNameEnglish, $motherNationalityBangla, $motherNationalityEnglish,
        $birthplaceBangla, $birthplaceEnglish, $permanentAddressBangla, $permanentAddressEnglish
    ]);

    // Update balance
    $updateStmt = $dbh->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $updateStmt->execute([$cost, $user_id]);

    // History
    $historyStmt = $dbh->prepare("
        INSERT INTO history_work (
            email, order_type, price, current_balance, balance_after_cut, about_order, order_time
        ) VALUES (?, 'Birth Certificate', ?, ?, ?, 'birth_log', ?)
    ");
    $balanceAfterCut = $balance - $cost;
    $orderTime = date('Y-m-d H:i:s');
    $historyStmt->execute([$email, $cost, $balance, $balanceAfterCut, $orderTime]);

    $dbh->commit();

?>

<html lang="en">
<head> 
	<meta charset="UTF-8">
	<title>birth-<?php echo $birthRegistrationNumber; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="assets/birth-card-files/barcode.js"></script>
	<link rel="shortcut icon" href="logo.png">
	<meta property="og:image" content="icon.png">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdn.rawgit.com/sh4hids/bangla-web-fonts/solaimanlipi/stylesheet.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.0/css/bootstrap.min.css" integrity="sha512-NZ19NrT58XPK5sXqXnnvtf9T5kLXSzGQlVZL9taZWeTBtXoN3xIfTdxbkQh6QSoJfJgpojRqMfhyqBAAEeiXcA==" crossorigin="anonymous" referrerpolicy="no-referrer">
	<script>var birthRegistrationNumber = "<?php echo $birthRegistrationNumber; ?>"; document.addEventListener('contextmenu', function (e) {e.preventDefault();});document.addEventListener('keydown', function (e) {if(e.ctrlKey){e.preventDefault();}});</script>
	<link rel="stylesheet" href="assets/birth-card-files/card.css">
	<style>@page {margin: 0;size: A4;}.bmarg{margin-top: -1px;} .bangla{font-family: SolaimanLipi!important;font-size: 17px!important;}</style>
	<style>
        body .a4_page .main_wrapper .mr_body .middle .new_td p,
        body .a4_page .main_wrapper .mr_body .middle .new_td_2 p,
        body .a4_page .main_wrapper .mr_body .middle .td p,
        body .a4_page .main_wrapper .mr_body .top_part1 .left p,
        body .a4_page .main_wrapper .mr_body .top_part1 .right p,
        body .a4_page .main_wrapper .mr_footer .top .left h2,
        body .a4_page .main_wrapper .mr_footer .top .left p,
        body .a4_page .main_wrapper .mr_footer .top .right h2,
        body .a4_page .main_wrapper .mr_footer .top .right p{
            color: #2f2f2f !important
        }
        
        @font-face {
            font-family: 'DejaVu';
            src: url('fonts/DejaVu.ttf') format('truetype');
        }

	</style>
</head>
<body>
	<div class="a4_page" id="a4_page">
		<div class="main_wrapper">
			<img src="assets/birth-card-files/ri_1.png" class="main_logo" alt="">
			<span style="z-index: 10;">
			<div class="mr_header">
					<div class="left_part_hidden"></div>
					<div class="left_part">
						<img style="height:110px; width:110px;" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&amp;data=<?php echo $qrLink; ?>" alt="">
						<h2><?php echo $barCode; ?></h2>
					</div>
					<div class="middle_part">
						<img src="assets/birth-card-files/bd_logo.png" alt="" class="main_logo_r">
						<img src="assets/birth-card-files/bd_logo.png" alt="" style="opacity: 0;">
						<h2>Government of the People’s Republic of Bangladesh</h2>
						<p class="office">Office of the Registrar, Birth and Death Registration</p>
						<p class="address1" style="text-transform: capitalize;"><?php echo $officeAddressFirst; ?></p>
						<p class="address2" style="text-transform: capitalize;"><?php echo $officeAddressSecond; ?></p>
						<p class="rule_y">(Rule 9, 10)</p>
						<h1><span class="bn" style="font-family: SolaimanLipi!important;font-size: 23px!important;">জন্ম নিবন্ধন সনদ /</span> <span class="en">Birth Registration Certificate</span></h1>
					</div>
					<div class="right_part_hidden"></div>
					<div class="right_part">
<canvas style="height: 26px; width:220px;" id="barcode" width="310" height="120"></canvas>
					</div>
				</div>
				<div class="mr_body">
					<div class="top_part1">
						<div class="left">
							<p>Date of Registration</p>
							<p><?php echo $dateOfRegistration; ?></p>
						</div>
						<div class="middle" style="font-family: 'Times New Roman', Times, serif;">
    <h2>Birth Registration Number</h2>
    <h1 style="font-family: 'DejaVu', sans-serif; font-weight: 600;font-size: 20px;"><?php echo $birthRegistrationNumber; ?></h1>
</div>

						
						
						
						<div class="right">
							<p>Date of Issuance</p>
							<p><?php echo $dateOfIssuance; ?></p>
						</div>
					</div>
					<div class="middle">
						<div style="margin-top: 2px;margin-bottom: 5px;" class="new_td_2">
							<div class="left">
								<div class="part1">
									<p class="bn">Date of Birth<span style="margin-left: 42px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span class="bn"><?php echo $dateOfBirth; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p><span style="margin-left: 95px;" class="clone">Sex :</span></p>
								</div>
								<div class="part2">
									<p><span><?php echo $gender; ?></span></p>
								</div>
							</div>
						</div>
						<div style="margin-top: 5px;margin-bottom: 24px !important;" class="td">
							<div class="left">
								<div style="width: 130px;" class="part1">
									<p>In Word<span>:</span></p>
								</div>
								<div class="part2" style="width: 400px;">

									<p><span style="margin-left:5px"><?php echo $dateOfBirthText; ?></span></p>
								</div>
							</div>
						</div>
						<div style="margin-top: 7px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">নাম<span style="margin-left: 103px;" class="clone">:</span></p>
								</div>
								<div class="part2" id="name_data_bn">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $nameBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="font-weight:500">Name<span style="margin-left: 95px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $nameEnglish; ?></span></p>
								</div>
							</div>
						</div>
						<div id="mother_content" style="margin-top: 17px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">মাতা<span style="margin-left: 98px;" class="clone">:</span></p>
								</div>
								<div class="part2" id="motherName_data_bn">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $motherNameBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="font-weight:500">Mother<span style="margin-left: 87px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $motherNameEnglish; ?></span></p>
								</div>
							</div>
						</div>
						<div id="motherNanality_content" style="margin-top: 17px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">মাতার জাতীয়তা<span style="margin-left: 26px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $motherNationalityBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="font-weight:500">Nationality<span style="margin-left: 64px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $motherNationalityEnglish; ?></span></p>
								</div>
							</div>
						</div>
						<div style="margin-top: 16px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">পিতা<span style="margin-left: 96px;" class="clone">:</span></p>
								</div>
								<div class="part2" id="fatherName_data_bn">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $fatherNameBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="font-weight:500">Father<span style="margin-left: 91px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $fatherNameEnglish; ?></span></p>
								</div>
							</div>
						</div>
						<div id="fatherNanality_content" style="margin-top: 17px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">পিতার জাতীয়তা<span style="margin-left: 26px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $fatherNationalityBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="font-weight:500">Nationality<span style="margin-left: 65px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $fatherNationalityEnglish; ?></span></p>
								</div>
							</div>
						</div>
						<div style="margin-top: 17px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">জন্মস্থান<span style="margin-left: 78px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $birthplaceBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="width: 153px; font-weight:500">Place of Birth<span style="margin-left: 46px;margin-right: 0;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $birthplaceEnglish; ?></span></p>
								</div>
							</div>
						</div>
						<div style="margin-top: 30px;" class="new_td">
							<div class="left">
								<div class="part1">
									<p class="bn" style="width: 146px;font-family: SolaimanLipi!important;font-size: 17px!important;margin-top: -2.5px;">স্থায়ী ঠিকানা<span style="margin-left:53px;margin-right: 0;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p style="margin-top: -2.5px;"><span class="bn" style="font-family: SolaimanLipi!important;font-size: 17px !important;"><?php echo $permanentAddressBangla; ?></span></p>
								</div>
							</div>
							<div class="right">
								<div class="part1">
									<p style="display:flex; width:154px; font-weight:500">Permanent<br>Address<span style="margin-left: 64px;" class="clone">:</span></p>
								</div>
								<div class="part2">
									<p><span style="font-weight:500;text-transform: capitalize;"><?php echo $permanentAddressEnglish; ?></span></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</span>
			<div class="mr_footer">
				<div class="top" style="margin-bottom: 57.5px;">
					<div class="left">
						<h2 style="width:10rem; margin-top: 0px;">Seal &amp; Signature</h2>
						<p style="margin-top: 0px;">Assistant to Registrar</p>
						<p style="margin-top: 0px;">(Preparation, Verification)</p>
					</div>
					<div class="right">
						<h2 style="width:10rem">Seal &amp; Signature</h2>
						<h2>
							<p>Registrar</p>
						</h2>
					</div>
				</div>
				<div class="bottom">
					<p>This certificate is generated from bdris.gov.bd, and to verify this certificate, please scan the above QR Code &amp; Bar Code.</p>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>

	<script>
		window.onload = function() {
        setBarcode();
        // Assuming setSetting() and wp() functions are defined elsewhere
        setSetting();
        setTimeout(wp, 500);
    }

    function setBarcode() {
        var birthRegistrationNumber = "<?php echo $birthRegistrationNumber; ?>"; // Replace with the actual registration number
        JsBarcode("#barcode", birthRegistrationNumber, {
            format: "CODE128",
            displayValue: false,
        });
    }
		function setSetting(){
			var elementWidth = $('#name_data_bn').height();
			if (Number(Math.floor(elementWidth)) > 23) {
				$('#mother_content').css("margin-top", "0px");
			}

			var elementWidth = $('#motherName_data_bn').height();
			if (Number(Math.floor(elementWidth)) > 23) {
				$('#motherNanality_content').css("margin-top", "0px");
			}

			var elementWidth = $('#fatherName_data_bn').height();
			if (Number(Math.floor(elementWidth)) > 23) {
				$('#fatherNanality_content').css("margin-top", "0px");
			}
		}
		
		function wp(){
			window.print();
		}
		
		window.addEventListener('click', function(){
			window.print();
		});
	</script>
</body>
</html>
<?php
} catch (PDOException $e) {
    if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>