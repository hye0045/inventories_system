<?php
  $page_title = 'Sửa Danh mục';
  require_once('includes/load.php');
  page_require_level(1);

  $categorie = find_categorie_by_id((int)$_GET['id']);
  if(!$categorie){
    $session->msg("d","Không tìm thấy ID danh mục.");
    redirect('categories.php');
  }

  if(isset($_POST['edit_cat'])){
    $req_field = array('categorie-name');
    validate_fields($req_field);
    $cat_name = remove_junk($_POST['categorie-name']);
    if(empty($errors)){
        if(update_categorie($cat_name, (int)$categorie['id'])){
          $session->msg("s", "Cập nhật danh mục thành công.");
          redirect('categories.php',false);
        } else {
          $session->msg("d", "Lỗi: Không thể cập nhật.");
          redirect('categories.php',false);
        }
    } else {
      $session->msg("d", $errors);
      redirect('categories.php',false);
    }
  }
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
  <div class="col-md-5">
      <div class="panel panel-default">
        <div class="panel-heading">
          <strong>
            <span class="glyphicon glyphicon-edit"></span>
            <span>Sửa danh mục "<?php echo remove_junk(ucfirst($categorie['name']));?>"</span>
          </strong>
        </div>
        <div class="panel-body">
          <form method="post" action="edit_categories.php?id=<?php echo (int)$categorie['id'];?>">
            <div class="form-group">
                <input type="text" class="form-control" name="categorie-name" value="<?php echo remove_junk(ucfirst($categorie['name']));?>">
            </div>
            <button type="submit" name="edit_cat" class="btn btn-primary">Cập nhật</button>
          </form>
        </div>
      </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
