<?php
require_once('includes/load.php');
// page_require_level(2);

// --- PHẦN LOGIC: Lấy dữ liệu cho form ---
$all_categories = find_all('categories');
$all_photo = find_all('media');

// --- PHẦN LOGIC: Xử lý khi form được submit ---
if (isset($_POST['add_product'])) {
    $req_fields = array('name', 'sku', 'sale_price', 'categorie_id');
    validate_fields($req_fields); // Hàm validate có sẵn trong project

    if (empty($errors)) {
        $p_name = remove_junk($_POST['name']);
        $p_sku = remove_junk($_POST['sku']);
        $p_sale = remove_junk($_POST['sale_price']);
        $p_cat = (int)$_POST['categorie_id'];
        $p_media = (int)$_POST['media_id'];
        
        $sql = "INSERT INTO products (name, sku, sale_price, categorie_id, media_id) VALUES (?, ?, ?, ?, ?)";
        $params = [$p_name, $p_sku, $p_sale, $p_cat, $p_media];

        try {
            $db->query($sql, $params);
            $session->msg('s', "Thành phẩm đã được thêm thành công.");
            redirect('products.php', false);
        } catch (Exception $e) {
            $session->msg('d', 'Thêm thành phẩm thất bại! Lỗi: ' . $e->getMessage());
            redirect('add_product.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_product.php', false);
    }
}
// --- PHẦN GIAO DIỆN (VIEW) ---
include_once('layouts/header.php');
?>
<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><span class="glyphicon glyphicon-th"></span><span>Thêm Thành Phẩm Mới</span></strong>
            </div>
            <div class="panel-body">
                <form method="post" action="add_product.php">
                    <div class="form-group">
                        <input type="text" class="form-control" name="name" placeholder="Tên thành phẩm">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                           <input type="text" class="form-control" name="sku" placeholder="SKU">
                        </div>
                        <div class="col-md-4">
                            <input type="number" class="form-control" name="sale_price" placeholder="Giá bán">
                        </div>
                         <div class="col-md-4">
                            <select class="form-control" name="categorie_id">
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo (int)$cat['id'] ?>"><?php echo $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                     <div class="form-group" style="margin-top: 1em;">
                        <select class="form-control" name="media_id">
                            <option value="">Chọn ảnh</option>
                            <?php foreach ($all_photo as $photo): ?>
                            <option value="<?php echo (int)$photo['id'] ?>"><?php echo $photo['file_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-success">Thêm Thành Phẩm</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once('layouts/footer.php'); ?>