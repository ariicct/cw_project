<?php
// เรียกใช้ไฟล์ connection.php
include 'connect/connection.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST['user_input'];
    $password = $_POST['password'];

    // ตรวจสอบทั้งชื่อผู้ใช้และอีเมล
    $sql = "SELECT * FROM user WHERE (User_Username=? OR User_email=?) AND User_password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $user_input, $user_input, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = $result->fetch_assoc();
        header("Location: home.php"); // เปลี่ยนเป็นหน้า home.php เมื่อเข้าสู่ระบบสำเร็จ
        exit();
    } else {
        $error = "ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>เข้าสู่ระบบ</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="user_input">ชื่อผู้ใช้หรืออีเมล</label>
                <input type="text" id="user_input" name="user_input" required>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">เข้าสู่ระบบ</button>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
