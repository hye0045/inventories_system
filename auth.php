<?php
require_once('includes/load.php');
require_once('includes/sql.php');
if(isset($_POST['btn-login'])){
    $req_fields = array('username','password' );
    validate_fields($req_fields);
    $username = remove_junk($_POST['username']);
    $password = remove_junk($_POST['password']);
    if(empty($errors)){
        $user_identified = authenticate($username, $password);
        if($user_identified){
            // Tạo session
            $session->login($user_identified['id']);
            // Cập nhật thời gian đăng nhập
            updateLastLogIn($user_identified['id']); // Hàm này có sẵn trong functions.php
            
            // CHUYỂN HƯỚNG ĐẾN TRANG ĐÚNG
            redirect('home.php', false);

        } else {
            $session->msg("d", "Tên đăng nhập hoặc mật khẩu không đúng.");
            redirect('index.php', true);
        }
    } else {
        $session->msg("d", $errors);
        redirect('index.php', true);
    }
}
?>