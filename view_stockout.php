<<<<<<< HEAD
<?php
include 'includes/load.php';
include_once('layouts/header.php');

// --- Lấy ID phiếu xuất ---
$so_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($so_id <= 0) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>ID phiếu xuất không hợp lệ.</div></div>";
    include_once('layouts/footer.php');
    exit;
}

// --- Lấy thông tin phiếu xuất ---
$sql_so = "SELECT so.*, w.name AS warehouse_name
           FROM sales_orders so
           LEFT JOIN warehouses w ON so.warehouse_id = w.id
           WHERE so.id = ?";
$stmt = $conn->prepare($sql_so);
$stmt->bind_param("i", $so_id);
$stmt->execute();
$so = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$so) {
    echo "<div class='container mt-4'><div class='alert alert-warning'>Không tìm thấy phiếu xuất.</div></div>";
    include_once('layouts/footer.php');
    exit;
}

// --- Lấy danh sách sản phẩm / nguyên liệu trong phiếu xuất ---
$sql_items = "SELECT i.*, 
                r.name AS raw_material_name,
                r.sku AS raw_material_sku,
                r.unit AS raw_material_unit,
                p.name AS product_name,
                p.sku AS product_sku,
                p.unit AS product_unit,
                wl.location_code AS location_code,
                (i.price * i.quantity) AS total_price
            FROM sales_order_items i
            LEFT JOIN raw_materials r ON i.raw_material_id = r.id
            LEFT JOIN products p ON i.product_id = p.id
            LEFT JOIN warehouse_locations wl ON i.location_id = wl.id
            WHERE i.sales_order_id = ?";

$stmt2 = $conn->prepare($sql_items);
$stmt2->bind_param("i", $so_id);
$stmt2->execute();
$items = $stmt2->get_result();
$stmt2->close();

// --- Tính tổng cộng ---
$total_amount = 0;
foreach ($items as $row) {
    $total_amount += $row['total_price'];
}
$items->data_seek(0); // reset con trỏ
?>

<div class="form-container">
    <h3>🚚 Chi tiết phiếu xuất kho</h3>

    <div class="card mb-4 p-3">
        <div class="row mb-2">
            <div class="col-md-4"><strong>Mã phiếu:</strong> <?= htmlspecialchars($so['code']) ?></div>
            <div class="col-md-4"><strong>Ngày xuất:</strong> <?= date('d/m/Y H:i', strtotime($so['order_date'])) ?></div>
            
        </div>
        <div class="row mb-2">
            <div class="col-md-4"><strong>Kho xuất:</strong> <?= htmlspecialchars($so['warehouse_name'] ?? '—') ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($so['notes'] ?? '—')) ?></div>
        </div>
    </div>

    <h5>📋 Danh sách sản phẩm / nguyên liệu xuất</h5>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>SKU</th>
                <th>Tên hàng</th>
                <th>Vị trí kho</th>
                <th>Số lượng</th>
                <th>Đơn vị</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php 
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

    <a href="xuat kho.php" class="btn btn-secondary mt-3">← Quay lại danh sách</a>
</div>

<?php include_once('layouts/footer.php'); ?>
=======
<?php
include 'includes/load.php';
include_once('layouts/header.php');

// --- Lấy ID phiếu xuất ---
$so_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($so_id <= 0) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>ID phiếu xuất không hợp lệ.</div></div>";
    include_once('layouts/footer.php');
    exit;
}

// --- Lấy thông tin phiếu xuất ---
$sql_so = "SELECT so.*, w.name AS warehouse_name
           FROM sales_orders so
           LEFT JOIN warehouses w ON so.warehouse_id = w.id
           WHERE so.id = ?";
$stmt = $conn->prepare($sql_so);
$stmt->bind_param("i", $so_id);
$stmt->execute();
$so = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$so) {
    echo "<div class='container mt-4'><div class='alert alert-warning'>Không tìm thấy phiếu xuất.</div></div>";
    include_once('layouts/footer.php');
    exit;
}

// --- Lấy danh sách sản phẩm / nguyên liệu trong phiếu xuất ---
$sql_items = "SELECT i.*, 
                r.name AS raw_material_name,
                r.sku AS raw_material_sku,
                r.unit AS raw_material_unit,
                p.name AS product_name,
                p.sku AS product_sku,
                p.unit AS product_unit,
                wl.location_code AS location_code,
                (i.price * i.quantity) AS total_price
            FROM sales_order_items i
            LEFT JOIN raw_materials r ON i.raw_material_id = r.id
            LEFT JOIN products p ON i.product_id = p.id
            LEFT JOIN warehouse_locations wl ON i.location_id = wl.id
            WHERE i.sales_order_id = ?";

$stmt2 = $conn->prepare($sql_items);
$stmt2->bind_param("i", $so_id);
$stmt2->execute();
$items = $stmt2->get_result();
$stmt2->close();

// --- Tính tổng cộng ---
$total_amount = 0;
foreach ($items as $row) {
    $total_amount += $row['total_price'];
}
$items->data_seek(0); // reset con trỏ
?>

<div class="form-container">
    <h3>🚚 Chi tiết phiếu xuất kho</h3>

    <div class="card mb-4 p-3">
        <div class="row mb-2">
            <div class="col-md-4"><strong>Mã phiếu:</strong> <?= htmlspecialchars($so['code']) ?></div>
            <div class="col-md-4"><strong>Ngày xuất:</strong> <?= date('d/m/Y H:i', strtotime($so['order_date'])) ?></div>
            
        </div>
        <div class="row mb-2">
            <div class="col-md-4"><strong>Kho xuất:</strong> <?= htmlspecialchars($so['warehouse_name'] ?? '—') ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($so['notes'] ?? '—')) ?></div>
        </div>
    </div>

    <h5>📋 Danh sách sản phẩm / nguyên liệu xuất</h5>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>SKU</th>
                <th>Tên hàng</th>
                <th>Vị trí kho</th>
                <th>Số lượng</th>
                <th>Đơn vị</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php 
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

    <a href="xuat kho.php" class="btn btn-secondary mt-3">← Quay lại danh sách</a>
</div>

<?php include_once('layouts/footer.php'); ?>
>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
