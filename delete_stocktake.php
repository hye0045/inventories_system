<<<<<<< HEAD
<?php
  require_once('includes/load.php');
  // Yêu cầu quyền Quản lý (level 1)
  page_require_level(1);

  if(!isset($_GET['id'])){
    $session->msg("d","Thiếu ID phiếu kiểm kê.");
    redirect('stocktakes.php');
  }

  $stocktake_id = (int)$_GET['id'];
  $stocktake = find_stocktake_by_id($stocktake_id);
  if(!$stocktake){
    $session->msg("d","Không tìm thấy thông tin phiếu kiểm kê.");
    redirect('stocktakes.php');
  }
  
  // Hàm delete_stocktake_by_id đã được viết để xóa cả phiếu chính và các item con
  if(delete_stocktake_by_id($stocktake_id)){
      $session->msg("s","Xóa phiếu kiểm kê và các chi tiết liên quan thành công.");
      redirect('stocktakes.php');
  } else {
      $session->msg("d","Lỗi: Xóa phiếu kiểm kê thất bại.");
      redirect('stocktakes.php');
  }
=======
<?php
  require_once('includes/load.php');
  // Yêu cầu quyền Quản lý (level 1)
  page_require_level(1);

  if(!isset($_GET['id'])){
    $session->msg("d","Thiếu ID phiếu kiểm kê.");
    redirect('stocktakes.php');
  }

  $stocktake_id = (int)$_GET['id'];
  $stocktake = find_stocktake_by_id($stocktake_id);
  if(!$stocktake){
    $session->msg("d","Không tìm thấy thông tin phiếu kiểm kê.");
    redirect('stocktakes.php');
  }
  
  // Hàm delete_stocktake_by_id đã được viết để xóa cả phiếu chính và các item con
  if(delete_stocktake_by_id($stocktake_id)){
      $session->msg("s","Xóa phiếu kiểm kê và các chi tiết liên quan thành công.");
      redirect('stocktakes.php');
  } else {
      $session->msg("d","Lỗi: Xóa phiếu kiểm kê thất bại.");
      redirect('stocktakes.php');
  }
>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
?>