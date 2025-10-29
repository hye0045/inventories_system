<?php
require_once('includes/load.php');
// Yêu cầu quyền Quản lý (level 1)
page_require_level(1);

if(!isset($_GET['id'])){
    // Không có ID, không làm gì cả
    redirect('stocktakes.php');
}

$stocktake_id = (int)$_GET['id'];
$stocktake = find_stocktake_by_id($stocktake_id);
if(!$stocktake){
    redirect('stocktakes.php');
}

$stocktake_items = find_stocktake_items_by_id($stocktake_id);

// Tạo tên file
$filename = "Phieu_Kiem_Ke_" . $stocktake['code'] . ".csv";

// Thiết lập HTTP Headers để trình duyệt hiểu đây là một file tải về
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Mở một "file" tạm trong bộ nhớ để ghi dữ liệu
$output = fopen('php://output', 'w');

// Ghi UTF-8 BOM để Excel hiển thị tiếng Việt đúng
fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

// Ghi các thông tin chung của phiếu
fputcsv($output, ['THONG TIN PHIEU KIEM KE']);
fputcsv($output, ['Ma Phieu', $stocktake['code']]);
fputcsv($output, ['Ngay Kiem Ke', $stocktake['stocktake_date']]);
fputcsv($output, ['Kho Hang', $stocktake['warehouse_name']]);
fputcsv($output, ['Nguoi Tao', $stocktake['user_name']]);
fputcsv($output, []); // Dòng trống

// Ghi tiêu đề của bảng chi tiết
fputcsv($output, [
    'Ten San Pham/NVL', 
    'Ma SKU', 
    'Don Vi',
    'Ton Kho He Thong', 
    'So Luong Thuc Te', 
    'Chenh Lech'
]);

// Ghi dữ liệu từng dòng
if(!empty($stocktake_items)){
    foreach ($stocktake_items as $item) {
        $row = [
            $item['item_name'],
            $item['sku'],
            $item['unit'],
            (float)$item['quantity_expected'],
            (float)$item['quantity_counted'],
            (float)$item['variance']
        ];
        fputcsv($output, $row);
    }
}

// Đóng file
fclose($output);
exit;

?>