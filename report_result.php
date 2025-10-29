<?php
$page_title = 'Kết quả báo cáo';
require_once('includes/load.php');
page_require_level(2);

// Hàm xuất Excel
function export_excel($filename, $html_table) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo $html_table;
    exit;
}

// Lấy tham số
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
$warehouse_id = isset($_GET['warehouse_id']) && $_GET['warehouse_id'] != '' ? intval($_GET['warehouse_id']) : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if (!$report_type || !$start_date || !$end_date) {
    echo "<div class='alert alert-danger'>Vui lòng chọn đầy đủ thông tin báo cáo.</div>";
    exit;
}

$html_table = "<table border='1'><thead><tr>";

switch ($report_type) {
    case 'stock_in':
        $html_table .= "<th>Mã phiếu</th><th>Ngày</th><th>Nhà cung cấp</th><th>Kho</th><th>Sản phẩm/Nguyên liệu</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>";

        $sql = "SELECT po.code, po.order_date, s.name AS supplier_name, w.name AS warehouse_name,
                       IF(poi.product_id IS NOT NULL, pr.name, rm.name) AS item_name,
                       poi.quantity, poi.price, poi.total_price
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN warehouses w ON po.warehouse_id = w.id
                LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
                LEFT JOIN products pr ON poi.product_id = pr.id
                LEFT JOIN raw_materials rm ON poi.raw_material_id = rm.id
                WHERE po.order_date BETWEEN ? AND ?";

        if ($warehouse_id) $sql .= " AND po.warehouse_id = $warehouse_id";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $html_table .= "<tr>
                <td>{$row['code']}</td>
                <td>{$row['order_date']}</td>
                <td>{$row['supplier_name']}</td>
                <td>{$row['warehouse_name']}</td>
                <td>{$row['item_name']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['price']}</td>
                <td>{$row['total_price']}</td>
            </tr>";
        }

        break;

    case 'stock_out':
        $html_table .= "<th>Mã phiếu</th><th>Ngày</th><th>Kho</th><th>Sản phẩm/Nguyên liệu</th><th>Số lượng</th><th>Đơn giá</th></tr></thead><tbody>";

        $sql = "SELECT so.code, so.order_date, w.name AS warehouse_name,
                       IF(soi.product_id IS NOT NULL, pr.name, rm.name) AS item_name,
                       soi.quantity, soi.price
                FROM sales_orders so
                LEFT JOIN warehouses w ON so.warehouse_id = w.id
                LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
                LEFT JOIN products pr ON soi.product_id = pr.id
                LEFT JOIN raw_materials rm ON soi.raw_material_id = rm.id
                WHERE so.order_date BETWEEN ? AND ?";

        if ($warehouse_id) $sql .= " AND so.warehouse_id = $warehouse_id";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $html_table .= "<tr>
                <td>{$row['code']}</td>
                <td>{$row['order_date']}</td>
                <td>{$row['warehouse_name']}</td>
                <td>{$row['item_name']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['price']}</td>
            </tr>";
        }

        break;

    case 'stock_balance':
        $html_table .= "<th>Kho</th><th>Sản phẩm/Nguyên liệu</th><th>Số lượng tồn</th></tr></thead><tbody>";

        $sql = "SELECT w.name AS warehouse_name,
                       IF(s.product_id IS NOT NULL, pr.name, rm.name) AS item_name,
                       s.quantity
                FROM stock s
                LEFT JOIN warehouses w ON s.warehouse_id = w.id
                LEFT JOIN products pr ON s.product_id = pr.id
                LEFT JOIN raw_materials rm ON s.raw_materials_id = rm.id
                WHERE 1=1";

        if ($warehouse_id) $sql .= " AND s.warehouse_id = $warehouse_id";

        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $html_table .= "<tr>
                <td>{$row['warehouse_name']}</td>
                <td>{$row['item_name']}</td>
                <td>{$row['quantity']}</td>
            </tr>";
        }

        break;

    case 'stock_take':
        $html_table .= "<th>Mã phiếu kiểm kê</th><th>Ngày kiểm kê</th><th>Kho</th><th>Sản phẩm/Nguyên liệu</th><th>Tồn hệ thống</th><th>Tồn thực tế</th><th>Chênh lệch</th></tr></thead><tbody>";

        $sql = "SELECT st.code, st.stocktake_date, w.name AS warehouse_name,
                       IF(sti.product_id IS NOT NULL, pr.name, rm.name) AS item_name,
                       sti.quantity_expected, sti.quantity_counted, sti.difference
                FROM stocktakes st
                LEFT JOIN warehouses w ON st.warehouse_id = w.id
                LEFT JOIN stocktake_items sti ON sti.stocktake_id = st.id
                LEFT JOIN products pr ON sti.product_id = pr.id
                LEFT JOIN raw_materials rm ON sti.raw_material_id = rm.id
                WHERE st.stocktake_date BETWEEN ? AND ?";

        if ($warehouse_id) $sql .= " AND st.warehouse_id = $warehouse_id";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $html_table .= "<tr>
                <td>{$row['code']}</td>
                <td>{$row['stocktake_date']}</td>
                <td>{$row['warehouse_name']}</td>
                <td>{$row['item_name']}</td>
                <td>{$row['quantity_expected']}</td>
                <td>{$row['quantity_counted']}</td>
                <td>{$row['difference']}</td>
            </tr>";
        }

        break;

    case 'stock_summary':
    $html_table .= "<th>Mã vật tư</th>
                    <th>Tên vật tư</th>
                    <th>ĐVT</th>
                    <th>Số lượng đầu kỳ</th>
                    <th>Thành tiền đầu kỳ</th>
                    <th>Nhập trong kỳ (SL)</th>
                    <th>Nhập trong kỳ (TT)</th>
                    <th>Xuất trong kỳ (SL)</th>
                    <th>Xuất trong kỳ (TT)</th>
                    <th>Tồn cuối kỳ (SL)</th>
                    <th>Tồn cuối kỳ (TT)</th>
                    </tr></thead><tbody>";

    // Lấy danh sách tất cả sản phẩm & nguyên liệu
    $items_sql = "SELECT id, name, unit, 'product' AS type FROM products
                  UNION ALL
                  SELECT id, name, unit, 'raw' AS type FROM raw_materials";
    $items_result = $conn->query($items_sql);

    while($item = $items_result->fetch_assoc()){
        $id = $item['id'];
        $type = $item['type'];
        $unit = $item['unit'];

        // Tồn đầu kỳ: trước start_date
        $sql_in = $type=='product' 
                  ? "SELECT SUM(quantity) AS qty, SUM(total_price) AS total FROM purchase_order_items poi
                     JOIN purchase_orders po ON poi.purchase_order_id = po.id
                     WHERE poi.product_id=$id AND po.order_date < '$start_date'"
                  : "SELECT SUM(quantity) AS qty, SUM(total_price) AS total FROM purchase_order_items poi
                     JOIN purchase_orders po ON poi.purchase_order_id = po.id
                     WHERE poi.raw_material_id=$id AND po.order_date < '$start_date'";

        $res_in = $conn->query($sql_in)->fetch_assoc();
        $begin_qty = $res_in['qty'] ?? 0;
        $begin_total = $res_in['total'] ?? 0;

        // Nhập trong kỳ
        $sql_in_period = $type=='product'
                  ? "SELECT SUM(quantity) AS qty, SUM(total_price) AS total FROM purchase_order_items poi
                     JOIN purchase_orders po ON poi.purchase_order_id = po.id
                     WHERE poi.product_id=$id AND po.order_date BETWEEN '$start_date' AND '$end_date'"
                  : "SELECT SUM(quantity) AS qty, SUM(total_price) AS total FROM purchase_order_items poi
                     JOIN purchase_orders po ON poi.purchase_order_id = po.id
                     WHERE poi.raw_material_id=$id AND po.order_date BETWEEN '$start_date' AND '$end_date'";
        $res_in_period = $conn->query($sql_in_period)->fetch_assoc();
        $in_qty = $res_in_period['qty'] ?? 0;
        $in_total = $res_in_period['total'] ?? 0;

        // Xuất trong kỳ
        $sql_out_period = $type=='product'
                  ? "SELECT SUM(quantity) AS qty, SUM(total_price) AS total FROM sales_order_items soi
                     JOIN sales_orders so ON soi.sales_order_id = so.id
                     WHERE soi.product_id=$id AND so.order_date BETWEEN '$start_date' AND '$end_date'"
                  : "SELECT SUM(quantity) AS qty, SUM(total_price) AS total FROM sales_order_items soi
                     JOIN sales_orders so ON soi.sales_order_id = so.id
                     WHERE soi.raw_material_id=$id AND so.order_date BETWEEN '$start_date' AND '$end_date'";
        $res_out_period = $conn->query($sql_out_period)->fetch_assoc();
        $out_qty = $res_out_period['qty'] ?? 0;
        $out_total = $res_out_period['total'] ?? 0;

        // Tồn cuối kỳ
        $end_qty = $begin_qty + $in_qty - $out_qty;
        $end_total = $begin_total + $in_total - $out_total;

        $html_table .= "<tr>
            <td>{$item['id']}</td>
            <td>{$item['name']}</td>
            <td>{$unit}</td>
            <td>{$begin_qty}</td>
            <td>{$begin_total}</td>
            <td>{$in_qty}</td>
            <td>{$in_total}</td>
            <td>{$out_qty}</td>
            <td>{$out_total}</td>
            <td>{$end_qty}</td>
            <td>{$end_total}</td>
        </tr>";
    }

    $html_table .= "</tbody></table>";
    break;

    default:
        echo "Loại báo cáo không hợp lệ.";
        exit;
}

$html_table .= "</tbody></table>";

// Xuất Excel nếu cần
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    export_excel("report_{$report_type}.xls", $html_table);
}

?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="form-conmain">
        <h3>Báo cáo: <?php echo ucfirst(str_replace('_',' ',$report_type)); ?></h3>
        <div class="text-right" style="margin-bottom:10px;">
            <a href="?<?php echo http_build_query($_GET).'&export=excel'; ?>" class="btn btn-success">Xuất Excel</a>
        </div>
        <div class="table-responsive">
            <?php echo $html_table; ?>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
