<?php
  require_once('includes/load.php');
  page_require_level(1);

  $categorie = find_categorie_by_id((int)$_GET['id']);
  if(!$categorie){
    $session->msg("d","Không tìm thấy ID danh mục.");
    redirect('categories.php');
  }

  if(delete_categorie_by_id((int)$categorie['id'])){
      $session->msg("s","Xóa danh mục thành công.");
      redirect('categories.php');
  } else {
      $session->msg("d","Lỗi: Không thể xóa danh mục.");
      redirect('categories.php');
  }
?>