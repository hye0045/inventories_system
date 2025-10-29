<?php
  $page_title = 'Tất cả Danh mục';
  require_once('includes/load.php');
  // Kiểm tra quyền truy cập
  page_require_level(1);
  
  $all_categories = find_all_categories();
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
</div>
<div class="row">
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-indent-left"></span>
          <span>Tất cả Danh mục</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Tên Danh mục</th>
              <th class="text-center" style="width: 100px;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_categories as $key => $cat):?>
            <tr>
              <td class="text-center"><?php echo count_id();?></td>
              <td><?php echo remove_junk(ucfirst($cat['name'])); ?></td>
              <td class="text-center">
                <div class="btn-group">
                  <a href="edit_categories.php?id=<?php echo (int)$cat['id'];?>"  class="btn btn-xs btn-warning" data-toggle="tooltip" title="Sửa">
                    <span class="glyphicon glyphicon-edit"></span>
                  </a>
                  <a href="delete_categorie.php?id=<?php echo (int)$cat['id'];?>"  class="btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                    <span class="glyphicon glyphicon-trash"></span>
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
        <strong>
          <span class="glyphicon glyphicon-plus"></span>
          <span>Thêm Danh mục mới</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="add_categories.php">
          <div class="form-group">
            <input type="text" class="form-control" name="categorie-name" placeholder="Tên danh mục" required>
          </div>
          <button type="submit" name="add_cat" class="btn btn-primary">Thêm Danh mục</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>