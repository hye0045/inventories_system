<?php
  require_once('includes/load.php');
  page_require_level(1);

  $warehouse = find_warehouse_by_id((int)$_GET['id']);
  if(!$warehouse){
    $session->msg("d","Không tìm thấy ID kho hàng.");
    redirect('warehouses.php');
  }

  if(delete_by_id('warehouses', (int)$warehouse['id'])){
      $session->msg("s","Xóa kho hàng thành công.");
      redirect('warehouses.php');
  } else {
      $session->msg("d","Lỗi: Không thể xóa kho hàng.");
      redirect('warehouses.php');
  }
?>