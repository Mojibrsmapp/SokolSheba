<?php

if (isset($_GET["id"]) && isset($_GET["nid"])) {
    $id = $_GET["id"];
    $tin = $_GET["nid"];
    
    include_once("includes/configuration.php");

    // Use a prepared statement for security
    $stmt = $conn->prepare("SELECT * FROM tin_data WHERE id = ? AND tin = ?");
    $stmt->bind_param("ss", $id, $tin); // "ss" indicates both parameters are strings

    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a row is returned
    if ($result->num_rows > 0) {
        // Fetch the data
        $row = $result->fetch_assoc();

        // Store each field in a variable
        $id = $row['id'];
        $tin = $row['tin'];
        $current_address = $row['current_address'];
        $date = $row['date'];
        $father_name = $row['father_name'];
        $last_update = $row['last_update'];
        $mother_name = $row['mother_name'];
        $name = $row['name'];
        $office_address = $row['full_address'];
        $office_circle = $row['circle'];
        $office_name = $row['office_name'];
        $office_phone = $row['phone'];
        $office_zone = $row['zone1'];
        $split = explode('Taxes Zone', $office_zone);
        $taxes_zone = $row['zone'];
        $zone_details = isset($split[1]) ? trim($split[1]) : '';
        $permanent_address = $row['permanent_address'];
        $previous_tin = $row['previous_tin'];
        $qr_code = $row['qr_code'];
        $status = $row['status'];
        // You can use other variables similarly
    } else {
       header("location: tinDownload.php");
       exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

?>



<style>
    body{
        font-family: "Roboto", sans-serif;
    }
    span{
        line-height: 25px;
        font-size: 14.5px;
    }
    span#office_details div {
    line-height: 13px;
}
	@media print {
        html, body {
            height:100%; margin: 0 !important; padding: 0 !important;overflow: hidden;
            }


            .print-button {
                display: none;
            }     
            }</style>
            <title><?php echo  $tin; ?></title>
</head>
<body data-new-gr-c-s-check-loaded="14.1195.0" data-gr-ext-installed="">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
<form action="/ViewCertiifcate" method="post">







    <div class="cert_div">
        
        <div>
                <img src="http://secure.incometax.gov.bd:80/Images/tin_cert_back.png" style=" position:absolute; z-index: -1; top: 225px; left:200px; height:300px; width:300px;">



            <table class="cert_table" style="background-position: center center; border-style: solid; border-color: Black; border-width: 1px; font-size:medium;">
                <tbody><tr>
                    <td>
                        <table class="cert_table" style="background-position: center center; border-style: solid; border-color: Black; border-width: 2px; font-size:medium;">
                            <tbody><tr>
                                <td>
                                    <table class="cert_table" style="background-position: center center; border-style: solid; border-color: Black; border-width: 1px; font-size:medium;">
                                        <tbody><tr>
                                            <td>
                                                
                                                <table class="inner_cert_table" style="padding-left:5px; padding-right:5px">
                                                    
                                                    
                                                    <tbody><tr>
                                                        <td colspan="2" style="text-align: right;">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                        <tr>
                                                            <td colspan="2" style="text-align: center">
                                                                <img style="text-align:center; margin-bottom: 5px!important;"  src="http://secure.incometax.gov.bd:80/Images/tin_cert_logo.png" alt="NBR" width="80px">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" style="text-align: center">
                                                                <span style="font-size: 20px; line-height:30px; font-weight: bold; ">Government of the People's Republic
                                                                    of Bangladesh</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" style="text-align: center">
                                                                <span style="font-size: 20px; line-height:30px; font-weight: bold;">National Board of Revenue</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" style="text-align: center">
                                                                <span style="font-size: large">Taxpayer's Identification Number (TIN) Certificate</span>
                                                            </td>
                                                        </tr>
                                                    <tr>
                                                        <td>
                                                        </td>
                                                        <td>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                        </td>
                                                        <td>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="text-align: center;">
                                                            <span style="font-weight: bold; font-size: large; text-align: center; text-decoration: underline; color: black">
                                                                <?php echo $tin; ?> 
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            &nbsp;<br>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            This is to Certify that <span style="font-weight: bold;"> <?php echo $name; ?>
                                                            </span>is a Registered Taxpayer of National Board of Revenue under the jurisdiction
                                                            of <span style="font-weight: bold;"><?php echo $office_circle; ?></span>
                                                            , <?php echo "Taxes Zone"; ?> <span style="font-weight: bold;"><?php echo $zone_details; ?></span>.
                                                        </td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td colspan="2">
                                                            <span style="font-weight: bold;">Taxpayer's Particulars : </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            1) Name : <span style="font-weight: bold;"> <?php echo $name; ?> </span>
                                                        </td>
                                                    </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                2) Father's Name : <span style="font-weight: bold;"><?php echo $father_name; ?> </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                3) Mother's Name : <span style="font-weight: bold;"><?php echo $mother_name; ?> </span>
                                                            </td>
                                                        </tr>                        
                                                        <tr>
                                                            <td colspan="2">
                                                                4.a) Current Address : <span style="font-weight: bold;"><?php echo $current_address; ?></span>
                                                            </td>
                                                        </tr>                        
                                                        <tr>
                                                            <td colspan="2">
                                                                4.b) Permanent Address : <span style="font-weight: bold;"><?php echo $permanent_address; ?> </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                5) Previous TIN : <span style="font-weight: bold;"><?php echo $previous_tin; ?> </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                6) Status : <span style="font-weight: bold;"><?php echo $status; ?> </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <br><br>
                                                            </td>
                                                        </tr>


                                                     
                                                    <tr>
                                                        <td colspan="2">
                                                           <?php echo  $date; ?>
                                                        </td>
                                                    </tr>
                                                    
                                                    
                                                    <tr>
                                                        <td colspan="2">
                                                            <table width="100%">
                                                                <tbody><tr>
                                                                    <td style="width: 30%; vertical-align: top;">
                                                                            <table style="width: 200px; vertical-align: top; text-align: left;">
                                                                                <tbody><tr>
                                                                                    <td colspan="2">
                                                                                        <span style="font-weight: bold; text-align: left; text-decoration: underline;">Please
                                                                                            Note:</span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="width: 10px;">
                                                                                    </td>
                                                                                    <td>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr style="text-align: left; font-size: x-small;">
                                                                                    <td style="width: 10px; vertical-align: top;">
                                                                                        1.
                                                                                    </td>
                                                                                    <td>
                                                                                        A Taxpayer is liable to file the Return of Income under section 166 of the Income Tax Act, 2023.
                                                                                    </td>
                                                                                </tr>
                                                                                <tr style="text-align: left; font-size: x-small;">
                                                                                    <td style="width: 10px; vertical-align: top;">
                                                                                        2.
                                                                                    </td>
                                                                                    <td>
                                                                                        Failure to file Return of Income under Section 166 is liable to-
                                                                                    </td>
                                                                                </tr>
                                                                                <tr style="text-align: left; font-size: x-small;">
                                                                                    <td style="width: 10px; vertical-align: top;">
                                                                                    </td>
                                                                                    <td>
                                                                                        (a) Penalty under section 266; and
                                                                                    </td>
                                                                                </tr>
                                                                                <tr style="text-align: left; font-size: x-small;">
                                                                                    <td style="width: 10px; vertical-align: top;">
                                                                                    </td>
                                                                                    <td>
                                                                                        (b) Prosecution under section 311 of the Income Tax Act, 2023.
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody></table>

                                                                    </td>
                                                                    <td style="text-align: center; width: 40%;">
                                                                        <img src="<?php echo $qr_code; ?>" height="150px;" width="150px;" alt="QR Code" style="text-align:center;">
                                                                    </td>
                                                                    <td style="text-align: left; width: 30%; vertical-align: top;">
                                                                        <span id="office_details" style="text-align: left; font-size: 10px;">
                                                                               <div class="office_title" style="font-weight:bold">
                                                                                Deputy Commissioner
                                                                                of Taxes </div>
                                                                            
                                                                            <div class="cirle"><?php echo    $office_circle; ?></div>
                                                                            
                                                                           <div class="office_zone"><?php echo    $office_zone; ?></div> 
                                                                           
                                                                          <div class="office_addr"> <?php echo $office_address; ?></div> 
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        </td>
                                                    </tr>
                                                     
                                                    <tr>
                                                        <td>
                                                        </td>
                                                        <td>
                                                        </td>
                                                    </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                &nbsp;<br>
                                                            </td>
                                                        </tr>

                                                    <tr>
                                                        <td colspan="2" style="text-align: center">
                                                            <span style="text-align: center; text-decoration: underline; font-size: x-small;">N.
                                                                B: This is a system generated certificate and requires no manual signature.
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="text-align: center">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                    
                                                </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>
            </tbody></table>

        </div>
    </div>
    <br>



            

        </div>

    </form>          

</body><grammarly-desktop-integration data-grammarly-shadow-root="true"></grammarly-desktop-integration></html>
<button class="print-button" style="padding: 10px 20px; background:orange;border:none;outline:none;color:white;" onclick="window.print();">Print Page</button>

<script>
        window.onload = function() {
            window.print();
        };
    </script>

<?php 
}else{
    
}

?>