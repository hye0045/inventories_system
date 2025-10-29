<?php
  $page_title = 'Sửa Vị trí kho';
  require_once('includes/load.php');
  page_require_level(1);

  // Lấy thông tin vị trí kho cần sửa
  $location = find_location_by_id((int)$_GET['id']);
  if(!$location){
    $session->msg("d", "Không tìm thấy ID vị trí kho.");
    redirect('locations.php');
  }

  // Lấy danh sách tất cả các kho hàng để hiển thị trong dropdown
  $all_warehouses = find_all_warehouses();

  // Xử lý khi người dùng nhấn nút Cập nhật
  if(isset($_POST['update_loc'])){
    $req_fields = array('warehouse-id', 'location-code');
    validate_fields($req_fields);
    
    if(empty($errors)){
       $l_wh_id = (int)$_POST['warehouse-id'];
       $l_code  = remove_junk($db->escape($_POST['location-code']));
       $l_area  = remove_junk($db->escape($_POST['area-name']));
       $l_shelf = remove_junk($db->escape($_POST['shelf']));
      
       $sql  = "UPDATE warehouse_locations SET ";
       $sql .= " warehouse_id = '{$l_wh_id}',";
       $sql .= " location_code = '{$l_code}',";
       $sql .= " area_name = '{$l_area}',";
       $sql .= " shelf = '{$l_shelf}'";
       $sql .= " WHERE id ='{$location['id']}'";

       $result = $db->query($sql);
       if($result && $db->affected_rows() === 1){
         $session->msg("s", "Cập nhật vị trí kho thành công.");
         redirect('locations.php', false);
       } else {
         $session->msg("d", "Lỗi: Không thể cập nhật hoặc không có gì thay đổi.");
         redirect('edit_location.php?id='.$location['id'], false);
       }
    } else {
      $session->msg("d", $errors);
      redirect('edit_location.php?id='.$location['id'], false);
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
          <span>Sửa vị trí kho: <?php echo remove_junk($location['location_code']); ?></span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="edit_location.php?id=<?php echo (int)$location['id'];?>">
          <div class="form-group">
            <label for="warehouse-id">Chọn Kho</label>
            <select class="form-control" name="warehouse-id" required>
              <option value="">-- Chọn Kho --</option>
              <?php foreach ($all_warehouses as $wh): ?>
              <option value="<?php echo (int)$wh['id']; ?>" <?php if($location['warehouse_id'] === $wh['id']): echo "selected"; endif; ?>>
                <?php echo remove_junk($wh['name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
              <label for="location-code">Mã vị trí (VD: A-01-01)</label>
              <input type="text" class="form-control" name="location-code" value="<?php echo remove_junk($location['location_code']); ?>" required>
          </div>
           <div class="form-group">
              <label for="area-name">Tên Khu vực (Tùy chọn)</label>
              <input type="text" class="form-control" name="area-name" value="<?php echo remove_junk($location['area_name']); ?>">
          </div>
           <div class="form-group">
              <label for="shelf">Tên Kệ (Tùy chọn)</label>
              <input type="text" class="form-control" name="shelf" value="<?php echo remove_junk($location['shelf']); ?>">
          </div>
          <button type="submit" name="update_loc" class="btn btn-primary">Cập nhật Vị trí</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>