<<<<<<< HEAD
<?php
  require_once('includes/load.php');
  page_require_level(1);

  if(isset($_POST['add_cat'])){
    $req_field = array('categorie-name');
    validate_fields($req_field);
    $cat_name = remove_junk($_POST['categorie-name']);
    if(empty($errors)){
        if(create_categorie($cat_name)){
           $session->msg("s", "Tạo danh mục thành công.");
           redirect('categories.php',false);
        } else {
           $session->msg("d", "Lỗi: Không thể tạo danh mục.");
           redirect('categories.php',false);
        }
    } else {
      $session->msg("d", $errors);
      redirect('categories.php',false);
    }
  }
=======
<?php
  require_once('includes/load.php');
  page_require_level(1);

  if(isset($_POST['add_cat'])){
    $req_field = array('categorie-name');
    validate_fields($req_field);
    $cat_name = remove_junk($_POST['categorie-name']);
    if(empty($errors)){
        if(create_categorie($cat_name)){
           $session->msg("s", "Tạo danh mục thành công.");
           redirect('categories.php',false);
        } else {
           $session->msg("d", "Lỗi: Không thể tạo danh mục.");
           redirect('categories.php',false);
        }
    } else {
      $session->msg("d", $errors);
      redirect('categories.php',false);
    }
  }
>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
?>