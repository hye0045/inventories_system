<<<<<<< HEAD
<?php
// xuat kho.php
include 'includes/load.php';
include_once('layouts/header.php');

// --- Query dữ liệu xuất kho ---
$sql = "SELECT so.id, so.code, so.order_date, so.notes,
               w.name AS warehouse_name,
               u.username AS user_name
        FROM sales_orders so
        LEFT JOIN warehouses w ON so.warehouse_id = w.id
        LEFT JOIN users u ON so.user_id = u.id
        ORDER BY so.order_date DESC";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        
        <div>
            <a href="xuat kho nguyen lieu.php" class="btn btn-primary me-2">➕ Xuất kho nguyên liệu</a>
            <a href="xuat kho thanh pham.php" class="btn btn-success">➕ Xuất kho thành phẩm</a>
        </div>
    </div>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Mã phiếu</th>
                <th>Ngày xuất</th>
                <th>Kho</th>
                <th>Người lập</th>
                <th>Ghi chú</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- Mã phiếu -->
                        <td><?php echo htmlspecialchars($row['code'] ?? '—'); ?></td>

                        <!-- Ngày xuất -->
                        <td>
                            <?php
                                $od = $row['order_date'] ?? null;
                                if (!empty($od) && strtotime($od) !== false) {
                                    echo date('d/m/Y', strtotime($od));
                                } else {
                                    echo '—';
                                }
                            ?>
                        </td>

                        <!-- Kho -->
                        <td><?php echo htmlspecialchars($row['warehouse_name'] ?? '—'); ?></td>

                        <!-- Người lập -->
                        <td><?php echo htmlspecialchars($row['user_name'] ?? '—'); ?></td>

                        <!-- Ghi chú -->
                        <td><?php echo htmlspecialchars($row['notes'] ?? '—'); ?></td>

                        <!-- Thao tác -->
                        <td>
                            <a href="chi tiet xuat kho.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-sm btn-info">
                                Xem
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Không có phiếu xuất kho nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once('layouts/footer.php'); ?>
=======
<?php
// xuat kho.php
include 'includes/load.php';
include_once('layouts/header.php');

// --- Query dữ liệu xuất kho ---
$sql = "SELECT so.id, so.code, so.order_date, so.notes,
               w.name AS warehouse_name,
               u.username AS user_name
        FROM sales_orders so
        LEFT JOIN warehouses w ON so.warehouse_id = w.id
        LEFT JOIN users u ON so.user_id = u.id
        ORDER BY so.order_date DESC";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        
        <div>
            <a href="xuat kho nguyen lieu.php" class="btn btn-primary me-2">➕ Xuất kho nguyên liệu</a>
            <a href="xuat kho thanh pham.php" class="btn btn-success">➕ Xuất kho thành phẩm</a>
        </div>
    </div>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Mã phiếu</th>
                <th>Ngày xuất</th>
                <th>Kho</th>
                <th>Người lập</th>
                <th>Ghi chú</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- Mã phiếu -->
                        <td><?php echo htmlspecialchars($row['code'] ?? '—'); ?></td>

                        <!-- Ngày xuất -->
                        <td>
                            <?php
                                $od = $row['order_date'] ?? null;
                                if (!empty($od) && strtotime($od) !== false) {
                                    echo date('d/m/Y', strtotime($od));
                                } else {
                                    echo '—';
                                }
                            ?>
                        </td>

                        <!-- Kho -->
                        <td><?php echo htmlspecialchars($row['warehouse_name'] ?? '—'); ?></td>

                        <!-- Người lập -->
                        <td><?php echo htmlspecialchars($row['user_name'] ?? '—'); ?></td>

                        <!-- Ghi chú -->
                        <td><?php echo htmlspecialchars($row['notes'] ?? '—'); ?></td>

                        <!-- Thao tác -->
                        <td>
                            <a href="chi tiet xuat kho.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-sm btn-info">
                                Xem
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Không có phiếu xuất kho nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once('layouts/footer.php'); ?>
>>>>>>> 3f20b3f81f4dcf803adb80cb641f0d131bee66e6
