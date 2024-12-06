<?php
// เชื่อมต่อกับฐานข้อมูล
include '../connect/connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// ฟังก์ชันตรวจสอบ User ID ซ้ำ
function isUserIdDuplicate($conn, $id, $exclude_id = null) {
    $sql = "SELECT * FROM user WHERE User_id = '$id'";
    if ($exclude_id) {
        $sql .= " AND User_id != '$exclude_id'";
    }
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// เพิ่มข้อมูลผู้ใช้
if (isset($_POST['add_user'])) {
    $id = $_POST['User_id'];
    $username = $_POST['User_Username'];
    $password = $_POST['User_password'];
    $name = $_POST['User_name'];
    $email = $_POST['User_email'];
    $phone = $_POST['User_phone'];
    $role = $_POST['User_role'];

    // ตรวจสอบว่า ID ซ้ำหรือไม่
    if (isUserIdDuplicate($conn, $id)) {
        $error_message = "รหัสผู้ใช้นี้มีอยู่แล้วในระบบ!";
    } else {
        // เพิ่มข้อมูลผู้ใช้
        $sql = "INSERT INTO user (User_id, User_Username, User_password, User_name, User_email, User_phone, User_role) 
                VALUES ('$id', '$username', '$password', '$name', '$email', '$phone', '$role')";
        if ($conn->query($sql) === TRUE) {
            $success_message = "เพิ่มผู้ใช้สำเร็จ!";
        } else {
            $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
}

// แก้ไขข้อมูลผู้ใช้
if (isset($_POST['edit_user'])) {
    $old_id = $_POST['Old_User_id'];
    $id = $_POST['User_id'];
    $username = $_POST['User_Username'];
    $password = $_POST['User_password'];
    $name = $_POST['User_name'];
    $email = $_POST['User_email'];
    $phone = $_POST['User_phone'];
    $role = $_POST['User_role'];

    // ตรวจสอบว่า ID ซ้ำหรือไม่ (ยกเว้น ID เดิมของผู้ใช้)
    if (isUserIdDuplicate($conn, $id, $old_id)) {
        $error_message = "ไม่สามารถแก้ไขรหัสผู้ใช้ได้ เนื่องจากรหัสนี้มีอยู่ในระบบแล้ว!";
    } else {
        // อัปเดตข้อมูล
        if (!empty($password)) {
            $sql = "UPDATE user SET 
                    User_id='$id',
                    User_Username='$username', 
                    User_password='$password', 
                    User_name='$name', 
                    User_email='$email', 
                    User_phone='$phone', 
                    User_role='$role' 
                    WHERE User_id='$old_id'";
        } else {
            $sql = "UPDATE user SET 
                    User_id='$id',
                    User_Username='$username', 
                    User_name='$name', 
                    User_email='$email', 
                    User_phone='$phone', 
                    User_role='$role' 
                    WHERE User_id='$old_id'";
        }
        if ($conn->query($sql) === TRUE) {
            $success_message = "แก้ไขข้อมูลผู้ใช้สำเร็จ!";
        } else {
            $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
}

// ลบข้อมูลผู้ใช้
if (isset($_POST['confirm_delete_user'])) {
    $id = $_POST['User_id'];
    $sql = "DELETE FROM user WHERE User_id='$id'";
    if ($conn->query($sql) === TRUE) {
        $success_message = "ลบผู้ใช้สำเร็จ!";
    } else {
        $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// ดึงข้อมูลจากฐานข้อมูล
$result = $conn->query("SELECT * FROM user");
require '../admin/sidebar_admin.php';
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 20px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            width: 80%;
            margin-right: 50px;
        }
        .table {
            margin-top: 20px;
        }
        .edit-form input, .edit-form select {
            margin: 5px 0;
            width: 80%;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .modal-body input, .modal-body select {
            margin: 10px 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">จัดการข้อมูลผู้ใช้งาน</h1>

        <?php 
        if (isset($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        }
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>$error_message</div>";
        }
        ?>

        <!-- Form เพิ่มผู้ใช้ -->
        <div class="card mb-4">
            <div class="card-header">เพิ่มบัญชีผู้ใช้</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="User_id" placeholder="รหัสผู้ใช้" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="User_Username" placeholder="ชื่อผู้ใช้" required>
                        </div>
                        <div class="col-md-4">
                        <input type="text" class="form-control" name="User_password" placeholder="รหัสผ่าน" required>

                        </div>
                        <div class="col-md-4 mt-2">
                            <input type="text" class="form-control" name="User_name" placeholder="ชื่อ-นามสกุล" required>
                        </div>
                        <div class="col-md-4 mt-2">
                            <input type="email" class="form-control" name="User_email" placeholder="อีเมล" required>
                        </div>
                        <div class="col-md-4 mt-2">
                            <input type="text" class="form-control" name="User_phone" placeholder="เบอร์โทรศัพท์" required>
                        </div>
                        <div class="col-md-4 mt-2">
                            <select class="form-select" name="User_role">
                                <option value="admin">ผู้ดูแลระบบ</option>
                                <option value="พนักงาน">พนักงาน</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary" name="add_user">เพิ่มบัญชีผู้ใช้</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ตารางแสดงข้อมูลผู้ใช้ -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>รหัสผู้ใช้</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>รหัสผ่าน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อีเมล</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>บทบาท</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_Username']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_password']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_role']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#editModal<?php echo $row['User_id']; ?>">
                                    แก้ไข
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal<?php echo $row['User_id']; ?>">
                                    ลบ
                                </button>
                            </div>
                        </td>
                    </tr>

                            <!-- Modal แก้ไข -->
        <div class="modal fade" id="editModal<?php echo $row['User_id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขข้อมูลผู้ใช้งาน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="" class="edit-form">
                        <div class="modal-body">
                            <!-- เก็บค่า ID เดิม -->
                            <input type="hidden" name="Old_User_id" value="<?php echo $row['User_id']; ?>">
                            
                            <input type="text" class="form-control" name="User_id" 
                                value="<?php echo $row['User_id']; ?>" required>
                            <input type="text" class="form-control" name="User_Username" 
                                value="<?php echo $row['User_Username']; ?>" required>
                            <input type="text" class="form-control" name="User_password" 
                                value="<?php echo $row['User_password']; ?>" placeholder="รหัสผ่าน">
                            <input type="text" class="form-control" name="User_name" 
                                value="<?php echo $row['User_name']; ?>" required>
                            <input type="email" class="form-control" name="User_email" 
                                value="<?php echo $row['User_email']; ?>" required>
                            <input type="text" class="form-control" name="User_phone" 
                                value="<?php echo $row['User_phone']; ?>" required>
                            <select class="form-select" name="User_role">
                                <option value="admin" <?php echo $row['User_role'] == 'admin' ? 'selected' : ''; ?>>
                                    ผู้ดูแลระบบ
                                </option>
                                <option value="พนักงาน" <?php echo $row['User_role'] == 'พนักงาน' ? 'selected' : ''; ?>>
                                    พนักงาน
                                </option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary" name="edit_user">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

                    <!-- Modal ลบ -->
                    <div class="modal fade" id="deleteModal<?php echo $row['User_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">ยืนยันการลบผู้ใช้</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?
                                </div>
                                <div class="modal-footer">
                                    <form method="POST" action="">
                                        <input type="hidden" name="User_id" value="<?php echo $row['User_id']; ?>">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                        <button type="submit" class="btn btn-danger" name="confirm_delete_user">ลบ</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>