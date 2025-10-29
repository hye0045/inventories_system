<?php
  $page_title = 'Quản lý Vị trí kho';
  require_once('includes/load.php');
  page_require_level(1);
  
  $all_locations = find_all_locations();
  $all_warehouses = find_all_warehouses(); // Cần để hiển thị trong form
?>
<?php
if(isset($_POST['add_loc'])){
   $req_fields = array('warehouse-id', 'location-code');
   validate_fields($req_fields);
   if(empty($errors)){
     $l_wh_id = (int)$_POST['warehouse-id'];
     $l_code  = remove_junk($db->escape($_POST['location-code']));
     $l_area  = remove_junk($db->escape($_POST['area-name']));
     $l_shelf = remove_junk($db->escape($_POST['shelf']));
     
     $sql  = "INSERT INTO warehouse_locations (warehouse_id, location_code, area_name, shelf)";
     $sql .= " VALUES ('{$l_wh_id}', '{$l_code}', '{$l_area}', '{$l_shelf}')";

     if($db->query($sql)){
       $session->msg("s", "Thêm vị trí thành công.");
       redirect('locations.php',false);
     } else {
       $session->msg("d", "Lỗi: Không thể thêm vị trí.");
       redirect('locations.php',false);
     }
   } else {
     $session->msg("d", $errors);
     redirect('locations.php',false);
   }
}
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
   <div class="col-md-12"><?php echo display_msg($msg); ?></div>
</div>
<div class="row">
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-map-marker"></span><span>Tất cả Vị trí</span></strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Kho</th>
              <th>Mã vị trí</th>
              <th>Khu vực</th>
              <th>Kệ</th>
              <th class="text-center" style="width: 100px;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_locations as $loc):?>
            <tr>
              <td class="text-center"><?php echo count_id();?></td>
              <td><?php echo remove_junk($loc['warehouse_name']); ?></td>
              <td><?php echo remove_junk($loc['location_code']); ?></td>
              <td><?php echo remove_junk($loc['area_name']); ?></td>
              <td><?php echo remove_junk($loc['shelf']); ?></td>
              <td class="text-center">
                <div class="btn-group">
                  <a href="edit_location.php?id=<?php echo (int)$loc['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Sửa">
                    <i class="glyphicon glyphicon-edit"></i>
                  </a>
                  <a href="delete_location.php?id=<?php echo (int)$loc['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa vị trí này?');">
                    <i class="glyphicon glyphicon-trash"></i>
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
        <strong><span class="glyphicon glyphicon-plus"></span><span>Thêm Vị trí mới</span></strong>
      </div>
      <div class="panel-body">
        <form method="post" action="locations.php">
          <div class="form-group">
            <label for="warehouse-id">Chọn Kho</label>
            <select class="form-control" name="warehouse-id" required>
              <option value="">-- Chọn Kho --</option>
              <?php foreach ($all_warehouses as $wh): ?>
              <option value="<?php echo (int)$wh['id']; ?>"><?php echo remove_junk($wh['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
              <label for="location-code">Mã vị trí (VD: A-01-01)</label>
              <input type="text" class="form-control" name="location-code" required>
          </div>
           <div class="form-group">
              <label for="area-name">Tên Khu vực (Tùy chọn)</label>
              <input type="text" class="form-control" name="area-name">
          </div>
           <div class="form-group">
              <label for="shelf">Tên Kệ (Tùy chọn)</label>
              <input type="text" class="form-control" name="shelf">
          </div>
          <button type="submit" name="add_loc" class="btn btn-primary">Thêm Vị trí</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>