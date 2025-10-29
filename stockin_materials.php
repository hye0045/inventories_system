<?php 
include 'includes/load.php';
include_once('layouts/header.php');
require_once('includes/functions.php');
page_require_level(1);
// ================== XỬ LÝ AJAX LOAD VỊ TRÍ KHO ==================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'locations') {
    $wid = intval($_GET['warehouse_id']);
    $result = $conn->query("
        SELECT wl.id, wl.location_code, wl.max_capacity, COALESCE(SUM(s.quantity),0) AS current_stock
        FROM warehouse_locations wl
        LEFT JOIN stock s ON wl.id = s.location_id
        WHERE wl.warehouse_id = '$wid' AND wl.status = 'active'
        GROUP BY wl.id
        HAVING current_stock < wl.max_capacity
    ");
    echo '<option value="">-- Chọn vị trí trống --</option>';
    while ($r = $result->fetch_assoc()) {
        echo '<option value="'.$r['id'].'">'
            .htmlspecialchars($r['location_code'])
            .' (Còn: '.($r['max_capacity'] - $r['current_stock']).')</option>';
    }
    exit;
}

// ================== LƯU DỮ LIỆU NHẬP KHO ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warehouse_id   = intval($_POST['warehouse_id']);
    $supplier_id    = intval($_POST['supplier_id']);
    $order_date     = $_POST['order_date'];
    $notes          = $_POST['notes'] ?? '';
    $user_id        = $_SESSION['user_id'];

    // --- Tạo mã phiếu nhập ---
    $prefix = 'PNNL' . date('Ymd');
    $result = $conn->query("SELECT COUNT(*) AS total FROM purchase_orders WHERE code LIKE '$prefix%'");
    $row = $result->fetch_assoc();
    $code = $prefix . str_pad($row['total'] + 1, 3, '0', STR_PAD_LEFT);

    // Thêm phiếu nhập
    $stmt = $conn->prepare("INSERT INTO purchase_orders (code, warehouse_id, supplier_id, order_date, status, notes, user_id) VALUES (?, ?, ?, ?, 'completed', ?, ?)");
    $stmt->bind_param("siissi", $code, $warehouse_id, $supplier_id, $order_date, $notes, $user_id);
    $stmt->execute();
    $purchase_order_id = $stmt->insert_id;
    $stmt->close();

    // --- Thêm sản phẩm vào phiếu nhập + cập nhật tồn kho ---
    if (!empty($_POST['product_id'])) {
        foreach ($_POST['product_id'] as $i => $pid) {
            $pid = intval($pid);
            $qty = floatval($_POST['quantity'][$i]);
            $price = floatval($_POST['price'][$i]);
            $loc_id = isset($_POST['location_id'][$i]) ? intval($_POST['location_id'][$i]) : 0;

            // Kiểm tra capacity của vị trí
            $available = null;
            if ($loc_id > 0) {
                $locStmt = $conn->prepare("
                    SELECT wl.max_capacity, COALESCE(SUM(s.quantity),0) AS current_stock
                    FROM warehouse_locations wl
                    LEFT JOIN stock s ON wl.id = s.location_id
                    WHERE wl.id = ?
                    GROUP BY wl.id
                ");
                $locStmt->bind_param("i", $loc_id);
                $locStmt->execute();
                $locData = $locStmt->get_result()->fetch_assoc();
                $locStmt->close();

                $available = $locData['max_capacity'] - $locData['current_stock'];
                if ($available < $qty) {
                    echo "<p style='color:red'>❌ Vị trí kho ".$locData['location_code']." chỉ còn ".$available." đơn vị!</p>";
                    continue; // bỏ qua dòng này
                }
            }

            // Thêm vào purchase_order_items
            if ($loc_id > 0) {
                $stmt = $conn->prepare("INSERT INTO purchase_order_items (purchase_order_id, raw_material_id, quantity, price, location_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiddi", $purchase_order_id, $pid, $qty, $price, $loc_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO purchase_order_items (purchase_order_id, raw_material_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidd", $purchase_order_id, $pid, $qty, $price);
            }
            $stmt->execute();
            $stmt->close();

            // --- Cập nhật stock ---
            if ($loc_id > 0) {
                $checkStock = $conn->prepare("SELECT quantity FROM stock WHERE raw_materials_id = ? AND warehouse_id = ? AND location_id = ?");
                $checkStock->bind_param("iii", $pid, $warehouse_id, $loc_id);
                $checkStock->execute();
                $resStock = $checkStock->get_result();
                if ($resStock->num_rows > 0) {
                    $updateStock = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE raw_materials_id = ? AND warehouse_id = ? AND location_id = ?");
                    $updateStock->bind_param("diii", $qty, $pid, $warehouse_id, $loc_id);
                    $updateStock->execute();
                    $updateStock->close();
                } else {
                    $insertStock = $conn->prepare("INSERT INTO stock (raw_materials_id, warehouse_id, location_id, quantity) VALUES (?, ?, ?, ?)");
                    $insertStock->bind_param("iiid", $pid, $warehouse_id, $loc_id, $qty);
                    $insertStock->execute();
                    $insertStock->close();
                }
                $checkStock->close();
            }
        }
    }

    $success_message = "✅ Phiếu nhập thành phẩm đã được tạo thành công!";
}

// ================== LẤY DỮ LIỆU CHO FORM ==================
$warehouses = $conn->query("SELECT id, name FROM warehouses");
$suppliers  = $conn->query("SELECT id, name FROM suppliers");
$raw_materials  = $conn->query("SELECT id, name FROM raw_materials");
$locations = $conn->query("
    SELECT wl.id, wl.location_code, wl.max_capacity, COALESCE(SUM(s.quantity),0) AS current_stock
    FROM warehouse_locations wl
    LEFT JOIN stock s ON wl.id = s.location_id
    WHERE wl.warehouse_id = 1 AND wl.status = 'active'
    GROUP BY wl.id
    HAVING current_stock < wl.max_capacity
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhập kho nguyên liệu</title>
    <link rel="stylesheet" href="assets/css/nhap_kho.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="background">
<div class="form-container">
    <h2>📥 Phiếu nhập kho nguyên liệu</h2>
    <?php if (isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Kho nhập:</label>
            <select name="warehouse_id" id="warehouse_id" required>
                <option value="">-- Chọn kho --</option>
                <?php while ($w = $warehouses->fetch_assoc()): ?>
                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Ngày nhập:</label>
            <input type="date" name="order_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Ghi chú:</label>
            <textarea name="notes" rows="2"></textarea>
        </div>

        <h2>📦 Danh sách nguyên liệu</h2>
        <table id="product-table">
            <tr>
                <th>Sản phẩm</th>
                <th>Nhà cung cấp</th>
                <th>Số lượng</th>
                <th>Đơn vị</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
                <th>Vị trí lưu kho</th>
                <th>Hành động</th>
            </tr>
            <tr class="product-row">
                <td>
                    <select name="product_id[]" required>
                        <?php $raw_materials->data_seek(0); while ($p = $raw_materials->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>
                <td>
                    <select name="supplier_id[]" required>
                        <?php $suppliers->data_seek(0); while ($s = $suppliers->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>
                <td><input type="number" name="quantity[]" step="0.01" class="qty" required></td>
                <td>
                    <select name="unit[]" required>
                        <option value="kg">kg</option>
                        <option value="cái">cái</option>
                        <option value="lon">lon</option>
                        <option value="chai">chai</option>
                    </select>
                </td>
                <td><input type="number" name="price[]" step="0.01" class="price" required></td>
                <td><input type="number" class="total" readonly value="0"></td>
                <td>
                    <select name="location_id[]" required>
                        <option value="">-- Chọn vị trí trống --</option>
                        <?php while ($l = $locations->fetch_assoc()): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['location_code']).' (Còn: '.($l['max_capacity']-$l['current_stock']).')' ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>
                <td><button type="button" onclick="removeRow(this)">❌</button></td>
            </tr>
        </table><br>
        <p><strong>Tổng giá trị phiếu nhập: <span id="grand-total">0</span></strong></p>
        <button type="button" class="add-btn" onclick="addRow()">+ Thêm nguyên liệu</button>
        <button type="submit" class="submit-btn">Lưu phiếu nhập</button>
    </form>
</div>
</div>

<script>
function calculateRowTotal(row) {
    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let price = parseFloat(row.querySelector('.price').value) || 0;
    row.querySelector('.total').value = (qty * price).toFixed(2);
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.product-row').forEach(row => {
        total += parseFloat(row.querySelector('.total').value) || 0;
    });
    document.getElementById('grand-total').textContent = total.toFixed(2);
}

function addRow() {
    let table = document.getElementById('product-table');
    let firstRow = table.querySelector('.product-row');
    let newRow = firstRow.cloneNode(true);
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    table.appendChild(newRow);
    bindEvents(newRow);
}

function removeRow(btn) {
    let table = document.getElementById('product-table');
    let rows = table.querySelectorAll('.product-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    } else {
        alert('Phải có ít nhất 1 nguyên liệu!');
    }
}

function bindEvents(row) {
    row.querySelector('.qty').addEventListener('input', () => { calculateRowTotal(row); calculateGrandTotal(); });
    row.querySelector('.price').addEventListener('input', () => { calculateRowTotal(row); calculateGrandTotal(); });
}

document.querySelectorAll('.product-row').forEach(row => bindEvents(row));
</script>
</body>
</html>
