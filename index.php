<?php
session_start();

// ตรวจสอบสถานะการล็อกอิน
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตามการผลิต</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            font-size: 1rem;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .btn-login {
            background-color: #007bff;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .btn-register {
            background-color: #28a745;
        }
        .btn-register:hover {
            background-color: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ยินดีต้อนรับสู่ระบบติดตามการผลิต</h1>
        <p>กรุณาเข้าสู่ระบบหรือลงทะเบียนเพื่อเริ่มต้นใช้งาน</p>
        <a href="login.php" class="btn btn-login">เข้าสู่ระบบ</a>
        <a href="register.php" class="btn btn-register">สมัครสมาชิก</a>
    </div>
</body>
</html>
