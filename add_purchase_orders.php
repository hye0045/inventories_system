<?php
$page_title = 'Add Purchase Order';
require_once('includes/load.php');
page_require_level(2);

// Lấy dữ liệu cho form
$all_suppliers = find_all('suppliers');
$all_warehouses = find_all('warehouses');
$all_raw_materials = find_all('raw_materials');

// --- PHẦN LOGIC XỬ LÝ FORM (Controller) ---
if(isset($_POST['add_purchase_order'])){
    $req_fields = array('code', 'supplier-id', 'warehouse-id', 'order-date', 'material-id', 'quantity', 'price');
    validate_fields($req_fields);

    if(empty($errors)){
        $p_code         = $_POST['code'];
        $p_supplier_id  = (int)$_POST['supplier-id'];
        $p_warehouse_id = (int)$_POST['warehouse-id'];
        $p_order_date   = $_POST['order-date'];
        $p_status       = 'completed'; // Mặc định là hoàn thành
        $p_user_id      = (int)$_SESSION['user_id'];

        // Dữ liệu chi tiết sản phẩm
        $p_material_ids = $_POST['material-id'];
        $p_quantities   = $_POST['quantity'];
        $p_prices       = $_POST['price'];

        // BẮT ĐẦU TRANSACTION ĐỂ ĐẢM BẢO TOÀN VẸN DỮ LIỆU
        $db->beginTransaction();

        try {
            // 1. Chèn vào bảng `purchase_orders`
            $sql_po = "INSERT INTO purchase_orders (code, supplier_id, warehouse_id, order_date, status, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $db->query($sql_po, [$p_code, $p_supplier_id, $p_warehouse_id, $p_order_date, $p_status, $p_user_id]);
            $purchase_order_id = $db->lastInsertId();

            // 2. Lặp qua từng sản phẩm để chèn vào `purchase_order_items` và `stock_movements`
            foreach ($p_material_ids as $index => $material_id) {
                $quantity = (float)$p_quantities[$index];
                $price = (float)$p_prices[$index];

                // Chèn vào chi tiết phiếu nhập
                $sql_items = "INSERT INTO purchase_order_items (purchase_order_id, raw_material_id, quantity, price) VALUES (?, ?, ?, ?)";
                $db->query($sql_items, [$purchase_order_id, $material_id, $quantity, $price]);

                // Chèn vào lịch sử giao dịch kho (NHẬP -> SỐ LƯỢNG DƯƠNG)
                $sql_stock = "INSERT INTO stock_movements (item_id, item_type, warehouse_id, quantity, transaction_type, reference_id, transaction_date, user_id) VALUES (?, 'raw_material', ?, ?, 'purchase', ?, NOW(), ?)";
                $db->query($sql_stock, [$material_id, $p_warehouse_id, $quantity, $purchase_order_id, $p_user_id]);
            }

            // Nếu mọi thứ thành công, commit transaction
            $db->commit();
            $session->msg('s', "Purchase order added successfully.");
            redirect('purchase_orders.php', false);

        } catch (Exception $e) {
            // Nếu có lỗi, rollback tất cả thay đổi
            $db->rollback();
            $session->msg('d', 'Sorry, failed to add purchase order: ' . $e->getMessage());
            redirect('add_purchase_order.php', false);
        }

    } else {
        $session->msg("d", $errors);
        redirect('add_purchase_order.php', false);
    }
}
?>

<?php include_once('layouts/header.php'); ?>
<!-- PHẦN GIAO DIỆN (View) -->
<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><span class="glyphicon glyphicon-th"></span> Add New Purchase Order</strong>
            </div>
            <div class="panel-body">
                <form method="post" action="add_purchase_order.php">
                    <!-- Các trường thông tin chung của phiếu -->
                    <div class="row">
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="code">PO Code</label>
                               <input type="text" class="form-control" name="code" required>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="supplier-id">Supplier</label>
                               <select class="form-control" name="supplier-id" required>
                                   <option value="">Select a Supplier</option>
                                   <?php foreach ($all_suppliers as $sup): ?>
                                   <option value="<?php echo (int)$sup['id'] ?>"><?php echo $sup['name'] ?></option>
                                   <?php endforeach; ?>
                               </select>
                           </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="order-date">Order Date</label>
                                <input type="date" class="form-control" name="order-date" required>
                            </div>
                        </div>
                         <div class="col-md-4">
                           <div class="form-group">
                               <label for="warehouse-id">Warehouse</label>
                               <select class="form-control" name="warehouse-id" required>
                                   <option value="">Select a Warehouse</option>
                                   <?php foreach ($all_warehouses as $wh): ?>
                                   <option value="<?php echo (int)$wh['id'] ?>"><?php echo $wh['name'] ?></option>
                                   <?php endforeach; ?>
                               </select>
                           </div>
                        </div>
                    </div>

                    <!-- Bảng chi tiết sản phẩm -->
                    <table class="table table-bordered" id="items-table">
                        <thead>
                            <tr>
                                <th>Raw Material</th>
                                <th>Quantity</th>
                                <th>Price per Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dòng sản phẩm mẫu (sẽ được nhân bản bằng JavaScript) -->
                            <tr>
                                <td>
                                    <select class="form-control" name="material-id[]" required>
                                        <option value="">Select a Material</option>
                                        <?php foreach ($all_raw_materials as $mat): ?>
                                        <option value="<?php echo (int)$mat['id'] ?>"><?php echo $mat['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" class="form-control" name="quantity[]" step="0.01" required></td>
                                <td><input type="number" class="form-control" name="price[]" step="0.01" required></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-info" id="add-row">Add More Item</button>
                    <hr>
                    <button type="submit" name="add_purchase_order" class="btn btn-primary">Add Purchase Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript để thêm/xóa dòng trong bảng -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addRowBtn = document.getElementById('add-row');
    const tableBody = document.querySelector('#items-table tbody');
    const firstRow = tableBody.querySelector('tr').cloneNode(true);

    addRowBtn.addEventListener('click', function() {
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        tableBody.appendChild(newRow);
    });

    tableBody.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-row')) {
            // Không xóa nếu chỉ còn 1 dòng
            if (tableBody.querySelectorAll('tr').length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
});
</script>

<?php include_once('layouts/footer.php'); ?>