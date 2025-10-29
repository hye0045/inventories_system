<<<<<<< HEAD
<?php
$page_title = 'Chọn báo cáo';
require_once('includes/load.php');
page_require_level(2);

// Lấy danh sách kho
$warehouse_list = find_all_warehouses();
?>

<?php include_once('layouts/header.php'); ?>


    <div class="form-container">
      <div class="report-header">
        <strong><span class="glyphicon glyphicon-list-alt"></span> CHỌN LOẠI BÁO CÁO</strong>
      </div>
      <br>
      <div class="report-body">
        <form id="report_form" method="GET" action="report_result.php">
          
          <!-- Loại báo cáo -->
          <div class="report-field">
            <label for="report_type_select"><strong>Loại báo cáo</strong></label>
            <select id="report_type_select" name="report_type" class="form-control" required>
              <option value="">-- Chọn loại --</option>
              <option value="stock_in">Báo cáo nhập kho</option>
              <option value="stock_out">Báo cáo xuất kho</option>
              <option value="stock_balance">Báo cáo tồn kho</option>
              <option value="stock_take">Báo cáo kiểm kê</option>
              <option value="stock_summary">Báo cáo tổng hợp</option>
            </select>
          </div>
<br>
          <!-- Kho -->
          <div class="report-field">
            <label for="warehouse_select"><strong>Kho</strong></label>
            <select id="warehouse_select" name="warehouse_id" class="form-control">
              <option value="">Tất cả kho</option>
              <?php foreach ($warehouse_list as $wh): ?>
                <option value="<?php echo (int)$wh['id']; ?>">
                  <?php echo remove_junk($wh['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
<br>
          <!-- Khoảng thời gian -->
          <div class="report-field">
            <label><strong>Khoảng thời gian</strong></label>
            <div class="row">
              <div class="col-md-6">
                <input type="date" name="start_date" id="start_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <input type="date" name="end_date" id="end_date" class="form-control" required>
              </div>
            </div>
          </div>

          <!-- Nút xem báo cáo -->
          <div class="report-action text-center" style="margin-top:15px;">
            <button type="button" class="btn" id="btn_view_report">
              <span class="report"></span> Xem báo cáo
            </button>
          </div>
        </form>
      </div>
    </div>
  

<!-- Script xử lý -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnView = document.getElementById('btn_view_report');
    const form = document.getElementById('report_form');
    const reportType = document.getElementById('report_type_select');

    btnView.addEventListener('click', function() {
      if (!reportType.value) {
        alert('Vui lòng chọn loại báo cáo.');
        return;
      }
      form.submit();
    });

    // Ngăn reload trang khi click vào các select
    document.querySelectorAll('select').forEach(select => {
      select.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    });
  });
</script>

<?php include_once('layouts/footer.php'); ?>
=======
<?php
$page_title = 'Chọn báo cáo';
require_once('includes/load.php');
page_require_level(2);

// Lấy danh sách kho
$warehouse_list = find_all_warehouses();
?>

<?php include_once('layouts/header.php'); ?>


    <div class="form-container">
      <div class="report-header">
        <strong><span class="glyphicon glyphicon-list-alt"></span> CHỌN LOẠI BÁO CÁO</strong>
      </div>
      <br>
      <div class="report-body">
        <form id="report_form" method="GET" action="report_result.php">
          
          <!-- Loại báo cáo -->
          <div class="report-field">
            <label for="report_type_select"><strong>Loại báo cáo</strong></label>
            <select id="report_type_select" name="report_type" class="form-control" required>
              <option value="">-- Chọn loại --</option>
              <option value="stock_in">Báo cáo nhập kho</option>
              <option value="stock_out">Báo cáo xuất kho</option>
              <option value="stock_balance">Báo cáo tồn kho</option>
              <option value="stock_take">Báo cáo kiểm kê</option>
              <option value="stock_summary">Báo cáo tổng hợp</option>
            </select>
          </div>
<br>
          <!-- Kho -->
          <div class="report-field">
            <label for="warehouse_select"><strong>Kho</strong></label>
            <select id="warehouse_select" name="warehouse_id" class="form-control">
              <option value="">Tất cả kho</option>
              <?php foreach ($warehouse_list as $wh): ?>
                <option value="<?php echo (int)$wh['id']; ?>">
                  <?php echo remove_junk($wh['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
<br>
          <!-- Khoảng thời gian -->
          <div class="report-field">
            <label><strong>Khoảng thời gian</strong></label>
            <div class="row">
              <div class="col-md-6">
                <input type="date" name="start_date" id="start_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <input type="date" name="end_date" id="end_date" class="form-control" required>
              </div>
            </div>
          </div>

          <!-- Nút xem báo cáo -->
          <div class="report-action text-center" style="margin-top:15px;">
            <button type="button" class="btn" id="btn_view_report">
              <span class="report"></span> Xem báo cáo
            </button>
          </div>
        </form>
      </div>
    </div>
  

<!-- Script xử lý -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnView = document.getElementById('btn_view_report');
    const form = document.getElementById('report_form');
    const reportType = document.getElementById('report_type_select');

    btnView.addEventListener('click', function() {
      if (!reportType.value) {
        alert('Vui lòng chọn loại báo cáo.');
        return;
      }
      form.submit();
    });

    // Ngăn reload trang khi click vào các select
    document.querySelectorAll('select').forEach(select => {
      select.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    });
  });
</script>

<?php include_once('layouts/footer.php'); ?>
>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
