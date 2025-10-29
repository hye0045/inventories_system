<?php
// Đặt mật khẩu bạn muốn vào đây
$new_password = 'admin'; // Hoặc '123456' tùy bạn

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo "Mật khẩu mới của bạn là: " . $new_password . "<br>";
echo "Chuỗi hash tương ứng (copy chuỗi này):<br>";
echo "<textarea rows='3' cols='80'>" . $hashed_password . "</textarea>";
?>