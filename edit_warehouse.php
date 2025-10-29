<?php
  $page_title = 'Sửa Kho hàng';
  require_once('includes/load.php');
  page_require_level(1);

  $warehouse = find_warehouse_by_id((int)$_GET['id']);
  if(!$warehouse){
    $session->msg("d","Không tìm thấy ID kho hàng.");
    redirect('warehouses.php');
  }

  if(isset($_POST['edit_wh'])){
    $req_fields = array('warehouse-name', 'warehouse-location');
    validate_fields($req_fields);
    if(empty($errors)){
      $w_name = remove_junk($_POST['warehouse-name']);
      $w_loc = remove_junk($_POST['warehouse-location']);
      
      $sql = "UPDATE warehouses SET name = '{$w_name}', location = '{$w_loc}' WHERE id = '{$warehouse['id']}'";
      $result = $db->query($sql);
      if($result && $db->affected_rows() === 1){
        $session->msg("s", "Cập nhật kho hàng thành công.");
        redirect('warehouses.php',false);
      } else {
        $session->msg("d", "Lỗi: Không thể cập nhật.");
        redirect('edit_warehouse.php?id='.$warehouse['id'], false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_warehouse.php?id='.$warehouse['id'], false);
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
          <span>Sửa kho hàng: <?php echo remove_junk($warehouse['name']); ?></span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="edit_warehouse.php?id=<?php echo (int)$warehouse['id'];?>">
          <div class="form-group">
              <label for="warehouse-name">Tên Kho hàng</label>
              <input type="text" class="form-control" name="warehouse-name" value="<?php echo remove_junk($warehouse['name']); ?>" required>
          </div>
          <div class="form-group">
              <label for="warehouse-location">Vị trí/Địa chỉ</label>
              <input type="text" class="form-control" name="warehouse-location" value="<?php echo remove_junk($warehouse['location']); ?>" required>
          </div>
          <button type="submit" name="edit_wh" class="btn btn-primary">Cập nhật</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>