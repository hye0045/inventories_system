<?php
require_once('includes/load.php');
page_require_level(2);

$item_id   = (int)$_GET['id'];
$item_type = $_GET['type'];

if ($item_type !== 'product' && $item_type !== 'raw_material') {
    $session->msg("d", "Loại sản phẩm không hợp lệ.");
    redirect('products.php');
}

$table_name = ($item_type === 'product') ? 'products' : 'raw_materials';

if (delete_by_id($table_name, $item_id)) {
    $session->msg("s", "Xóa thành công.");
    redirect('products.php');
} else {
    $session->msg("d", "Lỗi: Không thể xóa.");
    redirect('products.php');
}
?>