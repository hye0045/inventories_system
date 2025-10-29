<<<<<<< HEAD
<?php
  $page_title = 'Sửa Nhà cung cấp';
  require_once('includes/load.php');
  page_require_level(1);

  $supplier = find_supplier_by_id((int)$_GET['id']);
  if(!$supplier){
    $session->msg("d","Không tìm thấy ID nhà cung cấp.");
    redirect('suppliers.php');
  }

  if(isset($_POST['edit_sup'])){
    $req_fields = array('supplier-name', 'supplier-phone');
    validate_fields($req_fields);
    if(empty($errors)){
      $s_name = remove_junk($_POST['supplier-name']);
      $s_phone = remove_junk($_POST['supplier-phone']);
      
      $sql = "UPDATE suppliers SET name = '{$s_name}', phone = '{$s_phone}' WHERE id = '{$supplier['id']}'";
      $result = $db->query($sql);
      if($result && $db->affected_rows() === 1){
        $session->msg("s", "Cập nhật thành công.");
        redirect('suppliers.php',false);
      } else {
        $session->msg("d", "Lỗi: Không thể cập nhật.");
        redirect('edit_supplier.php?id='.$supplier['id'], false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_supplier.php?id='.$supplier['id'], false);
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
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-edit"></span>
          <span>Sửa nhà cung cấp: <?php echo remove_junk($supplier['name']); ?></span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="edit_supplier.php?id=<?php echo (int)$supplier['id'];?>">
          <div class="form-group">
              <label for="supplier-name">Tên Nhà cung cấp</label>
              <input type="text" class="form-control" name="supplier-name" value="<?php echo remove_junk($supplier['name']); ?>" required>
          </div>
          <div class="form-group">
              <label for="supplier-phone">Số điện thoại</label>
              <input type="text" class="form-control" name="supplier-phone" value="<?php echo remove_junk($supplier['phone']); ?>" required>
          </div>
          <button type="submit" name="edit_sup" class="btn btn-primary">Cập nhật</button>
        </form>
      </div>
    </div>
  </div>
</div>

=======
<?php
  $page_title = 'Sửa Nhà cung cấp';
  require_once('includes/load.php');
  page_require_level(1);

  $supplier = find_supplier_by_id((int)$_GET['id']);
  if(!$supplier){
    $session->msg("d","Không tìm thấy ID nhà cung cấp.");
    redirect('suppliers.php');
  }

  if(isset($_POST['edit_sup'])){
    $req_fields = array('supplier-name', 'supplier-phone');
    validate_fields($req_fields);
    if(empty($errors)){
      $s_name = remove_junk($_POST['supplier-name']);
      $s_phone = remove_junk($_POST['supplier-phone']);
      
      $sql = "UPDATE suppliers SET name = '{$s_name}', phone = '{$s_phone}' WHERE id = '{$supplier['id']}'";
      $result = $db->query($sql);
      if($result && $db->affected_rows() === 1){
        $session->msg("s", "Cập nhật thành công.");
        redirect('suppliers.php',false);
      } else {
        $session->msg("d", "Lỗi: Không thể cập nhật.");
        redirect('edit_supplier.php?id='.$supplier['id'], false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_supplier.php?id='.$supplier['id'], false);
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
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-edit"></span>
          <span>Sửa nhà cung cấp: <?php echo remove_junk($supplier['name']); ?></span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="edit_supplier.php?id=<?php echo (int)$supplier['id'];?>">
          <div class="form-group">
              <label for="supplier-name">Tên Nhà cung cấp</label>
              <input type="text" class="form-control" name="supplier-name" value="<?php echo remove_junk($supplier['name']); ?>" required>
          </div>
          <div class="form-group">
              <label for="supplier-phone">Số điện thoại</label>
              <input type="text" class="form-control" name="supplier-phone" value="<?php echo remove_junk($supplier['phone']); ?>" required>
          </div>
          <button type="submit" name="edit_sup" class="btn btn-primary">Cập nhật</button>
        </form>
      </div>
    </div>
  </div>
</div>

>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
<?php include_once('layouts/footer.php'); ?>