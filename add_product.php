<?php
$page_title = 'Thêm Sản phẩm/NVL';
require_once('includes/load.php');
page_require_level(2);

// Lấy danh sách tất cả danh mục và hình ảnh để người dùng chọn
$all_categories = find_all_categories();
$all_photo = find_all('media');
?>
<?php
if (isset($_POST['add_product'])) {
    // Các trường bắt buộc cho cả hai loại
    $req_fields = array('product-title', 'product-categorie', 'product-sku', 'product-unit');
    validate_fields($req_fields);

    // Lấy loại sản phẩm người dùng đã chọn
    $item_type = remove_junk($_POST['item-type']);
    
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
        
        $date    = make_date();

        // Xử lý dựa trên loại sản phẩm
        if ($item_type === 'product') {
            // Đây là Thành phẩm (product)
            $p_sale  = remove_junk($db->escape($_POST['saleing-price']));
            $sql  = "INSERT INTO products (name, sku, unit, sale_price, categorie_id, media_id, low_stock_level, high_stock_level, creat_at)";
            $sql .= " VALUES ('{$p_name}', '{$p_sku}', '{$p_unit}', '{$p_sale}', '{$p_cat}', '{$p_media}', '{$p_low}', '{$p_high}', '{$date}')";

        } elseif ($item_type === 'raw_material') {
            // Đây là Nguyên vật liệu (raw_material)
            $sql  = "INSERT INTO raw_materials (name, sku, unit, categorie_id, media_id, low_stock_level, high_stock_level, creat_at)";
            $sql .= " VALUES ('{$p_name}', '{$p_sku}', '{$p_unit}', '{$p_cat}', '{$p_media}', '{$p_low}', '{$p_high}', '{$date}')";
        }

        // Thực thi câu lệnh SQL 
        if ($db->query($sql)) {
            $session->msg('s', "Thêm mới thành công!");
            redirect('product.php', false);
        } else {
            $session->msg('d', 'Lỗi: Không thể thêm mới.');
            redirect('add_product.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_product.php', false);
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
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Thêm Sản phẩm/Nguyên vật liệu mới</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="add_product.php" class="clearfix">
                    <div class="form-group">
                        <label for="item-type">Loại mặt hàng</label>
                        <select class="form-control" name="item-type" id="item-type-select">
                            <option value="product">Thành phẩm</option>
                            <option value="raw_material">Nguyên vật liệu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product-title">Tên mặt hàng</label>
                        <input type="text" class="form-control" name="product-title" placeholder="Tên sản phẩm hoặc NVL" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-categorie">Danh mục</label>
                                <select class="form-control" name="product-categorie" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['id'] ?>">
                                            <?php echo $cat['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-photo">Hình ảnh</label>
                                <select class="form-control" name="product-photo">
                                    <option value="0">Không có ảnh</option>
                                    <?php foreach ($all_photo as $photo): ?>
                                        <option value="<?php echo (int)$photo['id'] ?>">
                                            <?php echo $photo['file_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-unit">Đơn vị tính</label>
                                <input type="text" class="form-control" name="product-unit" placeholder="VD: Cái, Kg, Thùng" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="product-sku">Mã SKU</label>
                                <input type="text" class="form-control" name="product-sku" placeholder="Mã SKU (phải là duy nhất)" required>
                            </div>
                            <div class="col-md-4" id="sale-price-group">
                                <label for="saleing-price">Giá bán</label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-usd"></i>
                                    </span>
                                    <input type="number" class="form-control" name="saleing-price" placeholder="Giá bán">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                     <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="low-stock">Định mức tồn kho tối thiểu</label>
                                <input type="number" class="form-control" name="low-stock" placeholder="VD: 10" value="0">
                            </div>
                            <div class="col-md-6">
                                <label for="high-stock">Định mức tồn kho tối đa</label>
                                <input type="number" class="form-control" name="high-stock" placeholder="VD: 100" value="0">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="add_product" class="btn btn-danger">Thêm mới</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Thêm đoạn Javascript này vào cuối file, trước footer -->
<script type="text/javascript">
document.getElementById('item-type-select').addEventListener('change', function() {
    var salePriceGroup = document.getElementById('sale-price-group');
    if (this.value === 'raw_material') {
        salePriceGroup.style.display = 'none'; // Ẩn giá bán nếu là NVL
    } else {
        salePriceGroup.style.display = 'block'; // Hiện giá bán nếu là Thành phẩm
    }
});
</script>

<?php include_once('layouts/footer.php'); ?>