<?php
  $page_title = 'All Purchase Orders';
  require_once('includes/load.php');
  page_require_level(2); // Chỉ Quản lý kho trở lên

  // Lấy tất cả phiếu nhập kho, JOIN với nhà cung cấp và người dùng
  $all_pos = $db->fetchAll(
      "SELECT po.*, s.name AS supplier_name, u.name AS user_name
       FROM purchase_orders po
       LEFT JOIN suppliers s ON po.supplier_id = s.id
       LEFT JOIN users u ON po.user_id = u.id
       ORDER BY po.order_date DESC"
  );
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="pull-left">
                    <strong><span class="glyphicon glyphicon-th"></span> Purchase Orders</strong>
                </div>
                <div class="pull-right">
                    <a href="add_purchase_order.php" class="btn btn-primary">Add New Purchase</a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Added by</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_pos as $po):?>
                        <tr>
                            <td><?php echo remove_junk($po['code']); ?></td>
                            <td><?php echo remove_junk($po['supplier_name']); ?></td>
                            <td><!-- Cần JOIN với bảng kho nếu cần --></td>
                            <td><?php echo remove_junk($po['order_date']); ?></td>
                            <td><?php echo remove_junk($po['status']); ?></td>
                            <td><?php echo remove_junk($po['user_name']); ?></td>
                            <td class="text-center">
                                <!-- Thêm các nút xem chi tiết, sửa, xóa sau -->
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