<?php
$user = current_user();
$page_title_content = "Coffee-Inventory";
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
    // Lấy thông tin thông báo
      $notifications = find_unread_notifications_for_user($user['id']);
      $notification_count = count($notifications);
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
                    <!-- Icon chuông thông báo -->
                    <!-- Dropdown thông báo -->
                    `<li class="notifications">
                    <a href="#" data-toggle="dropdown" class="toggle" aria-expanded="false">
                        <i class="glyphicon glyphicon-bell"></i>
                        <?php if ($notification_count > 0): ?>
                        <span class="badge" id="notification-badge"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">Thông báo mới</li>
                        <div id="notification-list">
                        <?php if(!empty($notifications)): ?>
                            <?php foreach($notifications as $notif): ?>
                            <li>
                                <a href="#" class="notification-item" data-id="<?php echo $notif['id']; ?>">
                                <small><i><?php echo date('d-m-Y H:i', strtotime($notif['created_at'])); ?></i></small><br>
                                <?php echo htmlspecialchars(substr($notif['message'], 0, 45)) . '...'; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-center" style="padding: 10px;">Không có thông báo mới.</li>
                        <?php endif; ?>
                        </div>
                    </ul>
                    </li>

                    <!-- Modal chi tiết thông báo -->
                    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <h4 class="modal-title" id="notificationModalLabel">Chi tiết Thông báo</h4>
                        </div>
                        <div class="modal-body">
                            <p><strong>Ngày gửi:</strong> <span id="notif-date"></span></p>
                            <hr>
                            <p><strong>Nội dung:</strong></p>
                            <div id="notif-message" class="message-content"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                            <a href="#" id="notif-action-btn" class="btn btn-primary" style="display: none;">Thực hiện</a>
                        </div>
                        </div>
                    </div>
                    </div>
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
<div class="page">
    <div class="container-fluid">
<!-- ===================================================================== -->
<!-- ===================================================================== -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="notificationModalLabel">Chi tiết Thông báo</h4>
      </div>
      <div class="modal-body">
        <p><strong>Ngày gửi:</strong> <span id="notif-date"></span></p>
        <hr>
        <p><strong>Nội dung:</strong></p>
        <div id="notif-message" class="message-content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
        <a href="#" id="notif-action-btn" class="btn btn-primary" style="display: none;">Thực hiện</a>
      </div>
    </div>
  </div>
</div>

<!-- ===================================================================== -->
<!-- ===================================================================== -->
<!-- Phần nội dung chính của trang -->
        <script>
        $(document).ready(function() {
            // Sửa lại selector để nó hoạt động khi click vào thông báo trong dropdown chuông
            // Dùng .on() cho phần tử cha để bắt sự kiện của các phần tử con được tạo động
            $('#notification-list').on('click', '.notification-item', function(e) {
                e.preventDefault();
                
                var notificationId = $(this).data('id');
                var clickedItem = $(this);

                $.ajax({
                    url: 'ajax.php',
                    type: 'POST',
                    data: { action: 'get_notification_details', id: notificationId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var notif = response.data;
                            
                            $('#notif-date').text(notif.formatted_date);
                            $('#notif-message').text(notif.message);

                            if (notif.link) {
                                $('#notif-action-btn').attr('href', notif.link).show();
                            } else {
                                $('#notif-action-btn').hide();
                            }

                            $('#notificationModal').modal('show');
                            
                            // Xóa thông báo khỏi danh sách và cập nhật số lượng
                            clickedItem.closest('li').remove();
                            var badge = $('#notification-badge');
                            var newCount = parseInt(badge.text()) - 1;

                            if (newCount > 0) {
                                badge.text(newCount);
                            } else {
                                badge.remove();
                                // Thêm dòng "Không có thông báo mới" vào danh sách
                                if ($('#notification-list').find('.notification-item').length === 0) {
                                $('#notification-list').html('<li class="text-center" style="padding: 10px;">Không có thông báo mới.</li>');
                                }
                            }
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Không thể kết nối đến máy chủ.');
                    }
                });
            });
        });
        </script>
    
    <script src="libs/js/sidebar.js"></script>