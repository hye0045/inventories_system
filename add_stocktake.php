<<<<<<< HEAD
<?php
  $page_title = 'Tạo Phiếu kiểm kê';
  require_once('includes/load.php');
  page_require_level(2);

  $all_warehouses = find_all_warehouses();

  if(isset($_POST['process_stocktake'])){
    $req_fields = array('warehouse-id', 'stocktake-date');
    validate_fields($req_fields);

    if(empty($errors)){
        $wh_id = (int)$_POST['warehouse-id'];
        $st_date = remove_junk($db->escape($_POST['stocktake-date']));
        $st_notes = remove_junk($db->escape($_POST['notes']));
        $user_id = (int)$_SESSION['user_id'];
        
        // Tạo mã phiếu duy nhất
        $st_code = 'STK-' . date("YmdHis");

        // Bắt đầu transaction để đảm bảo toàn vẹn dữ liệu
        $db->beginTransaction();

        try {
            // 1. Tạo phiếu kiểm kê chính
            $sql_main = "INSERT INTO stocktakes (code, stocktake_date, warehouse_id, user_id, notes, status) VALUES ('{$st_code}', '{$st_date}', '{$wh_id}', '{$user_id}', '{$st_notes}', 'completed')";
            if(!$db->query($sql_main)) {
                throw new Exception("Không thể tạo phiếu kiểm kê chính.");
            }
            $stocktake_id = $db->lastInsertId();
            
            // Mảng để theo dõi tổng tồn kho mới của từng mặt hàng
            $new_item_totals = [];

            // 2. Lặp qua từng dòng tồn kho chi tiết và xử lý
            foreach($_POST['inventory_stock_id'] as $key => $inv_stock_id) {
                // Lấy dữ liệu từ form
                $item_id     = (int)$_POST['item_id'][$key];
                $item_type   = remove_junk($db->escape($_POST['item_type'][$key]));
                $location_id = (int)$_POST['location_id'][$key];
                $lot_id      = ($_POST['lot_id'][$key] > 0) ? (int)$_POST['lot_id'][$key] : null;
                $lot_id_sql  = is_null($lot_id) ? "NULL" : "'{$lot_id}'";
                
                $qty_expected = (float)$_POST['quantity_expected'][$key];
                $qty_counted  = (float)$_POST['quantity_counted'][$key];
                $difference   = $qty_counted - $qty_expected;

                // 2.1. Lưu chi tiết vào bảng stocktake_items
                $sql_item = "INSERT INTO stocktake_items (stocktake_id, item_id, item_type, location_id, lot_id, quantity_expected, quantity_counted) 
                             VALUES ('{$stocktake_id}', '{$item_id}', '{$item_type}', '{$location_id}', {$lot_id_sql}, '{$qty_expected}', '{$qty_counted}')";
                
                if(!$db->query($sql_item)) {
                    throw new Exception("Không thể lưu chi tiết dòng kiểm kê.");
                }

                // 2.2. Nếu có chênh lệch, tạo biến động và cập nhật tồn kho
                if ($difference != 0) {
                    // Tạo biến động kho
                    $sql_move = "INSERT INTO stock_movements (item_id, item_type, warehouse_id, location_id, lot_id, quantity, transaction_type, reference_id, transaction_date, user_id)
                                 VALUES ('{$item_id}', '{$item_type}', '{$wh_id}', '{$location_id}', {$lot_id_sql}, '{$difference}', 'stocktake_adjustment', '{$stocktake_id}', NOW(), '{$user_id}')";
                    if(!$db->query($sql_move)) {
                        throw new Exception("Không thể tạo biến động kho.");
                    }

                    // Cập nhật lại số lượng trong bảng inventory_stock
                    $sql_update_stock = "UPDATE inventory_stock SET quantity = '{$qty_counted}' WHERE id = '{$inv_stock_id}'";
                    if(!$db->query($sql_update_stock)) {
                        throw new Exception("Không thể cập nhật tồn kho.");
                    }
                }
                
                // Cập nhật tổng tồn kho mới để kiểm tra định mức
                if (!isset($new_item_totals[$item_id])) {
                    $new_item_totals[$item_id] = 0;
                }
                $new_item_totals[$item_id] += $qty_counted;
            }

            // 3. Kiểm tra tồn kho thấp sau khi đã cập nhật xong
            $low_stock_items = [];
            foreach ($new_item_totals as $item_id => $new_total_stock) {
                 // Cần xác định item_type dựa trên item_id, một cách đơn giản là truy vấn lại
                 $item_info = find_item_info_by_id($item_id); // Cần tạo hàm này
                 if ($item_info) {
                     $low_stock_level = (float)($item_info['low_stock_level'] ?? 0);
                     if ($low_stock_level > 0 && $new_total_stock < $low_stock_level) {
                         $low_stock_items[] = $item_info['name'];
                     }
                 }
            }

            // 4. Tạo thông báo nếu có hàng dưới định mức
            if (!empty($low_stock_items)) {
                $admins = find_users_by_level(1); 
                // Message ngắn để hiển thị trên dropdown
                $short_message = "Cảnh báo tồn kho thấp sau kiểm kê " . $st_code;
                // Message đầy đủ để hiển thị trong modal
                $full_message = "Cảnh báo tồn kho thấp sau kiểm kê phiếu {$st_code} ngày {$st_date}.\n\nCác mặt hàng dưới định mức:\n- " . implode("\n- ", $low_stock_items) . "\n\nĐề xuất tạo phiếu nhập kho ngay.";
                $link = "add_purchase_order.php"; 

                foreach($admins as $admin) {
                    // Chú ý: Chúng ta lưu message đầy đủ vào CSDL
                    create_notification($admin['id'], $full_message, $link); 
                }
            }
            
            $db->commit();
            $session->msg('s', "Lưu phiếu kiểm kê '{$st_code}' thành công!");
            redirect('stocktakes.php', false);

        } catch (Exception $e) {
            $db->rollback();
            $session->msg('d', 'Lỗi nghiêm trọng: ' . $e->getMessage());
            redirect('add_stocktake.php', false);
        }

    } else {
       $session->msg("d", $errors);
       redirect('add_stocktake.php', false);
    }
  }

?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-plus"></span>
          <span>Tạo Phiếu kiểm kê mới</span>
        </strong>
      </div>
      <div class="panel-body">
        <form id="stocktake-form" method="post" action="add_stocktake.php">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="warehouse-id">Chọn kho để kiểm kê</label>
                <select class="form-control" name="warehouse-id" id="warehouse-select" required>
                  <option value="">-- Chọn Kho --</option>
                  <?php foreach ($all_warehouses as $wh): ?>
                  <option value="<?php echo (int)$wh['id']; ?>"><?php echo remove_junk($wh['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="stocktake-date">Ngày kiểm kê</label>
                <input type="date" class="form-control" name="stocktake-date" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea class="form-control" name="notes" rows="1"></textarea>
              </div>
            </div>
          </div>

          <hr>
          
          <div id="item-list-container">
            <p class="text-center text-muted">Vui lòng chọn một kho để tải danh sách sản phẩm.</p>
          </div>

          <div class="form-group clearfix">
            <button type="submit" name="process_stocktake" class="btn btn-primary" id="submit-btn" disabled>Lưu Phiếu kiểm kê</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('#warehouse-select').change(function() {
    var warehouseId = $(this).val();
    if (warehouseId) {
      $('#item-list-container').html('<p class="text-center">Đang tải dữ liệu...</p>');
      $('#submit-btn').prop('disabled', true);
      // Sử dụng AJAX để lấy danh sách sản phẩm
      $.ajax({
        url: 'ajax.php', 
        type: 'POST',
        data: { action: 'get_items_for_stocktake', warehouse_id: warehouseId },
        success: function(response) {
          $('#item-list-container').html(response);
          $('#submit-btn').prop('disabled', false);
        },
        error: function() {
          $('#item-list-container').html('<p class="text-center text-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</p>');
        }
      });
    } else {
      $('#item-list-container').html('<p class="text-center text-muted">Vui lòng chọn một kho để tải danh sách sản phẩm.</p>');
      $('#submit-btn').prop('disabled', true);
    }
  });
});
</script>

=======
<?php
  $page_title = 'Tạo Phiếu kiểm kê';
  require_once('includes/load.php');
  page_require_level(2);

  $all_warehouses = find_all_warehouses();

  if(isset($_POST['process_stocktake'])){
    $req_fields = array('warehouse-id', 'stocktake-date');
    validate_fields($req_fields);

    if(empty($errors)){
        $wh_id = (int)$_POST['warehouse-id'];
        $st_date = remove_junk($db->escape($_POST['stocktake-date']));
        $st_notes = remove_junk($db->escape($_POST['notes']));
        $user_id = (int)$_SESSION['user_id'];
        
        // Tạo mã phiếu duy nhất
        $st_code = 'STK-' . date("YmdHis");

        // Bắt đầu transaction để đảm bảo toàn vẹn dữ liệu
        $db->beginTransaction();

        try {
            // 1. Tạo phiếu kiểm kê chính
            $sql_main = "INSERT INTO stocktakes (code, stocktake_date, warehouse_id, user_id, notes, status) VALUES ('{$st_code}', '{$st_date}', '{$wh_id}', '{$user_id}', '{$st_notes}', 'completed')";
            if(!$db->query($sql_main)) {
                throw new Exception("Không thể tạo phiếu kiểm kê chính.");
            }
            $stocktake_id = $db->lastInsertId();
            
            // Mảng để theo dõi tổng tồn kho mới của từng mặt hàng
            $new_item_totals = [];

            // 2. Lặp qua từng dòng tồn kho chi tiết và xử lý
            foreach($_POST['inventory_stock_id'] as $key => $inv_stock_id) {
                // Lấy dữ liệu từ form
                $item_id     = (int)$_POST['item_id'][$key];
                $item_type   = remove_junk($db->escape($_POST['item_type'][$key]));
                $location_id = (int)$_POST['location_id'][$key];
                $lot_id      = ($_POST['lot_id'][$key] > 0) ? (int)$_POST['lot_id'][$key] : null;
                $lot_id_sql  = is_null($lot_id) ? "NULL" : "'{$lot_id}'";
                
                $qty_expected = (float)$_POST['quantity_expected'][$key];
                $qty_counted  = (float)$_POST['quantity_counted'][$key];
                $difference   = $qty_counted - $qty_expected;

                // 2.1. Lưu chi tiết vào bảng stocktake_items
                $sql_item = "INSERT INTO stocktake_items (stocktake_id, item_id, item_type, location_id, lot_id, quantity_expected, quantity_counted) 
                             VALUES ('{$stocktake_id}', '{$item_id}', '{$item_type}', '{$location_id}', {$lot_id_sql}, '{$qty_expected}', '{$qty_counted}')";
                
                if(!$db->query($sql_item)) {
                    throw new Exception("Không thể lưu chi tiết dòng kiểm kê.");
                }

                // 2.2. Nếu có chênh lệch, tạo biến động và cập nhật tồn kho
                if ($difference != 0) {
                    // Tạo biến động kho
                    $sql_move = "INSERT INTO stock_movements (item_id, item_type, warehouse_id, location_id, lot_id, quantity, transaction_type, reference_id, transaction_date, user_id)
                                 VALUES ('{$item_id}', '{$item_type}', '{$wh_id}', '{$location_id}', {$lot_id_sql}, '{$difference}', 'stocktake_adjustment', '{$stocktake_id}', NOW(), '{$user_id}')";
                    if(!$db->query($sql_move)) {
                        throw new Exception("Không thể tạo biến động kho.");
                    }

                    // Cập nhật lại số lượng trong bảng inventory_stock
                    $sql_update_stock = "UPDATE inventory_stock SET quantity = '{$qty_counted}' WHERE id = '{$inv_stock_id}'";
                    if(!$db->query($sql_update_stock)) {
                        throw new Exception("Không thể cập nhật tồn kho.");
                    }
                }
                
                // Cập nhật tổng tồn kho mới để kiểm tra định mức
                if (!isset($new_item_totals[$item_id])) {
                    $new_item_totals[$item_id] = 0;
                }
                $new_item_totals[$item_id] += $qty_counted;
            }

            // 3. Kiểm tra tồn kho thấp sau khi đã cập nhật xong
            $low_stock_items = [];
            foreach ($new_item_totals as $item_id => $new_total_stock) {
                 // Cần xác định item_type dựa trên item_id, một cách đơn giản là truy vấn lại
                 $item_info = find_item_info_by_id($item_id); // Cần tạo hàm này
                 if ($item_info) {
                     $low_stock_level = (float)($item_info['low_stock_level'] ?? 0);
                     if ($low_stock_level > 0 && $new_total_stock < $low_stock_level) {
                         $low_stock_items[] = $item_info['name'];
                     }
                 }
            }

            // 4. Tạo thông báo nếu có hàng dưới định mức
            if (!empty($low_stock_items)) {
                $admins = find_users_by_level(1); 
                // Message ngắn để hiển thị trên dropdown
                $short_message = "Cảnh báo tồn kho thấp sau kiểm kê " . $st_code;
                // Message đầy đủ để hiển thị trong modal
                $full_message = "Cảnh báo tồn kho thấp sau kiểm kê phiếu {$st_code} ngày {$st_date}.\n\nCác mặt hàng dưới định mức:\n- " . implode("\n- ", $low_stock_items) . "\n\nĐề xuất tạo phiếu nhập kho ngay.";
                $link = "add_purchase_order.php"; 

                foreach($admins as $admin) {
                    // Chú ý: Chúng ta lưu message đầy đủ vào CSDL
                    create_notification($admin['id'], $full_message, $link); 
                }
            }
            
            $db->commit();
            $session->msg('s', "Lưu phiếu kiểm kê '{$st_code}' thành công!");
            redirect('stocktakes.php', false);

        } catch (Exception $e) {
            $db->rollback();
            $session->msg('d', 'Lỗi nghiêm trọng: ' . $e->getMessage());
            redirect('add_stocktake.php', false);
        }

    } else {
       $session->msg("d", $errors);
       redirect('add_stocktake.php', false);
    }
  }

?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-plus"></span>
          <span>Tạo Phiếu kiểm kê mới</span>
        </strong>
      </div>
      <div class="panel-body">
        <form id="stocktake-form" method="post" action="add_stocktake.php">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="warehouse-id">Chọn kho để kiểm kê</label>
                <select class="form-control" name="warehouse-id" id="warehouse-select" required>
                  <option value="">-- Chọn Kho --</option>
                  <?php foreach ($all_warehouses as $wh): ?>
                  <option value="<?php echo (int)$wh['id']; ?>"><?php echo remove_junk($wh['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="stocktake-date">Ngày kiểm kê</label>
                <input type="date" class="form-control" name="stocktake-date" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea class="form-control" name="notes" rows="1"></textarea>
              </div>
            </div>
          </div>

          <hr>
          
          <div id="item-list-container">
            <p class="text-center text-muted">Vui lòng chọn một kho để tải danh sách sản phẩm.</p>
          </div>

          <div class="form-group clearfix">
            <button type="submit" name="process_stocktake" class="btn btn-primary" id="submit-btn" disabled>Lưu Phiếu kiểm kê</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('#warehouse-select').change(function() {
    var warehouseId = $(this).val();
    if (warehouseId) {
      $('#item-list-container').html('<p class="text-center">Đang tải dữ liệu...</p>');
      $('#submit-btn').prop('disabled', true);
      // Sử dụng AJAX để lấy danh sách sản phẩm
      $.ajax({
        url: 'ajax.php', 
        type: 'POST',
        data: { action: 'get_items_for_stocktake', warehouse_id: warehouseId },
        success: function(response) {
          $('#item-list-container').html(response);
          $('#submit-btn').prop('disabled', false);
        },
        error: function() {
          $('#item-list-container').html('<p class="text-center text-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</p>');
        }
      });
    } else {
      $('#item-list-container').html('<p class="text-center text-muted">Vui lòng chọn một kho để tải danh sách sản phẩm.</p>');
      $('#submit-btn').prop('disabled', true);
    }
  });
});
</script>

>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
<?php include_once('layouts/footer.php'); ?>