
<?php
require_once('includes/load.php');
require_once('includes/functions.php');
// page_require_level(2); // Bảo vệ trang

// --- PHẦN ĐIỀU HƯỚNG LOGIC (ROUTING) ---

// Kiểm tra xem có yêu cầu xóa không
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Gọi hàm logic từ functions.php
    if (delete_product_by_id($delete_id)) {
        $session->msg('s', "Sản phẩm đã được xóa thành công.");
    } else {
        $session->msg('d', "Xóa sản phẩm thất bại hoặc sản phẩm không tồn tại.");
    }
    redirect('product.php');
}

// --- PHẦN LẤY DỮ LIỆU ĐỂ HIỂN THỊ ---
$products = find_all_products();


// --- PHẦN HIỂN THỊ GIAO DIỆN (VIEW) ---
include_once('layouts/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="pull-right">
                    <a href="add_product.php" class="btn btn-primary">Thêm Thành Phẩm</a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <!-- Các tiêu đề cột -->
                            <th>Ảnh</th>
                            <th>Tên Thành Phẩm</th>
                            <th>SKU</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <!-- Hiển thị dữ liệu sản phẩm -->
                            <td>...</td>
                            <td><?php echo remove_junk($product['name']); ?></td>
                            <td><?php echo remove_junk($product['sku']); ?></td>
                            <td><?php echo remove_junk($product['category_name']); ?></td>
                            <td>...</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="edit_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-info btn-xs" title="Sửa">...</a>
                                    <!-- Link xóa giờ chỉ cần truyền delete_id -->
                                    <a href="product.php?delete_id=<?php echo (int)$product['id'];?>" class="btn btn-danger btn-xs" title="Xóa">...</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>