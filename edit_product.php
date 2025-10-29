<?php
$page_title = 'Sửa Sản phẩm/NVL';
require_once('includes/load.php');
page_require_level(2);

// Lấy thông tin từ URL
$item_id   = (int)$_GET['id'];
$item_type = $_GET['type'];

// Kiểm tra tính hợp lệ của type
if ($item_type !== 'product' && $item_type !== 'raw_material') {
    $session->msg("d", "Loại sản phẩm không hợp lệ.");
    redirect('products.php');
}

// Lấy thông tin chi tiết của item
$item = find_item_by_id_and_type($item_id, $item_type);
if (!$item) {
    $session->msg("d", "Không tìm thấy ID sản phẩm.");
    redirect('products.php');
}

// Lấy danh sách danh mục và hình ảnh
$all_categories = find_all_categories();
$all_photo = find_all('media');

if (isset($_POST['update_product'])) {
    $req_fields = array('product-title', 'product-categorie', 'product-sku', 'product-unit');
    validate_fields($req_fields);

    // Nếu là Thành phẩm, giá bán là bắt buộc
    if ($item_type === 'product') {
        $req_fields[] = 'saleing-price';
        validate_fields($req_fields);
    }

    if (empty($errors)) {
        $p_name  = remove_junk($db->escape($_POST['product-title']));
        $p_cat   = (int)$_POST['product-categorie'];
        $p_sku   = remove_junk($db->escape($_POST['product-sku']));
        $p_unit  = remove_junk($db->escape($_POST['product-unit']));
        $p_media = (int)$_POST['product-photo'];
        
        $p_low   = remove_junk($db->escape($_POST['low-stock']));
        $p_high  = remove_junk($db->escape($_POST['high-stock']));

        if ($item_type === 'product') {
            $p_sale  = remove_junk($db->escape($_POST['saleing-price']));
            $sql = "UPDATE products SET name='{$p_name}', sku='{$p_sku}', unit='{$p_unit}', sale_price='{$p_sale}', categorie_id='{$p_cat}', media_id='{$p_media}', low_stock_level='{$p_low}', high_stock_level='{$p_high}' WHERE id='{$item['id']}'";
        } else {
            $sql = "UPDATE raw_materials SET name='{$p_name}', sku='{$p_sku}', unit='{$p_unit}', categorie_id='{$p_cat}', media_id='{$p_media}', low_stock_level='{$p_low}', high_stock_level='{$p_high}' WHERE id='{$item['id']}'";
        }
        
        $result = $db->query($sql);
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Cập nhật thành công!");
            redirect('products.php', false);
        } else {
            $session->msg('d', 'Lỗi: Không thể cập nhật hoặc không có gì thay đổi.');
            redirect('edit_product.php?id=' . $item['id'] . '&type=' . $item_type, false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_product.php?id=' . $item['id'] . '&type=' . $item_type, false);
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
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-edit"></span>
                    <span>Sửa: <?php echo remove_junk($item['name']); ?></span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="edit_product.php?id=<?php echo $item['id']; ?>&type=<?php echo $item_type; ?>" class="clearfix">
                    
                    <div class="form-group">
                        <label>Loại mặt hàng: <strong><?php echo ($item_type === 'product') ? 'Thành phẩm' : 'Nguyên vật liệu'; ?></strong></label>
                    </div>

                    <div class="form-group">
                        <label for="product-title">Tên mặt hàng</label>
                        <input type="text" class="form-control" name="product-title" value="<?php echo remove_junk($item['name']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="product-categorie">Danh mục</label>
                                <select class="form-control" name="product-categorie">
                                  <option value="">Chọn danh mục</option>
                                <?php  foreach ($all_categories as $cat): ?>
                                  <option value="<?php echo (int)$cat['id']; ?>" <?php if($item['categorie_id'] === $cat['id']): echo "selected"; endif; ?> >
                                    <?php echo remove_junk($cat['name']); ?></option>
                                <?php endforeach; ?>
                                </select>
                             </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group">
                               <label for="product-photo">Hình ảnh</label>
                               <select class="form-control" name="product-photo">
                                 <option value="0">Không có ảnh</option>
                               <?php  foreach ($all_photo as $photo): ?>
                                 <option value="<?php echo (int)$photo['id'];?>" <?php if($item['media_id'] === $photo['id']): echo "selected"; endif; ?> >
                                   <?php echo $photo['file_name'] ?></option>
                               <?php endforeach; ?>
                               </select>
                             </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-unit">Đơn vị tính</label>
                                <input type="text" class="form-control" name="product-unit" value="<?php echo remove_junk($item['unit']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                       <div class="row">
                         <div class="col-md-4">
                            <label for="product-sku">Mã SKU</label>
                            <input type="text" class="form-control" name="product-sku" value="<?php echo remove_junk($item['sku']); ?>" required>
                         </div>
                         <?php if ($item_type === 'product'): ?>
                         <div class="col-md-4" id="sale-price-group">
                           <label for="saleing-price">Giá bán</label>
                           <div class="input-group">
                             <span class="input-group-addon">
                              <i class="glyphicon glyphicon-usd"></i>
                             </span>
                             <input type="number" class="form-control" name="saleing-price" value="<?php echo remove_junk($item['sale_price']);?>">
                          </div>
                         </div>
                         <?php endif; ?>
                       </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="low-stock">Định mức tồn kho tối thiểu</label>
                                <input type="number" class="form-control" name="low-stock" value="<?php echo remove_junk($item['low_stock_level']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="high-stock">Định mức tồn kho tối đa</label>
                                <input type="number" class="form-control" name="high-stock" value="<?php echo remove_junk($item['high_stock_level']); ?>">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_product" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once('layouts/footer.php'); ?>