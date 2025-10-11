<?php
$user = current_user();
$page_title_content = "Coffee-Inventory"; // Giá trị mặc định
if (!empty($page_title)) {
    $page_title_content = remove_junk($page_title);
} elseif (!empty($user)) {
    $page_title_content = ucfirst($user['name']);
}

// 2. Lấy thông tin cần thiết để hiển thị
if ($session->isUserLoggedIn(true)) {
    $display_name = remove_junk(ucfirst($user['name']));
    $user_image = 'uploads/users/' . $user['image'];
    
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $current_date = date("d-m-Y g:i A");

    // 3. Xác định menu cần hiển thị dựa trên cấp độ người dùng
    $menu_file = '';
    switch ($user['user_level']) {
        case '1':
            $menu_file = 'admin_menu.php';
            break;
        case '2':
            $menu_file = 'special_menu.php';
            break;
        case '3':
            $menu_file = 'user_menu.php';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="uploads/warehouse_icon.png" type="image/png">
    <title><?php echo $page_title_content; ?></title>
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
    <link rel="stylesheet" href="libs/css/main.css" />
</head>
<body>
<?php if (isset($user) && $session->isUserLoggedIn(true)): ?>
    <!-- Header của trang -->
    <header id="header">
        <div class="logo pull-left"> Coffee - Inventory </div>
        <div class="header-content">
            <div class="header-date pull-left">
                <strong><?php echo $current_date; ?></strong>
            </div>
            <div class="pull-right clearfix">
                <ul class="info-menu list-inline list-unstyled">
                    <li class="profile">
                        <a href="#" data-toggle="dropdown" class="toggle" aria-expanded="false">
                            <img src="<?php echo $user_image; ?>" alt="user-image" class="img-circle img-inline">
                            <span><?php echo $display_name; ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="profile.php?id=<?php echo (int)$user['id']; ?>">
                                    <i class="glyphicon glyphicon-user"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a href="edit_account.php" title="edit account">
                                    <i class="glyphicon glyphicon-cog"></i> Settings
                                </a>
                            </li>
                            <li class="last">
                                <a href="logout.php">
                                    <i class="glyphicon glyphicon-off"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar bg-dark">
        <button id="toggleSidebar" class="btn btn-primary m-2">☰</button>
        <?php
        if (!empty($menu_file)) {
            include_once($menu_file);
        }
        ?>
    </div>
<?php endif; ?>

<!-- Phần nội dung chính của trang -->
<div class="page">
    <div class="container-fluid">
        <script src="libs/js/sidebar.js"></script>