<?php
require_once('includes/load.php');
page_require_level(2);

// Chỉ xử lý các yêu cầu AJAX
if (isset($_POST['action']) && $_POST['action'] == 'get_items_for_stocktake') {
    if (!isset($_POST['warehouse_id']) || empty($_POST['warehouse_id'])) {
        echo '<p class="text-center text-danger">ID kho không hợp lệ.</p>';
        exit;
    }

    $warehouse_id = (int)$_POST['warehouse_id'];
    $items_in_warehouse = find_stock_details_in_warehouse($warehouse_id);

    if (empty($items_in_warehouse)) {
        echo '<p class="text-center text-muted">Không tìm thấy mặt hàng nào có tồn kho trong kho này.</p>';
        exit;
    }

    // Bắt đầu tạo bảng HTML
    $html = '<table class="table table-bordered">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th class="text-center" style="width: 50px;">#</th>';
    $html .= '<th>Tên Mặt Hàng</th>';
    $html .= '<th>Vị Trí (Location)</th>';
    $html .= '<th>Số Lô (Lot)</th>';
    $html .= '<th class="text-center" style="width: 15%;">Tồn kho Hệ thống</th>';
    $html .= '<th class="text-center" style="width: 15%;">Số lượng Thực tế</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $count = 1;
    foreach ($items_in_warehouse as $item) {
        $html .= '<tr>';
        $html .= '<td class="text-center">' . $count++ . '</td>';
        $html .= '<td>' . remove_junk($item['item_name']) . ' (' . remove_junk($item['sku']) . ')</td>';
        $html .= '<td>' . remove_junk($item['location_code']) . '</td>';
        $html .= '<td>' . ($item['lot_number'] ? remove_junk($item['lot_number']) : 'N/A') . '</td>';
        $html .= '<td class="text-center">' . (float)$item['quantity'] . ' ' . $item['unit'] . '</td>';
        
        // Input cho số lượng đếm được
        $html .= '<td>';
        $html .= '<input type="number" class="form-control text-right" name="quantity_counted[]" value="' . (float)$item['quantity'] . '" step="0.01" required>';
        // Các trường ẩn để gửi dữ liệu chi tiết
        $html .= '<input type="hidden" name="inventory_stock_id[]" value="' . (int)$item['inventory_stock_id'] . '">';
        $html .= '<input type="hidden" name="item_id[]" value="' . (int)$item['item_id'] . '">';
        $html .= '<input type="hidden" name="item_type[]" value="' . $item['item_type'] . '">';
        $html .= '<input type="hidden" name="location_id[]" value="' . (int)$item['location_id'] . '">';
        $html .= '<input type="hidden" name="lot_id[]" value="' . ($item['lot_id'] ? (int)$item['lot_id'] : '0') . '">';
        $html .= '<input type="hidden" name="quantity_expected[]" value="' . (float)$item['quantity'] . '">';
        $html .= '</td>';
        
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    echo $html;
}
// ===============================================================
// HÀNH ĐỘNG MỚI: Lấy chi tiết thông báo và đánh dấu đã đọc
// ===============================================================
if (isset($_POST['action']) && $_POST['action'] == 'get_notification_details') {
    header('Content-Type: application/json'); // Trả về dạng JSON

    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID thông báo không hợp lệ.']);
        exit;
    }

    $notification_id = (int)$_POST['id'];

    // Bắt đầu transaction
    $db->beginTransaction();

    try {
        // 1. Lấy thông tin chi tiết của thông báo
        $notification = find_by_id('notifications', $notification_id);

        if (!$notification) {
            throw new Exception("Không tìm thấy thông báo.");
        }

        // 2. Đánh dấu thông báo là đã đọc
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = '{$notification_id}'";
        if (!$db->query($sql)) {
            throw new Exception("Không thể cập nhật trạng thái thông báo.");
        }

        // 3. Commit transaction
        $db->commit();
        
        // Chuẩn bị dữ liệu trả về
        $response_data = [
            'id' => $notification['id'],
            'message' => $notification['message'],
            'link' => $notification['link'],
            'created_at' => $notification['created_at'],
            'formatted_date' => date('d-m-Y H:i:s', strtotime($notification['created_at']))
        ];

        echo json_encode(['success' => true, 'data' => $response_data]);

    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>