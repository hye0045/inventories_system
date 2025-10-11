<?php
$page_title = 'Add Sale';
require_once('includes/load.php');
page_require_level(3);

// --- PHẦN LOGIC XỬ LÝ FORM (Controller) ---
if(isset($_POST['add_sale'])){
    $req_fields = array('warehouse-id', 'order-date', 'product-id', 'quantity', 'price');
    validate_fields($req_fields);

    if(empty($errors)){
        // Dữ liệu chung
        $p_warehouse_id = (int)$_POST['warehouse-id'];
        $p_order_date   = $_POST['order-date'];
        $p_user_id      = (int)$_SESSION['user_id'];
        
        // Dữ liệu chi tiết
        $p_product_ids = $_POST['product-id'];
        $p_quantities  = $_POST['quantity'];
        $p_prices      = $_POST['price'];

        $db->beginTransaction();
        try {
            // Bước kiểm tra tồn kho trước khi thực hiện
            foreach($p_product_ids as $index => $product_id) {
                $required_qty = (int)$p_quantities[$index];
                $current_stock_result = $db->fetchOne(
                    "SELECT SUM(quantity) as stock FROM stock_movements WHERE item_id = ? AND item_type = 'product' AND warehouse_id = ?",
                    [$product_id, $p_warehouse_id]
                );
                $current_stock = (int)($current_stock_result['stock'] ?? 0);

                if ($current_stock < $required_qty) {
                    throw new Exception("Not enough stock for product ID {$product_id}. Required: {$required_qty}, Available: {$current_stock}");
                }
            }

            // Nếu tồn kho đủ, bắt đầu tạo phiếu
            // 1. Tạo phiếu sales_orders
            $sale_code = 'PXK' . date('YmdHis'); // Tự tạo mã phiếu
            $sql_so = "INSERT INTO sales_orders (code, warehouse_id, order_date, user_id) VALUES (?, ?, ?, ?)";
            $db->query($sql_so, [$sale_code, $p_warehouse_id, $p_order_date, $p_user_id]);
            $sales_order_id = $db->lastInsertId();

            // 2. Chèn chi tiết và cập nhật stock_movements
            foreach ($p_product_ids as $index => $product_id) {
                $quantity = (int)$p_quantities[$index];
                $price = (float)$p_prices[$index];

                // Chèn vào sales_order_items
                $sql_items = "INSERT INTO sales_order_items (sales_order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $db->query($sql_items, [$sales_order_id, $product_id, $quantity, $price]);

                // Chèn vào stock_movements (XUẤT -> SỐ LƯỢNG ÂM)
                $sql_stock = "INSERT INTO stock_movements (item_id, item_type, warehouse_id, quantity, transaction_type, reference_id, transaction_date, user_id) VALUES (?, 'product', ?, ?, 'sale', ?, NOW(), ?)";
                $db->query($sql_stock, [$product_id, $p_warehouse_id, -$quantity, $sales_order_id, $p_user_id]);
            }

            $db->commit();
            $session->msg('s', "Sale added successfully.");
            redirect('add_sale.php', false);

        } catch (Exception $e) {
            $db->rollback();
            $session->msg('d', 'Sorry, failed to add sale: ' . $e->getMessage());
            redirect('add_sale.php', false);
        }

    } else {
       $session->msg("d", $errors);
       redirect('add_sale.php',false);
    }
}
?>
<!-- Phần HTML của bạn giữ nguyên -->