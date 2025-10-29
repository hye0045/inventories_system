<?php
require_once('includes/database.php');

/*--------------------------------------------------------------*/
/* Các hàm truy vấn CSDL chung (CRUD cơ bản)
/*--------------------------------------------------------------*/

function find_all(string $table): array {
    global $db;
    $sql = "SELECT * FROM `{$table}`";
    return $db->fetchAll($sql);
}

function find_by_id(string $table, int $id): ?array {
    global $db;
    $sql = "SELECT * FROM `{$table}` WHERE id = ? LIMIT 1";
    return $db->fetchOne($sql, [$id]);
}

function delete_by_id(string $table, int $id): bool {
    global $db;
    $sql = "DELETE FROM `{$table}` WHERE id = ? LIMIT 1";
    try {
        return $db->query($sql, [$id]);
    } catch (Exception $e) {
        return false;
    }
}
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  // Sử dụng hàm fetchAll đã thêm trước đó
  $result_set = $db->fetchAll($sql ); 
  return $result_set;
}

/*--------------------------------------------------------------*/
/* Các hàm dành riêng cho Người dùng (Users)
/*--------------------------------------------------------------*/

function current_user(): ?array {
    global $session;
    if ($session->isUserLoggedIn()) {
        return find_by_id('users', (int)$_SESSION['user_id']);
    }
    return null;
}

function authenticate(string $username, string $password): ?array {
    global $db;
    
    // 1. Lấy thông tin user từ CSDL dựa trên username
    $sql = "SELECT id, username, password, user_level FROM users WHERE username = ? LIMIT 1";
    $user = $db->fetchOne($sql, [$username]);

    // 2. Nếu tìm thấy user VÀ mật khẩu khớp (sử dụng password_verify)
    if ($user && password_verify($password, $user['password'])) {
        // Mật khẩu đúng! Bỏ cột password ra khỏi kết quả trả về cho an toàn
        unset($user['password']);
        return $user;
    }

    // 3. Nếu không tìm thấy user hoặc mật khẩu sai
    return null;
}

function updateLastLogIn($user_id) {
    global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id='{$user_id}' LIMIT 1";
    return $db->query($sql);
}
function page_require_level($required_level){
  require_once('session.php');
  $session = new Session();
  $current_user = current_user();
  $login_level = find_by_groupLevel((int)$current_user['user_level']);
  if(!$login_level){
    $session->msg("d","Missing user level.");
    redirect('home.php',false);
  }
  if($login_level['group_level'] > $required_level){
    $session->msg("d","Sorry! you dont have permission to view this page.");
    redirect('home.php',false);
  }
}
/*--------------------------------------------------------------*/
/* Các hàm dành riêng cho Thành phẩm (Products)
/*--------------------------------------------------------------*/

function find_all_products(): array {
    global $db;
    $sql = "SELECT p.*, c.name AS category_name, m.file_name AS image_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.categorie_id
            LEFT JOIN media m ON m.id = p.media_id
            ORDER BY p.name ASC";
    return $db->fetchAll($sql);
}

/*--------------------------------------------------------------*/
/* CÁC HÀM NGHIỆP VỤ MỚI SẼ ĐƯỢC THÊM VÀO ĐÂY
/*--------------------------------------------------------------*/

function find_by_groupLevel(int $level): ?array {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = ? LIMIT 1";
    return $db->fetchOne($sql, [$level]);
}

function find_by_groupName(string $group_name): ?array {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = ? LIMIT 1";
    return $db->fetchOne($sql, [$group_name]);
}
function find_all_user(){
    global $db;
    $sql  = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
    $sql .= "g.group_name ";
    $sql .= "FROM users u ";
    $sql .= "LEFT JOIN user_groups g ON g.group_level=u.user_level ORDER BY u.name ASC";
    return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Danh mục (Categories)
/*--------------------------------------------------------------*/

// Tìm tất cả danh mục
function find_all_categories(){
    global $db;
    return find_by_sql("SELECT * FROM categories ORDER BY name ASC");
}

// Tìm danh mục theo ID
function find_categorie_by_id($categorie_id){
    global $db;
    return find_by_id('categories', (int)$categorie_id);
}

// Tạo danh mục mới
function create_categorie($cat_name) {
    global $db;
    $sql = "INSERT INTO categories (name) VALUES (?)";
    return $db->query($sql, [$cat_name]);
}

// Cập nhật danh mục
function update_categorie($cat_name, $cat_id) {
    global $db;
    $id = (int)$cat_id;
    $sql = "UPDATE categories SET name = ? WHERE id = ?";
    return $db->query($sql, [$cat_name, $id]);
}

// Xóa danh mục
function delete_categorie_by_id($categorie_id){
    global $db;
    return delete_by_id('categories', (int)$categorie_id);
}


/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Sản phẩm (Products & Raw Materials)
/*--------------------------------------------------------------*/

// LẤY TẤT CẢ SẢN PHẨM VÀ NGUYÊN VẬT LIỆU (Dùng UNION để gộp)
function find_all_products_and_materials($search_term = '') {
    global $db;
    $search_sql = '';
    if (!empty($search_term)) {
        $queryd_term = $db->query($search_term);
        // Thêm điều kiện tìm kiếm cho cả hai bảng
        $search_sql = " WHERE p.name LIKE '%{$queryd_term}%' OR p.sku LIKE '%{$queryd_term}%'";
    }

    $sql  = "SELECT";
    $sql .= " p.id, p.name, p.sku, p.unit, c.name AS categorie, m.file_name AS image,";
    $sql .= " 'Thành phẩm' AS item_type, p.sale_price"; // Thêm cột để phân biệt loại
    $sql .= " FROM products p";
    $sql .= " LEFT JOIN categories c ON c.id = p.categorie_id";
    $sql .= " LEFT JOIN media m ON m.id = p.media_id";
    $sql .= $search_sql; // Áp dụng tìm kiếm
    $sql .= " UNION ALL "; // Gộp kết quả
    $sql .= "SELECT";
    $sql .= " p.id, p.name, p.sku, p.unit, c.name AS categorie, m.file_name AS image,";
    $sql .= " 'Nguyên vật liệu' AS item_type, NULL AS sale_price"; // NVL không có giá bán
    $sql .= " FROM raw_materials p"; // Lấy từ bảng raw_materials
    $sql .= " LEFT JOIN categories c ON c.id = p.categorie_id";
    $sql .= " LEFT JOIN media m ON m.id = p.media_id";
    $sql .= $search_sql; // Áp dụng tìm kiếm
    $sql .= " ORDER BY name ASC";

    return find_by_sql($sql);
}

// Hàm tìm sản phẩm hoặc NVL theo ID và Loại
function find_item_by_id_and_type($id, $type) {
    global $db;
    $id = (int)$id;
    if ($type === 'product') {
        return find_by_id('products', $id);
    } elseif ($type === 'raw_material') {
        return find_by_id('raw_materials', $id);
    }
    return null;
}
/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Kho hàng (Warehouses)
/*--------------------------------------------------------------*/

// Tìm tất cả kho hàng
function find_all_warehouses(){
    global $db;
    return find_by_sql("SELECT * FROM warehouses ORDER BY name ASC");
}

// Tìm kho hàng theo ID
function find_warehouse_by_id($warehouse_id){
    global $db;
    return find_by_id('warehouses', (int)$warehouse_id);
}
/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Nhà cung cấp (Suppliers)
/*--------------------------------------------------------------*/

// Tìm tất cả nhà cung cấp
function find_all_suppliers(){
    global $db;
    return find_by_sql("SELECT * FROM suppliers ORDER BY name ASC");
}

// Tìm nhà cung cấp theo ID
function find_supplier_by_id($supplier_id){
    global $db;
    return find_by_id('suppliers', (int)$supplier_id);
}
/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Vị trí kho (Warehouse Locations)
/*--------------------------------------------------------------*/

// Tìm tất cả vị trí, gộp với tên kho để hiển thị
function find_all_locations() {
    global $db;
    $sql  = "SELECT l.*, w.name AS warehouse_name FROM warehouse_locations l";
    $sql .= " LEFT JOIN warehouses w ON w.id = l.warehouse_id";
    $sql .= " ORDER BY w.name, l.location_code ASC";
    return find_by_sql($sql);
}

// Tìm vị trí theo ID
function find_location_by_id($location_id) {
    global $db;
    return find_by_id('warehouse_locations', (int)$location_id);
}

// Kiểm tra xem một vị trí có còn tồn kho không (để ngăn việc xóa)
function is_location_empty($location_id) {
    global $db;
    $loc_id = (int)$location_id;
    $sql = "SELECT SUM(quantity) as total FROM inventory_stock WHERE location_id = '{$loc_id}'";
    $result = find_by_sql($sql);
    return ($result[0]['total'] == 0 || is_null($result[0]['total']));
}


/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Tồn kho (Inventory Stock) 
/*--------------------------------------------------------------*/

// Lấy tổng tồn kho của một mặt hàng
function calculate_total_stock_for_item($item_id, $item_type) {
    global $db;
    $id = (int)$item_id;
    $type = $db->escape($item_type); 

    // Kiểm tra để chắc chắn item_type là một trong hai giá trị hợp lệ
    if ($type !== 'product' && $type !== 'raw_material') {
        return 0; // Trả về 0 nếu type không hợp lệ
    }

    $sql = "SELECT SUM(quantity) as total FROM inventory_stock WHERE item_id = '{$id}' AND item_type = '{$type}'";
    $result = find_by_sql($sql);
    
    // Nếu không có record (is_null) hoặc không có tổng, trả về 0
    if (is_null($result[0]['total'])) {
        return 0;
    }
    
    return (float)$result[0]['total']; // Trả về float để giữ lại số thập phân nếu có
}

// Lấy tồn kho chi tiết theo từng vị trí của một mặt hàng
function get_inventory_details_for_item($item_id, $item_type) {
    global $db;
    $id = (int)$item_id;
    // Bước 1: Escape chuỗi để đảm bảo an toàn
    $type = $db->escape($item_type);

    // Kiểm tra để chắc chắn item_type là một trong hai giá trị hợp lệ
    if ($type !== 'product' && $type !== 'raw_material') {
        return array(); // Trả về mảng rỗng nếu type không hợp lệ
    }

    $sql  = "SELECT s.quantity, l.location_code, w.name as warehouse_name";
    $sql .= " FROM inventory_stock s";
    $sql .= " LEFT JOIN warehouse_locations l ON l.id = s.location_id";
    $sql .= " LEFT JOIN warehouses w ON w.id = l.warehouse_id";
    $sql .= " WHERE s.item_id = '{$id}' AND s.item_type = '{$type}' AND s.quantity > 0";
    $sql .= " ORDER BY w.name, l.location_code";
    
    return find_by_sql($sql);
}
// Hàm tìm user theo user_level
function find_users_by_level($level) {
    global $db;
    $lvl = (int)$level;
    return find_by_sql("SELECT id FROM users WHERE user_level = '{$lvl}' AND status = '1'");
}

// Hàm tạo thông báo mới
function create_notification($user_id, $message, $link = null) {
    global $db;
    $uid = (int)$user_id;
    $msg = $db->escape($message);
    $lnk = $db->escape($link);
    $sql = "INSERT INTO notifications (user_id, message, link) VALUES ('{$uid}', '{$msg}', '{$lnk}')";
    return $db->query($sql);
}

// Hàm lấy các thông báo chưa đọc cho một user
function find_unread_notifications_for_user($user_id) {
    global $db;
    $uid = (int)$user_id;
    return find_by_sql("SELECT * FROM notifications WHERE user_id = '{$uid}' AND is_read = 0 ORDER BY created_at DESC");
}
/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Kiểm kê kho (Stocktakes)
/*--------------------------------------------------------------*/

// Tìm tất cả các phiếu kiểm kê (gộp thông tin kho và người tạo)
function find_all_stocktakes() {
    global $db;
    $sql  = "SELECT s.*, w.name AS warehouse_name, u.name AS user_name";
    $sql .= " FROM stocktakes s";
    $sql .= " LEFT JOIN warehouses w ON w.id = s.warehouse_id";
    $sql .= " LEFT JOIN users u ON u.id = s.user_id";
    $sql .= " ORDER BY s.stocktake_date DESC, s.id DESC";
    return find_by_sql($sql);
}

// Tìm thông tin một phiếu kiểm kê theo ID
function find_stocktake_by_id($stocktake_id) {
    global $db;
    $id = (int)$stocktake_id;
    $sql  = "SELECT s.*, w.name AS warehouse_name, u.name AS user_name";
    $sql .= " FROM stocktakes s";
    $sql .= " LEFT JOIN warehouses w ON w.id = s.warehouse_id";
    $sql .= " LEFT JOIN users u ON u.id = s.user_id";
    $sql .= " WHERE s.id = '{$id}' LIMIT 1";
    $result = find_by_sql($sql);
    return array_shift($result); // Trả về dòng đầu tiên hoặc null
}
// Lấy tất cả sản phẩm và NVL có tồn kho trong một kho cụ thể
function find_all_items_in_warehouse($warehouse_id) {
    global $db;
    $wh_id = (int)$warehouse_id;
    
    $sql  = "SELECT i.item_id, i.item_type, i.quantity, p.name, p.sku, p.unit";
    $sql .= " FROM inventory_stock i";
    $sql .= " JOIN warehouse_locations l ON i.location_id = l.id";
    $sql .= " JOIN products p ON i.item_id = p.id AND i.item_type = 'product'";
    $sql .= " WHERE l.warehouse_id = '{$wh_id}' AND i.quantity > 0";
    $sql .= " UNION ALL ";
    $sql .= "SELECT i.item_id, i.item_type, i.quantity, rm.name, rm.sku, rm.unit";
    $sql .= " FROM inventory_stock i";
    $sql .= " JOIN warehouse_locations l ON i.location_id = l.id";
    $sql .= " JOIN raw_materials rm ON i.item_id = rm.id AND i.item_type = 'raw_material'";
    $sql .= " WHERE l.warehouse_id = '{$wh_id}' AND i.quantity > 0";
    
    return find_by_sql($sql);
}

// Hàm xóa phiếu kiểm kê và các item liên quan
function delete_stocktake_by_id($stocktake_id){
    global $db;
    $id = (int)$stocktake_id;
    // Xóa các item con trước
    $sql_items = "DELETE FROM stocktake_items WHERE stocktake_id = '{$id}'";
    $db->query($sql_items);
    // Xóa phiếu chính
    return delete_by_id('stocktakes', $id);
}
/*--------------------------------------------------------------*/
/* Các hàm cho Quản lý Kiểm kê kho (Stocktakes) - ĐÃ CẬP NHẬT
/*--------------------------------------------------------------*/

// HÀM MỚI: Lấy tồn kho chi tiết theo từng vị trí và lô trong một kho
function find_stock_details_in_warehouse($warehouse_id) {
    global $db;
    $wh_id = (int)$warehouse_id;

    $sql = "SELECT 
                i.id AS inventory_stock_id,
                i.item_id,
                i.item_type,
                i.location_id,
                i.lot_id,
                i.quantity,
                wl.location_code,
                pl.lot_number,
                CASE
                    WHEN i.item_type = 'product' THEN p.name
                    WHEN i.item_type = 'raw_material' THEN rm.name
                END AS item_name,
                CASE
                    WHEN i.item_type = 'product' THEN p.sku
                    WHEN i.item_type = 'raw_material' THEN rm.sku
                END AS sku,
                CASE
                    WHEN i.item_type = 'product' THEN p.unit
                    WHEN i.item_type = 'raw_material' THEN rm.unit
                END AS unit
            FROM inventory_stock i
            JOIN warehouse_locations wl ON i.location_id = wl.id
            LEFT JOIN product_lots pl ON i.lot_id = pl.id
            LEFT JOIN products p ON i.item_id = p.id AND i.item_type = 'product'
            LEFT JOIN raw_materials rm ON i.item_id = rm.id AND i.item_type = 'raw_material'
            WHERE wl.warehouse_id = '{$wh_id}' AND i.quantity > 0
            ORDER BY item_name, wl.location_code, pl.lot_number";

    return find_by_sql($sql);
}

// HÀM MỚI: Tìm thông tin cơ bản của một item (product hoặc raw material) bằng ID
function find_item_info_by_id($item_id) {
    global $db;
    $id = (int)$item_id;

    // Thử tìm trong bảng products trước
    $sql_product = "SELECT id, name, low_stock_level, 'product' as type FROM products WHERE id = '{$id}' LIMIT 1";
    $result = find_by_sql($sql_product);
    if (!empty($result)) {
        return array_shift($result);
    }

    // Nếu không thấy, tìm trong bảng raw_materials
    $sql_raw = "SELECT id, name, low_stock_level, 'raw_material' as type FROM raw_materials WHERE id = '{$id}' LIMIT 1";
    $result = find_by_sql($sql_raw);
    if (!empty($result)) {
        return array_shift($result);
    }

    return null; // Không tìm thấy
}

// Hàm find_stocktake_items_by_id cần được cập nhật
function find_stocktake_items_by_id($stocktake_id) {
    global $db;
    $id = (int)$stocktake_id;
    
    $sql  = "SELECT 
                si.*,
                wl.location_code,
                pl.lot_number,
                CASE
                    WHEN si.item_type = 'product' THEN p.name
                    WHEN si.item_type = 'raw_material' THEN rm.name
                END AS item_name,
                CASE
                    WHEN si.item_type = 'product' THEN p.sku
                    WHEN si.item_type = 'raw_material' THEN rm.sku
                END AS sku
            FROM stocktake_items si
            JOIN warehouse_locations wl ON si.location_id = wl.id
            LEFT JOIN product_lots pl ON si.lot_id = pl.id
            LEFT JOIN products p ON si.item_id = p.id AND si.item_type = 'product'
            LEFT JOIN raw_materials rm ON si.item_id = rm.id AND si.item_type = 'raw_material'
            WHERE si.stocktake_id = '{$id}'
            ORDER BY item_name, location_code";

    return find_by_sql($sql);
}

?>

