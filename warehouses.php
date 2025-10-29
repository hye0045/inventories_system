<?php
  $page_title = 'Tất cả Kho hàng';
  require_once('includes/load.php');
  page_require_level(1);
  
  $all_warehouses = find_all_warehouses();
?>
<?php
// Xử lý logic thêm và sửa kho hàng ngay tại file này
if(isset($_POST['add_wh'])){
   $req_fields = array('warehouse-name', 'warehouse-location');
   validate_fields($req_fields);
   if(empty($errors)){
     $w_name = remove_junk($_POST['warehouse-name']);
     $w_loc = remove_junk($_POST['warehouse-location']);
     
     $sql  = "INSERT INTO warehouses (name, location)";
     $sql .= " VALUES ('{$w_name}', '{$w_loc}')";
     if($db->query($sql)){
       $session->msg("s", "Thêm kho hàng thành công.");
       redirect('warehouses.php',false);
     } else {
       $session->msg("d", "Lỗi: Không thể thêm kho hàng.");
       redirect('warehouses.php',false);
     }
   } else {
     $session->msg("d", $errors);
     redirect('warehouses.php',false);
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
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-home"></span>
          <span>Tất cả Kho hàng</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Tên Kho hàng</th>
              <th>Vị trí/Địa chỉ</th>
              <th class="text-center" style="width: 100px;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_warehouses as $wh):?>
            <tr>
              <td class="text-center"><?php echo count_id();?></td>
              <td><?php echo remove_junk($wh['name']); ?></td>
              <td><?php echo remove_junk($wh['location']); ?></td>
              <td class="text-center">
                <div class="btn-group">
                  <a href="edit_warehouse.php?id=<?php echo (int)$wh['id'];?>"  class="btn btn-xs btn-warning" data-toggle="tooltip" title="Sửa">
                    <span class="glyphicon glyphicon-edit"></span>
                  </a>
                  <a href="delete_warehouse.php?id=<?php echo (int)$wh['id'];?>"  class="btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa kho hàng này?');">
                    <span class="glyphicon glyphicon-trash"></span>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-plus"></span>
          <span>Thêm Kho hàng mới</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="warehouses.php">
          <div class="form-group">
              <label for="warehouse-name">Tên Kho hàng</label>
              <input type="text" class="form-control" name="warehouse-name" placeholder="VD: Kho chính, Kho Hà Nội" required>
          </div>
          <div class="form-group">
              <label for="warehouse-location">Vị trí/Địa chỉ</label>
              <input type="text" class="form-control" name="warehouse-location" placeholder="Địa chỉ chi tiết của kho" required>
          </div>
          <button type="submit" name="add_wh" class="btn btn-primary">Thêm Kho hàng</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>