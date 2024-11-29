<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "cw_project");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// เมื่อผู้ใช้กดปุ่มสมัคร
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $status = 1; // ค่าเริ่มต้นสำหรับสถานะ

    // อัปโหลดรูปภาพ
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // เตรียมคำสั่ง SQL
    $stmt = $conn->prepare("INSERT INTO user (User_Username, User_password, User_name, User_email, User_phone, User_photo, User_status, User_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssbis", $username, $password, $name, $email, $phone, $photo, $status, $position);

    if ($stmt->execute()) {
        echo "สมัครสมาชิกสำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
</head>
<body>
    <h1>สมัครสมาชิก</h1>
    <form action="register.php" method="POST" enctype="multipart/form-data">
        <label>ชื่อผู้ใช้: <input type="text" name="username" required></label><br>
        <label>รหัสผ่าน: <input type="password" name="password
