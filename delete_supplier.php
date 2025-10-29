<<<<<<< HEAD
<?php
  require_once('includes/load.php');
  page_require_level(1);

  $supplier = find_supplier_by_id((int)$_GET['id']);
  if(!$supplier){
    $session->msg("d","Không tìm thấy ID nhà cung cấp.");
    redirect('suppliers.php');
  }

  if(delete_by_id('suppliers', (int)$supplier['id'])){
      $session->msg("s","Xóa nhà cung cấp thành công.");
      redirect('suppliers.php');
  } else {
      $session->msg("d","Lỗi: Không thể xóa nhà cung cấp.");
      redirect('suppliers.php');
  }
=======
<?php
  require_once('includes/load.php');
  page_require_level(1);

  $supplier = find_supplier_by_id((int)$_GET['id']);
  if(!$supplier){
    $session->msg("d","Không tìm thấy ID nhà cung cấp.");
    redirect('suppliers.php');
  }

  if(delete_by_id('suppliers', (int)$supplier['id'])){
      $session->msg("s","Xóa nhà cung cấp thành công.");
      redirect('suppliers.php');
  } else {
      $session->msg("d","Lỗi: Không thể xóa nhà cung cấp.");
      redirect('suppliers.php');
  }
>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
?>