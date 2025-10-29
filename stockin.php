<?php
// nhap kho.php
include 'includes/load.php';
include_once('layouts/header.php');

// --- Query dữ liệu ---
$sql = "SELECT po.id, po.code, po.order_date, po.status, po.notes,
               s.name AS supplier_name,
               w.name AS warehouse_name
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN warehouses w ON po.warehouse_id = w.id
        ORDER BY po.order_date DESC";

$result = $conn->query($sql);


?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        
        <div>
            <a href="nhap kho nguyen lieu.php" class="btn btn-primary me-2">➕ Nhập nguyên liệu</a>
            <a href="nhap kho thanh pham.php" class="btn btn-success">➕ Nhập thành phẩm</a>
        </div>
    </div>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Mã phiếu</th>
                <th>Ngày nhập</th>
                <th>Nhà cung cấp</th>
                <th>Kho</th>
                <th>Trạng thái</th>
                <th>Ghi chú</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['code'] ?? '—'); ?></td>

                        <!-- Xử lý ngày an toàn: nếu rỗng/không hợp lệ thì show '—' -->
                        <td>
                            <?php
                                $od = $row['order_date'] ?? null;
                                if (!empty($od) && strtotime($od) !== false) {
                                    // format theo giờ local server; nếu cần timezone khác, set trước
                                    echo date('d/m/Y H:i', strtotime($od));
                                } else {
                                    echo '—';
                                }
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($row['supplier_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['warehouse_name'] ?? '—'); ?></td>

                        <td><?php echo htmlspecialchars($row['status'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? '—'); ?></td>
                        <td>
                            <a href="chi tiet nhap kho.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-sm btn-info">Xem</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Không có đơn nhập kho nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once('layouts/footer.php'); ?>
