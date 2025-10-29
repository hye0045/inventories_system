<?php
  require_once('includes/load.php');
  page_require_level(1);

  // 1. Kiểm tra xem ID có hợp lệ không
  if(!isset($_GET['id'])){
    $session->msg("d","Thiếu ID vị trí.");
    redirect('locations.php');
  }

  $location_id = (int)$_GET['id'];
  $location = find_location_by_id($location_id);
  if(!$location){
    $session->msg("d","Không tìm thấy ID vị trí kho.");
    redirect('locations.php');
  }

  // 2. KIỂM TRA QUAN TRỌNG: Vị trí có còn hàng tồn kho không?
  if(!is_location_empty($location_id)){
      $session->msg("d","Lỗi: Không thể xóa vị trí này vì vẫn còn hàng tồn kho. Vui lòng chuyển hết hàng đi trước khi xóa.");
      redirect('locations.php');
  }

  // 3. Nếu vị trí đã trống, tiến hành xóa
  if(delete_by_id('warehouse_locations', $location_id)){
      $session->msg("s","Xóa vị trí kho thành công.");
      redirect('locations.php');
  } else {
      $session->msg("d","Lỗi: Xóa vị trí kho thất bại.");
      redirect('locations.php');
  }
?>