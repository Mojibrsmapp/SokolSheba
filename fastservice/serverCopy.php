<?php include("header.php"); ?>
<link rel="stylesheet" href="assets/css/server.css">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    body {
        background: #F0F4F8 !important;
        color: #1E293B;
        font-family: 'Hind Siliguri', 'Plus Jakarta Sans', Arial, sans-serif !important;
    }
    .custom-card {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid rgba(14, 165, 233, 0.15);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    .btn-primary {
        background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 14px !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25) !important;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35) !important;
    }
    .inputIcon {
        margin-bottom: 20px;
        text-align: left;
    }
    .inputIcon label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        display: block;
        font-size: 13px;
    }
    .input-box-wrap {
        position: relative;
        width: 100%;
        display: block;
    }
    .form-control, 
    .form-select, 
    select {
        background-color: #FFFFFF !important;
        border: 1.5px solid #CBD5E1 !important;
        border-radius: 12px !important;
        height: 50px !important;
        min-height: 50px !important;
        padding: 13px 48px 13px 16px !important;
        color: #1E293B !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif !important;
        box-sizing: border-box !important;
        width: 100% !important;
        transition: all 0.2s ease;
        outline: none !important;
    }
    select.form-select,
    select.form-control,
    select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        line-height: normal !important;
    }
    .form-control:focus, 
    select:focus, 
    .form-select:focus {
        border-color: #0EA5E9 !important;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15) !important;
    }
    .input-box-wrap .s_icon {
        position: absolute !important;
        right: 16px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #0EA5E9 !important;
        font-size: 16px !important;
        pointer-events: none !important;
        z-index: 10 !important;
    }
    .custom-table-container {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid rgba(14, 165, 233, 0.15);
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.05);
    }
    .table th {
        background-color: #F0F9FF !important;
        color: #0284C7 !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        padding: 14px !important;
        border-bottom: 1px solid rgba(14, 165, 233, 0.15) !important;
    }
    .table td {
        padding: 14px !important;
        color: #334155 !important;
        border-bottom: 1px solid #F1F5F9 !important;
    }
    .smart-paste-box {
        background: linear-gradient(135deg, #F0F9FF 0%, #EEF2FF 100%);
        border: 1.5px dashed #38BDF8;
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 22px;
        transition: all 0.2s ease;
    }
    .smart-paste-box:focus-within {
        border-color: #0EA5E9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }
</style>

<div id="loddingPdf">
  <div id="progress_main">
    <svg>
      <text x="50%" y="50%" dy=".35em" text-anchor="middle">
        তথ্য খোজা হচ্ছে
      </text>
    </svg>
  </div>
</div>

<div class="container-fluid px-2 px-md-4 py-3">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card mb-3 border-0 shadow-sm rounded-4">
        <?php
        include 'includes/marquee.php';
        $id = 6;
        $marquee = new Marquee($conn, $id);$marquee->display();
        ?>
      </div>
    </div>
    
    <div class="col-lg-8">
      <div class="custom-card p-4 p-md-5 mb-4">
        <form id="server_form" method="post" class="py-2">
            <h2 class="text-center fw-extrabold text-slate-800 mb-4" style="font-size: 24px;">সার্ভিস ড্যাশবোর্ড - FAST SERVICE</h2>
            
            <div class="smart-paste-box">
                <div class="flex items-center justify-between mb-2">
                    <label for="smartPasteInput" class="text-xs font-bold text-slate-700 flex items-center gap-1.5 mb-0">
                        <i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i> স্মার্ট অটো-ফিল (NID ও জন্মতারিখ যেকোনো ফরম্যাটে পেস্ট করুন)
                    </label>
                    <span class="text-[10px] font-bold text-sky-700 bg-sky-100 px-2 py-0.5 rounded-full">অটো ডিটেক্ট</span>
                </div>
                <div class="relative">
                    <input type="text" id="smartPasteInput" placeholder="উদাহরণ: NID 19901234567890123 DOB 12/10/1990 অথবা যেকোনো ফরম্যাটে পেস্ট করুন" class="form-control text-xs pr-20 !py-2.5" autocomplete="off">
                    <button type="button" id="clearSmartBtn" class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-2.5 py-1 rounded-lg transition hidden">
                        ক্লিয়ার
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5 mb-0 flex items-center gap-1">
                    <i class="fa-solid fa-circle-info text-sky-500"></i> বাংলা সংখ্যা, যেকোনো চিহ্ন (*, /, -, ., স্পেস) ও ১৩ ডিজিট থাকলে অটো ১৭ ডিজিট রূপান্তর হবে।
                </p>
            </div>

            <div class="inputIcon">
                <label for="versionSelect">Select Version</label> 
                <div class="input-box-wrap">
                    <select id="versionSelect" name="version" required class="form-select form-control">
                      <option selected value="1">এপিআই ১ (<?php echo file_get_contents('api_key/deduction_amount.txt'); ?>tk)</option>
                      <option value="2">এপিআই ২ (<?php echo file_get_contents('api_key/v3_deduction_amount.txt'); ?>tk)</option>
                    </select>
                    <i class="s_icon fa fa-server"></i>
                </div>
            </div>

            <div class="inputIcon">
                <label for="nidNumber">NID number (10 or 17 digits)</label> 
                <div class="input-box-wrap">
                    <input type="text" name="nid" id="nidNumber" required placeholder="Type Nid Number" class="form-control" autocomplete="off">
                    <i class="s_icon fa fa-id-card"></i>
                </div>
            </div>

            <div class="inputIcon">
                <label for="DateOfBirth">Date of Birth (YYYY-MM-DD)</label> 
                <div class="input-box-wrap">
                    <input type="text" name="dob" required id="DateOfBirth" placeholder="2000-12-21" class="form-control" autocomplete="off">
                    <i class="s_icon fa fa-calendar"></i>
                </div>
            </div>

            <input type="text" hidden value="" name="email">

            <?php
              if ($balance > 5) {
                 echo '<button type="submit" class="btn btn-primary w-100 mt-3" name="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>তথ্য অনুসন্ধান</button>';
              } else {
                 echo '<button class="btn btn-danger w-100 mt-3" disabled name="submit">Recharge Please</button>';
              }
            ?>
        </form>
      </div>
    </div>

    <div class="col-md-12 mt-3">
        <div class="custom-card p-3 p-md-4">
            <div class="flex items-center justify-between mb-3">
                <h5 class="fw-bold text-slate-800 mb-0"><i class="fa-solid fa-file-arrow-down text-sky-500 me-2"></i> ডাউনলোড লিস্ট</h5>
                <button type="button" id="refreshTableBtn" class="bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate-right"></i> রিফ্রেশ লিস্ট
                </button>
            </div>
            <div class="table-responsive custom-table-container">
                <table id="data-table" class="text-center table table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th class="text-center">Name</th>
                        <th class="text-center">NID</th>
                        <th class="text-center">Action</th>
                        <th class="text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody id="data-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

  </div>
</div>

<div style="position: fixed; bottom: 20px; right: 20px; z-index: 99;">
    <a href="<?php echo htmlspecialchars(get_site_setting($conn, "whatsapp_group", "#")); ?>" target="_blank" style="background: #0EA5E9; color: #FFF; padding: 12px 20px; border-radius: 30px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);">
        <i class="fa-brands fa-whatsapp text-lg"></i> Join Group
    </a>
</div>

<?php include('footer.php'); ?>
<script>
$(document).ready(function() {

  function convertBengaliToEnglish(str) {
      if (!str) return '';
      const banglaDigits = {'০':'0', '১':'1', '২':'2', '৩':'3', '৪':'4', '৫':'5', '৬':'6', '৭':'7', '৮':'8', '৯':'9'};
      return String(str).replace(/[০-৯]/g, match => banglaDigits[match]);
  }

  function smartExtractNidAndDob(rawText) {
      if (!rawText) return { nid: null, dob: null };

      let text = convertBengaliToEnglish(rawText).trim();

      let foundDob = null;
      let birthYear = null;

      let ymdMatch = text.match(/(19\d{2}|20\d{2})[\/\-\.\*\s\,]+(\d{1,2})[\/\-\.\*\s\,]+(\d{1,2})/);
      if (ymdMatch) {
          let y = parseInt(ymdMatch[1], 10);
          let m = parseInt(ymdMatch[2], 10);
          let d = parseInt(ymdMatch[3], 10);
          if (m >= 1 && m <= 12 && d >= 1 && d <= 31) {
              foundDob = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
              birthYear = String(y);
              text = text.replace(ymdMatch[0], ' ');
          }
      }

      if (!foundDob) {
          let dmyMatch = text.match(/(\d{1,2})[\/\-\.\*\s\,]+(\d{1,2})[\/\-\.\*\s\,]+(19\d{2}|20\d{2})/);
          if (dmyMatch) {
              let d = parseInt(dmyMatch[1], 10);
              let m = parseInt(dmyMatch[2], 10);
              let y = parseInt(dmyMatch[3], 10);
              if (m >= 1 && m <= 12 && d >= 1 && d <= 31) {
                  foundDob = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                  birthYear = String(y);
                  text = text.replace(dmyMatch[0], ' ');
              }
          }
      }

      let digitSequences = text.match(/\d{10,17}/g) || [];
      let foundNid = null;

      for (let seq of digitSequences) {
          if (seq.length === 17) {
              foundNid = seq;
              break;
          }
      }

      if (!foundNid) {
          for (let seq of digitSequences) {
              if (seq.length === 10) {
                  foundNid = seq;
                  break;
              }
          }
      }

      if (!foundNid) {
          for (let seq of digitSequences) {
              if (seq.length === 13) {
                  foundNid = (birthYear ? birthYear : '') + seq;
                  break;
              }
          }
      }

      if (!foundNid && digitSequences.length > 0) {
          foundNid = digitSequences[0];
      }

      return { nid: foundNid, dob: foundDob };
  }

  $('#smartPasteInput').on('input paste change', function() {
      setTimeout(() => {
          let val = $(this).val();
          if (val.trim()) {
              $('#clearSmartBtn').removeClass('hidden');
          } else {
              $('#clearSmartBtn').addClass('hidden');
          }

          let extracted = smartExtractNidAndDob(val);

          let updated = false;
          if (extracted.nid) {
              $('#nidNumber').val(extracted.nid);
              updated = true;
          }
          if (extracted.dob) {
              $('#DateOfBirth').val(extracted.dob);
              updated = true;
          }

          if (updated) {
              const Toast = Swal.mixin({
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: 1500,
                  timerProgressBar: true
              });
              Toast.fire({
                  icon: 'success',
                  title: 'অটো ফিল সম্পন্ন হয়েছে!'
              });
          }
      }, 30);
  });

  $('#clearSmartBtn').click(function() {
      $('#smartPasteInput').val('');
      $('#nidNumber').val('');
      $('#DateOfBirth').val('');
      $(this).addClass('hidden');
  });

  $('#nidNumber').on('paste', function(e) {
      let pastedData = (e.originalEvent || e).clipboardData.getData('text');
      let extracted = smartExtractNidAndDob(pastedData);

      if (extracted.nid && extracted.dob) {
          e.preventDefault();
          $('#nidNumber').val(extracted.nid);
          $('#DateOfBirth').val(extracted.dob);
          
          Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'তথ্য অটো ফিল সম্পন্ন হয়েছে!',
              showConfirmButton: false,
              timer: 1500
          });
      }
  });

  $('#DateOfBirth').on('change blur', function () {
      let input = $(this).val().trim();
      let extracted = smartExtractNidAndDob(input);
      if (extracted.dob) {
          $(this).val(extracted.dob);
      } else {
          $(this).val(convertBengaliToEnglish(input));
      }
  });

  $('#nidNumber').on('input', function() {
      $(this).val(convertBengaliToEnglish($(this).val()));
  });

  $(".menu-item").removeClass("active");
  $(".two_pages").addClass("active");

  $('#server_form').on('submit', function(event) {
      event.preventDefault();

      var nid = $('#nidNumber').val().trim();
      var dob = $('#DateOfBirth').val().trim();
      var email = $("input[name='email']").val();
      var version = $('#versionSelect').val();

      var requestUrl = '';
      if (version === '1') {
          requestUrl = 'insert_un_server.php';
      } else if (version === '2') {
          requestUrl = 'insert_un_server_v2.php';
      }

      if (!/^\d{10}$|^\d{13}$\vert{}^\d{17}$/.test(nid)) {
          Swal.fire({
              title: 'Validation Error!',
              text: 'আপনাকে অবশ্যই ১০ ডিজিট, ১৩ ডিজিট অথবা ১৭ ডিজিটের আইডি নাম্বার দিতে হবে।',
              icon: 'error',
              confirmButtonText: 'ঠিক আছে',
              confirmButtonColor: '#0EA5E9',
              background: '#FFFFFF',
              color: '#1E293B'
          });
          return;
      }

      if (nid.length === 13 && dob) {
          var birthYear = dob.split('-')[0];
          if (birthYear && birthYear.length === 4) {
              nid = birthYear + nid;
              $('#nidNumber').val(nid);
          }
      }

      Swal.fire({
          title: 'Are you sure?',
          html: `আপনি কি তথ্য NID: <b>${nid}</b> এবং Dob: <b>${dob}</b> সাবমিট করতে চান?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#0EA5E9',
          cancelButtonColor: '#ef4444',
          confirmButtonText: 'Yes, submit it!',
          background: '#FFFFFF',
          color: '#1E293B'
      }).then((result) => {
          if (result.isConfirmed) {
              $("#loddingPdf").css("display", "block");

              $.ajax({
                  url: requestUrl,
                  type: 'POST',
                  data: {
                      nid: nid,
                      dob: dob,
                      email: email
                  },
                  dataType: 'json',
                  success: function(response) {
                      $("#loddingPdf").css("display", "none");
                      if (response.status === 'success') {
                          Swal.fire({
                              title: 'Success!',
                              text: response.message,
                              icon: 'success',
                              confirmButtonText: 'OK',
                              confirmButtonColor: '#0EA5E9',
                              background: '#FFFFFF',
                              color: '#1E293B'
                          });
                          $('#server_form')[0].reset();
                          $('#smartPasteInput').val('');
                          $('#clearSmartBtn').addClass('hidden');
                          fetchData(); // অর্ডার সাবমিট করার পর অটো লিস্ট রিফ্রেশ হবে
                      } else {
                          Swal.fire({
                              title: 'Error!',
                              text: response.message,
                              icon: 'error',
                              confirmButtonText: 'OK',
                              confirmButtonColor: '#ef4444',
                              background: '#FFFFFF',
                              color: '#1E293B'
                          });
                      }
                  },
                  error: function(xhr, status, error) {
                      $("#loddingPdf").css("display", "none");
                      Swal.fire({
                          title: 'Error!',
                          text: 'An error occurred while submitting the form.',
                          icon: 'error',
                          confirmButtonText: 'OK',
                          confirmButtonColor: '#ef4444',
                          background: '#FFFFFF',
                          color: '#1E293B'
                      });
                  }
              });
          }
      });
  });

  var table = $('#data-table').DataTable({
      paging: true,
      searching: true,
      ordering: false,
      info: false
  });

  let cachedData = [];

  function fetchData() {
      $.ajax({
          url: 'fetch_data.php',
          type: 'GET',
          dataType: 'json',
          success: function(response) {
              if (JSON.stringify(response) !== JSON.stringify(cachedData)) {
                  cachedData = response;
                  table.clear();
                  
                  $.each(response, function(index, item) {
                      table.row.add([
                          item.nameEn,
                          item.nationalId,
                          '<button class="download-button btn btn-sm btn-primary me-1 px-3" onclick="downloadFile(\'' + item.nationalId + '\', \'' + item.id + '\')">V1</button>' +
                          '<button class="download-button btn btn-sm btn-info me-1 px-3 text-white" onclick="downloadFileOne(\'' + item.nationalId + '\', \'' + item.id + '\')">V2</button>', 
                          '<button class="download-button btn btn-sm btn-success px-3" onclick="downloadFileNID(\'' + item.nationalId + '\', \'' + item.id + '\')">NID</button>'
                      ]).draw(false);
                  });
              }
          },
          error: function(xhr, status, error) {
              console.error('Error fetching data:', error);
          }
      });
  }

  // setInterval پوری طرح سے ہٹا دیا گیا ہے تاکہ پیج سلو نہ ہو۔ 
  // اس کی جگہ اب یوزر اپنی مرضی سے ریفریش بٹن دبا سکتا ہے۔
  $('#refreshTableBtn').click(function() {
      const btn = $(this);
      btn.find('i').addClass('fa-spin');
      fetchData();
      setTimeout(() => {
          btn.find('i').removeClass('fa-spin');
      }, 600);
  });

  fetchData(); // পেজ লোড হওয়ার সময় একবার ডাটা ফেচ হবে
});

function downloadFile(nid, id) {
    const downloadUrl = 'server_download_v1.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}

function downloadFileOne(nid, id) {
    const downloadUrl = 'server_download_v2.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}

function downloadFileNID(nid, id) {
    const downloadUrl = 'auto_nid.php?nid=' + nid + '&id=' + id;
    window.open(downloadUrl, '_blank');
}
</script>