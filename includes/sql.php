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
        $stmt = $db->query($sql, [$id]);
        return $stmt->affected_rows === 1;
    } catch (Exception $e) {
        return false;
    }
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
/* Ví dụ: find_all_purchase_orders(), create_purchase_order(), ...
/*--------------------------------------------------------------*/

function find_by_groupLevel(int $level): ?array {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = ? LIMIT 1";
    return $db->fetchOne($sql, [$level]);
}

?>