<?php
include 'includes/load.php';
include_once('layouts/header.php');
require_once('includes/functions.php');
page_require_level(1);
// ================== AJAX: trả về <option> vị trí còn hàng của 1 nguyên liệu trong 1 kho ==================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'locations') {
    $wid = intval($_GET['warehouse_id'] ?? 0);
    $pid = intval($_GET['product_id'] ?? 0);

    if ($wid <= 0 || $pid <= 0) {
        echo '<option value="">-- Chọn vị trí --</option>';
        exit;
    }

    $stmt = $conn->prepare("
        SELECT wl.id, wl.location_code, s.quantity
        FROM stock s
        INNER JOIN warehouse_locations wl ON s.location_id = wl.id
        WHERE s.raw_materials_id = ? 
          AND s.warehouse_id = ? 
          AND s.quantity > 0
          AND wl.status = 'active'
        ORDER BY wl.location_code ASC
    ");
    $stmt->bind_param("ii", $pid, $wid);
    $stmt->execute();
    $res = $stmt->get_result();

    echo '<option value="">-- Chọn vị trí có hàng --</option>';
    while ($row = $res->fetch_assoc()) {
        echo '<option value="'.intval($row['id']).'">'.
             htmlspecialchars($row['location_code']).' (Tồn: '.floatval($row['quantity']).')'.
             '</option>';
    }

    $stmt->close();
    exit;
}


// ================== XỬ LÝ LƯU PHIẾU XUẤT ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warehouse_id = intval($_POST['warehouse_id']);
    $order_date   = $_POST['order_date'];
    $notes        = $_POST['notes'] ?? '';
    $user_id      = $_SESSION['user_id'];

    $conn->begin_transaction();
    $errors = [];

    // Bước 1: kiểm tra tồn kho
    if (!empty($_POST['product_id'])) {
        foreach ($_POST['product_id'] as $i => $pid) {
            $pid = intval($pid);
            $qty = floatval($_POST['quantity'][$i]);
            $loc_id = intval($_POST['location_id'][$i]);

            $check = $conn->prepare("
                SELECT quantity 
                FROM stock 
                WHERE raw_materials_id = ? AND warehouse_id = ? AND location_id = ?
            ");
            $check->bind_param("iii", $pid, $warehouse_id, $loc_id);
            $check->execute();
            $res = $check->get_result();
            $stockData = $res->fetch_assoc();
            $check->close();

            $current_stock = $stockData ? $stockData['quantity'] : 0;
            if ($current_stock < $qty) {
                $errors[] = "❌ Vị trí $loc_id không đủ hàng (Còn $current_stock, cần $qty)";
            }
        }
    }

    if (!empty($errors)) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>";
        echo "<strong>Không thể tạo phiếu xuất!</strong><br>";
        foreach ($errors as $err) echo htmlspecialchars($err)."<br>";
        echo "</div>";
        include_once('layouts/footer.php');
        exit;
    }

    // Bước 2: tạo phiếu xuất
    $prefix = 'PXNL' . date('Ymd');
    $result = $conn->query("SELECT COUNT(*) AS total FROM sales_orders WHERE code LIKE '$prefix%'");
    $row = $result->fetch_assoc();
    $code = $prefix . str_pad($row['total'] + 1, 3, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO sales_orders (code, warehouse_id, order_date, notes, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $code, $warehouse_id, $order_date, $notes, $user_id);
    $stmt->execute();
    $sales_order_id = $stmt->insert_id;
    $stmt->close();

    // Bước 3: lưu chi tiết phiếu & trừ tồn
    foreach ($_POST['product_id'] as $i => $pid) {
        $pid = intval($pid);
        $qty = floatval($_POST['quantity'][$i]);
        $price = floatval($_POST['price'][$i]);
        $loc_id = intval($_POST['location_id'][$i]);

        $stmt = $conn->prepare("INSERT INTO sales_order_items (sales_order_id, raw_material_id, quantity, price, location_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiddi", $sales_order_id, $pid, $qty, $price, $loc_id);
        $stmt->execute();
        $stmt->close();

        $update = $conn->prepare("
            UPDATE stock 
            SET quantity = quantity - ? 
            WHERE raw_materials_id = ? AND warehouse_id = ? AND location_id = ?
        ");
        $update->bind_param("diii", $qty, $pid, $warehouse_id, $loc_id);
        $update->execute();
        $update->close();
    }

    $conn->commit();
    $success_message = "✅ Phiếu xuất kho nguyên liệu đã được tạo thành công!";
}

// ================== DỮ LIỆU FORM ==================
$warehouses = $conn->query("SELECT id, name FROM warehouses");
$raw_materials = $conn->query("SELECT id, name FROM raw_materials");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xuất kho nguyên liệu</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/nhap_kho.css">
</head>
<body>
<div class="background">
<div class="form-container">
    <h2>📤 Phiếu xuất kho nguyên liệu</h2>
    <?php if (isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Kho xuất:</label>
            <select name="warehouse_id" id="warehouse_id" required>
                <option value="">-- Chọn kho --</option>
                <?php while ($w = $warehouses->fetch_assoc()): ?>
                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Ngày xuất:</label>
            <input type="date" name="order_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Ghi chú:</label>
            <textarea name="notes" rows="2"></textarea>
        </div>

        <h2>📦 Danh sách nguyên liệu xuất</h2>
        <table id="product-table">
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn vị</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
                <th>Vị trí</th>
                <th>Hành động</th>
            </tr>
            <tr class="product-row">
                <td>
                    <select name="product_id[]" class="product-select" required>
                        <option value="">-- Chọn nguyên liệu --</option>
                        <?php $raw_materials->data_seek(0); while ($p = $raw_materials->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
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
                    <select name="location_id[]" class="location-select" required>
                        <option value="">-- Chọn vị trí có hàng --</option>
                    </select>
                </td>
                <td><button type="button" onclick="removeRow(this)">❌</button></td>
            </tr>
        </table><br>
        <p><strong>Tổng giá trị phiếu xuất: <span id="grand-total">0</span></strong></p>
        <button type="button" class="add-btn" onclick="addRow()">+ Thêm nguyên liệu</button>
        <button type="submit" class="submit-btn">Lưu phiếu xuất</button>
    </form>
</div>
</div>

<script>
function calculateRowTotal(row) {
    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let price = parseFloat(row.querySelector('.price').value) || 0;
    row.querySelector('.total').value = (qty * price).toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.total').forEach(input => total += parseFloat(input.value) || 0);
    document.getElementById('grand-total').textContent = total.toFixed(2);
}

function loadLocations(row) {
    let warehouseId = $('#warehouse_id').val();
    let productId = row.find('.product-select').val();
    let locationSelect = row.find('.location-select');

    if (!warehouseId || !productId) {
        locationSelect.html('<option value="">-- Chọn vị trí có hàng --</option>');
        return;
    }

    $.get('<?= basename(__FILE__) ?>', {
        ajax: 'locations',
        warehouse_id: warehouseId,
        product_id: productId
    })
    .done(function(data) {
        locationSelect.html(data);
    })
    .fail(function() {
        locationSelect.html('<option value="">Lỗi tải vị trí</option>');
    });
}


function addRow() {
    let newRow = $('.product-row:first').clone();
    newRow.find('input').val('');
    newRow.find('.location-select').html('<option value="">-- Chọn vị trí có hàng --</option>');
    $('#product-table').append(newRow);
    bindEvents(newRow);
}

function removeRow(btn) {
    if ($('.product-row').length > 1) {
        $(btn).closest('tr').remove();
        calculateGrandTotal();
    } else {
        alert('Phải có ít nhất 1 nguyên liệu!');
    }
}

function bindEvents(row) {
    row.find('.qty').on('input', () => calculateRowTotal(row[0]));
    row.find('.price').on('input', () => calculateRowTotal(row[0]));
    row.find('.product-select').on('change', () => loadLocations(row));
}

$(document).ready(function() {
    bindEvents($('.product-row'));
});
</script>
</body>
</html>
