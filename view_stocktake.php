<?php
  $page_title = 'Chi tiết Phiếu kiểm kê';
  require_once('includes/load.php');
  page_require_level(2);

  if(!isset($_GET['id'])){
    $session->msg("d", "Thiếu ID phiếu kiểm kê.");
    redirect('stocktakes.php');
  }

  $stocktake_id = (int)$_GET['id'];
  $stocktake = find_stocktake_by_id($stocktake_id);

  if(!$stocktake){
    $session->msg("d", "Không tìm thấy thông tin phiếu kiểm kê.");
    redirect('stocktakes.php');
  }

  $stocktake_items = find_stocktake_items_by_id($stocktake_id);
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
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-eye-open"></span>
          <span>Chi tiết Phiếu kiểm kê: <?php echo remove_junk($stocktake['code']); ?></span>
        </strong>
        <div class="pull-right">
            <?php // Chỉ Quản lý mới thấy nút xuất file ?>
            <?php if($user['user_level'] <= 1): ?>
                <a href="export_stocktake.php?id=<?php echo $stocktake_id; ?>" class="btn btn-sm btn-success">
                    <span class="glyphicon glyphicon-download-alt"></span> Xuất Excel
                </a>
            <?php endif; ?>
        </div>
      </div>
      <div class="panel-body">
        <div class="row" style="margin-bottom: 20px;">
          <div class="col-md-4">
            <strong>Kho hàng:</strong> <?php echo remove_junk($stocktake['warehouse_name']); ?>
          </div>
          <div class="col-md-4">
            <strong>Ngày kiểm kê:</strong> <?php echo read_date($stocktake['stocktake_date']); ?>
          </div>
          <div class="col-md-4">
            <strong>Người tạo:</strong> <?php echo remove_junk($stocktake['user_name']); ?>
          </div>
        </div>
        <div class="row">
           <div class="col-md-12">
            <strong>Ghi chú:</strong> <?php echo remove_junk(!empty($stocktake['notes']) ? $stocktake['notes'] : 'Không có'); ?>           </div>
        </div>

        <hr>

        <h4>Danh sách mặt hàng đã kiểm kê</h4>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tên Sản phẩm/NVL</th>
              <th>Mã SKU</th>
              <th class="text-right">Tồn kho hệ thống</th>
              <th class="text-right">Số lượng thực tế</th>
              <th class="text-right">Chênh lệch</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stocktake_items as $item): ?>
            <tr>
              <td><?php echo remove_junk($item['item_name']); ?></td>
              <td><?php echo remove_junk($item['sku']); ?></td>
              <td class="text-right"><?php echo (float)$item['quantity_expected']; ?></td>
              <td class="text-right"><?php echo (float)$item['quantity_counted']; ?></td>
              <td class="text-right">
                <?php
                  $variance = (float)$item['variance'];
                  if ($variance > 0) {
                      echo '<span class="text-success"><strong>+' . $variance . '</strong></span>'; // Thừa
                  } elseif ($variance < 0) {
                      echo '<span class="text-danger"><strong>' . $variance . '</strong></span>'; // Thiếu
                  } else {
                      echo '0'; // Khớp
                  }
                ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
       <div class="panel-footer text-right">
            <a href="stocktakes.php" class="btn btn-default">Quay lại danh sách</a>
        </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>