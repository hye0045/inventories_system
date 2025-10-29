<?php
include 'includes/load.php';
include_once('layouts/header.php');

// --- Lấy ID phiếu nhập ---
$po_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($po_id <= 0) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>ID phiếu nhập không hợp lệ.</div></div>";
    include_once('layouts/footer.php');
    exit;
}

// --- Lấy thông tin phiếu nhập ---
$sql_po = "SELECT po.*, s.name AS supplier_name, w.name AS warehouse_name
           FROM purchase_orders po
           LEFT JOIN suppliers s ON po.supplier_id = s.id
           LEFT JOIN warehouses w ON po.location_id = w.id
           WHERE po.id = ?";
$stmt = $conn->prepare($sql_po);
$stmt->bind_param("i", $po_id);
$stmt->execute();
$po = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$po) {
    echo "<div class='container mt-4'><div class='alert alert-warning'>Không tìm thấy phiếu nhập.</div></div>";
    include_once('layouts/footer.php');
    exit;
}

// --- Lấy danh sách sản phẩm của phiếu ---
$sql_items = "SELECT i.*, 
            r.name AS raw_material_name,
             (i.price * i.quantity) AS total_price,
                r.sku AS raw_material_sku,
                r.unit AS raw_material_unit,
                p.name AS product_name,
                p.sku AS product_sku,
                p.unit AS product_unit,
                 wl.location_code AS location_code
              FROM purchase_order_items i
              LEFT JOIN raw_materials r ON i.raw_material_id = r.id
              LEFT JOIN products p ON i.product_id = p.id
              LEFT JOIN warehouse_locations wl ON i.location_id = wl.id
              WHERE i.purchase_order_id = ?";
$stmt2 = $conn->prepare($sql_items);
$stmt2->bind_param("i", $po_id);
$stmt2->execute();
$items = $stmt2->get_result();
$stmt2->close();

// --- Tính tổng cộng ---
$total_amount = 0;
foreach ($items as $row) {
    $total_amount += $row['total_price'];
}
$items->data_seek(0); // reset lại con trỏ để lặp lại ở dưới
?>

<div class="container mt-4">
<form>
        <div class="form-container">
    <h3>📦 Chi tiết phiếu nhập kho</h3>

    <div class="card mb-4 p-3">
        <div class="row mb-2">
            <div class="col-md-4"><strong>Mã phiếu:</strong> <?= htmlspecialchars($po['code']) ?></div>
            <div class="col-md-4"><strong>Ngày nhập:</strong> <?= date('d/m/Y H:i', strtotime($po['order_date'])) ?></div>
            <div class="col-md-4"><strong>Trạng thái:</strong> <?= htmlspecialchars($po['status']) ?></div>
        </div>
        <div class="row mb-2">
            
            <div class="col-md-4"><strong>Kho nhập:</strong> <?= htmlspecialchars($po['warehouse_name'] ?? '—') ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($po['notes'] ?? '—')) ?></div>
        </div>
    </div>

    <h5>📝 Danh sách sản phẩm nhập</h5>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
    
                <th>Mã SKU</th>
                <th>Tên</th>  
                <th>Vị trí kho</th>
                <th>Số lượng</th>
                <th>Đơn vị</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            $total_amount = 0;
            while ($row = $items->fetch_assoc()): 
                $total_amount += $row['total_price'];
            ?>
                <tr>
                    
                    <td><?= htmlspecialchars($row['raw_material_sku'] ?? $row['product_sku'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($row['raw_material_name'] ?? $row['product_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($row['location_code'] ?? '—') ?></td>
                    <td><?= number_format($row['quantity'], 2) ?></td>
                    <td><?= htmlspecialchars($row['raw_material_unit'] ?? $row['product_unit'] ?? '—') ?></td>
                    <td><?= number_format($row['price'], 0, ',', '.') ?> đ</td>
                    <td><?= number_format($row['total_price'], 0, ',', '.') ?> đ</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-end">Tổng cộng:</th>
                <th><?= number_format($total_amount, 0, ',', '.') ?> đ</th>
            </tr>
        </tfoot>
    </table>

    <a href="nhap kho.php" class="btn btn-secondary mt-3">← Quay lại danh sách</a>
</div>
</form>
<?php include_once('layouts/footer.php'); ?>
