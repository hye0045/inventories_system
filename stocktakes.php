<?php
  $page_title = 'Danh sách Phiếu kiểm kê';
  require_once('includes/load.php');
  // Cả Quản lý (level 1) và NV Kho (level 2) đều có thể xem
  page_require_level(2);
  
  $stocktakes = find_all_stocktakes();
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
          <div class="pull-left">
            <strong><span class="glyphicon glyphicon-list"></span> Danh sách Phiếu kiểm kê</strong>
          </div>
          <div class="pull-right">
            <?php // Chỉ NV Kho hoặc cao hơn mới được tạo phiếu ?>
            <a href="add_stocktake.php" class="btn btn-primary">Tạo phiếu kiểm kê mới</a>
          </div>
        </div>
        <div class="panel-body">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th>Mã Phiếu</th>
                <th>Ngày kiểm kê</th>
                <th>Kho hàng</th>
                <th>Người tạo</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-center" style="width: 100px;">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($stocktakes as $st):?>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td><?php echo remove_junk($st['code']); ?></td>
                <td><?php echo read_date($st['stocktake_date']); ?></td>
                <td><?php echo remove_junk($st['warehouse_name']); ?></td>
                <td><?php echo remove_junk($st['user_name']); ?></td>
                <td class="text-center">
                  <?php if($st['status'] === 'completed'): ?>
                    <span class="label label-success">Hoàn thành</span>
                  <?php else: ?>
                    <span class="label label-warning">Đang xử lý</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="btn-group">
                    <a href="view_stocktake.php?id=<?php echo (int)$st['id'];?>" class="btn btn-info btn-xs"  title="Xem chi tiết" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                    <?php // Chỉ Quản lý (level 1) mới được quyền xóa ?>
                    <?php if($user['user_level'] <= 1): ?>
                    <a href="delete_stocktake.php?id=<?php echo (int)$st['id'];?>" class="btn btn-danger btn-xs"  title="Xóa" data-toggle="tooltip" onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu này?');">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
             <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
<?php include_once('layouts/footer.php'); ?>