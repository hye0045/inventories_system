<?php
  $page_title = 'Tất cả Nhà cung cấp';
  require_once('includes/load.php');
  page_require_level(1);
  
  $all_suppliers = find_all_suppliers();
?>
<?php
if(isset($_POST['add_sup'])){
   $req_fields = array('supplier-name', 'supplier-phone');
   validate_fields($req_fields);
   if(empty($errors)){
     $s_name = remove_junk($db->escape($_POST['supplier-name']));
     $s_phone = remove_junk($db->escape($_POST['supplier-phone']));
     
     $sql  = "INSERT INTO suppliers (name, phone)";
     $sql .= " VALUES ('{$s_name}', '{$s_phone}')";
     if($db->query($sql)){
       $session->msg("s", "Thêm nhà cung cấp thành công.");
       redirect('suppliers.php',false);
     } else {
       $session->msg("d", "Lỗi: Không thể thêm nhà cung cấp.");
       redirect('suppliers.php',false);
     }
   } else {
     $session->msg("d", $errors);
     redirect('suppliers.php',false);
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
          <span class="glyphicon glyphicon-user"></span>
          <span>Tất cả Nhà cung cấp</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Tên Nhà cung cấp</th>
              <th>Số điện thoại</th>
              <th class="text-center" style="width: 100px;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_suppliers as $sup):?>
            <tr>
              <td class="text-center"><?php echo count_id();?></td>
              <td><?php echo remove_junk($sup['name']); ?></td>
              <td><?php echo remove_junk($sup['phone']); ?></td>
              <td class="text-center">
                <div class="btn-group">
                  <a href="edit_supplier.php?id=<?php echo (int)$sup['id'];?>"  class="btn btn-xs btn-warning" data-toggle="tooltip" title="Sửa">
                    <span class="glyphicon glyphicon-edit"></span>
                  </a>
                  <a href="delete_supplier.php?id=<?php echo (int)$sup['id'];?>"  class="btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?');">
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
          <span>Thêm Nhà cung cấp mới</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="suppliers.php">
          <div class="form-group">
              <label for="supplier-name">Tên Nhà cung cấp</label>
              <input type="text" class="form-control" name="supplier-name" required>
          </div>
          <div class="form-group">
              <label for="supplier-phone">Số điện thoại</label>
              <input type="text" class="form-control" name="supplier-phone" required>
          </div>
          <button type="submit" name="add_sup" class="btn btn-primary">Thêm Nhà cung cấp</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>